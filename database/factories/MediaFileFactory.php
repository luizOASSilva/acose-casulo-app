<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MediaFileFactory extends Factory
{
    public function definition(): array
    {
        $collection = fake()->randomElement([
            'articles',
            'activities',
            'partners',
            'general',
        ]);

        $extension = fake()->randomElement([
            'svg',
            'png',
            'jpg',
            'webp',
        ]);

        $filename = $collection . '-' . Str::uuid() . '.' . $extension;
        $path = 'media/' . $collection . '/' . $filename;

        return [
            'collection' => $collection,
            'disk' => 'public',
            'original_name' => fake()->slug(3) . '.' . $extension,
            'filename' => $filename,
            'path' => $path,
            'url' => '/storage/' . $path,
            'mime_type' => $this->mimeTypeFromExtension($extension),
            'size' => fake()->numberBetween(50_000, 900_000),
            'created_by' => Admin::query()->inRandomOrder()->value('id'),
        ];
    }

    public function collection(string $collection): static
    {
        return $this->state(function () use ($collection) {
            $extension = fake()->randomElement([
                'svg',
                'png',
                'jpg',
                'webp',
            ]);

            $filename = $collection . '-' . Str::uuid() . '.' . $extension;
            $path = 'media/' . $collection . '/' . $filename;

            return [
                'collection' => $collection,
                'original_name' => fake()->slug(3) . '.' . $extension,
                'filename' => $filename,
                'path' => $path,
                'url' => '/storage/' . $path,
                'mime_type' => $this->mimeTypeFromExtension($extension),
            ];
        });
    }

    private function mimeTypeFromExtension(string $extension): string
    {
        return match ($extension) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/webp',
        };
    }
}
