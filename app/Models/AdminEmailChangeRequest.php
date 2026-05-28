<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminEmailChangeRequest extends Model
{
    protected $fillable = [
        'target_admin_id',
        'requested_by_admin_id',
        'old_email',
        'new_email',
        'token_hash',
        'expires_at',
        'confirmed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function targetAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'target_admin_id');
    }

    public function requestedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'requested_by_admin_id');
    }

    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }
}
