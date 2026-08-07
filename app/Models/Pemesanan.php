<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pemesanan extends Model
{
    use HasFactory;

    public const STATUS_MENUNGGU = 'menunggu_konfirmasi';
    public const STATUS_DITERIMA = 'diterima';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_DIBATALKAN = 'dibatalkan';
    public const STATUS_SELESAI = 'selesai';

    public const STATUSES = [
        self::STATUS_MENUNGGU,
        self::STATUS_DITERIMA,
        self::STATUS_DITOLAK,
        self::STATUS_DIBATALKAN,
        self::STATUS_SELESAI,
    ];

    protected $fillable = [
        'penyewa_id',
        'kamar_id',
        'tanggal_pesan',
        'tanggal_masuk',
        'status_pemesanan',
        'catatan_penyewa',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal_pesan' => 'date',
        'tanggal_masuk' => 'date',
    ];

    public function penyewa(): BelongsTo
    {
        return $this->belongsTo(Penyewa::class);
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function pembayaranAwal(): HasOne
    {
        return $this->hasOne(PembayaranAwal::class);
    }
}
