<?php

namespace App\Http\Resources;

use App\Models\PartnerTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $this->resolveLocale($request);
        $translation = $this->translationFor($locale);

        return [
            'id' => $this->id,
            'name' => $this->name,

            'logo_path' => $this->logo_path,
            'logo_url' => $this->logo_path
                ? Storage::disk('public')->url($this->logo_path)
                : null,

            'locale' => $translation?->locale ?? PartnerTranslation::LOCALE_PT_BR,
            'translation_status' => $translation?->translation_status,

            'logo_alt' => $translation?->logo_alt ?? $this->logo_alt,

            'website_url' => $this->website_url,
            'bg_color' => $this->bg_color,
            'order' => $this->order,
            'is_active' => $this->is_active,

            'author' => $this->whenLoaded('admin', fn () => [
                'id' => $this->admin?->id,
                'name' => $this->admin?->name,
            ]),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function resolveLocale(Request $request): string
    {
        $locale = (string) (
            $request->query('locale')
            ?? $request->header('X-Locale')
            ?? PartnerTranslation::LOCALE_PT_BR
        );

        return match ($locale) {
            'en', 'en-US', 'en_US' => PartnerTranslation::LOCALE_EN,
            default => PartnerTranslation::LOCALE_PT_BR,
        };
    }
}
