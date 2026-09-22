<?php

namespace App\Services;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\OrderDelayedMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderService
{
    public function createFromCart(array $payload): Order
    {
        $items = collect(Arr::get($payload, 'items', []))
            ->map(function (array $item) {
                $product = Product::query()
                    ->whereKey($item['product_id'])
                    ->where('is_available', true)
                    ->first();

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => "El producto con ID {$item['product_id']} no está disponible.",
                    ]);
                }

                $quantity = max((int) $item['quantity'], 1);
                $unitPrice = (float) $product->price;

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'drink_choice' => $this->stringifyChoices($item['drink_choices'] ?? $item['drink_choice'] ?? null),
                    'sauce_choice' => $this->stringifyChoices($item['sauce_choices'] ?? $item['sauce_choice'] ?? null),
                    'unit_price' => $unitPrice,
                    'subtotal' => round($unitPrice * $quantity, 2),
                ];
            });

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'El carrito no puede estar vacío.',
            ]);
        }

        return DB::transaction(function () use ($payload, $items) {
            $paymentMethod = $payload['payment_method'] instanceof \BackedEnum
                ? $payload['payment_method']->value
                : $payload['payment_method'];

            $order = Order::query()->create([
                'customer_name' => $payload['customer_name'],
                'customer_phone' => PhoneNumber::normalizeSpanish($payload['customer_phone']),
                'customer_email' => $payload['customer_email'] ?? null,
                'customer_address' => $payload['customer_address'] ?? null,
                'delivery_type' => $payload['delivery_type'],
                'payment_method' => $payload['payment_method'],
                'payment_status' => $payload['payment_status'] ?? PaymentStatus::Pending,
                'status' => $payload['status'] ?? OrderStatus::PendingEmailConfirmation,
                'notes' => $payload['notes'] ?? null,
                'total' => $items->sum('subtotal'),
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'drink_choice' => $item['drink_choice'],
                    'sauce_choice' => $item['sauce_choice'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            $order->payment()->create([
                'provider' => $paymentMethod === 'stripe' ? 'stripe' : 'cash',
                'amount' => $order->total,
                'currency' => 'eur',
                'status' => $order->payment_status,
                'payload' => [],
            ]);

            Log::info('Order created from API cart.', [
                'order_public_id' => $order->public_id,
                'payment_method' => $paymentMethod,
                'total' => $order->total,
            ]);

            return $order->fresh(['items', 'payment']);
        });
    }

    public function createFromCatalogCart(array $payload, array $cartItems, float $deliveryFee = 0): Order
    {
        $items = collect($cartItems)
            ->map(function (array $item) {
                $quantity = max((int) ($item['quantity'] ?? 1), 1);
                $unitPrice = round((float) ($item['price'] ?? 0), 2);

                return [
                    'product_id' => $item['id'] ?? null,
                    'product_name' => (string) ($item['name'] ?? 'Producto'),
                    'drink_choice' => $this->stringifyChoices($item['drink_choices'] ?? $item['drink_choice'] ?? null),
                    'sauce_choice' => $this->stringifyChoices($item['sauce_choices'] ?? $item['sauce_choice'] ?? null),
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => round($unitPrice * $quantity, 2),
                ];
            })
            ->filter(fn (array $item) => filled($item['product_name']) && $item['subtotal'] > 0)
            ->values();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'El carrito no puede estar vacío.',
            ]);
        }

        return DB::transaction(function () use ($payload, $items, $deliveryFee) {
            $paymentMethod = $payload['payment_method'] instanceof \BackedEnum
                ? $payload['payment_method']->value
                : $payload['payment_method'];

            $itemsTotal = (float) $items->sum('subtotal');
            $orderTotal = round($itemsTotal + max($deliveryFee, 0), 2);

            $order = Order::query()->create([
                'customer_name' => $payload['customer_name'],
                'customer_phone' => PhoneNumber::normalizeSpanish($payload['customer_phone']),
                'customer_email' => $payload['customer_email'] ?? null,
                'customer_address' => $payload['customer_address'] ?? null,
                'delivery_type' => $payload['delivery_type'],
                'payment_method' => $payload['payment_method'],
                'payment_status' => $payload['payment_status'] ?? PaymentStatus::Pending,
                'status' => $payload['status'] ?? OrderStatus::PendingEmailConfirmation,
                'notes' => $payload['notes'] ?? null,
                'total' => $orderTotal,
            ]);

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            $order->payment()->create([
                'provider' => $paymentMethod === 'stripe' ? 'stripe' : 'cash',
                'amount' => $order->total,
                'currency' => 'eur',
                'status' => $order->payment_status,
                'payload' => [
                    'source' => 'catalog_checkout',
                    'delivery_fee' => round(max($deliveryFee, 0), 2),
                ],
            ]);

            Log::info('Order created from storefront cart.', [
                'order_public_id' => $order->public_id,
                'payment_method' => $paymentMethod,
                'total' => $order->total,
            ]);

            return $order->fresh(['items', 'payment']);
        });
    }

    public function updateOrderStatus(Order $order, OrderStatus $status, ?int $preparationTime = null): Order
    {
        if ($order->status === $status) {
            return $order->fresh(['items', 'payment']);
        }

        if (! $this->canTransitionTo($order->status, $status)) {
            throw ValidationException::withMessages([
                'status' => "No se puede cambiar el pedido de {$order->status->getLabel()} a {$status->getLabel()}.",
            ]);
        }

        $data = ['status' => $status];

        if ($preparationTime !== null && $status === OrderStatus::Accepted) {
            $data['preparation_time'] = max(5, (int) $preparationTime);
        }

        if ($status === OrderStatus::Delivered) {
            $data['delivered_at'] = now();
        }

        $previousStatus = $order->status?->value;
        $order->update($data);

        Log::info('Order status changed.', [
            'order_public_id' => $order->public_id,
            'from' => $previousStatus,
            'to' => $status->value,
            'preparation_time' => $data['preparation_time'] ?? null,
            'actor_user_id' => auth()->id(),
        ]);

        return $order->fresh(['items', 'payment']);
    }

    public function assignRepartidor(Order $order, User $repartidor): Order
    {
        if ($order->delivery_type !== DeliveryType::Delivery) {
            throw ValidationException::withMessages([
                'repartidor_id' => 'Este pedido no es a domicilio.',
            ]);
        }

        if ($order->repartidor_id !== null && $order->repartidor_id !== $repartidor->id) {
            throw ValidationException::withMessages([
                'repartidor_id' => 'Este pedido ya ha sido asignado a otro repartidor.',
            ]);
        }

        $order->update(['repartidor_id' => $repartidor->id]);

        Log::info('Order assigned to repartidor.', [
            'order_public_id' => $order->public_id,
            'repartidor_id' => $repartidor->id,
        ]);

        return $order->fresh(['items', 'payment']);
    }

    public function extendPreparationTime(Order $order, int $additionalMinutes): Order
    {
        if ($order->status !== OrderStatus::Accepted) {
            throw ValidationException::withMessages([
                'status' => 'Solo se puede añadir tiempo a pedidos en preparación.',
            ]);
        }

        if (! in_array($additionalMinutes, [5, 10, 15, 20], true)) {
            throw ValidationException::withMessages([
                'preparation_time' => 'El tiempo adicional debe ser de 5, 10, 15 o 20 minutos.',
            ]);
        }

        $previousTime = (int) ($order->preparation_time ?? RestaurantSetting::current()->default_preparation_time);
        $newTime = min(180, $previousTime + $additionalMinutes);

        $order->update([
            'preparation_time' => $newTime,
        ]);

        Log::info('Order preparation time extended.', [
            'order_public_id' => $order->public_id,
            'previous_time' => $previousTime,
            'additional_minutes' => $additionalMinutes,
            'new_time' => $newTime,
            'actor_user_id' => auth()->id(),
        ]);

        $updated = $order->fresh(['items', 'payment']);

        if (filled($updated->customer_email)) {
            try {
                Mail::to($updated->customer_email)->send(new OrderDelayedMail($updated, $additionalMinutes, $previousTime));
            } catch (Throwable $exception) {
                Log::warning('No se pudo enviar el email de retraso del pedido.', [
                    'order_id' => $updated->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $updated;
    }

    private function canTransitionTo(?OrderStatus $from, OrderStatus $to): bool
    {
        return match ($to) {
            OrderStatus::Accepted => in_array($from, [OrderStatus::Confirmed, OrderStatus::Pending], true),
            OrderStatus::Rejected => in_array($from, [OrderStatus::Confirmed, OrderStatus::Pending], true),
            OrderStatus::Ready => $from === OrderStatus::Accepted,
            OrderStatus::Delivered => in_array($from, [OrderStatus::Accepted, OrderStatus::Ready], true),
            default => false,
        };
    }

    private function stringifyChoices(array|string|null $choices): ?string
    {
        if (is_array($choices)) {
            $choices = implode(', ', array_values(array_filter($choices, fn ($choice) => filled($choice))));
        }

        return filled($choices) ? (string) $choices : null;
    }
}
