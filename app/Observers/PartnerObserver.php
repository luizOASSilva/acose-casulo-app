<?php

namespace App\Observers;

use App\Models\Partner;
use App\Services\AdminActionLogger;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PartnerObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Partner $partner): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $name = $this->getPartnerName($partner);
        $changedValues = $this->createdValues($partner);

        AdminActionLogger::log(
            action: 'partner.created',
            title: 'Parceiro criado',
            description: $this->adminName() . ' criou o parceiro "' . $name . '".',
            subject: $partner,
            subjectName: $name,
            properties: [
                'partner_id' => $partner->id,
                'website_url' => $partner->website_url ?? null,
                'is_active' => $partner->is_active ?? null,
                'changed_fields' => array_keys($changedValues),
                'new_values' => $this->newValuesFromChanges($changedValues),
                'changed_values' => $changedValues,
                'request' => $this->requestContext(),
            ]
        );
    }

    public function updated(Partner $partner): void
    {
        if (! $this->shouldLog() || ! $this->hasRelevantChanges($partner)) {
            return;
        }

        $changedValues = $this->changedValues($partner);

        if (empty($changedValues)) {
            return;
        }

        $name = $this->getPartnerName($partner);

        AdminActionLogger::log(
            action: 'partner.updated',
            title: 'Parceiro atualizado',
            description: $this->adminName() . ' atualizou o parceiro "' . $name . '".',
            subject: $partner,
            subjectName: $name,
            properties: [
                'partner_id' => $partner->id,
                'changed_fields' => array_keys($changedValues),
                'old_values' => $this->oldValuesFromChanges($changedValues),
                'new_values' => $this->newValuesFromChanges($changedValues),
                'changed_values' => $changedValues,
                'request' => $this->requestContext(),
            ]
        );
    }

    public function deleted(Partner $partner): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $name = $this->getPartnerName($partner);

        AdminActionLogger::log(
            action: 'partner.deleted',
            title: 'Parceiro removido',
            description: $this->adminName() . ' removeu o parceiro "' . $name . '".',
            subject: $partner,
            subjectName: $name,
            properties: [
                'partner_id' => $partner->id,
                'website_url' => $partner->website_url ?? null,
                'old_values' => $this->filledSnapshot($partner),
                'request' => $this->requestContext(),
            ]
        );
    }

    private function getPartnerName(Partner $partner): string
    {
        return $partner->name ?: 'Parceiro #' . $partner->id;
    }

    private function shouldLog(): bool
    {
        return auth('admin')->check();
    }

    private function adminName(): string
    {
        return auth('admin')->user()?->name ?: 'Administrador';
    }

    private function hasRelevantChanges(Partner $partner): bool
    {
        return count($this->changedFields($partner)) > 0;
    }

    private function changedFields(Partner $partner): array
    {
        return collect(array_keys($partner->getChanges()))
            ->reject(fn ($field) => in_array($field, $this->ignoredFields(), true))
            ->values()
            ->all();
    }

    private function changedValues(Partner $partner): array
    {
        return collect($this->changedFields($partner))
            ->mapWithKeys(fn ($field) => [
                $field => [
                    'old' => $partner->getOriginal($field),
                    'new' => $partner->{$field},
                ],
            ])
            ->reject(fn ($value) => $this->normalizeForCompare($value['old']) === $this->normalizeForCompare($value['new']))
            ->all();
    }

    private function createdValues(Partner $partner): array
    {
        return collect($this->snapshot($partner))
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

    private function filledSnapshot(Partner $partner): array
    {
        return collect($this->snapshot($partner))
            ->reject(fn ($value) => $this->isEmptyValue($value))
            ->all();
    }

    private function oldSnapshot(Partner $partner): array
    {
        return collect($this->auditableFields())
            ->mapWithKeys(fn ($field) => [
                $field => $partner->getOriginal($field),
            ])
            ->reject(fn ($value) => $this->isEmptyValue($value))
            ->all();
    }

    private function snapshot(Partner $partner): array
    {
        return collect($this->auditableFields())
            ->mapWithKeys(fn ($field) => [
                $field => $partner->{$field},
            ])
            ->all();
    }

    private function auditableFields(): array
    {
        return [
            'id',
            'name',
            'website_url',
            'logo_url',
            'logo_path',
            'is_active',
            'order',
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
