<?php

namespace Database\Factories;

use App\Models\Product;
use App\StockStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'price_minor' => fake()->numberBetween(10000, 250000),
            'currency' => 'RUB',
            'stock_status' => StockStatus::Available,
            'is_purchasable' => true,
            'is_published' => true,
            'is_featured' => false,
            'position' => fake()->numberBetween(0, 20),
            'metadata' => [],
        ];
    }
}
