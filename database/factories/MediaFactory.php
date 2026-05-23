<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'url' => '/logo.svg',
            'alt_text' => fake()->sentence(4),
            'caption' => fake()->optional(0.7)->sentence(6),
        ];
    }
}

