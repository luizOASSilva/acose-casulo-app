<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'file_url',
        'admin_id',
        'category_id',
        'year',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DocumentTranslation::class);
    }

    public function portugueseTranslation(): HasOne
    {
        return $this->hasOne(DocumentTranslation::class)
            ->where('locale', DocumentTranslation::LOCALE_PT_BR);
    }

    public function englishTranslation(): HasOne
    {
        return $this->hasOne(DocumentTranslation::class)
            ->where('locale', DocumentTranslation::LOCALE_EN);
    }

    public function translationFor(?string $locale): ?DocumentTranslation
    {
        $locale = $locale ?: DocumentTranslation::LOCALE_PT_BR;

        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale)
                ?: $this->translations->firstWhere(
                    'locale',
                    DocumentTranslation::LOCALE_PT_BR
                );
        }

        return $this->translations()
            ->where('locale', $locale)
            ->first()
            ?: $this->translations()
                ->where('locale', DocumentTranslation::LOCALE_PT_BR)
                ->first();
    }

    public function translatedTitle(?string $locale = null): string
    {
        return $this->translationFor($locale)?->title ?: $this->title;
    }
}
