<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\PembayaranAwal;
use App\Models\PembayaranBulanan;
use App\Models\Pemesanan;
use App\Models\Penghuni;
use App\Models\Penyewa;
use App\Models\TagihanBulanan;
use Carbon\Carbon;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $this->markOverdueBills();

        $bulanIni = now()->month;
        $tahunIni = now()->year;

        $pendapatanBulanIni = PembayaranAwal::where('status_pembayaran', PembayaranAwal::STATUS_LUNAS)
                ->whereMonth('tanggal_bayar', $bulanIni)
                ->whereYear('tanggal_bayar', $tahunIni)
                ->sum('jumlah_bayar')
            + PembayaranBulanan::where('status_pembayaran', PembayaranBulanan::STATUS_LUNAS)
                ->whereMonth('tanggal_bayar', $bulanIni)
                ->whereYear('tanggal_bayar', $tahunIni)
                ->sum('jumlah_bayar');

        $pendapatanTotal = PembayaranAwal::where('status_pembayaran', PembayaranAwal::STATUS_LUNAS)->sum('jumlah_bayar')
            + PembayaranBulanan::where('status_pembayaran', PembayaranBulanan::STATUS_LUNAS)->sum('jumlah_bayar');

        $stats = [
            'total_kamar' => Kamar::count(),
            'kamar_tersedia' => Kamar::where('status', Kamar::STATUS_TERSEDIA)->count(),
            'kamar_dipesan' => Kamar::where('status', Kamar::STATUS_DIPESAN)->count(),
            'kamar_terisi' => Kamar::where('status', Kamar::STATUS_TERISI)->count(),
            'kamar_maintenance' => Kamar::where('status', Kamar::STATUS_MAINTENANCE)->count(),
            'total_penyewa' => Penyewa::count(),
            'penghuni_aktif' => Penghuni::where('status_penghuni', Penghuni::STATUS_AKTIF)->count(),
            'pemesanan_masuk' => Pemesanan::where('status_pemesanan', Pemesanan::STATUS_MENUNGGU)->count(),
            'dp_menunggu' => PembayaranAwal::where('status_pembayaran', PembayaranAwal::STATUS_MENUNGGU)->count(),
            'bulanan_menunggu' => PembayaranBulanan::where('status_pembayaran', PembayaranBulanan::STATUS_MENUNGGU)->count(),
            'tagihan_belum_bayar' => TagihanBulanan::where('status_tagihan', TagihanBulanan::STATUS_BELUM_BAYAR)->count(),
            'tagihan_terlambat' => TagihanBulanan::where('status_tagihan', TagihanBulanan::STATUS_TERLAMBAT)->count(),
            'pendapatan_bulan_ini' => $pendapatanBulanIni,
            'pendapatan_total' => $pendapatanTotal,
        ];

        $pendapatanBulanan = collect(range(5, 0))->map(function ($offset) {
            $date = now()->subMonths($offset);
            $awal = PembayaranAwal::where('status_pembayaran', PembayaranAwal::STATUS_LUNAS)
                ->whereMonth('tanggal_bayar', $date->month)
                ->whereYear('tanggal_bayar', $date->year)
                ->sum('jumlah_bayar');
            $bulanan = PembayaranBulanan::where('status_pembayaran', PembayaranBulanan::STATUS_LUNAS)
                ->whereMonth('tanggal_bayar', $date->month)
                ->whereYear('tanggal_bayar', $date->year)
                ->sum('jumlah_bayar');

            return [
                'label' => $date->translatedFormat('M Y'),
                'value' => $awal + $bulanan,
            ];
        });

        $statusKamar = collect(Kamar::STATUSES)->mapWithKeys(fn ($status) => [
            $status => Kamar::where('status', $status)->count(),
        ]);

        $reminders = [
            'belum_bayar' => TagihanBulanan::with('penghuni.penyewa')->where('status_tagihan', TagihanBulanan::STATUS_BELUM_BAYAR)->latest()->take(5)->get(),
            'mendekati_jatuh_tempo' => TagihanBulanan::with('penghuni.penyewa')
                ->whereIn('status_tagihan', [TagihanBulanan::STATUS_BELUM_BAYAR, TagihanBulanan::STATUS_DITOLAK])
                ->whereBetween('tanggal_jatuh_tempo', [Carbon::today(), Carbon::today()->addDays(5)])
                ->get(),
        ];

        return view('admin.dashboard', compact('stats', 'pendapatanBulanan', 'statusKamar', 'reminders'));
    }

    private function markOverdueBills(): void
    {
        TagihanBulanan::whereIn('status_tagihan', [TagihanBulanan::STATUS_BELUM_BAYAR, TagihanBulanan::STATUS_DITOLAK])
            ->whereDate('tanggal_jatuh_tempo', '<', today())
            ->update(['status_tagihan' => TagihanBulanan::STATUS_TERLAMBAT]);
    }
}
