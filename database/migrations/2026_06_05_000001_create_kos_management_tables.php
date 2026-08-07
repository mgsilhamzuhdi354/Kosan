<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyewas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nama_lengkap');
            $table->string('no_hp', 30);
            $table->text('alamat');
            $table->string('jenis_kelamin');
            $table->timestamps();
        });

        Schema::create('kamars', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kamar')->unique();
            $table->string('tipe_kamar');
            $table->unsignedBigInteger('harga_bulanan');
            $table->text('deskripsi');
            $table->string('foto')->nullable();
            $table->string('status')->default('tersedia')->index();
            $table->timestamps();
        });

        Schema::create('fasilitas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_fasilitas')->unique();
            $table->timestamps();
        });

        Schema::create('kamar_fasilitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamar_id')->constrained('kamars')->cascadeOnDelete();
            $table->foreignId('fasilitas_id')->constrained('fasilitas')->cascadeOnDelete();
            $table->unique(['kamar_id', 'fasilitas_id']);
        });

        Schema::create('pemesanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyewa_id')->constrained('penyewas')->cascadeOnDelete();
            $table->foreignId('kamar_id')->constrained('kamars')->restrictOnDelete();
            $table->date('tanggal_pesan');
            $table->date('tanggal_masuk');
            $table->string('status_pemesanan')->default('menunggu_konfirmasi')->index();
            $table->text('catatan_penyewa')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });

        Schema::create('pembayaran_awals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->unique()->constrained('pemesanans')->cascadeOnDelete();
            $table->unsignedBigInteger('jumlah_bayar')->default(0);
            $table->date('tanggal_bayar')->nullable();
            $table->string('bukti_bayar')->nullable();
            $table->string('status_pembayaran')->default('belum_bayar')->index();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });

        Schema::create('penghunis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyewa_id')->constrained('penyewas')->cascadeOnDelete();
            $table->foreignId('kamar_id')->constrained('kamars')->restrictOnDelete();
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->unsignedBigInteger('harga_bulanan');
            $table->date('tanggal_jatuh_tempo');
            $table->string('status_penghuni')->default('aktif')->index();
            $table->timestamps();
        });

        Schema::create('tagihan_bulanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penghuni_id')->constrained('penghunis')->cascadeOnDelete();
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->unsignedBigInteger('jumlah_tagihan');
            $table->date('tanggal_jatuh_tempo');
            $table->string('status_tagihan')->default('belum_bayar')->index();
            $table->timestamps();
            $table->unique(['penghuni_id', 'bulan', 'tahun']);
        });

        Schema::create('pembayaran_bulanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_bulanan_id')->unique()->constrained('tagihan_bulanans')->cascadeOnDelete();
            $table->date('tanggal_bayar');
            $table->unsignedBigInteger('jumlah_bayar');
            $table->string('bukti_bayar');
            $table->string('status_pembayaran')->default('menunggu_konfirmasi')->index();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });

        Schema::create('keluhans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penghuni_id')->constrained('penghunis')->cascadeOnDelete();
            $table->string('kategori');
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('foto')->nullable();
            $table->string('status_keluhan')->default('dikirim')->index();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keluhans');
        Schema::dropIfExists('pembayaran_bulanans');
        Schema::dropIfExists('tagihan_bulanans');
        Schema::dropIfExists('penghunis');
        Schema::dropIfExists('pembayaran_awals');
        Schema::dropIfExists('pemesanans');
        Schema::dropIfExists('kamar_fasilitas');
        Schema::dropIfExists('fasilitas');
        Schema::dropIfExists('kamars');
        Schema::dropIfExists('penyewas');
    }
};
