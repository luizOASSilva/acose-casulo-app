<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory;

    public const ROLE_MASTER = 'master';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name',
        'email',
        'role',
        'is_active',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isMaster(): bool
    {
        return $this->role === self::ROLE_MASTER;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'admin_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'admin_id');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class, 'admin_id');
    }
}
