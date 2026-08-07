<?php

namespace App\Http\Controllers;

use App\Models\Penyewa;
use Illuminate\View\View;

class PenyewaController extends Controller
{
    public function index(): View
    {
        $penyewas = Penyewa::with(['user', 'penghuniAktif.kamar'])
            ->latest()
            ->paginate(12);

        return view('admin.penyewas.index', compact('penyewas'));
    }

    public function show(Penyewa $penyewa): View
    {
        $penyewa->load(['user', 'pemesanans.kamar', 'penghuni.kamar', 'penghuni.tagihanBulanans.pembayaranBulanan']);

        return view('admin.penyewas.show', compact('penyewa'));
    }
}
