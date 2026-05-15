<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    /**
     * Transforma o recurso em um array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo_url' => $this->logo_path ? asset('storage/' . $this->logo_path) : null,
            'website_url' => $this->website_url,
            'bg_color' => $this->bg_color ?? '#ffffff',
            'order' => $this->order,
            'is_active' => (bool) $this->is_active,
            'author' => [
                'id' => $this->admin->id ?? null,
                'name' => $this->admin->name ?? 'Sistema',
            ],
            'created_at' => $this->created_at->format('d/m/Y'),
            'updated_at' => $this->updated_at->format('d/m/Y H:i'),
        ];
    }
}

