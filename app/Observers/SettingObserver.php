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
        $changedValues = $this->createdValues($setting);

        AdminActionLogger::log(
            action: 'setting.created',
            title: 'Configuração criada',
            description: $this->adminName() . ' criou a configuração "' . $name . '".',
            subject: $setting,
            subjectName: $name,
            properties: [
                'setting_id' => $setting->id,
                'key' => $setting->key ?? null,
                'changed_fields' => array_keys($changedValues),
                'new_values' => $this->newValuesFromChanges($changedValues),
                'changed_values' => $changedValues,
                'request' => $this->requestContext(),
            ]
        );
    }

    public function updated(Setting $setting): void
    {
        if (! $this->shouldLog() || ! $this->hasRelevantChanges($setting)) {
            return;
        }

        $changedValues = $this->changedValues($setting);

        if (empty($changedValues)) {
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
                'changed_fields' => array_keys($changedValues),
                'old_values' => $this->oldValuesFromChanges($changedValues),
                'new_values' => $this->newValuesFromChanges($changedValues),
                'changed_values' => $changedValues,
                'request' => $this->requestContext(),
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
                'old_values' => $this->filledSnapshot($setting),
                'request' => $this->requestContext(),
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
            ->reject(fn ($field) => in_array($field, $this->ignoredFields(), true))
            ->values()
            ->all();
    }

    private function changedValues(Setting $setting): array
    {
        return collect($this->changedFields($setting))
            ->mapWithKeys(fn ($field) => [
                $field => [
                    'old' => $setting->getOriginal($field),
                    'new' => $setting->{$field},
                ],
            ])
            ->reject(fn ($value) => $this->normalizeForCompare($value['old']) === $this->normalizeForCompare($value['new']))
            ->all();
    }

    private function createdValues(Setting $setting): array
    {
        return collect($this->snapshot($setting))
            ->reject(fn ($value) => $this->isEmptyValue($value))
            ->mapWithKeys(fn ($value, $field) => [
                $field => [
                    'old' => null,
                    'new' => $value,
                ],
            ])
            ->all();
    }

    private function oldValuesFromChanges(array $changedValues): array
    {
        return collect($changedValues)
            ->mapWithKeys(fn ($value, $field) => [
                $field => $value['old'] ?? null,
            ])
            ->all();
    }

    private function newValuesFromChanges(array $changedValues): array
    {
        return collect($changedValues)
            ->mapWithKeys(fn ($value, $field) => [
                $field => $value['new'] ?? null,
            ])
            ->all();
    }

    private function filledSnapshot(Setting $setting): array
    {
        return collect($this->snapshot($setting))
            ->reject(fn ($value) => $this->isEmptyValue($value))
            ->all();
    }

    private function oldSnapshot(Setting $setting): array
    {
        return collect($this->auditableFields())
            ->mapWithKeys(fn ($field) => [
                $field => $setting->getOriginal($field),
            ])
            ->reject(fn ($value) => $this->isEmptyValue($value))
            ->all();
    }

    private function snapshot(Setting $setting): array
    {
        return collect($this->auditableFields())
            ->mapWithKeys(fn ($field) => [
                $field => $setting->{$field},
            ])
            ->all();
    }

    private function auditableFields(): array
    {
        return [
            'id',
            'key',
            'value',
            'name',
            'title',
            'description',
            'type',
            'group',
            'is_public',
        ];
    }

    private function ignoredFields(): array
    {
        return [
            'updated_at',
            'created_at',
        ];
    }

    private function isEmptyValue(mixed $value): bool
    {
        return $value === null || $value === '' || (is_array($value) && count($value) === 0);
    }

    private function normalizeForCompare(mixed $value): mixed
    {
        if ($value === '') {
            return null;
        }

        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => $this->normalizeForCompare($item))
                ->values()
                ->all();
        }

        return $value;
    }

    private function requestContext(): array
    {
        $request = request();

        return [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'route_name' => $request->route()?->getName(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }
}
