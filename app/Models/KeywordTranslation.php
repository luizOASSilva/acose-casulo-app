<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeywordTranslation extends Model
{
    public const LOCALE_PT_BR = 'pt-BR';
    public const LOCALE_EN = 'en';

    public const STATUS_ORIGINAL = 'original';
    public const STATUS_PENDING = 'pending';
    public const STATUS_TRANSLATED = 'translated';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'keyword_id',
        'locale',
        'word',
        'translation_status',
        'translated_at',
    ];

    protected $casts = [
        'translated_at' => 'datetime',
    ];

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }
}
