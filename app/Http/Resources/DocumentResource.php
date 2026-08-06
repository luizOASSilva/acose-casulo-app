<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
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

            /*
             * Mantidos para compatibilidade com o frontend.
             * Os dados agora utilizam somente os valores originais.
             */
            'locale' => 'pt-BR',
            'translation_status' => null,

            'title' => $this->title,

            'file_url' => $this->file_url,
            'year' => $this->year,

            'category' => $this->category
                ? [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'description' => $this->category->description,
                    'featured' => $this->category->featured,

                    /*
                     * Mantidos para compatibilidade com o frontend.
                     */
                    'locale' => 'pt-BR',
                    'translation_status' => null,
                ]
                : null,

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
