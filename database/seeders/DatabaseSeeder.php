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
use App\Models\Media;
use App\Models\MediaFile;
use App\Models\Partner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            DocumentCategorySeeder::class,
        ]);

        $this->resetMediaStorage();

        $admin = Admin::factory()->master()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'role' => Admin::ROLE_MASTER,
            'is_active' => true,
            'password' => 'password',
        ]);

        Admin::factory(2)->admin()->create([
            'is_active' => true,
        ]);

        Admin::factory()->admin()->inactive()->create([
            'name' => 'Admin Inativo Teste',
            'email' => 'admin-inativo@test.com',
            'password' => 'password',
        ]);

        $keywords = Keyword::factory(10)->create();

        $this->createArticlesWithStorageImages($admin, $keywords);
        $this->createActivitiesWithoutScheduleConflicts($admin);
        $this->createPartnersWithSvgLogos($admin);
        $this->createDocuments($admin);
    }

    private function resetMediaStorage(): void
    {
        $collections = [
            'articles',
            'activities',
            'partners',
            'general',
        ];

        foreach ($collections as $collection) {
            Storage::disk('public')->deleteDirectory('media/' . $collection);
            Storage::disk('public')->makeDirectory('media/' . $collection);
        }
    }

    private function createArticlesWithStorageImages(Admin $admin, $keywords): void
    {
        $articles = Article::factory(10)->create();

        $articles->each(function (Article $article, int $index) use ($keywords, $admin) {
            $article->keywords()->attach(
                $keywords->random(rand(1, 4))->pluck('id')
            );

            $article->load('publication');

            $media = $this->createPicsumMediaForPublication(
                collection: 'articles',
                title: $article->publication?->title ?? 'Artigo',
                label: 'Artigo',
                index: $index,
                adminId: $admin->id
            );

            if ($article->publication) {
                $article->publication->update([
                    'media_id' => $media->id,
                ]);
            }
        });
    }

    private function createActivitiesWithoutScheduleConflicts(Admin $admin): void
    {
        $weekdays = [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
        ];

        $timeSlots = [
            ['08:00', '10:00'],
            ['10:00', '12:00'],
            ['13:00', '15:00'],
        ];

        $activities = Activity::factory(15)->create();

        $activities->each(function (Activity $activity, int $index) use (
            $weekdays,
            $timeSlots,
            $admin
        ) {
            $weekday = $weekdays[$index % count($weekdays)];
            $slotIndex = intdiv($index, count($weekdays)) % count($timeSlots);

            [$startTime, $endTime] = $timeSlots[$slotIndex];

            ActivitySchedule::factory()
                ->for($activity)
                ->create([
                    'weekday' => $weekday,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);

            ActivityLike::factory()
                ->count(fake()->numberBetween(10, 500))
                ->for($activity)
                ->create();

            $activity->load('publication');

            $media = $this->createPicsumMediaForPublication(
                collection: 'activities',
                title: $activity->publication?->title ?? 'Atividade',
                label: 'Atividade',
                index: $index,
                adminId: $admin->id
            );

            if ($activity->publication) {
                $activity->publication->update([
                    'media_id' => $media->id,
                ]);
            }
        });
    }

    private function createPartnersWithSvgLogos(Admin $admin): void
    {
        $partners = [
            [
                'name' => 'Instituto Aurora',
                'website_url' => 'https://example.com/instituto-aurora',
                'bg_color' => '#fff7ed',
                'is_active' => true,
            ],
            [
                'name' => 'Clínica Bem Viver',
                'website_url' => 'https://example.com/clinica-bem-viver',
                'bg_color' => '#f0fdf4',
                'is_active' => true,
            ],
            [
                'name' => 'Rede Apoio+',
                'website_url' => 'https://example.com/rede-apoio',
                'bg_color' => '#eff6ff',
                'is_active' => true,
            ],
            [
                'name' => 'Projeto Laços',
                'website_url' => 'https://example.com/projeto-lacos',
                'bg_color' => '#fef2f2',
                'is_active' => true,
            ],
            [
                'name' => 'Grupo Esperança',
                'website_url' => 'https://example.com/grupo-esperanca',
                'bg_color' => '#ffffff',
                'is_active' => true,
            ],
            [
                'name' => 'Associação Caminhar',
                'website_url' => 'https://example.com/associacao-caminhar',
                'bg_color' => '#f8fafc',
                'is_active' => true,
            ],
            [
                'name' => 'Parceiro Inativo Teste',
                'website_url' => 'https://example.com/inativo',
                'bg_color' => '#f8fafc',
                'is_active' => false,
            ],
        ];

        foreach ($partners as $index => $item) {
            $filename = 'partners-seed-' . Str::slug($item['name']) . '-' . Str::uuid() . '.svg';
            $path = 'media/partners/' . $filename;

            Storage::disk('public')->put(
                $path,
                $this->generatePartnerSvg(
                    name: $item['name'],
                    bgColor: $item['bg_color'],
                    index: $index
                )
            );

            $url = asset(Storage::url($path));

            MediaFile::query()->create([
                'collection' => 'partners',
                'disk' => 'public',
                'original_name' => $filename,
                'filename' => $filename,
                'path' => $path,
                'url' => $url,
                'mime_type' => 'image/svg+xml',
                'size' => Storage::disk('public')->size($path),
                'created_by' => $admin->id,
            ]);

            Partner::factory()->create([
                'admin_id' => $admin->id,
                'name' => $item['name'],
                'logo_path' => $path,
                'website_url' => $item['website_url'],
                'bg_color' => $item['bg_color'],
                'order' => $index + 1,
                'is_active' => $item['is_active'],
            ]);
        }
    }

    private function createDocuments(Admin $admin): void
    {
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

    private function createPicsumMediaForPublication(
        string $collection,
        string $title,
        string $label,
        int $index,
        int $adminId
    ): Media {
        $seed = Str::slug($collection . '-' . $index . '-' . $title);
        $sourceUrl = 'https://picsum.photos/seed/' . $seed . '/1200/675';

        $filename = $collection . '-seed-' . Str::slug($title) . '-' . Str::uuid() . '.jpg';
        $path = 'media/' . $collection . '/' . $filename;
        $mimeType = 'image/jpeg';

        try {
            $imageContent = Http::withoutVerifying()
                ->timeout(20)
                ->retry(3, 300)
                ->get($sourceUrl)
                ->throw()
                ->body();

            Storage::disk('public')->put($path, $imageContent);
        } catch (\Throwable) {
            $filename = $collection . '-seed-' . Str::slug($title) . '-' . Str::uuid() . '.svg';
            $path = 'media/' . $collection . '/' . $filename;
            $mimeType = 'image/svg+xml';

            Storage::disk('public')->put(
                $path,
                $this->generateFallbackSvg(
                    title: $title,
                    label: $label,
                    index: $index
                )
            );
        }

        $url = asset(Storage::url($path));
        $size = Storage::disk('public')->size($path);

        MediaFile::query()->create([
            'collection' => $collection,
            'disk' => 'public',
            'original_name' => $filename,
            'filename' => $filename,
            'path' => $path,
            'url' => $url,
            'mime_type' => $mimeType,
            'size' => $size,
            'created_by' => $adminId,
        ]);

        return Media::query()->create([
            'url' => $url,
            'alt_text' => $label . ': ' . $title,
            'caption' => $label . ' gerado automaticamente para teste.',
        ]);
    }

    private function generatePartnerSvg(
        string $name,
        string $bgColor,
        int $index
    ): string {
        $initials = collect(explode(' ', $name))
            ->filter()
            ->take(2)
            ->map(fn (string $word) => mb_substr($word, 0, 1))
            ->implode('');

        $initials = mb_strtoupper($initials ?: 'P');

        $colors = [
            '#c2410c',
            '#047857',
            '#1d4ed8',
            '#be123c',
            '#7c2d12',
            '#4f46e5',
        ];

        $accentColor = $colors[$index % count($colors)];

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeInitials = htmlspecialchars($initials, ENT_QUOTES, 'UTF-8');

        return <<<SVG
<svg width="640" height="360" viewBox="0 0 640 360" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect width="640" height="360" rx="32" fill="{$bgColor}"/>
  <rect x="32" y="32" width="576" height="296" rx="28" fill="white" stroke="#E5E7EB" stroke-width="2"/>
  <circle cx="320" cy="140" r="64" fill="{$accentColor}" fill-opacity="0.12"/>
  <circle cx="320" cy="140" r="46" fill="{$accentColor}"/>
  <text x="320" y="155" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="34" font-weight="700" fill="white">{$safeInitials}</text>
  <text x="320" y="238" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="30" font-weight="700" fill="#111827">{$safeName}</text>
  <text x="320" y="272" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="16" font-weight="500" fill="#6B7280">Parceiro institucional</text>
</svg>
SVG;
    }

    private function generateFallbackSvg(
        string $title,
        string $label,
        int $index
    ): string {
        $colors = [
            '#fff7ed',
            '#f0fdf4',
            '#eff6ff',
            '#fef2f2',
            '#f8fafc',
            '#faf5ff',
        ];

        $accentColors = [
            '#c2410c',
            '#047857',
            '#1d4ed8',
            '#be123c',
            '#7c2d12',
            '#4f46e5',
        ];

        $bgColor = $colors[$index % count($colors)];
        $accentColor = $accentColors[$index % count($accentColors)];

        $safeTitle = htmlspecialchars(Str::limit($title, 42, ''), ENT_QUOTES, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

        return <<<SVG
<svg width="1200" height="675" viewBox="0 0 1200 675" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect width="1200" height="675" rx="36" fill="{$bgColor}"/>
  <rect x="56" y="56" width="1088" height="563" rx="32" fill="white" stroke="#E5E7EB" stroke-width="3"/>
  <circle cx="600" cy="250" r="104" fill="{$accentColor}" fill-opacity="0.12"/>
  <circle cx="600" cy="250" r="76" fill="{$accentColor}"/>
  <text x="600" y="272" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="54" font-weight="800" fill="white">{$safeLabel}</text>
  <text x="600" y="420" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="44" font-weight="800" fill="#111827">{$safeTitle}</text>
  <text x="600" y="472" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="24" font-weight="600" fill="#6B7280">Imagem fallback de teste</text>
</svg>
SVG;
    }
}
