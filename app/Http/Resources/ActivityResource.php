<?php

namespace App\Http\Resources;

use App\Models\ActivityLike;
use App\Models\PublicationTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $this->resolveLocale($request);
        $translation = $this->publication?->translationFor($locale);

        $visitorId =
            $request->cookie('visitor_id')
            ?? $request->header('X-Visitor-ID')
            ?? $request->query('visitor_id');

        $likesCount = $this->likes_count ?? $this->likes()->count();

        $isLiked = $visitorId
            ? ActivityLike::query()
                ->where('activity_id', $this->id)
                ->where('visitor_id', $visitorId)
                ->exists()
            : false;

        return [
            'id' => $this->id,

            'locale' => $translation?->locale ?? PublicationTranslation::LOCALE_PT_BR,
            'translation_status' => $translation?->translation_status,

            'slug' => $translation?->slug ?? $this->publication?->slug,

            'title' => $translation?->title ?? $this->publication?->title,
            'content' => $translation?->content ?? $this->publication?->content,

            'likes' => $likesCount,
            'likes_count' => $likesCount,

            'is_liked' => $isLiked,
            'liked' => $isLiked,

            'media' => MediaResource::make($this->publication?->media),

            'schedules' => ActivityScheduleResource::collection(
                $this->whenLoaded('schedules')
            ),

            'created_at' => $this->publication?->created_at?->toIso8601String(),
            'updated_at' => $this->publication?->updated_at?->toIso8601String(),
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
