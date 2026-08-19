<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penyewas', function (Blueprint $table) {
            $table->string('kode_penyewa', 20)->nullable()->unique()->after('id');
        });

        Schema::table('keluhans', function (Blueprint $table) {
            $table->string('kode_keluhan', 20)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('keluhans', function (Blueprint $table) {
            $table->dropUnique(['kode_keluhan']);
            $table->dropColumn('kode_keluhan');
        });

        Schema::table('penyewas', function (Blueprint $table) {
            $table->dropUnique(['kode_penyewa']);
            $table->dropColumn('kode_penyewa');
        });
    }
};
