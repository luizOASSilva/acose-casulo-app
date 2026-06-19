<?php

namespace App\Http\Resources;

use App\Models\KeywordTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KeywordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $this->resolveLocale($request);
        $translation = $this->translationFor($locale);

        return [
            'id' => $this->id,

            'locale' => $translation?->locale ?? KeywordTranslation::LOCALE_PT_BR,
            'translation_status' => $translation?->translation_status,

            'word' => $translation?->word ?? $this->word,
            'original_word' => $this->word,

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function resolveLocale(Request $request): string
    {
        $locale = (string) (
            $request->query('locale')
            ?? $request->header('X-Locale')
            ?? KeywordTranslation::LOCALE_PT_BR
        );

        return match ($locale) {
            'en', 'en-US', 'en_US' => KeywordTranslation::LOCALE_EN,
            default => KeywordTranslation::LOCALE_PT_BR,
        };
    }
}
