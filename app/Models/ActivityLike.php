<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'visitor_id',
        'ip_address',
        'user_agent',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
