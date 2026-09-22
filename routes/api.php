<?php

use App\Http\Controllers\Api\AdminOrderController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderEmailConfirmationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StripeCheckoutController;
use App\Http\Controllers\Api\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api-public-read')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);
});

Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:orders-store');
Route::post('/orders/{order:public_id}/resend-email', [OrderEmailConfirmationController::class, 'resend'])->middleware('throttle:order-email-confirmation');
Route::post('/stripe/create-checkout-session', [StripeCheckoutController::class, 'store'])->middleware('throttle:checkout-create');
Route::post('/stripe/webhook', StripeWebhookController::class)->middleware('throttle:stripe-webhook');

Route::middleware(['web', 'auth', 'admin', 'throttle:admin-orders-api'])->prefix('admin')->group(function () {
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::patch('/orders/{order}/accept', [AdminOrderController::class, 'accept']);
    Route::patch('/orders/{order}/reject', [AdminOrderController::class, 'reject']);
    Route::patch('/orders/{order}/delivered', [AdminOrderController::class, 'delivered']);
});
