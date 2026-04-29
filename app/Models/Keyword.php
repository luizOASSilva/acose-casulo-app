<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Keyword extends Model
{
    use HasFactory;
    protected $fillable = ['word'];
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }
}
