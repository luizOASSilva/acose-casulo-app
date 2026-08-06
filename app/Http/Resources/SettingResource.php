<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
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
            'group' => $this->group,
            'key' => $this->key,
            'label' => $this->label,
            'description' => $this->description,
            'type' => $this->type,
            'value' => $this->value,
            'is_public' => $this->is_public,
            'sort_order' => $this->sort_order,

            /*
             * Mantidos para compatibilidade com o frontend.
             * Não existe mais tradução de configurações.
             */
            'is_translatable' => false,
            'translated_value' => $this->value,
            'translation' => null,
        ];
    }
}
