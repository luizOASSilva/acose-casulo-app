<?php

namespace App\Http\Resources;

use App\Models\MediaTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (! $this->resource) {
            return [];
        }

        $locale = $this->resolveLocale($request);
        $translation = $this->translationFor($locale);

        return [
            'id' => $this->id,
            'url' => $this->url,

            'locale' => $translation?->locale ?? MediaTranslation::LOCALE_PT_BR,
            'translation_status' => $translation?->translation_status,

            'alt_text' => $translation?->alt_text ?? $this->alt_text,
            'caption' => $translation?->caption ?? $this->caption,

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function resolveLocale(Request $request): string
    {
        $locale = (string) (
            $request->query('locale')
            ?? $request->header('X-Locale')
            ?? MediaTranslation::LOCALE_PT_BR
        );

        return match ($locale) {
            'en', 'en-US', 'en_US' => MediaTranslation::LOCALE_EN,
            default => MediaTranslation::LOCALE_PT_BR,
        };
    }
}
