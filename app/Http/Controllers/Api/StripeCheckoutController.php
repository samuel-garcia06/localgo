<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStripeCheckoutSessionRequest;
use App\Models\Order;
use App\Services\StripeCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StripeCheckoutController extends Controller
{
    public function __construct(private readonly StripeCheckoutService $stripeCheckoutService) {}

    public function store(CreateStripeCheckoutSessionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $order = Order::query()
            ->with('items')
            ->where('public_id', $validated['order_public_id'])
            ->first();

        if (
            ! $order
            || ! $order->hasValidCheckoutToken($validated['checkout_token'])
            || in_array($order->status, [OrderStatus::PendingVerification, OrderStatus::PendingEmailConfirmation], true)
            || $order->payment_method !== PaymentMethod::Stripe
            || $order->payment_status === PaymentStatus::Paid
        ) {
            throw new NotFoundHttpException;
        }

        $ttl = now()->addMinutes((int) config('services.stripe.checkout_url_ttl_minutes', 120));

        $session = $this->stripeCheckoutService->createSession(
            $order,
            URL::temporarySignedRoute('checkout.success', $ttl, ['order' => $order->public_id]),
            URL::temporarySignedRoute('checkout.cancel', $ttl, ['order' => $order->public_id]),
        );

        return response()->json([
            'checkout_url' => $session->url,
            'session_id' => $session->id,
        ]);
    }
}
