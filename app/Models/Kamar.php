<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Kamar extends Model
{
    use HasFactory;

    public const STATUS_TERSEDIA = 'tersedia';

    public const STATUS_DIPESAN = 'dipesan';

    public const STATUS_TERISI = 'terisi';

    public const STATUS_MAINTENANCE = 'maintenance';

    public const STATUSES = [
        self::STATUS_TERSEDIA,
        self::STATUS_DIPESAN,
        self::STATUS_TERISI,
        self::STATUS_MAINTENANCE,
    ];

    protected $fillable = [
        'kos_id',
        'nama_kamar',
        'tipe_kamar',
        'harga_bulanan',
        'deskripsi',
        'foto',
        'status',
    ];

    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class);
    }

    public function fasilitas(): BelongsToMany
    {
        return $this->belongsToMany(Fasilitas::class, 'kamar_fasilitas')->withPivot('id');
    }

    public function pemesanans(): HasMany
    {
        return $this->hasMany(Pemesanan::class);
    }

    public function penghunis(): HasMany
    {
        return $this->hasMany(Penghuni::class);
    }

    public function favoritKamars(): HasMany
    {
        return $this->hasMany(FavoritKamar::class);
    }

    public function scopeInActiveKos(Builder $query): Builder
    {
        return $query->whereHas('kos', fn (Builder $query) => $query->where('status', Kos::STATUS_AKTIF));
    }

    public function isInActiveKos(): bool
    {
        $this->loadMissing('kos');

        return $this->kos?->status === Kos::STATUS_AKTIF;
    }

    public function isBookable(): bool
    {
        return $this->status === self::STATUS_TERSEDIA && $this->isInActiveKos();
    }

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto) {
            return Storage::disk('public')->url($this->foto);
        }

        return 'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=900&q=80';
    }

    public function getHargaFormatAttribute(): string
    {
        return 'Rp '.number_format($this->harga_bulanan, 0, ',', '.');
    }
}
