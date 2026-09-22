<?php

namespace Tests\Feature\Security;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_orders_api_requires_authentication(): void
    {
        $this->getJson('/api/admin/orders')->assertUnauthorized();
    }

    public function test_non_admin_users_cannot_access_admin_orders_api(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($user)->getJson('/api/admin/orders')->assertForbidden();
    }

    public function test_admin_orders_api_hides_sensitive_payment_payload(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $order = Order::factory()->create();

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => null,
            'product_name' => 'Coca-Cola',
            'unit_price' => 2.50,
            'quantity' => 1,
            'subtotal' => 2.50,
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'provider' => 'stripe',
            'provider_payment_id' => 'pi_secret_value',
            'provider_checkout_session_id' => 'cs_secret_value',
            'payload' => ['secret' => 'do-not-leak'],
        ]);

        $this->actingAs($admin)
            ->getJson('/api/admin/orders')
            ->assertOk()
            ->assertJsonMissingPath('0.payment.payload')
            ->assertJsonMissingPath('0.payment.provider_payment_id')
            ->assertJsonMissingPath('0.payment.provider_checkout_session_id');
    }

    public function test_admin_orders_api_hides_pending_email_confirmation_orders(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        Order::factory()->create([
            'status' => OrderStatus::PendingEmailConfirmation,
        ]);

        $confirmed = Order::factory()->create([
            'status' => OrderStatus::Confirmed,
        ]);

        $this->actingAs($admin)
            ->getJson('/api/admin/orders')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $confirmed->id);
    }
}
