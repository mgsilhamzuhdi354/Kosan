<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Penghuni;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PenghuniController extends Controller
{
    public function index(): View
    {
        $penghunis = Penghuni::with(['penyewa.user', 'kamar'])
            ->latest()
            ->paginate(12);

        return view('admin.penghunis.index', compact('penghunis'));
    }

    public function show(Penghuni $penghuni): View
    {
        $penghuni->load(['penyewa.user', 'kamar', 'tagihanBulanans.pembayaranBulanan', 'keluhans']);

        return view('admin.penghunis.show', compact('penghuni'));
    }

    public function keluar(Penghuni $penghuni): RedirectResponse
    {
        abort_if($penghuni->status_penghuni !== Penghuni::STATUS_AKTIF, 422);

        DB::transaction(function () use ($penghuni) {
            $penghuni->update([
                'status_penghuni' => Penghuni::STATUS_KELUAR,
                'tanggal_keluar' => today(),
            ]);

            $penghuni->kamar()->update(['status' => Kamar::STATUS_TERSEDIA]);
        });

        return back()->with('success', 'Penghuni ditandai keluar dan kamar tersedia kembali.');
    }
}
