<?php

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ActivitySchedule>
 */
class ActivityScheduleFactory extends Factory
{
    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 15);
        $endHour = $startHour + fake()->numberBetween(1, 3);

        return [
            'activity_id' => Activity::factory(),
            'weekday' => fake()->randomElement([
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
            ]),
            'start_time' => str_pad((string) $startHour, 2, '0', STR_PAD_LEFT) . ':00',
            'end_time' => str_pad((string) $endHour, 2, '0', STR_PAD_LEFT) . ':00',
        ];
    }
}
