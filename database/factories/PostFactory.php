<?php

namespace Database\Factories;

use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'likes' => fake()->numberBetween(0, 500),
            'publication_id' => Publication::factory(),
        ];
    }
}
