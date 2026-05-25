<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'logo_path' => $this->logo_path,
            'logo_alt' => $this->logo_alt,
            'logo_url' => $this->logoUrl(),
            'website_url' => $this->website_url,
            'bg_color' => $this->bg_color ?? '#ffffff',
            'order' => $this->order,
            'is_active' => (bool) $this->is_active,
            'author' => [
                'id' => $this->admin?->id,
                'name' => $this->admin?->name ?? 'Sistema',
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    private function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        if (Str::startsWith($this->logo_path, ['http://', 'https://', '/storage/'])) {
            return $this->logo_path;
        }

        return Storage::disk('public')->url($this->logo_path);
    }
}
