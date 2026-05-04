<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'featured',
        'order',
    ];

    protected $casts = [
        'featured' => 'boolean',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'category_id');
    }
}
