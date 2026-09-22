<?php

namespace Tests\Feature\Security;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\StripeCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Stripe\Checkout\Session;
use Tests\TestCase;

class CheckoutSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_stripe_checkout_session_requires_valid_public_id_and_checkout_token(): void
    {
        $order = Order::factory()->create([
            'payment_method' => PaymentMethod::Stripe,
            'payment_status' => PaymentStatus::Pending,
            'status' => OrderStatus::Pending,
            'delivery_type' => DeliveryType::Delivery,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => null,
            'product_name' => 'Menu Burger',
            'unit_price' => 12.90,
            'quantity' => 1,
            'subtotal' => 12.90,
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'provider' => 'stripe',
            'amount' => 12.90,
            'status' => PaymentStatus::Pending,
        ]);

        $checkoutToken = $order->issueCheckoutToken();

        $fakeSession = new Session([
            'id' => 'cs_test_123',
            'url' => 'https://checkout.stripe.test/session/cs_test_123',
            'mode' => 'payment',
            'status' => 'open',
            'payment_status' => 'unpaid',
        ]);

        $service = Mockery::mock(StripeCheckoutService::class);
        $service->shouldReceive('createSession')
            ->once()
            ->andReturn($fakeSession);
        $this->app->instance(StripeCheckoutService::class, $service);

        $this->postJson('/api/stripe/create-checkout-session', [
            'order_public_id' => $order->public_id,
            'checkout_token' => str_repeat('x', 64),
        ])->assertNotFound();

        $this->postJson('/api/stripe/create-checkout-session', [
            'order_public_id' => $order->public_id,
            'checkout_token' => $checkoutToken,
        ])->assertOk()
            ->assertJsonPath('session_id', 'cs_test_123');
    }

    public function test_checkout_success_route_requires_a_valid_signature(): void
    {
        $order = Order::factory()->create();

        $this->get("/checkout/success/{$order->public_id}")
            ->assertForbidden();
    }
}
