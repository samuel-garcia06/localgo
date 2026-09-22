<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->server('HTTP_STRIPE_SIGNATURE');
        $secret = config('services.stripe.webhook_secret');
        $allowUnsigned = (bool) config('services.stripe.allow_unsigned_webhooks', false);

        if (blank($secret) && ! $allowUnsigned) {
            Log::warning('Stripe webhook rejected because no webhook secret is configured.');

            return response('Webhook unavailable.', 503);
        }

        try {
            $event = $secret
                ? Webhook::constructEvent($payload, $signature, $secret)
                : json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
        } catch (UnexpectedValueException|SignatureVerificationException $exception) {
            Log::warning('Stripe webhook rejected', ['message' => $exception->getMessage()]);

            return response('Invalid webhook.', 400);
        }

        $eventType = is_array($event) ? $event['type'] : $event->type;
        $eventData = is_array($event) ? $event['data']['object'] : $event->data->object;

        if ($eventType === 'checkout.session.completed') {
            $order = Order::query()
                ->with('payment')
                ->where('public_id', $eventData['metadata']['order_public_id'] ?? $eventData->metadata->order_public_id ?? null)
                ->first();

            if ($order) {
                $order->update([
                    'payment_status' => PaymentStatus::Paid,
                    'status' => OrderStatus::Pending,
                    'checkout_token' => null,
                ]);

                $order->payment()->update([
                    'provider_payment_id' => $eventData['payment_intent'] ?? $eventData->payment_intent,
                    'provider_checkout_session_id' => $eventData['id'] ?? $eventData->id,
                    'status' => PaymentStatus::Paid,
                    'paid_at' => now(),
                    'payload' => [
                        'event' => 'checkout.session.completed',
                        'mode' => is_array($eventData) ? ($eventData['mode'] ?? null) : $eventData->mode,
                        'payment_status' => is_array($eventData) ? ($eventData['payment_status'] ?? null) : $eventData->payment_status,
                        'amount_total' => is_array($eventData) ? ($eventData['amount_total'] ?? null) : $eventData->amount_total,
                    ],
                ]);

                Log::info('Stripe checkout completed.', [
                    'order_public_id' => $order->public_id,
                    'payment_status' => PaymentStatus::Paid->value,
                ]);
            }
        }

        return response('Webhook handled.');
    }
}
