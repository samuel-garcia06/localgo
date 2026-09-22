<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Mail\OrderAcceptedMail;
use App\Mail\OrderDelayedMail;
use App\Mail\OrderEmailConfirmationMail;
use App\Mail\OrderRejectedMail;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderStatusEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepted_email_sent_when_order_transitions_to_accepted(): void
    {
        Mail::fake();

        $order = Order::factory()->create([
            'status' => OrderStatus::Confirmed,
            'customer_email' => 'cliente@example.com',
        ]);

        $order->update(['status' => OrderStatus::Accepted]);

        Mail::assertSent(OrderAcceptedMail::class, function (OrderAcceptedMail $mail) use ($order) {
            return $mail->hasTo('cliente@example.com') && $mail->order->is($order);
        });
        Mail::assertNotSent(OrderRejectedMail::class);
    }

    public function test_rejected_email_sent_when_order_transitions_to_rejected(): void
    {
        Mail::fake();

        $order = Order::factory()->create([
            'status' => OrderStatus::Confirmed,
            'customer_email' => 'cliente@example.com',
        ]);

        $order->update(['status' => OrderStatus::Rejected]);

        Mail::assertSent(OrderRejectedMail::class, function (OrderRejectedMail $mail) use ($order) {
            return $mail->hasTo('cliente@example.com') && $mail->order->is($order);
        });
        Mail::assertNotSent(OrderAcceptedMail::class);
    }

    public function test_no_email_sent_when_status_does_not_change(): void
    {
        Mail::fake();

        $order = Order::factory()->create([
            'status' => OrderStatus::Accepted,
            'customer_email' => 'cliente@example.com',
        ]);

        // Same status — model is not dirty, observer does not fire, no email sent.
        $order->update(['status' => OrderStatus::Accepted]);

        Mail::assertNotSent(OrderAcceptedMail::class);
        Mail::assertNotSent(OrderRejectedMail::class);
    }

    public function test_no_email_sent_when_order_has_no_customer_email(): void
    {
        Mail::fake();

        $order = Order::factory()->create([
            'status' => OrderStatus::Confirmed,
            'customer_email' => null,
        ]);

        $order->update(['status' => OrderStatus::Accepted]);

        Mail::assertNotSent(OrderAcceptedMail::class);
    }

    public function test_no_status_email_sent_for_non_terminal_status_transitions(): void
    {
        Mail::fake();

        $order = Order::factory()->create([
            'status' => OrderStatus::Confirmed,
            'customer_email' => 'cliente@example.com',
        ]);

        // Delivered does not trigger any of the two mailables.
        $order->update(['status' => OrderStatus::Delivered]);

        Mail::assertNotSent(OrderAcceptedMail::class);
        Mail::assertNotSent(OrderRejectedMail::class);
    }

    public function test_invalid_status_transitions_are_rejected(): void
    {
        Mail::fake();

        $rejected = Order::factory()->create([
            'status' => OrderStatus::Rejected,
            'customer_email' => 'cliente@example.com',
        ]);

        $this->expectException(ValidationException::class);

        app(OrderService::class)->updateOrderStatus($rejected, OrderStatus::Accepted);

        Mail::assertNothingSent();
    }

    public function test_rejected_order_cannot_be_marked_delivered(): void
    {
        Mail::fake();

        $rejected = Order::factory()->create([
            'status' => OrderStatus::Rejected,
            'customer_email' => 'cliente@example.com',
        ]);

        try {
            app(OrderService::class)->updateOrderStatus($rejected, OrderStatus::Delivered);
            $this->fail('Expected validation exception.');
        } catch (ValidationException) {
            $this->assertSame(OrderStatus::Rejected, $rejected->fresh()->status);
            Mail::assertNothingSent();
        }
    }

    public function test_same_status_update_is_a_noop_and_sends_no_email(): void
    {
        Mail::fake();

        $accepted = Order::factory()->create([
            'status' => OrderStatus::Accepted,
            'customer_email' => 'cliente@example.com',
        ]);

        app(OrderService::class)->updateOrderStatus($accepted, OrderStatus::Accepted);

        $this->assertSame(OrderStatus::Accepted, $accepted->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_extending_preparation_time_updates_order_and_notifies_customer(): void
    {
        Mail::fake();

        $order = Order::factory()->create([
            'status' => OrderStatus::Accepted,
            'customer_email' => 'cliente@example.com',
            'preparation_time' => 20,
        ]);

        $updated = app(OrderService::class)->extendPreparationTime($order, 10);

        $this->assertSame(30, $updated->preparation_time);

        Mail::assertSent(OrderDelayedMail::class, function (OrderDelayedMail $mail) use ($updated) {
            return $mail->hasTo('cliente@example.com')
                && $mail->order->is($updated)
                && $mail->additionalMinutes === 10
                && $mail->previousPreparationTime === 20;
        });
    }

    public function test_extending_preparation_time_is_only_allowed_for_accepted_orders(): void
    {
        Mail::fake();

        $order = Order::factory()->create([
            'status' => OrderStatus::Pending,
            'customer_email' => 'cliente@example.com',
            'preparation_time' => null,
        ]);

        $this->expectException(ValidationException::class);

        app(OrderService::class)->extendPreparationTime($order, 10);

        Mail::assertNothingSent();
    }

    public function test_confirmation_email_subject_includes_customer_name(): void
    {
        $order = Order::factory()->make(['customer_name' => 'María García']);

        $mail = new OrderEmailConfirmationMail($order, 'https://example.com/confirm');

        $this->assertStringContainsString('María García', $mail->envelope()->subject);
    }

    public function test_confirmation_email_subject_without_name_is_generic(): void
    {
        $order = Order::factory()->make(['customer_name' => null]);

        $mail = new OrderEmailConfirmationMail($order, 'https://example.com/confirm');

        $this->assertSame('Confirma tu pedido', $mail->envelope()->subject);
    }
}
