<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

class PublicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'content' => fake()->paragraphs(4, true),
            'admin_id' => Admin::factory(),
            'media_id' => Media::factory(),
        ];
    }
}
