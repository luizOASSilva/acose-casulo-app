<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PublicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => Str::limit(fake()->sentence(4), 50, ''),
            'content' => fake()->paragraphs(4, true),
            'admin_id' => Admin::factory(),
            'media_id' => Media::factory(),
        ];
    }
}
