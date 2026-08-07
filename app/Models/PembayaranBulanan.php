<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PembayaranBulanan extends Model
{
    use HasFactory;

    public const STATUS_MENUNGGU = 'menunggu_konfirmasi';
    public const STATUS_LUNAS = 'lunas';
    public const STATUS_DITOLAK = 'ditolak';

    public const STATUSES = [
        self::STATUS_MENUNGGU,
        self::STATUS_LUNAS,
        self::STATUS_DITOLAK,
    ];

    protected $fillable = [
        'tagihan_bulanan_id',
        'tanggal_bayar',
        'jumlah_bayar',
        'bukti_bayar',
        'status_pembayaran',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
    ];

    public function tagihanBulanan(): BelongsTo
    {
        return $this->belongsTo(TagihanBulanan::class);
    }

    public function getBuktiUrlAttribute(): ?string
    {
        return $this->bukti_bayar ? Storage::disk('public')->url($this->bukti_bayar) : null;
    }

    public function getJumlahFormatAttribute(): string
    {
        return 'Rp '.number_format($this->jumlah_bayar, 0, ',', '.');
    }
}
