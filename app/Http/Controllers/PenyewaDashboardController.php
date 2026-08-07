<?php

namespace App\Http\Controllers;

use App\Models\PembayaranAwal;
use App\Models\PembayaranBulanan;
use App\Models\Pemesanan;
use App\Models\TagihanBulanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PenyewaDashboardController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        $penyewa = auth()->user()->penyewa;

        if (! $penyewa) {
            return redirect()->route('penyewa.profil.edit')->with('error', 'Lengkapi profil penyewa terlebih dahulu.');
        }

        TagihanBulanan::whereHas('penghuni', fn ($query) => $query->where('penyewa_id', $penyewa->id))
            ->whereIn('status_tagihan', [TagihanBulanan::STATUS_BELUM_BAYAR, TagihanBulanan::STATUS_DITOLAK])
            ->whereDate('tanggal_jatuh_tempo', '<', today())
            ->update(['status_tagihan' => TagihanBulanan::STATUS_TERLAMBAT]);

        $pemesananAktif = Pemesanan::with(['kamar', 'pembayaranAwal'])
            ->where('penyewa_id', $penyewa->id)
            ->latest()
            ->first();

        $penghuniAktif = $penyewa->penghuniAktif()->with('kamar')->first();

        $tagihanAktif = $penghuniAktif
            ? TagihanBulanan::with('pembayaranBulanan')
                ->where('penghuni_id', $penghuniAktif->id)
                ->whereIn('status_tagihan', [
                    TagihanBulanan::STATUS_BELUM_BAYAR,
                    TagihanBulanan::STATUS_TERLAMBAT,
                    TagihanBulanan::STATUS_MENUNGGU,
                    TagihanBulanan::STATUS_DITOLAK,
                ])
                ->orderBy('tanggal_jatuh_tempo')
                ->get()
            : collect();

        $riwayatPembayaran = PembayaranBulanan::with('tagihanBulanan')
            ->whereHas('tagihanBulanan.penghuni', fn ($query) => $query->where('penyewa_id', $penyewa->id))
            ->latest()
            ->take(5)
            ->get();

        $pembayaranAwal = PembayaranAwal::with('pemesanan.kamar')
            ->whereHas('pemesanan', fn ($query) => $query->where('penyewa_id', $penyewa->id))
            ->latest()
            ->first();

        return view('penyewa.dashboard', compact(
            'penyewa',
            'pemesananAktif',
            'penghuniAktif',
            'tagihanAktif',
            'riwayatPembayaran',
            'pembayaranAwal'
        ));
    }
}
