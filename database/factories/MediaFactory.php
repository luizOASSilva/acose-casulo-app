<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'url' => fake()->unique()->imageUrl(),
            'alt_text' => fake()->sentence(4),
            'caption' => fake()->optional()->sentence(6),
        ];
    }
}
