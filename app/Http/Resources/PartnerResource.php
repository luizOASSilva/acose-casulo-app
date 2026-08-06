<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PartnerResource extends JsonResource
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
            'name' => $this->name,

            'logo_path' => $this->logo_path,

            'logo_url' => $this->logo_path
                ? Storage::disk('public')->url($this->logo_path)
                : null,

            /*
             * Mantidos para compatibilidade com o frontend.
             * O conteúdo agora usa somente o valor original.
             */
            'locale' => 'pt-BR',
            'translation_status' => null,

            'logo_alt' => $this->logo_alt,

            'website_url' => $this->website_url,
            'bg_color' => $this->bg_color,
            'order' => $this->order,
            'is_active' => $this->is_active,

            'author' => $this->whenLoaded(
                'admin',
                fn () => [
                    'id' => $this->admin?->id,
                    'name' => $this->admin?->name,
                ]
            ),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
