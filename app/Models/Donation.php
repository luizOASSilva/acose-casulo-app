<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_EXPIRED = 'expired';

    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'amount',
        'name',
        'email',
        'cpf',
        'phone',
        'zip_code',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'size',
        'payment_id',
        'status',
        'pix_copy_paste',
        'pix_qr_code',
        'pix_expires_at',
        'has_gift',
        'gift_status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'has_gift' => 'boolean',
        'pix_expires_at' => 'datetime',
    ];

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_APPROVED);
    }

    public function scopePixExpired(Builder $q): Builder
    {
        return $q->pending()->where('pix_expires_at', '<', now()->subMinutes(2));
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || ($this->pix_expires_at && $this->pix_expires_at->isPast());
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function markExpired(): bool
    {
        return $this->update(['status' => self::STATUS_EXPIRED]);
    }
}
