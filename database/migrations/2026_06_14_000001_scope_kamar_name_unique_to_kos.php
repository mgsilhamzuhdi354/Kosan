<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kamars', function (Blueprint $table) {
            $table->dropUnique('kamars_nama_kamar_unique');
        });

        Schema::table('kamars', function (Blueprint $table) {
            $table->unique(['kos_id', 'nama_kamar'], 'kamars_kos_id_nama_kamar_unique');
        });
    }

    public function down(): void
    {
        Schema::table('kamars', function (Blueprint $table) {
            $table->dropUnique('kamars_kos_id_nama_kamar_unique');
        });

        Schema::table('kamars', function (Blueprint $table) {
            $table->unique('nama_kamar', 'kamars_nama_kamar_unique');
        });
    }
};
