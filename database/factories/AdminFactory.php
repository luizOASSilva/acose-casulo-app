<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdminFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => Admin::ROLE_ADMIN,
            'is_active' => true,
            'password' => 'password',
        ];
    }

    public function master(): static
    {
        return $this->state(fn () => [
            'role' => Admin::ROLE_MASTER,
            'is_active' => true,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => Admin::ROLE_ADMIN,
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
