<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Fasilitas extends Model
{
    use HasFactory;

    protected $table = 'fasilitas';

    protected $fillable = ['nama_fasilitas'];

    public function kamars(): BelongsToMany
    {
        return $this->belongsToMany(Kamar::class, 'kamar_fasilitas')->withPivot('id');
    }
}
