<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Keyword extends Model
{
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }
}
