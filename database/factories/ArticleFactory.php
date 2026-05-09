<?php

namespace Database\Factories;

use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'summary' => fake()->text(160),
            'publication_id' => Publication::factory(),
        ];
    }
}
