<?php

namespace App\Http\Resources;

use App\Models\ActivityLike;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
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
            'slug' => $this->publication?->slug,

            'title' => $this->publication?->title,
            'content' => $this->publication?->content,

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
}
