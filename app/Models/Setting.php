<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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

    public static function emailLogoValue(): ?string
    {
        return self::query()
            ->whereIn('key', [
                'site_logo_url',
                'site_footer_logo_url',
            ])
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->orderByRaw("FIELD(`key`, 'site_logo_url', 'site_footer_logo_url')")
            ->value('value');
    }

    public static function emailLogoPath(): ?string
    {
        $logo = self::emailLogoValue();

        if (! $logo) {
            return null;
        }

        $logo = trim($logo);

        if (Str::startsWith($logo, ['http://', 'https://'])) {
            $path = parse_url($logo, PHP_URL_PATH);

            if (! $path) {
                return null;
            }

            $localPath = public_path(ltrim($path, '/'));

            return file_exists($localPath) ? $localPath : null;
        }

        if (Str::startsWith($logo, ['/storage/', 'storage/'])) {
            $localPath = public_path(ltrim($logo, '/'));

            return file_exists($localPath) ? $localPath : null;
        }

        $localPath = public_path('storage/' . ltrim($logo, '/'));

        return file_exists($localPath) ? $localPath : null;
    }

    public static function emailLogoUrl(): ?string
    {
        $logo = self::emailLogoValue();

        if (! $logo) {
            return null;
        }

        $logo = trim($logo);

        if (Str::startsWith($logo, ['http://', 'https://'])) {
            return $logo;
        }

        if (Str::startsWith($logo, ['/storage/', 'storage/'])) {
            return url('/' . ltrim($logo, '/'));
        }

        return url('/storage/' . ltrim($logo, '/'));
    }
}
