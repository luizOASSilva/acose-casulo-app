<?php

namespace App\Http\Resources;

use App\Models\ActivityLike;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $visitorId = $request->header('X-Visitor-ID') ?? $request->query('visitor_id');

        return [
            'id' => $this->id,
            'slug' => $this->publication?->slug,
            'likes' => $this->likes_count ?? $this->likes()->count(),
            'is_liked' => $visitorId
                ? ActivityLike::query()
                    ->where('activity_id', $this->id)
                    ->where('visitor_id', $visitorId)
                    ->exists()
                : false,
            'title' => $this->publication?->title,
            'content' => $this->publication?->content,
            'media' => MediaResource::make($this->publication?->media),
            'schedules' => ActivityScheduleResource::collection(
                $this->whenLoaded('schedules')
            ),
            'created_at' => $this->publication?->created_at?->toIso8601String(),
        ];
    }
}

