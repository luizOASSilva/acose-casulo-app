<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'likes',
        'publication_id',
    ];

    public function resolveRouteBinding($value, $field = null): ?self
    {
        return $this->whereHas('publication', function ($query) use ($value) {
                $query->where('slug', $value);
            })
            ->with('publication.media')
            ->firstOrFail();
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }
}
