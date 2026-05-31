<?php

namespace App\Observers;

use App\Models\Keyword;
use App\Services\AdminActionLogger;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class KeywordObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Keyword $keyword): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $name = $this->getKeywordName($keyword);

        $changedValues = $this->createdValues($keyword);

        AdminActionLogger::log(
            action: 'keyword.created',
            title: 'Palavra-chave criada',
            description: $this->adminName() . ' criou a palavra-chave "' . $name . '".',
            subject: $keyword,
            subjectName: $name,
            properties: [
                'keyword_id' => $keyword->id,
                'word' => $keyword->word ?? null,
                'changed_fields' => array_keys($changedValues),
                'new_values' => $this->newValuesFromChanges($changedValues),
                'changed_values' => $changedValues,
                'request' => $this->requestContext(),
            ]
        );
    }

    public function updated(Keyword $keyword): void
    {
        if (! $this->shouldLog() || ! $this->hasRelevantChanges($keyword)) {
            return;
        }

        $changedValues = $this->changedValues($keyword);

        if (empty($changedValues)) {
            return;
        }

        $name = $this->getKeywordName($keyword);

        AdminActionLogger::log(
            action: 'keyword.updated',
            title: 'Palavra-chave atualizada',
            description: $this->adminName() . ' atualizou a palavra-chave "' . $name . '".',
            subject: $keyword,
            subjectName: $name,
            properties: [
                'keyword_id' => $keyword->id,
                'word' => $keyword->word ?? null,
                'changed_fields' => array_keys($changedValues),
                'old_values' => $this->oldValuesFromChanges($changedValues),
                'new_values' => $this->newValuesFromChanges($changedValues),
                'changed_values' => $changedValues,
                'request' => $this->requestContext(),
            ]
        );
    }

    public function deleted(Keyword $keyword): void
    {
        if (! $this->shouldLog()) {
            return;
        }

        $name = $this->getKeywordName($keyword);

        AdminActionLogger::log(
            action: 'keyword.deleted',
            title: 'Palavra-chave removida',
            description: $this->adminName() . ' removeu a palavra-chave "' . $name . '".',
            subject: $keyword,
            subjectName: $name,
            properties: [
                'keyword_id' => $keyword->id,
                'word' => $keyword->word ?? null,
                'old_values' => $this->filledSnapshot($keyword),
                'request' => $this->requestContext(),
            ]
        );
    }

    private function getKeywordName(Keyword $keyword): string
    {
        return $keyword->word ?: 'Palavra-chave #' . $keyword->id;
    }

    private function shouldLog(): bool
    {
        return auth('admin')->check();
    }

    private function adminName(): string
    {
        return auth('admin')->user()?->name ?: 'Administrador';
    }

    private function hasRelevantChanges(Keyword $keyword): bool
    {
        return count($this->changedFields($keyword)) > 0;
    }

    private function changedFields(Keyword $keyword): array
    {
        return collect(array_keys($keyword->getChanges()))
            ->reject(fn ($field) => in_array($field, $this->ignoredFields(), true))
            ->values()
            ->all();
    }

    private function changedValues(Keyword $keyword): array
    {
        return collect($this->changedFields($keyword))
            ->mapWithKeys(fn ($field) => [
                $field => [
                    'old' => $keyword->getOriginal($field),
                    'new' => $keyword->{$field},
                ],
            ])
            ->reject(fn ($value) => $this->normalizeForCompare($value['old']) === $this->normalizeForCompare($value['new']))
            ->all();
    }

    private function createdValues(Keyword $keyword): array
    {
        return collect($this->snapshot($keyword))
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

    private function filledSnapshot(Keyword $keyword): array
    {
        return collect($this->snapshot($keyword))
            ->reject(fn ($value) => $this->isEmptyValue($value))
            ->all();
    }

    private function oldSnapshot(Keyword $keyword): array
    {
        return collect($this->auditableFields())
            ->mapWithKeys(fn ($field) => [
                $field => $keyword->getOriginal($field),
            ])
            ->reject(fn ($value) => $this->isEmptyValue($value))
            ->all();
    }

    private function snapshot(Keyword $keyword): array
    {
        return collect($this->auditableFields())
            ->mapWithKeys(fn ($field) => [
                $field => $keyword->{$field},
            ])
            ->all();
    }

    private function auditableFields(): array
    {
        return [
            'id',
            'word',
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
