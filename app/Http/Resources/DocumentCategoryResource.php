<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'featured' => $this->featured,
            'order' => $this->order,
            'documents_count' => $this->whenCounted('documents'),
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}
