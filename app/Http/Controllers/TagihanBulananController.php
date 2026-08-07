<?php

namespace App\Http\Controllers;

use App\Models\Penghuni;
use App\Models\TagihanBulanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagihanBulananController extends Controller
{
    public function adminIndex(Request $request): View
    {
        $this->markOverdue();

        $tagihans = TagihanBulanan::with(['penghuni.penyewa.user', 'penghuni.kamar', 'pembayaranBulanan'])
            ->when($request->filled('status'), fn ($query) => $query->where('status_tagihan', $request->status))
            ->when($request->filled('bulan'), fn ($query) => $query->where('bulan', (int) $request->bulan))
            ->when($request->filled('tahun'), fn ($query) => $query->where('tahun', (int) $request->tahun))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.tagihan-bulanan.index', [
            'tagihans' => $tagihans,
            'statuses' => TagihanBulanan::STATUSES,
        ]);
    }

    public function generate(): RedirectResponse
    {
        $created = 0;

        Penghuni::where('status_penghuni', Penghuni::STATUS_AKTIF)->get()->each(function (Penghuni $penghuni) use (&$created) {
            $lastBill = $penghuni->tagihanBulanans()->orderByDesc('tanggal_jatuh_tempo')->first();
            $dueDate = $lastBill
                ? $lastBill->tanggal_jatuh_tempo->copy()->addMonthNoOverflow()
                : $penghuni->tanggal_jatuh_tempo;

            $bill = TagihanBulanan::firstOrCreate(
                [
                    'penghuni_id' => $penghuni->id,
                    'bulan' => (int) $dueDate->month,
                    'tahun' => (int) $dueDate->year,
                ],
                [
                    'jumlah_tagihan' => $penghuni->harga_bulanan,
                    'tanggal_jatuh_tempo' => $dueDate,
                    'status_tagihan' => TagihanBulanan::STATUS_BELUM_BAYAR,
                ]
            );

            if ($bill->wasRecentlyCreated) {
                $created++;
            }
        });

        return back()->with('success', "Generate tagihan selesai. {$created} tagihan baru dibuat.");
    }

    public function penyewaIndex(): View
    {
        $this->markOverdue();

        $penyewa = auth()->user()->penyewa;
        $tagihans = TagihanBulanan::with(['penghuni.kamar', 'pembayaranBulanan'])
            ->whereHas('penghuni', fn ($query) => $query->where('penyewa_id', $penyewa->id))
            ->latest()
            ->paginate(12);

        return view('penyewa.tagihan.index', compact('tagihans'));
    }

    public function bayar(TagihanBulanan $tagihan): View
    {
        $this->authorizePenyewa($tagihan);
        abort_if($tagihan->status_tagihan === TagihanBulanan::STATUS_LUNAS, 422, 'Tagihan lunas tidak dapat dibayar ulang.');
        abort_if($tagihan->status_tagihan === TagihanBulanan::STATUS_MENUNGGU, 422, 'Pembayaran tagihan sedang menunggu validasi.');

        return view('penyewa.tagihan.bayar', compact('tagihan'));
    }

    private function authorizePenyewa(TagihanBulanan $tagihan): void
    {
        $tagihan->loadMissing('penghuni');
        abort_unless($tagihan->penghuni->penyewa_id === auth()->user()->penyewa->id, 403);
    }

    private function markOverdue(): void
    {
        TagihanBulanan::whereIn('status_tagihan', [TagihanBulanan::STATUS_BELUM_BAYAR, TagihanBulanan::STATUS_DITOLAK])
            ->whereDate('tanggal_jatuh_tempo', '<', today())
            ->update(['status_tagihan' => TagihanBulanan::STATUS_TERLAMBAT]);
    }
}
