<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'amount' => $this->amount,
            'status' => $this->status,
            'size' => $this->size,
            'has_gift' => $this->has_gift,
            'gift_status' => $this->gift_status,
            'created_at' => $this->created_at,
            'pix_expires_at' => $this->pix_expires_at,
        ];
    }
}
