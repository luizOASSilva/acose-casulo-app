<?php

namespace App\Http\Resources;

use App\Models\DocumentCategoryTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $this->resolveLocale($request);
        $translation = $this->translationFor($locale);

        return [
            'id' => $this->id,

            'locale' => $translation?->locale ?? DocumentCategoryTranslation::LOCALE_PT_BR,
            'translation_status' => $translation?->translation_status,

            'name' => $translation?->name ?? $this->name,
            'description' => $translation?->description ?? $this->description,

            'featured' => $this->featured,
            'order' => $this->order,

            'documents_count' => $this->whenCounted('documents'),

            'documents' => DocumentResource::collection(
                $this->whenLoaded('documents')
            ),
        ];
    }

    private function resolveLocale(Request $request): string
    {
        $locale = (string) (
            $request->query('locale')
            ?? $request->header('X-Locale')
            ?? DocumentCategoryTranslation::LOCALE_PT_BR
        );

        return match ($locale) {
            'en', 'en-US', 'en_US' => DocumentCategoryTranslation::LOCALE_EN,
            default => DocumentCategoryTranslation::LOCALE_PT_BR,
        };
    }
}
