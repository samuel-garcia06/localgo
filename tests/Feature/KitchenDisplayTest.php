<?php

namespace Tests\Feature;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Livewire\KitchenDisplay;
use App\Mail\OrderAcceptedMail;
use App\Mail\OrderDelayedMail;
use App\Mail\OrderRejectedMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class KitchenDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_kitchen_route_is_available_to_admin_users(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/kitchen')
            ->assertOk()
            ->assertSee('Cocina')
            ->assertSee('Volver al panel');
    }

    public function test_kitchen_opens_order_details(): void
    {
        $order = $this->createOrder([
            'customer_name' => 'Ana Cliente',
            'customer_phone' => '+34 612 345 678',
            'customer_email' => 'ana@example.com',
            'customer_address' => 'Calle Prueba 7',
            'notes' => 'Sin cebolla',
        ]);

        Livewire::test(KitchenDisplay::class)
            ->call('selectOrder', $order->id)
            ->assertSee('Pedido #'.$order->id)
            ->assertSee('Ana Cliente')
            ->assertSee('Calle Prueba 7')
            ->assertSee('Sin cebolla')
            ->assertSee('Burger Test')
            ->assertSee('Bebidas: Cola')
            ->assertSee('Salsas: BBQ');
    }

    public function test_kitchen_accepts_order_with_custom_preparation_time(): void
    {
        Mail::fake();

        $order = $this->createOrder([
            'status' => OrderStatus::Confirmed,
            'customer_email' => 'ana@example.com',
        ]);

        Livewire::test(KitchenDisplay::class)
            ->call('setAcceptTime', $order->id, 30)
            ->call('accept', $order->id);

        $order->refresh();

        $this->assertSame(OrderStatus::Accepted, $order->status);
        $this->assertSame(30, $order->preparation_time);

        Mail::assertSent(OrderAcceptedMail::class);
    }

    public function test_kitchen_rejects_pending_orders(): void
    {
        Mail::fake();

        $order = $this->createOrder([
            'status' => OrderStatus::Pending,
            'customer_email' => 'ana@example.com',
        ]);

        Livewire::test(KitchenDisplay::class)
            ->call('reject', $order->id);

        $this->assertSame(OrderStatus::Rejected, $order->fresh()->status);

        Mail::assertSent(OrderRejectedMail::class);
    }

    public function test_kitchen_adds_more_time_to_accepted_orders(): void
    {
        Mail::fake();

        $order = $this->createOrder([
            'status' => OrderStatus::Accepted,
            'customer_email' => 'ana@example.com',
            'preparation_time' => 20,
        ]);

        Livewire::test(KitchenDisplay::class)
            ->call('addTime', $order->id, 10);

        $this->assertSame(30, $order->fresh()->preparation_time);

        Mail::assertSent(OrderDelayedMail::class);
    }

    private function createOrder(array $overrides = []): Order
    {
        $order = Order::factory()->create([
            'delivery_type' => DeliveryType::Delivery,
            'status' => OrderStatus::Confirmed,
            'total' => 12.90,
            ...$overrides,
        ]);

        $order->items()->create([
            'product_name' => 'Burger Test',
            'drink_choice' => 'Cola',
            'sauce_choice' => 'BBQ',
            'unit_price' => 12.90,
            'quantity' => 1,
            'subtotal' => 12.90,
        ]);

        return $order;
    }
}
