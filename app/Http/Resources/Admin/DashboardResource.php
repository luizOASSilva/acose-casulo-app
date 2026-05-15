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
                'visitors' => (int) $this['analytics']['visitors'],
                'visitors_growth' => $this['analytics']['growth'],
                'donations' => (int) $this['analytics']['donations'],
                'donations_growth' => $this['analytics']['donations_growth'],
                'conversion_rate' => $this['analytics']['conversion'] . '%',
            ],
            'cms' => [
                'total_articles' => $this['cms']['articles'],
                'total_partners' => $this['cms']['partners'],
                'total_documents' => $this['cms']['documents'],
            ],
            'server_status' => [
                'api' => $this['status']['api'],
                'last_sync' => now()->format('H:i:s'),
            ]
        ];
    }
}
