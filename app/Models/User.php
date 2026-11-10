<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name', 'full_name', 'username', 'password', 'password_hash', 'role', 'profile_photo',
    ];

    protected $hidden = [
        'password', 'password_hash', 'remember_token',
    ];

    public const UPDATED_AT = null;

    public function getAuthPassword(): string
    {
        return (string) ($this->password_hash ?? $this->password ?? '');
    }
}
