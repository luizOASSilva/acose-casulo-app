<?php

namespace App\Http\Resources;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = Setting::normalizeLocale(
            $request->query('locale') ?? $request->header('X-Locale')
        );

        $translation = $this->translationFor($locale);

        return [
            'id' => $this->id,
            'group' => $this->group,
            'key' => $this->key,
            'label' => $this->label,
            'description' => $this->description,
            'type' => $this->type,
            'value' => $this->value,
            'is_public' => $this->is_public,
            'sort_order' => $this->sort_order,

            'is_translatable' => $this->isTranslatablePublicValue(),
            'translated_value' => $this->translatedValue($locale),
            'translation' => $translation
                ? [
                    'locale' => $translation->locale,
                    'value' => $translation->value,
                    'translation_status' => $translation->translation_status,
                    'translated_at' => $translation->translated_at?->toIso8601String(),
                ]
                : null,
        ];
    }
}
