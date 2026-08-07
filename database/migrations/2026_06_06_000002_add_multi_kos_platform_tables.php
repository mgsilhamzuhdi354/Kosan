<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyedia_kos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nama_lengkap');
            $table->string('no_hp', 30);
            $table->text('alamat');
            $table->timestamps();
        });

        Schema::create('kos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyedia_kos_id')->constrained('penyedia_kos')->cascadeOnDelete();
            $table->string('nama_kos');
            $table->text('alamat');
            $table->string('kota')->default('Betung');
            $table->text('deskripsi');
            $table->string('foto')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status')->default('aktif')->index();
            $table->boolean('is_promoted')->default(false)->index();
            $table->timestamps();
        });

        Schema::table('kamars', function (Blueprint $table) {
            $table->foreignId('kos_id')->nullable()->after('id')->constrained('kos')->nullOnDelete();
        });

        Schema::create('favorit_kamars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyewa_id')->constrained('penyewas')->cascadeOnDelete();
            $table->foreignId('kamar_id')->constrained('kamars')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['penyewa_id', 'kamar_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorit_kamars');

        Schema::table('kamars', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kos_id');
        });

        Schema::dropIfExists('kos');
        Schema::dropIfExists('penyedia_kos');
    }
};
