<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'provider' => 'cash',
            'provider_payment_id' => null,
            'provider_checkout_session_id' => null,
            'amount' => fake()->randomFloat(2, 10, 60),
            'currency' => 'eur',
            'status' => PaymentStatus::Pending,
            'payload' => [],
            'paid_at' => null,
        ];
    }
}
