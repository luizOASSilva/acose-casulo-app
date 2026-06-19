<?php

namespace App\Http\Resources;

use App\Models\PublicationTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = $this->resolveLocale($request);
        $translation = $this->publication?->translationFor($locale);

        return [
            'id' => $this->id,

            'locale' => $translation?->locale ?? PublicationTranslation::LOCALE_PT_BR,
            'translation_status' => $translation?->translation_status,

            'slug' => $translation?->slug ?? $this->publication?->slug,

            'author' => [
                'name' => $this->publication?->admin?->name,
            ],

            'summary' => $translation?->summary ?? $this->summary,

            'title' => $translation?->title ?? $this->publication?->title,
            'content' => $translation?->content ?? $this->publication?->content,

            'media' => MediaResource::make($this->publication?->media),

            'keywords' => $this->whenLoaded(
                'keywords',
                fn () => $this->keywords
                    ->map(fn ($keyword) => $keyword->translatedWord($locale))
                    ->filter()
                    ->values(),
                []
            ),

            'created_at' => $this->publication?->created_at?->toIso8601String(),
        ];
    }

    private function resolveLocale(Request $request): string
    {
        $locale = (string) (
            $request->query('locale')
            ?? $request->header('X-Locale')
            ?? PublicationTranslation::LOCALE_PT_BR
        );

        return match ($locale) {
            'en', 'en-US', 'en_US' => PublicationTranslation::LOCALE_EN,
            default => PublicationTranslation::LOCALE_PT_BR,
        };
    }
}
