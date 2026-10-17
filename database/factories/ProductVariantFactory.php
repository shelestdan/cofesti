<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\StockStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => '250 г',
            'attributes' => ['Вес' => '250 г'],
            'price_minor' => fake()->numberBetween(10000, 250000),
            'regular_price_minor' => fake()->numberBetween(10000, 250000),
            'stock_status' => StockStatus::Available,
            'is_purchasable' => true,
            'is_active' => true,
            'position' => 0,
        ];
    }
}
