<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int         $id
 * @property float       $amount
 * @property string      $name
 * @property string      $email
 * @property string|null $cpf
 * @property string|null $phone
 * @property string|null $zip_code
 * @property string|null $street
 * @property string|null $number
 * @property string|null $complement
 * @property string|null $neighborhood
 * @property string|null $city
 * @property string|null $state
 * @property string|null $size
 * @property string|null $payment_id
 * @property string      $status       pending|approved|expired|cancelled
 * @property string|null $pix_copy_paste
 * @property string|null $pix_qr_code
 * @property bool        $has_gift
 * @property string|null $gift_status
 * @property \Carbon\Carbon|null $pix_expires_at
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 */
class Donation extends Model
{
    // ── Status ─────────────────────────────────────────────────────────────
    const STATUS_PENDING   = 'pending';
    const STATUS_APPROVED  = 'approved';
    const STATUS_EXPIRED   = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    // ── Fillable ────────────────────────────────────────────────────────────
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
        'amount'         => 'decimal:2',
        'has_gift'       => 'boolean',
        'pix_expires_at' => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $q): Builder
    {
        return $q->where('status', self::STATUS_APPROVED);
    }

    /** Doações pendentes cujo PIX já passou do prazo de validade */
    public function scopePixExpired(Builder $q): Builder
    {
        return $q->pending()->where('pix_expires_at', '<', now());
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

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
