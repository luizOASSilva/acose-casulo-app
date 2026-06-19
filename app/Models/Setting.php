<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Setting extends Model
{
    public const PUBLIC_CACHE_KEY = 'settings.public';
    public const ADMIN_CACHE_KEY = 'settings.admin';

    public const NON_TRANSLATABLE_PUBLIC_KEYS = [
        'site_logo_url',
        'site_footer_logo_url',
        'site_og_image_url',
        'og_image_url',

        'contact_email',
        'contact_phone',
        'contact_whatsapp',

        'google_maps_embed_url',
        'google_maps_url',

        'facebook_url',
        'instagram_url',
        'youtube_url',

        'donation_enabled',
    ];

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
        Cache::forget(self::PUBLIC_CACHE_KEY . '.pt-BR');
        Cache::forget(self::PUBLIC_CACHE_KEY . '.en');
        Cache::forget(self::ADMIN_CACHE_KEY);
    }

    public static function normalizeLocale(?string $locale): string
    {
        return match ($locale) {
            'en', 'en-US', 'en_US' => SettingTranslation::LOCALE_EN,
            default => SettingTranslation::LOCALE_PT_BR,
        };
    }

    public static function publicCached(?string $locale = null): array
    {
        $locale = self::normalizeLocale($locale);
        $cacheKey = self::PUBLIC_CACHE_KEY . '.' . $locale;

        return Cache::rememberForever($cacheKey, function () use ($locale) {
            return self::query()
                ->with('translations')
                ->where('is_public', true)
                ->orderBy('group')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->mapWithKeys(fn (Setting $setting) => [
                    $setting->key => $setting->translatedValue($locale),
                ])
                ->toArray();
        });
    }

    public static function adminCached(): Collection
    {
        return self::query()
            ->with('translations')
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public static function syncAllPublicPortugueseTranslations(): void
    {
        self::query()
            ->where('is_public', true)
            ->get()
            ->each(fn (Setting $setting) => $setting->syncPortugueseTranslation());

        self::clearCache();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SettingTranslation::class);
    }

    public function portugueseTranslation(): HasOne
    {
        return $this->hasOne(SettingTranslation::class)
            ->where('locale', SettingTranslation::LOCALE_PT_BR);
    }

    public function englishTranslation(): HasOne
    {
        return $this->hasOne(SettingTranslation::class)
            ->where('locale', SettingTranslation::LOCALE_EN);
    }

    public function isTranslatablePublicValue(): bool
    {
        if (! $this->is_public) {
            return false;
        }

        if (in_array($this->key, self::NON_TRANSLATABLE_PUBLIC_KEYS, true)) {
            return false;
        }

        return in_array($this->type, ['text', 'textarea'], true);
    }

    public function translationFor(?string $locale): ?SettingTranslation
    {
        $locale = self::normalizeLocale($locale);

        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale)
                ?: $this->translations->firstWhere(
                    'locale',
                    SettingTranslation::LOCALE_PT_BR
                );
        }

        return $this->translations()
            ->where('locale', $locale)
            ->first()
            ?: $this->translations()
                ->where('locale', SettingTranslation::LOCALE_PT_BR)
                ->first();
    }

    public function translatedValue(?string $locale = null): ?string
    {
        if (! $this->isTranslatablePublicValue()) {
            return $this->value;
        }

        return $this->translationFor($locale)?->value ?? $this->value;
    }

    public function syncPortugueseTranslation(): void
    {
        if (! $this->isTranslatablePublicValue()) {
            return;
        }

        SettingTranslation::updateOrCreate(
            [
                'setting_id' => $this->id,
                'locale' => SettingTranslation::LOCALE_PT_BR,
            ],
            [
                'value' => $this->value,
                'translation_status' => SettingTranslation::STATUS_ORIGINAL,
                'translated_at' => null,
            ]
        );
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
