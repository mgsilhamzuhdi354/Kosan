<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\PembayaranAwal;
use App\Models\Pemesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PemesananController extends Controller
{
    public function adminIndex(Request $request): View
    {
        $pemesanans = Pemesanan::with(['penyewa.user', 'kamar', 'pembayaranAwal'])
            ->when($request->filled('status'), fn ($query) => $query->where('status_pemesanan', $request->status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.pemesanans.index', [
            'pemesanans' => $pemesanans,
            'statuses' => Pemesanan::STATUSES,
        ]);
    }

    public function adminShow(Pemesanan $pemesanan): View
    {
        $pemesanan->load(['penyewa.user', 'kamar.fasilitas', 'pembayaranAwal']);

        return view('admin.pemesanans.show', compact('pemesanan'));
    }

    public function approve(Request $request, Pemesanan $pemesanan): RedirectResponse
    {
        $request->validate(['catatan_admin' => ['nullable', 'string', 'max:1000']]);

        DB::transaction(function () use ($request, $pemesanan) {
            $pemesanan->load('kamar');

            abort_if($pemesanan->status_pemesanan !== Pemesanan::STATUS_MENUNGGU, 422, 'Pemesanan tidak bisa dikonfirmasi ulang.');
            abort_if($pemesanan->kamar->status !== Kamar::STATUS_TERSEDIA, 422, 'Kamar sudah tidak tersedia.');

            $pemesanan->update([
                'status_pemesanan' => Pemesanan::STATUS_DITERIMA,
                'catatan_admin' => $request->catatan_admin,
            ]);

            $pemesanan->kamar->update(['status' => Kamar::STATUS_DIPESAN]);

            PembayaranAwal::firstOrCreate(
                ['pemesanan_id' => $pemesanan->id],
                ['status_pembayaran' => PembayaranAwal::STATUS_BELUM_BAYAR]
            );
        });

        return back()->with('success', 'Pemesanan diterima dan kamar berubah menjadi dipesan.');
    }

    public function reject(Request $request, Pemesanan $pemesanan): RedirectResponse
    {
        $request->validate(['catatan_admin' => ['required', 'string', 'max:1000']]);

        DB::transaction(function () use ($request, $pemesanan) {
            abort_if(! in_array($pemesanan->status_pemesanan, [Pemesanan::STATUS_MENUNGGU, Pemesanan::STATUS_DITERIMA], true), 422);

            $pemesanan->load('kamar');
            $pemesanan->update([
                'status_pemesanan' => Pemesanan::STATUS_DITOLAK,
                'catatan_admin' => $request->catatan_admin,
            ]);

            if ($pemesanan->kamar->status === Kamar::STATUS_DIPESAN) {
                $pemesanan->kamar->update(['status' => Kamar::STATUS_TERSEDIA]);
            }
        });

        return back()->with('success', 'Pemesanan ditolak.');
    }

    public function penyewaIndex(): View
    {
        $penyewa = auth()->user()->penyewa;
        $pemesanans = Pemesanan::with(['kamar', 'pembayaranAwal'])
            ->where('penyewa_id', $penyewa->id)
            ->latest()
            ->paginate(10);

        return view('penyewa.pemesanans.index', compact('pemesanans'));
    }

    public function create(Kamar $kamar): View
    {
        abort_unless($kamar->isInActiveKos(), 404);
        abort_unless($kamar->isBookable(), 403, 'Kamar tidak tersedia untuk dipesan.');

        return view('penyewa.pemesanans.create', compact('kamar'));
    }

    public function store(Request $request, Kamar $kamar): RedirectResponse
    {
        abort_unless($kamar->isInActiveKos(), 404);
        abort_unless($kamar->isBookable(), 403, 'Kamar tidak tersedia untuk dipesan.');

        $data = $request->validate([
            'tanggal_masuk' => ['required', 'date', 'after_or_equal:today'],
            'catatan_penyewa' => ['nullable', 'string', 'max:1000'],
        ]);

        $penyewa = $request->user()->penyewa;

        $hasActiveBooking = Pemesanan::where('penyewa_id', $penyewa->id)
            ->whereIn('status_pemesanan', [Pemesanan::STATUS_MENUNGGU, Pemesanan::STATUS_DITERIMA])
            ->exists();

        abort_if($hasActiveBooking || $penyewa->penghuniAktif()->exists(), 422, 'Anda masih memiliki pemesanan atau hunian aktif.');

        $hasRoomBooking = Pemesanan::where('kamar_id', $kamar->id)
            ->whereIn('status_pemesanan', [Pemesanan::STATUS_MENUNGGU, Pemesanan::STATUS_DITERIMA])
            ->exists();

        abort_if($hasRoomBooking, 422, 'Kamar sedang dalam proses pemesanan.');

        Pemesanan::create([
            'penyewa_id' => $penyewa->id,
            'kamar_id' => $kamar->id,
            'tanggal_pesan' => today(),
            'tanggal_masuk' => $data['tanggal_masuk'],
            'status_pemesanan' => Pemesanan::STATUS_MENUNGGU,
            'catatan_penyewa' => $data['catatan_penyewa'] ?? null,
        ]);

        return redirect()->route('penyewa.pemesanan.index')->with('success', 'Pemesanan berhasil dikirim. Tunggu konfirmasi admin.');
    }

    public function show(Pemesanan $pemesanan): View
    {
        $this->authorizePenyewa($pemesanan);
        $pemesanan->load(['kamar.fasilitas', 'pembayaranAwal']);

        return view('penyewa.pemesanans.show', compact('pemesanan'));
    }

    public function cancel(Pemesanan $pemesanan): RedirectResponse
    {
        $this->authorizePenyewa($pemesanan);

        abort_if(! in_array($pemesanan->status_pemesanan, [Pemesanan::STATUS_MENUNGGU, Pemesanan::STATUS_DITERIMA], true), 422);

        DB::transaction(function () use ($pemesanan) {
            $pemesanan->load('kamar');
            $pemesanan->update(['status_pemesanan' => Pemesanan::STATUS_DIBATALKAN]);

            if ($pemesanan->kamar->status === Kamar::STATUS_DIPESAN) {
                $pemesanan->kamar->update(['status' => Kamar::STATUS_TERSEDIA]);
            }
        });

        return back()->with('success', 'Pemesanan berhasil dibatalkan.');
    }

    private function authorizePenyewa(Pemesanan $pemesanan): void
    {
        abort_unless($pemesanan->penyewa_id === auth()->user()->penyewa->id, 403);
    }
}
