<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TagihanBulanan extends Model
{
    use HasFactory;

    public const STATUS_BELUM_BAYAR = 'belum_bayar';
    public const STATUS_MENUNGGU = 'menunggu_konfirmasi';
    public const STATUS_LUNAS = 'lunas';
    public const STATUS_TERLAMBAT = 'terlambat';
    public const STATUS_DITOLAK = 'ditolak';

    public const STATUSES = [
        self::STATUS_BELUM_BAYAR,
        self::STATUS_MENUNGGU,
        self::STATUS_LUNAS,
        self::STATUS_TERLAMBAT,
        self::STATUS_DITOLAK,
    ];

    protected $fillable = [
        'penghuni_id',
        'bulan',
        'tahun',
        'jumlah_tagihan',
        'tanggal_jatuh_tempo',
        'status_tagihan',
    ];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
    ];

    public function penghuni(): BelongsTo
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function pembayaranBulanan(): HasOne
    {
        return $this->hasOne(PembayaranBulanan::class);
    }

    public function getJumlahFormatAttribute(): string
    {
        return 'Rp '.number_format($this->jumlah_tagihan, 0, ',', '.');
    }

    public function getPeriodeAttribute(): string
    {
        return str_pad((string) $this->bulan, 2, '0', STR_PAD_LEFT).'/'.$this->tahun;
    }
}
