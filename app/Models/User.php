<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Penyewa;
use App\Models\PenyediaKos;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_PENYEDIA_KOS = 'penyedia_kos';
    public const ROLE_PENYEWA = 'penyewa';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function penyewa(): HasOne
    {
        return $this->hasOne(Penyewa::class);
    }

    public function penyediaKos(): HasOne
    {
        return $this->hasOne(PenyediaKos::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPenyewa(): bool
    {
        return $this->role === self::ROLE_PENYEWA;
    }

    public function isPenyediaKos(): bool
    {
        return $this->role === self::ROLE_PENYEDIA_KOS;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
