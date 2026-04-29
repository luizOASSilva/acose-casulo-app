<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug'  => $this->publication?->slug,
            'likes' => $this->likes,
            'title' => $this->publication?->title,
            'content' => $this->publication?->content,
            'media' => MediaResource::make($this->publication?->media),
            'created_at' => $this->publication?->created_at?->toIso8601String(),
        ];
    }
}
