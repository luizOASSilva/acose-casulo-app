<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTranslation extends Model
{
    public const LOCALE_PT_BR = 'pt-BR';
    public const LOCALE_EN = 'en';

    public const STATUS_ORIGINAL = 'original';
    public const STATUS_PENDING = 'pending';
    public const STATUS_TRANSLATED = 'translated';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'document_id',
        'locale',
        'title',
        'translation_status',
        'translated_at',
    ];

    protected $casts = [
        'translated_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
