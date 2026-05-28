<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'analytics' => [
                'visitors' => $this['analytics']['visitors'] ?? 0,
                'visitors_growth' => $this['analytics']['visitors_growth'] ?? '0%',
                'donations' => $this['analytics']['donations'] ?? 0,
                'donations_growth' => $this['analytics']['donations_growth'] ?? '0%',
                'articles_read' => $this['analytics']['articles_read'] ?? 0,
                'conversion' => $this['analytics']['conversion'] ?? '0%',
                'conversion_growth' => $this['analytics']['conversion_growth'] ?? '0%',
            ],

            'cms' => [
                'articles' => $this['cms']['articles'] ?? 0,
                'activities' => $this['cms']['activities'] ?? 0,
                'partners' => $this['cms']['partners'] ?? 0,
                'documents' => $this['cms']['documents'] ?? 0,
                'media' => $this['cms']['media'] ?? 0,
            ],

            'status' => [
                'api' => $this['status']['api'] ?? 'Online',
                'analytics' => $this['status']['analytics'] ?? 'Indisponível',
                'last_sync' => now()->format('H:i:s'),
            ],

            'recent_activity' => collect($this['recent_activity'] ?? [])
                ->map(function ($item) {
                    return [
                        'type' => $item['type'] ?? 'system',
                        'title' => $item['title'] ?? 'Atividade registrada',
                        'description' => $item['description'] ?? '',
                        'time' => $item['time'] ?? '—',
                        'date' => $item['date'] ?? null,
                    ];
                })
                ->values()
                ->all(),
        ];
    }
}
