<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const PUBLIC_CACHE_KEY = 'settings.public';
    public const ADMIN_CACHE_KEY = 'settings.admin';

    protected $fillable = [
        'group',
        'key',
        'label',
        'description',
        'type',
        'value',
        'is_public',
        'sort_order',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }

    public static function clearCache(): void
    {
        Cache::forget(self::PUBLIC_CACHE_KEY);
        Cache::forget(self::ADMIN_CACHE_KEY);
    }

    public static function publicCached(): array
    {
        return Cache::rememberForever(self::PUBLIC_CACHE_KEY, function () {
            return self::query()
                ->where('is_public', true)
                ->orderBy('group')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->mapWithKeys(fn (Setting $setting) => [
                    $setting->key => $setting->value,
                ])
                ->toArray();
        });
    }

    public static function adminCached(): Collection
    {
        return self::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
