<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Article;
use App\Models\Document;
use App\Models\Keyword;
use App\Models\Activity;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Admin::factory()->create([
            'name'  => 'Admin Test',
            'email' => 'admin@test.com',
        ]);

        $keywords = Keyword::factory(10)->create();

        Article::factory(5)
            ->create()
            ->each(function ($article) use ($keywords) {
                $article->keywords()->attach(
                    $keywords->random(rand(1, 4))->pluck('id')
                );
            });

        Activity::factory(5)->create();

        Document::factory(5)->create([
            'admin_id' => Admin::first()->id,
        ]);
    }
}
