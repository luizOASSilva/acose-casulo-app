<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'file_url'   => $this->file_url,
            'year'       => $this->year,
            'category'   => [
                'id'       => $this->category->id,
                'name'     => $this->category->name,
                'featured' => $this->category->featured,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
