<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageBlock>
 */
class PageBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'type' => 'text',
            'data' => ['title' => fake()->sentence(3)],
            'position' => 0,
            'is_visible' => true,
        ];
    }
}
