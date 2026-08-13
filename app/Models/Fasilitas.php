<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Fasilitas extends Model
{
    use HasFactory;

    protected $table = 'fasilitas';

    protected $fillable = ['penyedia_kos_id', 'nama_fasilitas'];

    public function penyediaKos(): BelongsTo
    {
        return $this->belongsTo(PenyediaKos::class);
    }

    public function kamars(): BelongsToMany
    {
        return $this->belongsToMany(Kamar::class, 'kamar_fasilitas')->withPivot('id');
    }

    public function scopeVisibleForPenyedia(Builder $query, int $penyediaId): Builder
    {
        return $query->where(fn (Builder $query) => $query
            ->whereNull('penyedia_kos_id')
            ->orWhere('penyedia_kos_id', $penyediaId)
        );
    }
}
