<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Publication extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = [
        'title',
        'content',
        'media_id',
        'admin_id',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(80);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function article(): HasOne
    {
        return $this->hasOne(Article::class);
    }

    public function post(): HasOne
    {
        return $this->hasOne(Activity::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PublicationTranslation::class);
    }

    public function portugueseTranslation(): HasOne
    {
        return $this->hasOne(PublicationTranslation::class)
            ->where('locale', PublicationTranslation::LOCALE_PT_BR);
    }

    public function englishTranslation(): HasOne
    {
        return $this->hasOne(PublicationTranslation::class)
            ->where('locale', PublicationTranslation::LOCALE_EN);
    }

    public function translationFor(?string $locale): ?PublicationTranslation
    {
        $locale = $locale ?: PublicationTranslation::LOCALE_PT_BR;

        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale)
                ?: $this->translations->firstWhere(
                    'locale',
                    PublicationTranslation::LOCALE_PT_BR
                );
        }

        return $this->translations()
            ->where('locale', $locale)
            ->first()
            ?: $this->translations()
                ->where('locale', PublicationTranslation::LOCALE_PT_BR)
                ->first();
    }

    public function translatedTitle(?string $locale = null): string
    {
        return $this->translationFor($locale)?->title ?: $this->title;
    }

    public function translatedSlug(?string $locale = null): string
    {
        return $this->translationFor($locale)?->slug ?: $this->slug;
    }

    public function translatedContent(?string $locale = null): string
    {
        return $this->translationFor($locale)?->content ?: $this->content;
    }

    public function translatedSummary(?string $locale = null): ?string
    {
        return $this->translationFor($locale)?->summary;
    }
}
