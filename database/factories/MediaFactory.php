<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'disk' => 'public',
            'path' => 'media/'.fake()->unique()->uuid().'.jpg',
            'filename' => fake()->uuid().'.jpg',
            'alt_text' => fake()->sentence(4),
            'mime_type' => 'image/jpeg',
            'byte_size' => fake()->numberBetween(50000, 2000000),
            'width' => 1200,
            'height' => 900,
            'metadata' => [],
        ];
    }
}
