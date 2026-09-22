<?php

namespace Tests\Feature;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Livewire\Cart;
use App\Mail\OrderEmailConfirmationMail;
use App\Models\Order;
use App\Models\Product;
use App\Support\LocalgoStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class OrderEmailConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_stays_pending_email_confirmation_and_sends_email(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/orders', $this->orderPayload());

        $response->assertCreated()
            ->assertJsonPath('status', OrderStatus::PendingEmailConfirmation->value)
            ->assertJsonPath('customer_email', 'ana@example.com')
            ->assertJsonPath('email_confirmation_required', true)
            ->assertJsonMissingPath('checkout_token');

        $order = Order::query()->firstOrFail();

        $this->assertNotNull($order->email_confirmation_token_hash);
        $this->assertNotNull($order->email_confirmation_expires_at);
        $this->assertNotNull($order->confirmation_sent_at);
        $this->assertNull($order->email_confirmed_at);
        $this->assertNull($order->verified_at);

        Mail::assertSent(OrderEmailConfirmationMail::class, function (OrderEmailConfirmationMail $mail) use ($order) {
            return $mail->hasTo('ana@example.com')
                && $mail->order->is($order)
                && str_contains($mail->confirmationUrl, "/orders/{$order->public_id}/confirm-email?token=");
        });
    }

    public function test_correct_token_confirms_order_and_invalidates_token(): void
    {
        Mail::fake();
        $this->postJson('/api/orders', $this->orderPayload())->assertCreated();

        $order = Order::query()->firstOrFail();
        $url = $this->confirmationUrl();

        $this->get($url)
            ->assertRedirect(route('home'));

        $order->refresh();

        $this->assertSame(OrderStatus::Confirmed, $order->status);
        $this->assertNotNull($order->email_confirmed_at);
        $this->assertNotNull($order->verified_at);
        $this->assertNull($order->email_confirmation_token_hash);
        $this->assertNull($order->email_confirmation_expires_at);
    }

    public function test_incorrect_token_does_not_confirm_order(): void
    {
        Mail::fake();
        $this->postJson('/api/orders', $this->orderPayload())->assertCreated();

        $order = Order::query()->firstOrFail();

        $this->get(route('orders.confirm-email', [
            'order' => $order->public_id,
            'token' => 'wrong-token',
        ]))
            ->assertStatus(422)
            ->assertSee('no es valido');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::PendingEmailConfirmation->value,
        ]);
    }

    public function test_expired_token_does_not_confirm_order(): void
    {
        Mail::fake();
        $this->postJson('/api/orders', $this->orderPayload())->assertCreated();

        $order = Order::query()->firstOrFail();
        $url = $this->confirmationUrl();
        $order->update([
            'email_confirmation_expires_at' => now()->subMinute(),
        ]);

        $this->get($url)
            ->assertStatus(422)
            ->assertSee('ha caducado');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::PendingEmailConfirmation->value,
        ]);
    }

    public function test_token_cannot_be_reused(): void
    {
        Mail::fake();
        $this->postJson('/api/orders', $this->orderPayload())->assertCreated();

        $url = $this->confirmationUrl();

        // First use confirms the order.
        $this->get($url)->assertRedirect(route('home'));

        // Second use: order already confirmed → controller redirects to home gracefully.
        $this->get($url)->assertRedirect(route('home'));

        $order = Order::query()->firstOrFail();
        $this->assertSame(OrderStatus::Confirmed, $order->status);
        $this->assertNull($order->email_confirmation_token_hash);
    }

    public function test_pending_order_is_hidden_from_admin_and_confirmed_order_is_visible(): void
    {
        $pending = Order::factory()->create([
            'status' => OrderStatus::PendingEmailConfirmation,
        ]);

        $confirmed = Order::factory()->create([
            'status' => OrderStatus::Confirmed,
        ]);

        $this->assertFalse(Order::query()->whereIn('status', OrderStatus::visibleToRestaurantValues())->whereKey($pending->id)->exists());
        $this->assertTrue(Order::query()->whereIn('status', OrderStatus::visibleToRestaurantValues())->whereKey($confirmed->id)->exists());
    }

    public function test_resend_limit_blocks_fourth_resend(): void
    {
        Mail::fake();
        $this->postJson('/api/orders', $this->orderPayload())->assertCreated();

        $order = Order::query()->firstOrFail();

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->postJson("/api/orders/{$order->public_id}/resend-email")->assertOk();
            $order->refresh();
        }

        $this->postJson("/api/orders/{$order->public_id}/resend-email")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['customer_email']);

        $this->assertSame(3, $order->fresh()->confirmation_resend_count);
    }

    public function test_livewire_checkout_clears_active_cart_but_keeps_pending_email_notice(): void
    {
        Mail::fake();

        $firstProduct = Product::factory()->create([
            'price' => 12.50,
        ]);

        $secondProduct = Product::factory()->create([
            'price' => 8.50,
        ]);

        session()->put(LocalgoStore::CART_SESSION_KEY, [$firstProduct->id => 2]);

        Livewire::test(Cart::class)
            ->set('customerName', 'Ana Cliente')
            ->set('customerPhone', '612 345 678')
            ->set('customerEmail', 'ana@example.com')
            ->set('customerAddress', 'Calle Segura 123')
            ->call('submitOrder')
            ->assertSet('verificationEmail', 'ana@example.com')
            ->assertSee('Te hemos enviado un email para confirmar tu pedido')
            ->assertSee('Tu carrito está vacío')
            ->call('resendConfirmationEmail')
            ->assertSee('Hemos reenviado el email de confirmacion');

        $order = Order::query()->firstOrFail();

        $this->assertSame(OrderStatus::PendingEmailConfirmation, $order->status);
        $this->assertFalse(session()->has(LocalgoStore::CART_SESSION_KEY));
        $this->assertFalse(session()->has('localgo_pending_order_public_id'));

        Mail::assertSent(OrderEmailConfirmationMail::class, 2);

        session()->put(LocalgoStore::CART_SESSION_KEY, [$secondProduct->id => 1]);

        Livewire::test(Cart::class)
            ->assertDontSee('Te hemos enviado un email para confirmar tu pedido')
            ->assertDontSee('Pedido pendiente')
            ->assertSee('Nombre')
            ->assertSee('Teléfono')
            ->assertSee('Email')
            ->assertSet('customerName', '')
            ->assertSet('customerPhone', '')
            ->assertSet('customerEmail', '');
    }

    private function orderPayload(): array
    {
        $product = Product::factory()->create([
            'price' => 12.90,
        ]);

        return [
            'customer_name' => 'Ana Cliente',
            'customer_phone' => '612 345 678',
            'customer_email' => 'Ana@Example.com',
            'customer_address' => 'Calle Segura 123',
            'delivery_type' => DeliveryType::Delivery->value,
            'payment_method' => PaymentMethod::Cash->value,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];
    }

    private function confirmationUrl(): string
    {
        $mail = Mail::sent(OrderEmailConfirmationMail::class)->last();

        return $mail->confirmationUrl;
    }
}
