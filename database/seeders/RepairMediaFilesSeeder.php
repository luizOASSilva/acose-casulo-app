<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\MediaFile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RepairMediaFilesSeeder extends Seeder
{
    public function run(): void
    {
        $files = MediaFile::query()->get();

        foreach ($files as $file) {
            $oldUrl = $file->url;
            $collection = $file->collection ?: 'general';

            $shouldRepair =
                !$file->path ||
                !Storage::disk('public')->exists($file->path);

            if (!$shouldRepair) {
                continue;
            }

            $baseName = pathinfo($file->filename ?: $file->original_name ?: 'media', PATHINFO_FILENAME);
            $baseName = Str::slug($baseName) ?: 'media';

            $filename = $collection . '-repair-' . $baseName . '-' . Str::uuid() . '.svg';
            $path = 'media/' . $collection . '/' . $filename;

            Storage::disk('public')->makeDirectory('media/' . $collection);

            Storage::disk('public')->put(
                $path,
                $this->generateFallbackSvg(
                    title: $file->original_name ?: $file->filename ?: 'Imagem',
                    label: $this->getCollectionLabel($collection)
                )
            );

            $url = asset(Storage::url($path));
            $size = Storage::disk('public')->size($path);

            $file->update([
                'original_name' => $filename,
                'filename' => $filename,
                'path' => $path,
                'url' => $url,
                'mime_type' => 'image/svg+xml',
                'size' => $size,
            ]);

            if ($oldUrl) {
                Media::query()
                    ->where('url', $oldUrl)
                    ->update([
                        'url' => $url,
                    ]);
            }

            $this->command?->info('Mídia reparada: ' . $path);
        }

        $this->command?->info('Reparo de mídias finalizado.');
    }

    private function getCollectionLabel(string $collection): string
    {
        return match ($collection) {
            'articles' => 'Artigo',
            'activities' => 'Atividade',
            'partners' => 'Parceiro',
            default => 'Imagem',
        };
    }

    private function generateFallbackSvg(string $title, string $label): string
    {
        $safeTitle = htmlspecialchars(Str::limit($title, 42, ''), ENT_QUOTES, 'UTF-8');
        $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

        return <<<SVG
<svg width="1200" height="675" viewBox="0 0 1200 675" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect width="1200" height="675" rx="36" fill="#fff7ed"/>
  <rect x="56" y="56" width="1088" height="563" rx="32" fill="white" stroke="#E5E7EB" stroke-width="3"/>
  <circle cx="600" cy="250" r="104" fill="#c2410c" fill-opacity="0.12"/>
  <circle cx="600" cy="250" r="76" fill="#c2410c"/>
  <text x="600" y="270" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="54" font-weight="800" fill="white">{$safeLabel}</text>
  <text x="600" y="420" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="44" font-weight="800" fill="#111827">{$safeTitle}</text>
  <text x="600" y="472" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="24" font-weight="600" fill="#6B7280">Imagem restaurada automaticamente</text>
</svg>
SVG;
    }
}
