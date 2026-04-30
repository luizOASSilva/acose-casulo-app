<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    public function definition(): array
    {
        $seed = fake()->unique()->numberBetween(1, 1000);

        return [
            'url' => "https://picsum.photos/seed/{$seed}/800/600",
            'alt_text' => fake()->sentence(4),
            'caption' => fake()->optional()->sentence(6),
        ];
    }
}
