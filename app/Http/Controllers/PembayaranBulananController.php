<?php

namespace App\Http\Controllers;

use App\Models\PembayaranBulanan;
use App\Models\TagihanBulanan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PembayaranBulananController extends Controller
{
    public function adminIndex(Request $request): View
    {
        $pembayarans = PembayaranBulanan::with(['tagihanBulanan.penghuni.penyewa.user', 'tagihanBulanan.penghuni.kamar'])
            ->when($request->filled('status'), fn ($query) => $query->where('status_pembayaran', $request->status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.pembayaran-bulanan.index', [
            'pembayarans' => $pembayarans,
            'statuses' => PembayaranBulanan::STATUSES,
        ]);
    }

    public function store(Request $request, TagihanBulanan $tagihan): RedirectResponse
    {
        $tagihan->load(['penghuni', 'pembayaranBulanan']);
        abort_unless($tagihan->penghuni->penyewa_id === $request->user()->penyewa->id, 403);
        abort_if(in_array($tagihan->status_tagihan, [TagihanBulanan::STATUS_LUNAS, TagihanBulanan::STATUS_MENUNGGU], true), 422);

        $pembayaran = $tagihan->pembayaranBulanan;
        abort_if(
            $pembayaran && in_array($pembayaran->status_pembayaran, [PembayaranBulanan::STATUS_MENUNGGU, PembayaranBulanan::STATUS_LUNAS], true),
            422,
            'Pembayaran bulanan sedang diproses atau sudah lunas.'
        );

        $data = $request->validate([
            'tanggal_bayar' => ['required', 'date', 'before_or_equal:today'],
            'jumlah_bayar' => ['required', 'integer', 'min:1'],
            'bukti_bayar' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $oldProof = $pembayaran?->bukti_bayar;

        DB::transaction(function () use ($request, $tagihan, $data, $oldProof) {
            $data['bukti_bayar'] = $request->file('bukti_bayar')->store('pembayaran-bulanan', 'public');
            $data['status_pembayaran'] = PembayaranBulanan::STATUS_MENUNGGU;
            $data['catatan_admin'] = null;

            PembayaranBulanan::updateOrCreate(['tagihan_bulanan_id' => $tagihan->id], $data);
            $tagihan->update(['status_tagihan' => TagihanBulanan::STATUS_MENUNGGU]);

            if ($oldProof && $oldProof !== $data['bukti_bayar']) {
                Storage::disk('public')->delete($oldProof);
            }
        });

        return redirect()->route('penyewa.tagihan.index')->with('success', 'Bukti pembayaran bulanan berhasil diunggah.');
    }

    public function approve(PembayaranBulanan $pembayaranBulanan): RedirectResponse
    {
        DB::transaction(function () use ($pembayaranBulanan) {
            abort_if($pembayaranBulanan->status_pembayaran !== PembayaranBulanan::STATUS_MENUNGGU, 422);

            $pembayaranBulanan->update([
                'status_pembayaran' => PembayaranBulanan::STATUS_LUNAS,
                'catatan_admin' => null,
            ]);

            $pembayaranBulanan->tagihanBulanan()->update(['status_tagihan' => TagihanBulanan::STATUS_LUNAS]);
        });

        return back()->with('success', 'Pembayaran bulanan disetujui.');
    }

    public function reject(Request $request, PembayaranBulanan $pembayaranBulanan): RedirectResponse
    {
        abort_if($pembayaranBulanan->status_pembayaran !== PembayaranBulanan::STATUS_MENUNGGU, 422);

        $data = $request->validate(['catatan_admin' => ['required', 'string', 'max:1000']]);

        DB::transaction(function () use ($pembayaranBulanan, $data) {
            $pembayaranBulanan->update([
                'status_pembayaran' => PembayaranBulanan::STATUS_DITOLAK,
                'catatan_admin' => $data['catatan_admin'],
            ]);

            $pembayaranBulanan->tagihanBulanan()->update(['status_tagihan' => TagihanBulanan::STATUS_DITOLAK]);
        });

        return back()->with('success', 'Pembayaran bulanan ditolak.');
    }

    public function riwayat(): View
    {
        $penyewa = auth()->user()->penyewa;
        $pembayarans = PembayaranBulanan::with('tagihanBulanan.penghuni.kamar')
            ->whereHas('tagihanBulanan.penghuni', fn ($query) => $query->where('penyewa_id', $penyewa->id))
            ->latest()
            ->paginate(12);

        return view('penyewa.riwayat.index', compact('pembayarans'));
    }

    public function receipt(PembayaranBulanan $pembayaranBulanan)
    {
        $pembayaranBulanan->load('tagihanBulanan.penghuni.penyewa.user', 'tagihanBulanan.penghuni.kamar');

        if (! auth()->user()->isAdmin()) {
            abort_unless($pembayaranBulanan->tagihanBulanan->penghuni->penyewa_id === auth()->user()->penyewa->id, 403);
        }

        abort_if($pembayaranBulanan->status_pembayaran !== PembayaranBulanan::STATUS_LUNAS, 422);

        return Pdf::loadView('pdf.bukti-pembayaran', compact('pembayaranBulanan'))->download('bukti-pembayaran-'.$pembayaranBulanan->id.'.pdf');
    }
}
