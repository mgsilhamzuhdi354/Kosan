<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penghuni extends Model
{
    use HasFactory;

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_KELUAR = 'keluar';

    public const STATUSES = [
        self::STATUS_AKTIF,
        self::STATUS_KELUAR,
    ];

    protected $fillable = [
        'penyewa_id',
        'kamar_id',
        'tanggal_masuk',
        'tanggal_keluar',
        'harga_bulanan',
        'tanggal_jatuh_tempo',
        'status_penghuni',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_keluar' => 'date',
        'tanggal_jatuh_tempo' => 'date',
    ];

    public function penyewa(): BelongsTo
    {
        return $this->belongsTo(Penyewa::class);
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function tagihanBulanans(): HasMany
    {
        return $this->hasMany(TagihanBulanan::class);
    }

    public function keluhans(): HasMany
    {
        return $this->hasMany(Keluhan::class);
    }

    public function getHargaFormatAttribute(): string
    {
        return 'Rp '.number_format($this->harga_bulanan, 0, ',', '.');
    }
}
