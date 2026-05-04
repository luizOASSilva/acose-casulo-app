<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\DocumentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'       => fake()->sentence(4),
            'file_url'    => 'https://drive.google.com/file/' . fake()->unique()->uuid(),
            'admin_id'    => Admin::factory(),
            'category_id' => DocumentCategory::factory(),
            'year'        => fake()->numberBetween(2022, 2025),
        ];
    }
}
