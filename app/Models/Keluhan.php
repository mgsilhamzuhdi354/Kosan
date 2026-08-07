<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Keluhan extends Model
{
    use HasFactory;

    public const STATUS_DIKIRIM = 'dikirim';
    public const STATUS_DIPROSES = 'diproses';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DITOLAK = 'ditolak';

    public const STATUSES = [
        self::STATUS_DIKIRIM,
        self::STATUS_DIPROSES,
        self::STATUS_SELESAI,
        self::STATUS_DITOLAK,
    ];

    public const KATEGORI = [
        'Kamar rusak',
        'Air bermasalah',
        'Listrik bermasalah',
        'Kebersihan',
        'Keamanan',
        'Lainnya',
    ];

    protected $fillable = [
        'penghuni_id',
        'kategori',
        'judul',
        'deskripsi',
        'foto',
        'status_keluhan',
        'catatan_admin',
    ];

    public function penghuni(): BelongsTo
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? Storage::disk('public')->url($this->foto) : null;
    }
}
