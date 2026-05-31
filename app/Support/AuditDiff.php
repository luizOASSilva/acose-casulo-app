<?php

namespace App\Support;

use Illuminate\Http\Request;

class AuditDiff
{
    public static function changedValues(array $before, array $after): array
    {
        return collect($after)
            ->mapWithKeys(function ($newValue, string $field) use ($before) {
                $oldValue = $before[$field] ?? null;

                return [
                    $field => [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ],
                ];
            })
            ->reject(function (array $value) {
                return self::normalizeForCompare($value['old'])
                    === self::normalizeForCompare($value['new']);
            })
            ->all();
    }

    public static function createdValues(array $values): array
    {
        return collect(self::filterFilled($values))
            ->mapWithKeys(fn ($value, string $field) => [
                $field => [
                    'old' => null,
                    'new' => $value,
                ],
            ])
            ->all();
    }

    public static function oldValuesFromChanges(array $changedValues): array
    {
        return collect($changedValues)
            ->mapWithKeys(fn (array $value, string $field) => [
                $field => $value['old'] ?? null,
            ])
            ->all();
    }

    public static function newValuesFromChanges(array $changedValues): array
    {
        return collect($changedValues)
            ->mapWithKeys(fn (array $value, string $field) => [
                $field => $value['new'] ?? null,
            ])
            ->all();
    }

    public static function filterFilled(array $values): array
    {
        return collect($values)
            ->reject(fn ($value) => self::isEmptyValue($value))
            ->all();
    }

    public static function requestContext(?Request $request = null): array
    {
        $request ??= request();

        return [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'route_name' => $request->route()?->getName(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }

    private static function normalizeForCompare(mixed $value): mixed
    {
        if ($value === '') {
            return null;
        }

        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => self::normalizeForCompare($item))
                ->values()
                ->all();
        }

        return $value;
    }

    private static function isEmptyValue(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (is_array($value)) {
            return count($value) === 0;
        }

        return false;
    }
}
