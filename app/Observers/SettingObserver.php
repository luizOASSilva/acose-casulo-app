<?php

namespace App\Observers;

use App\Models\Setting;
use App\Services\AdminActionLogger;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class SettingObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Setting $setting): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $name = $this->getSettingName($setting);

        AdminActionLogger::log(
            action: 'setting.created',
            title: 'Configuração criada',
            description: $this->adminName() . ' criou a configuração "' . $name . '".',
            subject: $setting,
            subjectName: $name,
            properties: [
                'setting_id' => $setting->id,
                'key' => $setting->key ?? null,
            ]
        );
    }

    public function updated(Setting $setting): void
    {
        if (! $this->shouldLog() || ! $this->hasRelevantChanges($setting)) {
            return;
        }

        $name = $this->getSettingName($setting);

        AdminActionLogger::log(
            action: 'setting.updated',
            title: 'Configuração atualizada',
            description: $this->adminName() . ' atualizou a configuração "' . $name . '".',
            subject: $setting,
            subjectName: $name,
            properties: [
                'setting_id' => $setting->id,
                'key' => $setting->key ?? null,
                'changed_fields' => $this->changedFields($setting),
            ]
        );
    }

    public function deleted(Setting $setting): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $name = $this->getSettingName($setting);

        AdminActionLogger::log(
            action: 'setting.deleted',
            title: 'Configuração removida',
            description: $this->adminName() . ' removeu a configuração "' . $name . '".',
            subject: $setting,
            subjectName: $name,
            properties: [
                'setting_id' => $setting->id,
                'key' => $setting->key ?? null,
            ]
        );
    }

    private function getSettingName(Setting $setting): string
    {
        return $setting->key
            ?: $setting->name
            ?: $setting->title
            ?: 'Configuração #' . $setting->id;
    }

    private function shouldLog(): bool
    {
        return auth('admin')->check();
    }

    private function adminName(): string
    {
        return auth('admin')->user()?->name ?: 'Administrador';
    }

    private function hasRelevantChanges(Setting $setting): bool
    {
        return count($this->changedFields($setting)) > 0;
    }

    private function changedFields(Setting $setting): array
    {
        return collect(array_keys($setting->getChanges()))
            ->reject(fn ($field) => in_array($field, ['updated_at'], true))
            ->values()
            ->all();
    }
}
