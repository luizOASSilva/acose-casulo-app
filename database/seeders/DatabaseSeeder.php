<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityLike;
use App\Models\ActivitySchedule;
use App\Models\Admin;
use App\Models\Article;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Keyword;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            DocumentCategorySeeder::class,
        ]);

        $admin = Admin::factory()->master()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        Admin::factory(2)->admin()->create();

        $keywords = Keyword::factory(10)->create();

        Article::factory(5)
            ->create()
            ->each(function ($article) use ($keywords) {
                $article->keywords()->attach(
                    $keywords->random(rand(1, 4))->pluck('id')
                );
            });

        $weekdays = [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
        ];

        Activity::factory(15)
            ->create()
            ->each(function (Activity $activity, int $index) use ($weekdays) {
                $weekday = $weekdays[$index % count($weekdays)];

                $startHour = 8 + ($index % 5);
                $endHour = $startHour + 2;

                ActivitySchedule::factory()
                    ->for($activity)
                    ->create([
                        'weekday' => $weekday,
                        'start_time' => str_pad((string) $startHour, 2, '0', STR_PAD_LEFT) . ':00',
                        'end_time' => str_pad((string) $endHour, 2, '0', STR_PAD_LEFT) . ':00',
                    ]);

                ActivityLike::factory()
                    ->count(fake()->numberBetween(10, 500))
                    ->for($activity)
                    ->create();
            });

        $categories = DocumentCategory::query()
            ->orderBy('order')
            ->get();

        foreach ($categories as $category) {
            foreach ([2024, 2025, 2026] as $year) {
                Document::factory(2)->create([
                    'admin_id' => $admin->id,
                    'category_id' => $category->id,
                    'year' => $year,
                ]);
            }
        }
    }
}
