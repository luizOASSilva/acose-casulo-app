<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'name',
        'logo_path',
        'logo_alt',
        'website_url',
        'bg_color',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PartnerTranslation::class);
    }

    public function portugueseTranslation(): HasOne
    {
        return $this->hasOne(PartnerTranslation::class)
            ->where('locale', PartnerTranslation::LOCALE_PT_BR);
    }

    public function englishTranslation(): HasOne
    {
        return $this->hasOne(PartnerTranslation::class)
            ->where('locale', PartnerTranslation::LOCALE_EN);
    }

    public function translationFor(?string $locale): ?PartnerTranslation
    {
        $locale = $locale ?: PartnerTranslation::LOCALE_PT_BR;

        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale)
                ?: $this->translations->firstWhere(
                    'locale',
                    PartnerTranslation::LOCALE_PT_BR
                );
        }

        return $this->translations()
            ->where('locale', $locale)
            ->first()
            ?: $this->translations()
                ->where('locale', PartnerTranslation::LOCALE_PT_BR)
                ->first();
    }

    public function translatedLogoAlt(?string $locale = null): ?string
    {
        return $this->translationFor($locale)?->logo_alt ?: $this->logo_alt;
    }
}
