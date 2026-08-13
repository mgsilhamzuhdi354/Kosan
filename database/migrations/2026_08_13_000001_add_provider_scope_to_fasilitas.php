<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fasilitas', function (Blueprint $table) {
            $table->dropUnique('fasilitas_nama_fasilitas_unique');
            $table->foreignId('penyedia_kos_id')->nullable()->after('id')->constrained('penyedia_kos')->nullOnDelete();
            $table->unique(['penyedia_kos_id', 'nama_fasilitas'], 'fasilitas_penyedia_kos_id_nama_unique');
        });
    }

    public function down(): void
    {
        Schema::table('fasilitas', function (Blueprint $table) {
            $table->dropUnique('fasilitas_penyedia_kos_id_nama_unique');
            $table->dropConstrainedForeignId('penyedia_kos_id');
            $table->unique('nama_fasilitas', 'fasilitas_nama_fasilitas_unique');
        });
    }
};
