<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Article extends Model
{
    use HasFactory;
    protected $fillable = [
        'summary',
        'publication_id',
    ];

    public function resolveRouteBinding($value, $field = null): ?self
    {
        return $this->whereHas('publication', function ($query) use ($value) {
                $query->where('slug', $value);
            })
            ->with('publication.media', 'keywords')
            ->firstOrFail();
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class);
    }
}
