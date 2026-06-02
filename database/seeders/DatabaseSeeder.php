<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Keyword;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            DocumentCategorySeeder::class,
        ]);

        $this->ensureMediaCollections();
        $this->createMasterAdmin();
        $this->createDefaultKeywords();
    }

    private function ensureMediaCollections(): void
    {
        $collections = [
            'articles',
            'activities',
            'partners',
            'general',
        ];

        foreach ($collections as $collection) {
            Storage::disk('public')->makeDirectory('media/' . $collection);
        }
    }

    private function createMasterAdmin(): void
    {
        $email = 'luizotavioassilva@gmail.com';

        Admin::query()->updateOrCreate(
            [
                'email' => $email,
            ],
            [
                'name' => 'Luiz Otávio',
                'role' => Admin::ROLE_MASTER,
                'is_active' => true,
                'password' => Hash::make('@Lo210405'),
            ]
        );
    }

    private function createDefaultKeywords(): void
    {
        $keywords = [
            'Institucional',
            'Inclusão',
            'Acessibilidade',
            'Atividades',
            'Família',
            'Comunidade',
            'Transparência',
            'Doações',
            'Centro Dia',
            'Pessoa com Deficiência',
        ];

        foreach ($keywords as $keyword) {
            Keyword::query()->firstOrCreate([
                'word' => $keyword,
            ]);
        }
    }
}
