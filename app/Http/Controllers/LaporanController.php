<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\PembayaranAwal;
use App\Models\PembayaranBulanan;
use App\Models\Pemesanan;
use App\Models\Penghuni;
use App\Models\Penyewa;
use App\Models\TagihanBulanan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public const TYPES = [
        'kamar' => 'Laporan Data Kamar',
        'penyewa' => 'Laporan Data Penyewa',
        'penghuni' => 'Laporan Data Penghuni Aktif',
        'pemesanan' => 'Laporan Pemesanan Kamar',
        'pembayaran-awal' => 'Laporan Pembayaran Awal',
        'tagihan-bulanan' => 'Laporan Tagihan Bulanan',
        'pembayaran-bulanan' => 'Laporan Pembayaran Bulanan',
        'terlambat' => 'Laporan Penghuni Terlambat Bayar',
        'pendapatan' => 'Laporan Pendapatan Bulanan',
    ];

    public function index(string $type = 'kamar'): View
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        return view('admin.laporan.index', [
            'types' => self::TYPES,
            'type' => $type,
            'title' => self::TYPES[$type],
        ]);
    }

    public function pdf(Request $request, string $type)
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        $data = $this->dataFor($request, $type);
        $title = self::TYPES[$type];

        return Pdf::loadView('pdf.laporan', [
            'title' => $title,
            'type' => $type,
            'rows' => $data['rows'],
            'totalPendapatan' => $data['totalPendapatan'],
            'filters' => $request->only(['tanggal_awal', 'tanggal_akhir', 'bulan', 'tahun', 'status']),
        ])->download(str($title)->slug('-').'.pdf');
    }

    private function dataFor(Request $request, string $type): array
    {
        $totalPendapatan = 0;

        $dateFilter = function ($query, string $column = 'created_at') use ($request) {
            $query->when($request->filled('tanggal_awal'), fn ($q) => $q->whereDate($column, '>=', $request->tanggal_awal))
                ->when($request->filled('tanggal_akhir'), fn ($q) => $q->whereDate($column, '<=', $request->tanggal_akhir));
        };

        $rows = match ($type) {
            'kamar' => Kamar::with('fasilitas')->orderBy('nama_kamar')->get(),
            'penyewa' => Penyewa::with('user')->latest()->get(),
            'penghuni' => Penghuni::with(['penyewa.user', 'kamar'])->where('status_penghuni', Penghuni::STATUS_AKTIF)->latest()->get(),
            'pemesanan' => Pemesanan::with(['penyewa.user', 'kamar'])
                ->when($request->filled('status'), fn ($q) => $q->where('status_pemesanan', $request->status))
                ->tap(fn ($q) => $dateFilter($q, 'tanggal_pesan'))
                ->latest()->get(),
            'pembayaran-awal' => PembayaranAwal::with(['pemesanan.penyewa.user', 'pemesanan.kamar'])
                ->when($request->filled('status'), fn ($q) => $q->where('status_pembayaran', $request->status))
                ->tap(fn ($q) => $dateFilter($q, 'tanggal_bayar'))
                ->latest()->get(),
            'tagihan-bulanan' => TagihanBulanan::with(['penghuni.penyewa.user', 'penghuni.kamar'])
                ->when($request->filled('bulan'), fn ($q) => $q->where('bulan', (int) $request->bulan))
                ->when($request->filled('tahun'), fn ($q) => $q->where('tahun', (int) $request->tahun))
                ->when($request->filled('status'), fn ($q) => $q->where('status_tagihan', $request->status))
                ->latest()->get(),
            'pembayaran-bulanan', 'pendapatan' => PembayaranBulanan::with(['tagihanBulanan.penghuni.penyewa.user', 'tagihanBulanan.penghuni.kamar'])
                ->when($request->filled('status'), fn ($q) => $q->where('status_pembayaran', $request->status))
                ->tap(fn ($q) => $dateFilter($q, 'tanggal_bayar'))
                ->latest()->get(),
            'terlambat' => TagihanBulanan::with(['penghuni.penyewa.user', 'penghuni.kamar'])
                ->where('status_tagihan', TagihanBulanan::STATUS_TERLAMBAT)
                ->latest()->get(),
        };

        if (in_array($type, ['pembayaran-awal', 'pembayaran-bulanan', 'pendapatan'], true)) {
            $totalPendapatan = $rows->where('status_pembayaran', 'lunas')->sum('jumlah_bayar');
        }

        return compact('rows', 'totalPendapatan');
    }
}
