<?php

namespace App\Http\Controllers;

use App\Models\Keluhan;
use App\Models\Penghuni;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KeluhanController extends Controller
{
    public function adminIndex(Request $request): View
    {
        $keluhans = Keluhan::with(['penghuni.penyewa.user', 'penghuni.kamar'])
            ->when($request->filled('status'), fn ($query) => $query->where('status_keluhan', $request->status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.keluhans.index', [
            'keluhans' => $keluhans,
            'statuses' => Keluhan::STATUSES,
        ]);
    }

    public function adminShow(Keluhan $keluhan): View
    {
        $keluhan->load(['penghuni.penyewa.user', 'penghuni.kamar']);

        return view('admin.keluhans.show', compact('keluhan'));
    }

    public function updateStatus(Request $request, Keluhan $keluhan): RedirectResponse
    {
        $data = $request->validate([
            'status_keluhan' => ['required', Rule::in(Keluhan::STATUSES)],
            'catatan_admin' => ['nullable', 'string', 'max:1000'],
        ]);

        $keluhan->update($data);

        return back()->with('success', 'Status keluhan berhasil diperbarui.');
    }

    public function penyewaIndex(): View
    {
        $penghuni = auth()->user()->penyewa->penghuniAktif;
        $keluhans = Keluhan::with('penghuni.kamar')
            ->when($penghuni, fn ($query) => $query->where('penghuni_id', $penghuni->id), fn ($query) => $query->whereRaw('1 = 0'))
            ->latest()
            ->paginate(12);

        return view('penyewa.keluhan.index', compact('keluhans', 'penghuni'));
    }

    public function create(): View
    {
        $penghuni = auth()->user()->penyewa->penghuniAktif;
        abort_if(! $penghuni, 403, 'Keluhan hanya tersedia untuk penghuni aktif.');

        return view('penyewa.keluhan.create', [
            'kategori' => Keluhan::KATEGORI,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $penghuni = $request->user()->penyewa->penghuniAktif;
        abort_if(! $penghuni, 403);

        $data = $request->validate([
            'kategori' => ['required', Rule::in(Keluhan::KATEGORI)],
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['required', 'string'],
            'foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('keluhan', 'public');
        }

        $data['penghuni_id'] = $penghuni->id;
        $data['status_keluhan'] = Keluhan::STATUS_DIKIRIM;

        Keluhan::create($data);

        return redirect()->route('penyewa.keluhan.index')->with('success', 'Keluhan berhasil dikirim.');
    }

    public function penyewaShow(Keluhan $keluhan): View
    {
        abort_unless($keluhan->penghuni->penyewa_id === auth()->user()->penyewa->id, 403);
        $keluhan->load('penghuni.kamar');

        return view('penyewa.keluhan.show', compact('keluhan'));
    }
}
