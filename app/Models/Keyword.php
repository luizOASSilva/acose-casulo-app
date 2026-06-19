<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Keyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'word',
    ];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(KeywordTranslation::class);
    }

    public function portugueseTranslation(): HasOne
    {
        return $this->hasOne(KeywordTranslation::class)
            ->where('locale', KeywordTranslation::LOCALE_PT_BR);
    }

    public function englishTranslation(): HasOne
    {
        return $this->hasOne(KeywordTranslation::class)
            ->where('locale', KeywordTranslation::LOCALE_EN);
    }

    public function translationFor(?string $locale): ?KeywordTranslation
    {
        $locale = match ($locale) {
            'en', 'en-US', 'en_US' => KeywordTranslation::LOCALE_EN,
            default => KeywordTranslation::LOCALE_PT_BR,
        };

        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale)
                ?: $this->translations->firstWhere(
                    'locale',
                    KeywordTranslation::LOCALE_PT_BR
                );
        }

        return $this->translations()
            ->where('locale', $locale)
            ->first()
            ?: $this->translations()
                ->where('locale', KeywordTranslation::LOCALE_PT_BR)
                ->first();
    }

    public function translatedWord(?string $locale = null): string
    {
        return $this->translationFor($locale)?->word ?: $this->word;
    }

    public function syncPortugueseTranslation(): void
    {
        KeywordTranslation::updateOrCreate(
            [
                'keyword_id' => $this->id,
                'locale' => KeywordTranslation::LOCALE_PT_BR,
            ],
            [
                'word' => $this->word,
                'translation_status' => KeywordTranslation::STATUS_ORIGINAL,
                'translated_at' => null,
            ]
        );
    }
}
