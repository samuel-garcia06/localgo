<?php

namespace App\Http\Controllers\Api;

use App\Enums\DeliveryType;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderEmailConfirmationService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly OrderEmailConfirmationService $emailConfirmationService,
    ) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($validated['delivery_type'] === DeliveryType::Delivery->value && blank($validated['customer_address'] ?? null)) {
            return response()->json([
                'message' => 'La direccion es obligatoria para pedidos a domicilio.',
                'errors' => [
                    'customer_address' => ['La direccion es obligatoria para pedidos a domicilio.'],
                ],
            ], 422);
        }

        $validated['payment_status'] = PaymentStatus::Pending;

        $order = DB::transaction(function () use ($validated, $request) {
            $order = $this->orderService->createFromCart($validated);
            $this->emailConfirmationService->send($order, $request->ip());

            return $order->fresh();
        });

        return response()->json([
            ...$order->load(['items', 'payment'])->toArray(),
            'email_confirmation_required' => true,
            'message' => 'Te hemos enviado un email para confirmar el pedido.',
        ], 201);
    }
}
