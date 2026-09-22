<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class StripeCheckoutService
{
    public function createSession(Order $order, string $successUrl, string $cancelUrl): Session
    {
        $stripe = new StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'customer_creation' => 'always',
            'metadata' => [
                'order_public_id' => (string) $order->public_id,
            ],
            'line_items' => $order->items->map(fn ($item) => [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) round($item->unit_price * 100),
                    'product_data' => [
                        'name' => $item->product_name,
                    ],
                ],
                'quantity' => $item->quantity,
            ])->all(),
        ]);

        $order->payment()->update([
            'provider_checkout_session_id' => $session->id,
            'status' => PaymentStatus::Pending,
            'payload' => [
                'checkout_session_id' => $session->id,
                'mode' => $session->mode,
                'status' => $session->status,
                'payment_status' => $session->payment_status,
            ],
        ]);

        return $session;
    }
}
