<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Events\OrderUpdated;
use App\Mail\OrderAcceptedMail;
use App\Mail\OrderConfirmedAdminMail;
use App\Mail\OrderReceivedMail;
use App\Mail\OrderRejectedMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderObserver
{
    public function created(Order $order): void
    {
        $this->broadcastSafely($order);
    }

    public function updated(Order $order): void
    {
        $this->broadcastSafely($order);
        $this->sendStatusEmail($order);
    }

    private function broadcastSafely(Order $order): void
    {
        if (in_array($order->status, [OrderStatus::PendingVerification, OrderStatus::PendingEmailConfirmation], true)) {
            return;
        }

        try {
            OrderUpdated::dispatch($order->public_id);
        } catch (Throwable $exception) {
            Log::warning('No se pudo emitir el evento del pedido en tiempo real.', [
                'order_id' => $order->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function sendStatusEmail(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        if (blank($order->customer_email)) {
            return;
        }

        $mailable = match ($order->status) {
            OrderStatus::Confirmed => new OrderReceivedMail($order),
            OrderStatus::Accepted => new OrderAcceptedMail($order),
            OrderStatus::Rejected => new OrderRejectedMail($order),
            default => null,
        };

        if ($mailable === null) {
            return;
        }

        try {
            Mail::to($order->customer_email)->send($mailable);
        } catch (Throwable $exception) {
            Log::warning('No se pudo enviar el email de estado del pedido.', [
                'order_id' => $order->id,
                'status' => $order->status->value,
                'message' => $exception->getMessage(),
            ]);
        }

        if ($order->status === OrderStatus::Confirmed) {
            $adminEmail = config('mail.admin_email');
            if (filled($adminEmail)) {
                try {
                    Mail::to($adminEmail)->send(new OrderConfirmedAdminMail($order->fresh(['items'])));
                } catch (Throwable $exception) {
                    Log::warning('No se pudo enviar el email de pedido confirmado al admin.', [
                        'order_id' => $order->id,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }
        }
    }
}
