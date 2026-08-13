<?php

namespace App\Http\Controllers;

use App\Models\PembayaranAwal;
use App\Models\PembayaranBulanan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenyediaKeuanganController extends Controller
{
    public function __invoke(Request $request): View
    {
        $kosIds = $request->user()->penyediaKos()->firstOrFail()->kos()->pluck('id');
        $status = $request->input('status');

        $pembayaranAwalQuery = PembayaranAwal::whereHas('pemesanan.kamar', fn ($query) => $query->whereIn('kos_id', $kosIds));
        $pembayaranBulananQuery = PembayaranBulanan::whereHas('tagihanBulanan.penghuni.kamar', fn ($query) => $query->whereIn('kos_id', $kosIds));

        $stats = [
            'total_lunas' => (clone $pembayaranAwalQuery)->where('status_pembayaran', PembayaranAwal::STATUS_LUNAS)->sum('jumlah_bayar')
                + (clone $pembayaranBulananQuery)->where('status_pembayaran', PembayaranBulanan::STATUS_LUNAS)->sum('jumlah_bayar'),
            'bulan_ini' => (clone $pembayaranAwalQuery)->where('status_pembayaran', PembayaranAwal::STATUS_LUNAS)->whereMonth('tanggal_bayar', now()->month)->whereYear('tanggal_bayar', now()->year)->sum('jumlah_bayar')
                + (clone $pembayaranBulananQuery)->where('status_pembayaran', PembayaranBulanan::STATUS_LUNAS)->whereMonth('tanggal_bayar', now()->month)->whereYear('tanggal_bayar', now()->year)->sum('jumlah_bayar'),
            'menunggu' => (clone $pembayaranAwalQuery)->where('status_pembayaran', PembayaranAwal::STATUS_MENUNGGU)->count()
                + (clone $pembayaranBulananQuery)->where('status_pembayaran', PembayaranBulanan::STATUS_MENUNGGU)->count(),
        ];

        $pembayaranAwal = (clone $pembayaranAwalQuery)
            ->with(['pemesanan.penyewa.user', 'pemesanan.kamar.kos'])
            ->when($status, fn ($query) => $query->where('status_pembayaran', $status))
            ->latest()
            ->paginate(8, ['*'], 'awal_page')
            ->withQueryString();

        $pembayaranBulanan = (clone $pembayaranBulananQuery)
            ->with(['tagihanBulanan.penghuni.penyewa.user', 'tagihanBulanan.penghuni.kamar.kos'])
            ->when($status, fn ($query) => $query->where('status_pembayaran', $status))
            ->latest()
            ->paginate(8, ['*'], 'bulanan_page')
            ->withQueryString();

        return view('penyedia.keuangan.index', [
            'pembayaranAwal' => $pembayaranAwal,
            'pembayaranBulanan' => $pembayaranBulanan,
            'statuses' => collect(PembayaranAwal::STATUSES)->merge(PembayaranBulanan::STATUSES)->unique()->values(),
            'stats' => $stats,
        ]);
    }
}
