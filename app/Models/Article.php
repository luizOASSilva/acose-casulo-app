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
        $query = $this->with([
            'publication.media.translations',
            'publication.admin',
            'publication.translations',
            'keywords.translations',
        ]);

        if (is_numeric($value)) {
            return $query->findOrFail($value);
        }

        return $query
            ->whereHas('publication', function ($publicationQuery) use ($value) {
                $publicationQuery->where('slug', $value);
            })
            ->orWhereHas('publication.translations', function ($translationQuery) use ($value) {
                $translationQuery->where('slug', $value);
            })
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
