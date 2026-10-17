<?php

namespace Database\Factories;

use App\Models\CafeMenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CafeMenuItem>
 */
class CafeMenuItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'category' => 'Напитки',
            'description' => fake()->sentence(),
            'price_minor' => fake()->numberBetween(15000, 60000),
            'currency' => 'RUB',
            'is_available' => true,
            'is_published' => true,
            'position' => 0,
        ];
    }
}
