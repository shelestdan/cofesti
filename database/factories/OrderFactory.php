<?php

namespace Database\Factories;

use App\Models\Order;
use App\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'number' => 'CF-'.fake()->unique()->numerify('######'),
            'idempotency_key' => fake()->uuid(),
            'status' => OrderStatus::New,
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->e164PhoneNumber(),
            'customer_email' => fake()->safeEmail(),
            'subtotal_minor' => 120000,
            'total_minor' => 120000,
            'currency' => 'RUB',
        ];
    }
}
