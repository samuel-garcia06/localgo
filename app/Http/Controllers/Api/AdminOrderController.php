<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class AdminOrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(): JsonResponse
    {
        return response()->json(
            Order::query()
                ->with(['items', 'payment'])
                ->whereIn('status', OrderStatus::visibleToRestaurantValues())
                ->orderByRaw('case when status = ? then 1 else 0 end asc', [OrderStatus::Delivered->value])
                ->orderBy('created_at')
                ->get()
        );
    }

    public function accept(Order $order): JsonResponse
    {
        abort_unless(in_array($order->status->value, OrderStatus::visibleToRestaurantValues(), true), 404);

        return response()->json($this->orderService->updateOrderStatus($order, OrderStatus::Accepted)->load(['items', 'payment']));
    }

    public function reject(Order $order): JsonResponse
    {
        abort_unless(in_array($order->status->value, OrderStatus::visibleToRestaurantValues(), true), 404);

        return response()->json($this->orderService->updateOrderStatus($order, OrderStatus::Rejected)->load(['items', 'payment']));
    }

    public function delivered(Order $order): JsonResponse
    {
        abort_unless(in_array($order->status->value, OrderStatus::visibleToRestaurantValues(), true), 404);

        return response()->json($this->orderService->updateOrderStatus($order, OrderStatus::Delivered)->load(['items', 'payment']));
    }
}
