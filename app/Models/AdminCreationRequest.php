<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminCreationRequest extends Model
{
    protected $fillable = [
        'requested_by_admin_id',
        'name',
        'email',
        'role',
        'is_active',
        'token_hash',
        'expires_at',
        'confirmed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

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
