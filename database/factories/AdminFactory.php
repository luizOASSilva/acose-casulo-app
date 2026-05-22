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
            'password' => 'password',
        ];
    }

    public function master(): static
    {
        return $this->state(fn () => [
            'role' => Admin::ROLE_MASTER,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => Admin::ROLE_ADMIN,
        ]);
    }
}

