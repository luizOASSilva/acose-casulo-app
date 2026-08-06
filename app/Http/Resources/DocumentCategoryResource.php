<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentCategoryResource extends JsonResource
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
             * Os dados agora são sempre os valores originais.
             */
            'locale' => 'pt-BR',
            'translation_status' => null,

            'name' => $this->name,
            'description' => $this->description,

            'featured' => $this->featured,
            'order' => $this->order,

            'documents_count' => $this->whenCounted('documents'),

            'documents' => DocumentResource::collection(
                $this->whenLoaded('documents')
            ),
        ];
    }
}
