<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_id',
    ];

    public function resolveRouteBinding($value, $field = null): ?self
    {
        return $this->whereHas('publication', function ($query) use ($value) {
            $query->where('slug', $value);
        })
            ->with([
                'publication.media',
                'publication.admin',
                'schedules',
            ])
            ->firstOrFail();
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ActivitySchedule::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ActivityLike::class);
    }
}

