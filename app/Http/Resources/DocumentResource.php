<?php

namespace App\Http\Resources;

use App\Models\DocumentCategoryTranslation;
use App\Models\DocumentTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $this->resolveLocale($request);
        $translation = $this->translationFor($locale);
        $categoryTranslation = $this->category?->translationFor($locale);

        return [
            'id' => $this->id,

            'locale' => $translation?->locale ?? DocumentTranslation::LOCALE_PT_BR,
            'translation_status' => $translation?->translation_status,

            'title' => $translation?->title ?? $this->title,

            'file_url' => $this->file_url,
            'year' => $this->year,

            'category' => $this->category
                ? [
                    'id' => $this->category->id,
                    'name' => $categoryTranslation?->name ?? $this->category->name,
                    'description' => $categoryTranslation?->description ?? $this->category->description,
                    'featured' => $this->category->featured,
                    'locale' => $categoryTranslation?->locale ?? DocumentCategoryTranslation::LOCALE_PT_BR,
                    'translation_status' => $categoryTranslation?->translation_status,
                ]
                : null,

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    private function resolveLocale(Request $request): string
    {
        $locale = (string) (
            $request->query('locale')
            ?? $request->header('X-Locale')
            ?? DocumentTranslation::LOCALE_PT_BR
        );

        return match ($locale) {
            'en', 'en-US', 'en_US' => DocumentTranslation::LOCALE_EN,
            default => DocumentTranslation::LOCALE_PT_BR,
        };
    }
}
