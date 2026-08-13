<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\PembayaranAwal;
use App\Models\Pemesanan;
use App\Models\Penghuni;
use App\Models\TagihanBulanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PembayaranAwalController extends Controller
{
    public function adminIndex(Request $request): View
    {
        $pembayarans = PembayaranAwal::with(['pemesanan.penyewa.user', 'pemesanan.kamar'])
            ->when($request->filled('status'), fn ($query) => $query->where('status_pembayaran', $request->status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.pembayaran-awal.index', [
            'pembayarans' => $pembayarans,
            'statuses' => PembayaranAwal::STATUSES,
        ]);
    }

    public function penyewaIndex(): View
    {
        $penyewa = auth()->user()->penyewa;
        $pembayarans = PembayaranAwal::with(['pemesanan.kamar'])
            ->whereHas('pemesanan', fn ($query) => $query->where('penyewa_id', $penyewa->id))
            ->latest()
            ->paginate(10);

        return view('penyewa.pembayaran-awal.index', compact('pembayarans'));
    }

    public function create(Pemesanan $pemesanan): View
    {
        $this->authorizePenyewa($pemesanan);
        abort_if($pemesanan->status_pemesanan !== Pemesanan::STATUS_DITERIMA, 422, 'Pembayaran awal hanya untuk pemesanan diterima.');

        $pembayaran = $pemesanan->pembayaranAwal ?: new PembayaranAwal(['status_pembayaran' => PembayaranAwal::STATUS_BELUM_BAYAR]);

        return view('penyewa.pembayaran-awal.create', compact('pemesanan', 'pembayaran'));
    }

    public function store(Request $request, Pemesanan $pemesanan): RedirectResponse
    {
        $this->authorizePenyewa($pemesanan);
        abort_if($pemesanan->status_pemesanan !== Pemesanan::STATUS_DITERIMA, 422);

        $pembayaran = $pemesanan->pembayaranAwal;
        abort_if(
            $pembayaran && in_array($pembayaran->status_pembayaran, [PembayaranAwal::STATUS_MENUNGGU, PembayaranAwal::STATUS_LUNAS], true),
            422,
            'Pembayaran awal sedang diproses atau sudah lunas.'
        );

        $data = $request->validate([
            'jumlah_bayar' => ['required', 'integer', 'min:1'],
            'tanggal_bayar' => ['required', 'date', 'before_or_equal:today'],
            'bukti_bayar' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $oldProof = $pembayaran?->bukti_bayar;
        $data['bukti_bayar'] = $request->file('bukti_bayar')->store('pembayaran-awal', 'public');
        $data['status_pembayaran'] = PembayaranAwal::STATUS_MENUNGGU;
        $data['catatan_admin'] = null;

        PembayaranAwal::updateOrCreate(['pemesanan_id' => $pemesanan->id], $data);

        if ($oldProof && $oldProof !== $data['bukti_bayar']) {
            Storage::disk('public')->delete($oldProof);
        }

        return redirect()->route('penyewa.pembayaran-awal.index')->with('success', 'Bukti pembayaran awal berhasil diunggah.');
    }

    public function approve(PembayaranAwal $pembayaranAwal): RedirectResponse
    {
        $this->approvePayment($pembayaranAwal);

        return back()->with('success', 'Pembayaran awal disetujui. Penyewa menjadi penghuni aktif.');
    }

    public function reject(Request $request, PembayaranAwal $pembayaranAwal): RedirectResponse
    {
        $data = $request->validate(['catatan_admin' => ['required', 'string', 'max:1000']]);
        $this->rejectPayment($pembayaranAwal, $data['catatan_admin']);

        return back()->with('success', 'Pembayaran awal ditolak.');
    }

    public function penyediaApprove(PembayaranAwal $pembayaranAwal): RedirectResponse
    {
        $this->authorizeOwnedPembayaran($pembayaranAwal);
        $this->approvePayment($pembayaranAwal);

        return back()->with('success', 'Pembayaran awal disetujui. Penyewa menjadi penghuni aktif.');
    }

    public function penyediaReject(Request $request, PembayaranAwal $pembayaranAwal): RedirectResponse
    {
        $this->authorizeOwnedPembayaran($pembayaranAwal);
        $data = $request->validate(['catatan_admin' => ['required', 'string', 'max:1000']]);
        $this->rejectPayment($pembayaranAwal, $data['catatan_admin']);

        return back()->with('success', 'Pembayaran awal ditolak.');
    }

    private function authorizePenyewa(Pemesanan $pemesanan): void
    {
        abort_unless($pemesanan->penyewa_id === auth()->user()->penyewa->id, 403);
    }

    private function approvePayment(PembayaranAwal $pembayaranAwal): void
    {
        DB::transaction(function () use ($pembayaranAwal) {
            $pembayaranAwal->load('pemesanan.kamar', 'pemesanan.penyewa');

            abort_if($pembayaranAwal->status_pembayaran !== PembayaranAwal::STATUS_MENUNGGU, 422);

            $pemesanan = $pembayaranAwal->pemesanan;
            $tanggalMasuk = $pemesanan->tanggal_masuk;
            $jatuhTempo = $tanggalMasuk->copy()->addMonthNoOverflow();

            $pembayaranAwal->update([
                'status_pembayaran' => PembayaranAwal::STATUS_LUNAS,
                'catatan_admin' => null,
            ]);

            $pemesanan->update(['status_pemesanan' => Pemesanan::STATUS_SELESAI]);
            $pemesanan->kamar->update(['status' => Kamar::STATUS_TERISI]);

            $penghuni = Penghuni::firstOrCreate(
                [
                    'penyewa_id' => $pemesanan->penyewa_id,
                    'kamar_id' => $pemesanan->kamar_id,
                    'status_penghuni' => Penghuni::STATUS_AKTIF,
                ],
                [
                    'tanggal_masuk' => $tanggalMasuk,
                    'harga_bulanan' => $pemesanan->kamar->harga_bulanan,
                    'tanggal_jatuh_tempo' => $jatuhTempo,
                ]
            );

            TagihanBulanan::firstOrCreate(
                [
                    'penghuni_id' => $penghuni->id,
                    'bulan' => (int) $jatuhTempo->month,
                    'tahun' => (int) $jatuhTempo->year,
                ],
                [
                    'jumlah_tagihan' => $penghuni->harga_bulanan,
                    'tanggal_jatuh_tempo' => $jatuhTempo,
                    'status_tagihan' => TagihanBulanan::STATUS_BELUM_BAYAR,
                ]
            );
        });
    }

    private function rejectPayment(PembayaranAwal $pembayaranAwal, string $catatanAdmin): void
    {
        abort_if($pembayaranAwal->status_pembayaran !== PembayaranAwal::STATUS_MENUNGGU, 422);

        $pembayaranAwal->update([
            'status_pembayaran' => PembayaranAwal::STATUS_DITOLAK,
            'catatan_admin' => $catatanAdmin,
        ]);
    }

    private function authorizeOwnedPembayaran(PembayaranAwal $pembayaranAwal): void
    {
        $pembayaranAwal->loadMissing('pemesanan.kamar');
        $kosIds = auth()->user()->penyediaKos?->kos()->pluck('id')->map(fn ($id) => (int) $id)->all() ?? [];

        abort_unless(in_array((int) $pembayaranAwal->pemesanan->kamar->kos_id, $kosIds, true), 403);
    }
}
