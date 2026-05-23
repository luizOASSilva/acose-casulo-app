<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'admin_id' => Admin::factory(),
            'name' => fake()->company(),
            'logo_path' => 'media/partners/placeholder.svg',
            'website_url' => fake()->optional(0.8)->url(),
            'bg_color' => fake()->randomElement([
                '#ffffff',
                '#fff7ed',
                '#f8fafc',
                '#fef2f2',
                '#f0fdf4',
                '#eff6ff',
            ]),
            'order' => fake()->numberBetween(1, 99),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
