<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'alt_text',
        'caption',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(MediaTranslation::class);
    }

    public function portugueseTranslation(): HasOne
    {
        return $this->hasOne(MediaTranslation::class)
            ->where('locale', MediaTranslation::LOCALE_PT_BR);
    }

    public function englishTranslation(): HasOne
    {
        return $this->hasOne(MediaTranslation::class)
            ->where('locale', MediaTranslation::LOCALE_EN);
    }

    public function translationFor(?string $locale): ?MediaTranslation
    {
        $locale = $locale ?: MediaTranslation::LOCALE_PT_BR;

        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale)
                ?: $this->translations->firstWhere(
                    'locale',
                    MediaTranslation::LOCALE_PT_BR
                );
        }

        return $this->translations()
            ->where('locale', $locale)
            ->first()
            ?: $this->translations()
                ->where('locale', MediaTranslation::LOCALE_PT_BR)
                ->first();
    }

    public function translatedAltText(?string $locale = null): ?string
    {
        return $this->translationFor($locale)?->alt_text ?: $this->alt_text;
    }

    public function translatedCaption(?string $locale = null): ?string
    {
        return $this->translationFor($locale)?->caption ?: $this->caption;
    }
}
