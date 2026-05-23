<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'collection',
        'disk',
        'original_name',
        'filename',
        'path',
        'url',
        'mime_type',
        'size',
        'created_by',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
