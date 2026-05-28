<?php

namespace App\Http\Resources\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminActionLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'admin' => [
                'id' => $this->admin_id,
                'name' => $this->admin_name ?: 'Sistema',
            ],

            'action' => $this->action,

            'subject' => [
                'type' => $this->subject_type,
                'id' => $this->subject_id,
                'name' => $this->subject_name,
            ],

            'title' => $this->title,
            'description' => $this->description,

            'properties' => $this->properties ?? [],

            'ip_address' => $this->ip_address,

            'time' => $this->formatTime($this->created_at),
            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }

    private function formatTime($date): string
    {
        if (! $date) {
            return '—';
        }

        $date = Carbon::parse($date)->locale('pt_BR');

        if ($date->isToday()) {
            return 'Hoje às ' . $date->format('H:i');
        }

        if ($date->isYesterday()) {
            return 'Ontem';
        }

        return $date->diffForHumans();
    }
}
