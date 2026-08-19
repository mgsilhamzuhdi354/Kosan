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
        'penyewa' => 'Laporan Data Penyewa',
        'penyewaan' => 'Report Penyewaan',
        'kamar' => 'Laporan Data Kamar',
        'penghuni' => 'Laporan Data Penghuni Aktif',
        'pemesanan' => 'Laporan Pemesanan Kamar',
        'pembayaran-awal' => 'Laporan Pembayaran Awal',
        'tagihan-bulanan' => 'Laporan Tagihan Bulanan',
        'pembayaran-bulanan' => 'Laporan Pembayaran Bulanan',
        'terlambat' => 'Laporan Penghuni Terlambat Bayar',
        'pendapatan' => 'Laporan Pendapatan Bulanan',
    ];

    public function index(Request $request, string $type = 'penyewa'): View
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        $data = $this->dataFor($request, $type);

        return view('admin.laporan.index', [
            'types' => self::TYPES,
            'type' => $type,
            'title' => self::TYPES[$type],
            'rows' => $data['rows'],
            'totalPendapatan' => $data['totalPendapatan'],
            'summary' => $data['summary'],
            'statusOptions' => $this->statusOptions($type),
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
            'summary' => $data['summary'],
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
            'penyewaan' => Penghuni::with(['penyewa.user', 'kamar.kos', 'tagihanBulanans'])
                ->when($request->filled('status'), fn ($q) => $q->where('status_penghuni', $request->status))
                ->tap(fn ($q) => $dateFilter($q, 'tanggal_masuk'))
                ->latest('tanggal_masuk')->get(),
            'kamar' => Kamar::with('fasilitas')
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->orderBy('nama_kamar')->get(),
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

        if ($type === 'penyewaan') {
            $totalPendapatan = $rows
                ->where('status_penghuni', Penghuni::STATUS_AKTIF)
                ->sum('harga_bulanan');
        }

        $summary = $this->summaryFor($type, $rows, $totalPendapatan);

        return compact('rows', 'totalPendapatan', 'summary');
    }

    private function summaryFor(string $type, $rows, int $totalPendapatan): array
    {
        if ($type === 'penyewaan') {
            return [
                ['label' => 'Total Data Sewa', 'value' => $rows->count()],
                ['label' => 'Penghuni Aktif', 'value' => $rows->where('status_penghuni', Penghuni::STATUS_AKTIF)->count()],
                ['label' => 'Kamar Tersedia', 'value' => Kamar::where('status', Kamar::STATUS_TERSEDIA)->count()],
                ['label' => 'Estimasi Sewa Aktif', 'value' => 'Rp '.number_format($totalPendapatan, 0, ',', '.')],
            ];
        }

        return [
            ['label' => 'Total Data', 'value' => $rows->count()],
            ['label' => 'Kamar Terisi', 'value' => Kamar::where('status', Kamar::STATUS_TERISI)->count()],
            ['label' => 'Pemesanan Menunggu', 'value' => Pemesanan::where('status_pemesanan', Pemesanan::STATUS_MENUNGGU)->count()],
            ['label' => 'Tagihan Terlambat', 'value' => TagihanBulanan::where('status_tagihan', TagihanBulanan::STATUS_TERLAMBAT)->count()],
        ];
    }

    private function statusOptions(string $type): array
    {
        return match ($type) {
            'penyewaan' => Penghuni::STATUSES,
            'pemesanan' => Pemesanan::STATUSES,
            'pembayaran-awal' => PembayaranAwal::STATUSES,
            'tagihan-bulanan' => TagihanBulanan::STATUSES,
            'pembayaran-bulanan', 'pendapatan' => PembayaranBulanan::STATUSES,
            'kamar' => Kamar::STATUSES,
            default => [],
        };
    }
}
