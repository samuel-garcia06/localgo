<?php

namespace App\Livewire;

use App\Enums\DeliveryType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\RestaurantSetting;
use App\Services\OrderEmailConfirmationService;
use App\Services\OrderService;
use App\Support\InputSanitizer;
use App\Support\LocalgoStore;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Cart extends Component
{
    private const PHONE_RULE = 'regex:/^(?=(?:\D*\d){9,15}\D*$)\+?[0-9\s().-]+$/';

    public string $fulfillment = 'delivery';

    public string $customerName = '';

    public string $customerPhone = '';

    public string $customerEmail = '';

    public string $customerAddress = '';

    public string $notes = '';

    public ?string $pendingOrderPublicId = null;

    public ?string $verificationEmail = null;

    public ?string $verificationMessage = null;

    public bool $pendingOrderExpired = false;

    public bool $pendingOrderNoticeDismissed = false;

    public function mount(): void
    {
        if (session()->has('localgo_fulfillment')) {
            $this->fulfillment = session('localgo_fulfillment', 'delivery');
        }
    }

    public function setFulfillment(string $mode): void
    {
        if (! in_array($mode, ['delivery', 'pickup'], true)) {
            return;
        }

        $this->fulfillment = $mode;
        session()->put('localgo_fulfillment', $mode);
    }

    public function increment(int $productId): void
    {
        $cart = session()->get(LocalgoStore::CART_SESSION_KEY, []);

        if (! isset($cart[$productId])) {
            return;
        }

        $cart[$productId]++;
        session()->put(LocalgoStore::CART_SESSION_KEY, $cart);
    }

    public function decrement(int $productId): void
    {
        $cart = session()->get(LocalgoStore::CART_SESSION_KEY, []);

        if (! isset($cart[$productId])) {
            return;
        }

        $cart[$productId]--;

        if ($cart[$productId] <= 0) {
            unset($cart[$productId]);
        }

        session()->put(LocalgoStore::CART_SESSION_KEY, $cart);
    }

    public function remove(int $productId): void
    {
        $cart = session()->get(LocalgoStore::CART_SESSION_KEY, []);
        unset($cart[$productId]);
        session()->put(LocalgoStore::CART_SESSION_KEY, $cart);
    }

    public function clearCart(): void
    {
        session()->forget(LocalgoStore::CART_SESSION_KEY);
    }

    public function submitOrder(): void
    {
        $settings = RestaurantSetting::current();

        if ($settings->isClosed()) {
            $this->addError('cart', 'El restaurante no está aceptando pedidos en este momento. Inténtalo de nuevo más tarde.');

            return;
        }

        if ($this->fulfillment === 'delivery' && ! $settings->allow_delivery) {
            $this->addError('fulfillment', 'La entrega a domicilio no está disponible en este momento.');
            $this->fulfillment = $settings->allow_pickup ? 'pickup' : 'delivery';

            return;
        }

        if ($this->fulfillment === 'pickup' && ! $settings->allow_pickup) {
            $this->addError('fulfillment', 'La recogida en local no está disponible en este momento.');
            $this->fulfillment = $settings->allow_delivery ? 'delivery' : 'pickup';

            return;
        }

        $this->customerName = InputSanitizer::text($this->customerName, 120) ?? '';
        $this->customerPhone = InputSanitizer::phone($this->customerPhone, 30) ?? '';
        $this->customerEmail = mb_strtolower(InputSanitizer::text($this->customerEmail, 160) ?? '');
        $this->customerAddress = InputSanitizer::text($this->customerAddress, 180) ?? '';
        $this->notes = InputSanitizer::text($this->notes, 400) ?? '';

        if (LocalgoStore::cartItems()->isEmpty()) {
            $this->addError('cart', 'Añade al menos un producto antes de finalizar el pedido.');

            return;
        }

        $validated = $this->validate([
            'customerName' => ['required', 'string', 'max:120'],
            'customerPhone' => ['required', 'string', 'max:30', self::PHONE_RULE],
            'customerEmail' => ['required', 'email:rfc', 'max:160'],
            'fulfillment' => ['required', 'in:delivery,pickup'],
            'customerAddress' => ['nullable', 'string', 'max:180'],
            'notes' => ['nullable', 'string', 'max:400'],
        ], [
            'customerPhone.regex' => 'Introduce un telefono valido, por ejemplo +34 612 345 678.',
        ]);

        if ($validated['fulfillment'] === 'delivery') {
            $this->validate([
                'customerAddress' => ['required', 'string', 'max:180'],
            ]);
        }

        $cartItems = LocalgoStore::cartItems();
        $subtotal = (float) $cartItems->sum('line_total');

        $minOrder = (float) $settings->minimum_order;
        if ($validated['fulfillment'] === 'delivery' && $minOrder > 0 && $subtotal < $minOrder) {
            $this->addError('cart', 'El pedido mínimo para entrega a domicilio es '.number_format($minOrder, 2, ',', '.').' €.');

            return;
        }

        $deliveryFee = LocalgoStore::deliveryFee($validated['fulfillment'], $subtotal);

        $order = DB::transaction(function () use ($validated, $cartItems, $deliveryFee) {
            $order = app(OrderService::class)->createFromCatalogCart([
                'customer_name' => $validated['customerName'],
                'customer_phone' => $validated['customerPhone'],
                'customer_email' => $validated['customerEmail'],
                'customer_address' => $validated['fulfillment'] === 'delivery' ? $validated['customerAddress'] : null,
                'delivery_type' => $validated['fulfillment'] === 'delivery' ? DeliveryType::Delivery : DeliveryType::Takeaway,
                'payment_method' => PaymentMethod::Cash,
                'payment_status' => PaymentStatus::Pending,
                'notes' => $validated['notes'] ?: null,
            ], $cartItems->all(), $deliveryFee);

            app(OrderEmailConfirmationService::class)->send($order, request()->ip());

            return $order->fresh();
        });

        $this->pendingOrderPublicId = $order->public_id;
        $this->verificationEmail = $order->fresh()->customer_email;
        $this->verificationMessage = 'Te hemos enviado un email para confirmar tu pedido. Revisa tambien la carpeta de spam.';
        $this->pendingOrderExpired = false;

        session()->forget(LocalgoStore::CART_SESSION_KEY);

        $this->reset('customerName', 'customerPhone', 'customerEmail', 'customerAddress', 'notes');
        $this->fulfillment = 'delivery';
        session()->put('localgo_fulfillment', 'delivery');
        $this->resetValidation();
    }

    public function resendConfirmationEmail(): void
    {
        $order = $this->pendingOrder();

        if ($this->pendingOrderExpired || $order->email_confirmation_expires_at?->isPast()) {
            $this->pendingOrderExpired = true;
            $this->verificationMessage = 'El enlace de confirmacion ha caducado. Haz un pedido nuevo para recibir un nuevo email.';

            return;
        }

        app(OrderEmailConfirmationService::class)->resend($order, request()->ip());

        $this->verificationMessage = 'Hemos reenviado el email de confirmacion. Usa el ultimo enlace recibido.';
        $this->pendingOrderExpired = false;
        $this->pendingOrderNoticeDismissed = false;
    }

    public function dismissPendingOrderNotice(): void
    {
        $this->resetPendingOrderNotice();
    }

    private function pendingOrder(): Order
    {
        $order = Order::query()
            ->where('public_id', $this->pendingOrderPublicId)
            ->first();

        if (! $order) {
            abort(404);
        }

        return $order;
    }

    private function resetPendingOrderNotice(): void
    {
        $this->pendingOrderPublicId = null;
        $this->verificationEmail = null;
        $this->verificationMessage = null;
        $this->pendingOrderExpired = false;
        $this->pendingOrderNoticeDismissed = false;
    }

    public function render(): View
    {
        $restaurant = LocalgoStore::restaurant();
        $cartItems = LocalgoStore::cartItems();
        $subtotal = LocalgoStore::subtotal();
        $deliveryFee = LocalgoStore::deliveryFee($this->fulfillment, $subtotal);
        $total = $subtotal + $deliveryFee;
        $restaurantSettings = RestaurantSetting::current();

        return view('livewire.cart', [
            'restaurant' => $restaurant,
            'cartItems' => $cartItems,
            'cartQuantity' => LocalgoStore::cartQuantity(),
            'subtotal' => $subtotal,
            'deliveryFee' => $deliveryFee,
            'total' => $total,
            'cartUrl' => route('cart'),
            'homeUrl' => route('home'),
            'currentRoute' => 'cart',
            'headerTitle' => 'Tu carrito',
            'headerSubtitle' => 'Revisa tu pedido y termina el checkout.',
            'restaurantSettings' => $restaurantSettings,
        ])->layout('layouts.app', [
            'title' => 'Urban Bites | Carrito',
        ]);
    }
}
