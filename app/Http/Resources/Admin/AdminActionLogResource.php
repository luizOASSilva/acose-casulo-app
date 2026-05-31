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
                'name' => $this->getAdminName(),
                'email' => $this->getAdminEmail(),
                'role' => $this->getAdminRole(),
            ],

            'action' => $this->action,
            'type' => $this->action,

            'subject' => [
                'type' => $this->subject_type,
                'id' => $this->subject_id,
                'name' => $this->subject_name,
            ],

            'title' => $this->title,
            'description' => $this->description,

            'properties' => $this->properties ?? [],

            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,

            'time' => $this->formatTime($this->created_at),
            'date' => optional($this->created_at)->toISOString(),
            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }

    private function getAdminName(): string
    {
        if ($this->relationLoaded('admin') && $this->admin) {
            return $this->admin->name ?: ($this->admin_name ?: 'Sistema');
        }

        return $this->admin_name ?: 'Sistema';
    }

    private function getAdminEmail(): ?string
    {
        if ($this->relationLoaded('admin') && $this->admin) {
            return $this->admin->email ?? null;
        }

        return null;
    }

    private function getAdminRole(): ?string
    {
        if ($this->relationLoaded('admin') && $this->admin) {
            return $this->admin->role ?? null;
        }

        return null;
    }

    private function formatTime($date): string
    {
        if (! $date) {
            return '—';
        }

        $date = Carbon::parse($date)
            ->timezone(config('app.timezone'))
            ->locale('pt_BR');

        if ($date->isToday()) {
            return 'Hoje às ' . $date->format('H:i');
        }

        if ($date->isYesterday()) {
            return 'Ontem às ' . $date->format('H:i');
        }

        return $date->diffForHumans();
    }
}
