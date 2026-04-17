<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Publication extends Model
{
    protected $fillable = [
        'title',
        'content',
        'image_url',
        'image_description',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function article(): HasOne
    {
        return $this->hasOne(Article::class);
    }

    public function post(): HasOne
    {
        return $this->hasOne(Post::class);
    }
}
