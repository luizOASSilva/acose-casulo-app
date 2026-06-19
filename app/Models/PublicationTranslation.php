<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicationTranslation extends Model
{
    public const LOCALE_PT_BR = 'pt-BR';
    public const LOCALE_EN = 'en';

    public const STATUS_ORIGINAL = 'original';
    public const STATUS_PENDING = 'pending';
    public const STATUS_TRANSLATED = 'translated';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'publication_id',
        'locale',
        'title',
        'slug',
        'content',
        'summary',
        'translation_status',
        'translated_at',
    ];

    protected $casts = [
        'translated_at' => 'datetime',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function isTranslated(): bool
    {
        return $this->translation_status === self::STATUS_TRANSLATED;
    }

    public function isPending(): bool
    {
        return $this->translation_status === self::STATUS_PENDING;
    }

    public function isFailed(): bool
    {
        return $this->translation_status === self::STATUS_FAILED;
    }
}
