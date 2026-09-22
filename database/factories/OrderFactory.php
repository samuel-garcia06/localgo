<?php

namespace Database\Factories;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => (string) Str::uuid(),
            'customer_name' => fake()->name(),
            'customer_phone' => '+34 612 345 678',
            'customer_email' => fake()->safeEmail(),
            'customer_address' => fake()->streetAddress(),
            'delivery_type' => DeliveryType::Delivery,
            'payment_method' => PaymentMethod::Cash,
            'payment_status' => PaymentStatus::Pending,
            'status' => OrderStatus::Pending,
            'notes' => fake()->sentence(),
            'total' => fake()->randomFloat(2, 10, 60),
        ];
    }
}
