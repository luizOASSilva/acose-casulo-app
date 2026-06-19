<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DocumentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'featured',
        'order',
    ];

    protected $casts = [
        'featured' => 'boolean',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'category_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DocumentCategoryTranslation::class);
    }

    public function portugueseTranslation(): HasOne
    {
        return $this->hasOne(DocumentCategoryTranslation::class)
            ->where('locale', DocumentCategoryTranslation::LOCALE_PT_BR);
    }

    public function englishTranslation(): HasOne
    {
        return $this->hasOne(DocumentCategoryTranslation::class)
            ->where('locale', DocumentCategoryTranslation::LOCALE_EN);
    }

    public function translationFor(?string $locale): ?DocumentCategoryTranslation
    {
        $locale = $locale ?: DocumentCategoryTranslation::LOCALE_PT_BR;

        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale)
                ?: $this->translations->firstWhere(
                    'locale',
                    DocumentCategoryTranslation::LOCALE_PT_BR
                );
        }

        return $this->translations()
            ->where('locale', $locale)
            ->first()
            ?: $this->translations()
                ->where('locale', DocumentCategoryTranslation::LOCALE_PT_BR)
                ->first();
    }

    public function translatedName(?string $locale = null): string
    {
        return $this->translationFor($locale)?->name ?: $this->name;
    }

    public function translatedDescription(?string $locale = null): ?string
    {
        return $this->translationFor($locale)?->description ?: $this->description;
    }
}
