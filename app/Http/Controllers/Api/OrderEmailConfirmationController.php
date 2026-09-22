<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderEmailConfirmationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderEmailConfirmationController extends Controller
{
    public function __construct(private readonly OrderEmailConfirmationService $confirmationService) {}

    public function resend(Request $request, Order $order): JsonResponse
    {
        $this->confirmationService->resend($order, $request->ip());

        return response()->json([
            'message' => 'Email de confirmación reenviado.',
        ]);
    }
}
