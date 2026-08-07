<?php

namespace App\Http\Controllers;

use App\Models\FavoritKamar;
use App\Models\Kamar;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FavoritKamarController extends Controller
{
    public function index(): View
    {
        $penyewa = auth()->user()->penyewa;
        $favorits = FavoritKamar::with('kamar.kos')
            ->where('penyewa_id', $penyewa->id)
            ->latest()
            ->paginate(12);

        return view('penyewa.favorit.index', compact('favorits'));
    }

    public function store(Kamar $kamar): RedirectResponse
    {
        abort_unless($kamar->isInActiveKos(), 404);

        $penyewa = auth()->user()->penyewa;

        FavoritKamar::firstOrCreate([
            'penyewa_id' => $penyewa->id,
            'kamar_id' => $kamar->id,
        ]);

        return back()->with('success', 'Kamar ditambahkan ke favorit.');
    }

    public function destroy(Kamar $kamar): RedirectResponse
    {
        $penyewa = auth()->user()->penyewa;

        FavoritKamar::where('penyewa_id', $penyewa->id)
            ->where('kamar_id', $kamar->id)
            ->delete();

        return back()->with('success', 'Kamar dihapus dari favorit.');
    }
}
