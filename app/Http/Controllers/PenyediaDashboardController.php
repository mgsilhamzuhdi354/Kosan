<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\PembayaranAwal;
use App\Models\PembayaranBulanan;
use App\Models\Pemesanan;
use Illuminate\View\View;

class PenyediaDashboardController extends Controller
{
    public function __invoke(): View
    {
        $penyedia = auth()->user()->penyediaKos()->with('kos')->firstOrFail();
        $kosIds = $penyedia->kos->pluck('id');

        $stats = [
            'total_kos' => $penyedia->kos->count(),
            'total_kamar' => Kamar::whereIn('kos_id', $kosIds)->count(),
            'kamar_tersedia' => Kamar::whereIn('kos_id', $kosIds)->where('status', Kamar::STATUS_TERSEDIA)->count(),
            'pemesanan_masuk' => Pemesanan::whereHas('kamar', fn ($query) => $query->whereIn('kos_id', $kosIds))
                ->where('status_pemesanan', Pemesanan::STATUS_MENUNGGU)
                ->count(),
            'dp_menunggu' => PembayaranAwal::whereHas('pemesanan.kamar', fn ($query) => $query->whereIn('kos_id', $kosIds))
                ->where('status_pembayaran', PembayaranAwal::STATUS_MENUNGGU)
                ->count(),
            'uang_masuk' => PembayaranAwal::whereHas('pemesanan.kamar', fn ($query) => $query->whereIn('kos_id', $kosIds))
                ->where('status_pembayaran', PembayaranAwal::STATUS_LUNAS)
                ->sum('jumlah_bayar')
                + PembayaranBulanan::whereHas('tagihanBulanan.penghuni.kamar', fn ($query) => $query->whereIn('kos_id', $kosIds))
                    ->where('status_pembayaran', PembayaranBulanan::STATUS_LUNAS)
                    ->sum('jumlah_bayar'),
        ];

        $kos = $penyedia->kos;

        return view('penyedia.dashboard', compact('penyedia', 'kos', 'stats'));
    }
}
