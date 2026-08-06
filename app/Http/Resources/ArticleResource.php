<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
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
             * Mantidos para não quebrar o frontend que já espera
             * esses campos. Agora o conteúdo é somente em português.
             */
            'locale' => 'pt-BR',
            'translation_status' => null,

            'slug' => $this->publication?->slug,

            'author' => [
                'name' => $this->publication?->admin?->name,
            ],

            'summary' => $this->summary,

            'title' => $this->publication?->title,
            'content' => $this->publication?->content,

            'media' => MediaResource::make(
                $this->publication?->media
            ),

            'keywords' => $this->whenLoaded(
                'keywords',
                fn () => $this->keywords
                    ->map(fn ($keyword) => $keyword->word)
                    ->filter()
                    ->values(),
                []
            ),

            'created_at' => $this
                ->publication
                ?->created_at
                ?->toIso8601String(),
        ];
    }
}
