<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranAwal extends Model
{
    use HasFactory;

    public const STATUS_BELUM_BAYAR = 'belum_bayar';
    public const STATUS_MENUNGGU = 'menunggu_konfirmasi';
    public const STATUS_LUNAS = 'lunas';
    public const STATUS_DITOLAK = 'ditolak';

    public const STATUSES = [
        self::STATUS_BELUM_BAYAR,
        self::STATUS_MENUNGGU,
        self::STATUS_LUNAS,
        self::STATUS_DITOLAK,
    ];

    protected $fillable = [
        'pemesanan_id',
        'jumlah_bayar',
        'tanggal_bayar',
        'bukti_bayar',
        'status_pembayaran',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
    ];

    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class);
    }

    public function getBuktiUrlAttribute(): ?string
    {
        return $this->bukti_bayar ? route('bukti-pembayaran.show', ['path' => $this->bukti_bayar]) : null;
    }

    public function getJumlahFormatAttribute(): string
    {
        return 'Rp '.number_format($this->jumlah_bayar, 0, ',', '.');
    }
}
