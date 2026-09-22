<?php

namespace Tests\Feature\Security;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creation_sanitizes_and_returns_checkout_token_without_sensitive_payment_payload(): void
    {
        Mail::fake();

        $product = Product::factory()->create([
            'price' => 12.90,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer_name' => '  <script>alert(1)</script> Ana Cliente  ',
            'customer_phone' => ' +34 612 345 678 ',
            'customer_email' => 'ANA@example.com',
            'customer_address' => ' <b>Calle Segura 123</b> ',
            'delivery_type' => DeliveryType::Delivery->value,
            'payment_method' => PaymentMethod::Stripe->value,
            'notes' => '<img src=x onerror=alert(1)> Portal verde ',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'drink_choices' => [' Cola ', '<b>Agua</b> '],
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('customer_name', 'alert1 Ana Cliente')
            ->assertJsonPath('customer_phone', '+34612345678')
            ->assertJsonPath('customer_email', 'ana@example.com')
            ->assertJsonPath('status', OrderStatus::PendingEmailConfirmation->value)
            ->assertJsonPath('customer_address', 'Calle Segura 123')
            ->assertJsonPath('notes', 'Portal verde')
            ->assertJsonPath('items.0.quantity', 2)
            ->assertJsonPath('email_confirmation_required', true)
            ->assertJsonStructure(['public_id', 'payment'])
            ->assertJsonMissingPath('checkout_token')
            ->assertJsonMissingPath('payment.payload')
            ->assertJsonMissingPath('payment.provider_payment_id')
            ->assertJsonMissingPath('payment.provider_checkout_session_id');

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'alert1 Ana Cliente',
            'customer_address' => 'Calle Segura 123',
            'notes' => 'Portal verde',
        ]);
    }

    public function test_delivery_orders_require_an_address(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Ana Cliente',
            'customer_phone' => '+34 612 345 678',
            'customer_email' => 'ana@example.com',
            'delivery_type' => DeliveryType::Delivery->value,
            'payment_method' => PaymentMethod::Cash->value,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_address']);
    }

    public function test_order_creation_is_rate_limited(): void
    {
        Mail::fake();

        $product = Product::factory()->create();

        $payload = [
            'customer_name' => 'Ana Cliente',
            'customer_phone' => '+34 612 345 678',
            'customer_email' => 'ana@example.com',
            'delivery_type' => DeliveryType::Takeaway->value,
            'payment_method' => PaymentMethod::Cash->value,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/orders', $payload)->assertCreated();
        }

        $this->postJson('/api/orders', $payload)->assertStatus(429);
    }
}
