<?php

use App\Http\Controllers\OrderEmailConfirmationController;
use App\Livewire\Cart;
use App\Livewire\KitchenDisplay;
use App\Livewire\Storefront;
use App\Models\Order;
use App\Support\LocalgoStore;
use Illuminate\Support\Facades\Route;

Route::get('/', Storefront::class)->name('home');
Route::get('/cart', Cart::class)->name('cart');
Route::get('/kitchen', KitchenDisplay::class)->name('kitchen')->middleware(['auth']);
Route::get('/orders/{order:public_id}/confirm-email', [OrderEmailConfirmationController::class, 'confirm'])
    ->middleware('throttle:order-email-confirmation')
    ->name('orders.confirm-email');
Route::post('/orders/{order:public_id}/resend-confirmation-email', [OrderEmailConfirmationController::class, 'resend'])
    ->middleware('throttle:order-email-confirmation')
    ->name('orders.resend-confirmation-email');

Route::get('/checkout/success/{order:public_id}', function (Order $order) {
    session()->forget(LocalgoStore::CART_SESSION_KEY);

    return redirect()->route('home')->with('order-success', "Pago confirmado para el pedido #{$order->id}.");
})->middleware('signed')->name('checkout.success');

Route::get('/checkout/cancel/{order:public_id}', function (Order $order) {
    return redirect()->route('home')->with('order-success', "El pago del pedido #{$order->id} fue cancelado. Puedes intentarlo de nuevo.");
})->middleware('signed')->name('checkout.cancel');
