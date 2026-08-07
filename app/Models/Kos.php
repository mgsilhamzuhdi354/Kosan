<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Kos extends Model
{
    protected $table = 'kos';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    protected $fillable = [
        'penyedia_kos_id',
        'nama_kos',
        'alamat',
        'kota',
        'deskripsi',
        'foto',
        'latitude',
        'longitude',
        'status',
        'is_promoted',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_promoted' => 'boolean',
    ];

    public function penyediaKos(): BelongsTo
    {
        return $this->belongsTo(PenyediaKos::class);
    }

    public function kamars(): HasMany
    {
        return $this->hasMany(Kamar::class);
    }

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto) {
            return Storage::disk('public')->url($this->foto);
        }

        return 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1200&q=80';
    }
}
