<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $fillable = [
        'amount',
        'name',
        'email',
        'phone',
        'zip_code',
        'street',
        'number',
        'complement',
        'neighborhood',
        'cty',
        'state',
        'payment_id',
        'status',
        'pix_copy_paste',
        'pix_qr_code',
        'has_gift',
        'gift_status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'has_gift' => 'boolean',
    ];

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function hasGiftPending(): bool
    {
        return $this->has_gift && $this->gift_status === 'pending';
    }

}
