<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // GUNAKAN PROTECTED ARRAY SEPERTI INI
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // <-- role sudah masuk
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Method sudah benar!
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}