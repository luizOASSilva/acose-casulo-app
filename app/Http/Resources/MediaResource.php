<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (! $this->resource) {
            return [];
        }

        return [
            'id' => $this->id,
            'url' => $this->url,

            /*
             * Mantidos para não quebrar o frontend.
             * O conteúdo agora é sempre o valor original.
             */
            'locale' => 'pt-BR',
            'translation_status' => null,

            'alt_text' => $this->alt_text,
            'caption' => $this->caption,

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
