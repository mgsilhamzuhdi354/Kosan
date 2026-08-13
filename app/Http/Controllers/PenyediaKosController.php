<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenyediaKosController extends Controller
{
    public function index(Request $request): View
    {
        $penyedia = $request->user()->penyediaKos()->firstOrFail();

        $kos = $penyedia->kos()
            ->withCount([
                'kamars',
                'kamars as kamar_tersedia_count' => fn ($query) => $query->where('status', Kamar::STATUS_TERSEDIA),
                'kamars as kamar_terisi_count' => fn ($query) => $query->where('status', Kamar::STATUS_TERISI),
            ])
            ->when($request->filled('q'), fn ($query) => $query->where('nama_kos', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('penyedia.kos.index', compact('kos'));
    }

    public function create(): View
    {
        return view('penyedia.kos.form', [
            'kos' => new Kos(['status' => Kos::STATUS_AKTIF, 'kota' => 'Betung']),
            'statuses' => [Kos::STATUS_AKTIF, Kos::STATUS_NONAKTIF],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $penyedia = $request->user()->penyediaKos()->firstOrFail();
        $data = $this->validatedData($request);
        $data['penyedia_kos_id'] = $penyedia->id;

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('kos', 'public');
        }

        $penyedia->kos()->create($data);

        return redirect()->route('penyedia.kos.index')->with('success', 'Kos berhasil ditambahkan.');
    }

    public function edit(Kos $kos): View
    {
        $this->authorizeOwnedKos($kos);

        return view('penyedia.kos.form', [
            'kos' => $kos,
            'statuses' => [Kos::STATUS_AKTIF, Kos::STATUS_NONAKTIF],
        ]);
    }

    public function update(Request $request, Kos $kos): RedirectResponse
    {
        $this->authorizeOwnedKos($kos);
        $data = $this->validatedData($request, $kos);

        if ($request->hasFile('foto')) {
            if ($kos->foto && ! str_starts_with($kos->foto, 'assets/')) {
                Storage::disk('public')->delete($kos->foto);
            }

            $data['foto'] = $request->file('foto')->store('kos', 'public');
        }

        $kos->update($data);

        return redirect()->route('penyedia.kos.index')->with('success', 'Kos berhasil diperbarui.');
    }

    public function destroy(Kos $kos): RedirectResponse
    {
        $this->authorizeOwnedKos($kos);

        $hasTransactionHistory = $kos->kamars()
            ->where(fn ($query) => $query->whereHas('pemesanans')->orWhereHas('penghunis'))
            ->exists();

        if ($hasTransactionHistory) {
            return back()->with('error', 'Kos sudah memiliki riwayat transaksi. Ubah status menjadi nonaktif agar tidak tampil untuk penyewa.');
        }

        $kos->kamars()->get()->each(function (Kamar $kamar) {
            if ($kamar->foto && ! str_starts_with($kamar->foto, 'assets/')) {
                Storage::disk('public')->delete($kamar->foto);
            }

            $kamar->delete();
        });

        if ($kos->foto && ! str_starts_with($kos->foto, 'assets/')) {
            Storage::disk('public')->delete($kos->foto);
        }

        $kos->delete();

        return redirect()->route('penyedia.kos.index')->with('success', 'Kos berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Kos $kos = null): array
    {
        $penyediaId = $request->user()->penyediaKos()->value('id');

        return $request->validate([
            'nama_kos' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kos', 'nama_kos')
                    ->where(fn ($query) => $query->where('penyedia_kos_id', $penyediaId))
                    ->ignore($kos),
            ],
            'alamat' => ['required', 'string', 'max:1000'],
            'kota' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', Rule::in([Kos::STATUS_AKTIF, Kos::STATUS_NONAKTIF])],
            'is_promoted' => ['nullable', 'boolean'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'nama_kos.unique' => 'Nama kos sudah digunakan pada akun Anda.',
        ]);
    }

    private function authorizeOwnedKos(Kos $kos): void
    {
        abort_unless($kos->penyedia_kos_id === auth()->user()->penyediaKos()->value('id'), 403);
    }
}
