<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KeywordResource extends JsonResource
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
             * A palavra agora utiliza somente o valor original.
             */
            'locale' => 'pt-BR',
            'translation_status' => null,

            'word' => $this->word,
            'original_word' => $this->word,

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
