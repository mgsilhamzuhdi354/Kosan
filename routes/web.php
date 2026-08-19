<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserAccountController;
use App\Http\Controllers\FasilitasController;
use App\Http\Controllers\FavoritKamarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\KeluhanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PembayaranAwalController;
use App\Http\Controllers\PembayaranBulananController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\PenghuniController;
use App\Http\Controllers\PenyediaDashboardController;
use App\Http\Controllers\PenyediaKeuanganController;
use App\Http\Controllers\PenyediaKosController;
use App\Http\Controllers\PenyewaController;
use App\Http\Controllers\PenyewaDashboardController;
use App\Http\Controllers\PenyewaProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TagihanBulananController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/kamar', [HomeController::class, 'kamarIndex'])->name('public.kamar.index');
Route::get('/kamar/{kamar}', [HomeController::class, 'kamarShow'])->name('public.kamar.show');
Route::get('/maps', [HomeController::class, 'maps'])->name('public.maps');

Route::get('/dashboard', function () {
    $user = auth()->user();

    return redirect()->route(match (true) {
        $user->isAdmin() => 'admin.dashboard',
        $user->isPenyediaKos() => 'penyedia.dashboard',
        default => 'penyewa.dashboard',
    });
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth', 'role:penyedia_kos'])->prefix('penyedia')->name('penyedia.')->group(function () {
    Route::get('/dashboard', PenyediaDashboardController::class)->name('dashboard');
    Route::get('/kos', [PenyediaKosController::class, 'index'])->name('kos.index');
    Route::get('/kos/create', [PenyediaKosController::class, 'create'])->name('kos.create');
    Route::post('/kos', [PenyediaKosController::class, 'store'])->name('kos.store');
    Route::get('/kos/{kos}/edit', [PenyediaKosController::class, 'edit'])->name('kos.edit');
    Route::put('/kos/{kos}', [PenyediaKosController::class, 'update'])->name('kos.update');
    Route::delete('/kos/{kos}', [PenyediaKosController::class, 'destroy'])->name('kos.destroy');
    Route::get('/fasilitas', [FasilitasController::class, 'penyediaIndex'])->name('fasilitas.index');
    Route::get('/fasilitas/create', [FasilitasController::class, 'penyediaCreate'])->name('fasilitas.create');
    Route::post('/fasilitas', [FasilitasController::class, 'penyediaStore'])->name('fasilitas.store');
    Route::get('/fasilitas/{fasilitas}/edit', [FasilitasController::class, 'penyediaEdit'])->name('fasilitas.edit');
    Route::put('/fasilitas/{fasilitas}', [FasilitasController::class, 'penyediaUpdate'])->name('fasilitas.update');
    Route::delete('/fasilitas/{fasilitas}', [FasilitasController::class, 'penyediaDestroy'])->name('fasilitas.destroy');
    Route::get('/kamar', [KamarController::class, 'penyediaIndex'])->name('kamar.index');
    Route::get('/kamar/create', [KamarController::class, 'penyediaCreate'])->name('kamar.create');
    Route::post('/kamar', [KamarController::class, 'penyediaStore'])->name('kamar.store');
    Route::get('/kamar/{kamar}', [KamarController::class, 'penyediaShow'])->name('kamar.show');
    Route::get('/kamar/{kamar}/edit', [KamarController::class, 'penyediaEdit'])->name('kamar.edit');
    Route::put('/kamar/{kamar}', [KamarController::class, 'penyediaUpdate'])->name('kamar.update');
    Route::delete('/kamar/{kamar}', [KamarController::class, 'penyediaDestroy'])->name('kamar.destroy');
    Route::get('/pemesanan', [PemesananController::class, 'penyediaIndex'])->name('pemesanan.index');
    Route::get('/pemesanan/{pemesanan}', [PemesananController::class, 'penyediaShow'])->name('pemesanan.show');
    Route::patch('/pemesanan/{pemesanan}/terima', [PemesananController::class, 'penyediaApprove'])->name('pemesanan.approve');
    Route::patch('/pemesanan/{pemesanan}/tolak', [PemesananController::class, 'penyediaReject'])->name('pemesanan.reject');
    Route::get('/keuangan', PenyediaKeuanganController::class)->name('keuangan.index');
    Route::patch('/pembayaran-awal/{pembayaranAwal}/setujui', [PembayaranAwalController::class, 'penyediaApprove'])->name('pembayaran-awal.approve');
    Route::patch('/pembayaran-awal/{pembayaranAwal}/tolak', [PembayaranAwalController::class, 'penyediaReject'])->name('pembayaran-awal.reject');
    Route::patch('/pembayaran-bulanan/{pembayaranBulanan}/setujui', [PembayaranBulananController::class, 'penyediaApprove'])->name('pembayaran-bulanan.approve');
    Route::patch('/pembayaran-bulanan/{pembayaranBulanan}/tolak', [PembayaranBulananController::class, 'penyediaReject'])->name('pembayaran-bulanan.reject');
    Route::get('/pembayaran-bulanan/{pembayaranBulanan}/bukti', [PembayaranBulananController::class, 'receipt'])->name('pembayaran-bulanan.receipt');
});

Route::middleware('auth')->group(function () {
    Route::get('/bukti-pembayaran/{path}', [PaymentProofController::class, 'show'])
        ->where('path', '.*')
        ->name('bukti-pembayaran.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::get('/akun', [AdminUserAccountController::class, 'index'])->name('akun.index');
    Route::patch('/akun/{user}/reset-password', [AdminUserAccountController::class, 'resetPassword'])->name('akun.reset-password');
    Route::resource('kamar', KamarController::class);
    Route::resource('fasilitas', FasilitasController::class)->except(['show'])->parameters(['fasilitas' => 'fasilitas']);
    Route::get('/penyewa', [PenyewaController::class, 'index'])->name('penyewa.index');
    Route::get('/penyewa/{penyewa}', [PenyewaController::class, 'show'])->name('penyewa.show');

    Route::get('/pemesanan', [PemesananController::class, 'adminIndex'])->name('pemesanan.index');
    Route::get('/pemesanan/{pemesanan}', [PemesananController::class, 'adminShow'])->name('pemesanan.show');
    Route::patch('/pemesanan/{pemesanan}/terima', [PemesananController::class, 'approve'])->name('pemesanan.approve');
    Route::patch('/pemesanan/{pemesanan}/tolak', [PemesananController::class, 'reject'])->name('pemesanan.reject');

    Route::get('/pembayaran-awal', [PembayaranAwalController::class, 'adminIndex'])->name('pembayaran-awal.index');
    Route::patch('/pembayaran-awal/{pembayaranAwal}/setujui', [PembayaranAwalController::class, 'approve'])->name('pembayaran-awal.approve');
    Route::patch('/pembayaran-awal/{pembayaranAwal}/tolak', [PembayaranAwalController::class, 'reject'])->name('pembayaran-awal.reject');

    Route::get('/penghuni', [PenghuniController::class, 'index'])->name('penghuni.index');
    Route::get('/penghuni/{penghuni}', [PenghuniController::class, 'show'])->name('penghuni.show');
    Route::patch('/penghuni/{penghuni}/keluar', [PenghuniController::class, 'keluar'])->name('penghuni.keluar');

    Route::get('/tagihan-bulanan', [TagihanBulananController::class, 'adminIndex'])->name('tagihan-bulanan.index');
    Route::post('/tagihan-bulanan/generate', [TagihanBulananController::class, 'generate'])->name('tagihan-bulanan.generate');

    Route::get('/pembayaran-bulanan', [PembayaranBulananController::class, 'adminIndex'])->name('pembayaran-bulanan.index');
    Route::patch('/pembayaran-bulanan/{pembayaranBulanan}/setujui', [PembayaranBulananController::class, 'approve'])->name('pembayaran-bulanan.approve');
    Route::patch('/pembayaran-bulanan/{pembayaranBulanan}/tolak', [PembayaranBulananController::class, 'reject'])->name('pembayaran-bulanan.reject');
    Route::get('/pembayaran-bulanan/{pembayaranBulanan}/bukti', [PembayaranBulananController::class, 'receipt'])->name('pembayaran-bulanan.receipt');

    Route::get('/keluhan', [KeluhanController::class, 'adminIndex'])->name('keluhan.index');
    Route::get('/keluhan/{keluhan}', [KeluhanController::class, 'adminShow'])->name('keluhan.show');
    Route::patch('/keluhan/{keluhan}', [KeluhanController::class, 'updateStatus'])->name('keluhan.update-status');

    Route::get('/laporan/{type?}', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/cetak-pdf/{type}', [LaporanController::class, 'pdf'])->name('laporan.pdf');
});

Route::middleware(['auth', 'role:penyewa'])->prefix('penyewa')->name('penyewa.')->group(function () {
    Route::get('/dashboard', PenyewaDashboardController::class)->name('dashboard');
    Route::get('/profil', [PenyewaProfileController::class, 'edit'])->name('profil.edit');
    Route::patch('/profil', [PenyewaProfileController::class, 'update'])->name('profil.update');

    Route::get('/kamar', [KamarController::class, 'penyewaIndex'])->name('kamar.index');
    Route::get('/kamar/{kamar}', [KamarController::class, 'penyewaShow'])->name('kamar.show');
    Route::get('/kamar/{kamar}/pesan', [PemesananController::class, 'create'])->name('pemesanan.create');
    Route::post('/kamar/{kamar}/pesan', [PemesananController::class, 'store'])->name('pemesanan.store');

    Route::get('/pemesanan', [PemesananController::class, 'penyewaIndex'])->name('pemesanan.index');
    Route::get('/pemesanan/{pemesanan}', [PemesananController::class, 'show'])->name('pemesanan.show');
    Route::patch('/pemesanan/{pemesanan}/batal', [PemesananController::class, 'cancel'])->name('pemesanan.cancel');

    Route::get('/pembayaran-awal', [PembayaranAwalController::class, 'penyewaIndex'])->name('pembayaran-awal.index');
    Route::get('/pemesanan/{pemesanan}/pembayaran-awal', [PembayaranAwalController::class, 'create'])->name('pembayaran-awal.create');
    Route::post('/pemesanan/{pemesanan}/pembayaran-awal', [PembayaranAwalController::class, 'store'])->name('pembayaran-awal.store');

    Route::get('/tagihan', [TagihanBulananController::class, 'penyewaIndex'])->name('tagihan.index');
    Route::get('/tagihan/{tagihan}/bayar', [TagihanBulananController::class, 'bayar'])->name('tagihan.bayar');
    Route::post('/tagihan/{tagihan}/bayar', [PembayaranBulananController::class, 'store'])->name('tagihan.store-payment');

    Route::get('/riwayat-pembayaran', [PembayaranBulananController::class, 'riwayat'])->name('riwayat-pembayaran.index');
    Route::get('/riwayat-pembayaran/{pembayaranBulanan}/bukti', [PembayaranBulananController::class, 'receipt'])->name('riwayat-pembayaran.receipt');

    Route::get('/favorit', [FavoritKamarController::class, 'index'])->name('favorit.index');
    Route::post('/favorit/{kamar}', [FavoritKamarController::class, 'store'])->name('favorit.store');
    Route::delete('/favorit/{kamar}', [FavoritKamarController::class, 'destroy'])->name('favorit.destroy');

    Route::get('/keluhan', [KeluhanController::class, 'penyewaIndex'])->name('keluhan.index');
    Route::get('/keluhan/create', [KeluhanController::class, 'create'])->name('keluhan.create');
    Route::post('/keluhan', [KeluhanController::class, 'store'])->name('keluhan.store');
    Route::get('/keluhan/{keluhan}', [KeluhanController::class, 'penyewaShow'])->name('keluhan.show');
});

require __DIR__.'/auth.php';
