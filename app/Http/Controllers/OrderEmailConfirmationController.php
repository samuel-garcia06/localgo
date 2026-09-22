<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderEmailConfirmationService;
use App\Support\LocalgoStore;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderEmailConfirmationController extends Controller
{
    public function __construct(private readonly OrderEmailConfirmationService $confirmationService) {}

    public function confirm(Request $request, Order $order)
    {
        if ($order->status === OrderStatus::Confirmed) {
            return redirect()->route('home')
                ->with('order-success', $this->alreadyConfirmedMessage($order->customer_name));
        }

        try {
            $confirmedOrder = $this->confirmationService->confirm($order, $request->query('token'));
            session()->forget(LocalgoStore::CART_SESSION_KEY);

            return redirect()->route('home')
                ->with('order-success', $this->successMessage($confirmedOrder->customer_name));
        } catch (ValidationException $exception) {
            return response()->view('orders.email-confirmation-result', [
                'status' => 'error',
                'order' => $order,
                'message' => $exception->validator->errors()->first('token') ?: 'No hemos podido confirmar el pedido.',
                'canResend' => $this->canResend($order),
            ], 422);
        }
    }

    public function resend(Request $request, Order $order)
    {
        $validated = $request->validate([
            'customer_email' => ['required', 'email:rfc', 'max:160'],
        ]);

        if (mb_strtolower($validated['customer_email']) !== mb_strtolower((string) $order->customer_email)) {
            throw ValidationException::withMessages([
                'customer_email' => 'El email no coincide con el pedido.',
            ]);
        }

        $this->confirmationService->resend($order, $request->ip());

        return view('orders.email-confirmation-result', [
            'status' => 'pending',
            'order' => $order->fresh(),
            'message' => 'Hemos reenviado el email de confirmación. Revisa también la carpeta de spam.',
            'canResend' => $this->canResend($order->fresh()),
        ]);
    }

    private function successMessage(?string $customerName): string
    {
        $firstName = $this->extractFirstName($customerName);

        return $firstName
            ? "Gracias por tu pedido, {$firstName}. Ya lo hemos enviado al restaurante."
            : 'Gracias por tu pedido. Ya lo hemos enviado al restaurante.';
    }

    private function alreadyConfirmedMessage(?string $customerName): string
    {
        $firstName = $this->extractFirstName($customerName);

        return $firstName
            ? "Gracias, {$firstName}. Tu pedido ya estaba confirmado, estamos preparándolo."
            : 'Tu pedido ya estaba confirmado, estamos preparándolo.';
    }

    private function extractFirstName(?string $fullName): string
    {
        $trimmed = trim((string) $fullName);

        if ($trimmed === '') {
            return '';
        }

        return mb_convert_case(explode(' ', $trimmed)[0], MB_CASE_TITLE, 'UTF-8');
    }

    private function canResend(Order $order): bool
    {
        return $order->status === OrderStatus::PendingEmailConfirmation
            && $order->confirmation_resend_count < 3;
    }
}
