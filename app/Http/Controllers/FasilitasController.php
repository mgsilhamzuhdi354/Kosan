<?php

namespace App\Http\Controllers;

use App\Models\Fasilitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FasilitasController extends Controller
{
    public function index(): View
    {
        return view('admin.fasilitas.index', [
            'fasilitas' => Fasilitas::withCount('kamars')->orderBy('nama_fasilitas')->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.fasilitas.form', ['fasilitas' => new Fasilitas]);
    }

    public function store(Request $request): RedirectResponse
    {
        Fasilitas::create($request->validate([
            'nama_fasilitas' => ['required', 'string', 'max:100', 'unique:fasilitas,nama_fasilitas'],
        ]));

        return redirect()->route('admin.fasilitas.index')->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit(Fasilitas $fasilitas): View
    {
        return view('admin.fasilitas.form', compact('fasilitas'));
    }

    public function update(Request $request, Fasilitas $fasilitas): RedirectResponse
    {
        $fasilitas->update($request->validate([
            'nama_fasilitas' => ['required', 'string', 'max:100', Rule::unique('fasilitas', 'nama_fasilitas')->ignore($fasilitas)],
        ]));

        return redirect()->route('admin.fasilitas.index')->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Fasilitas $fasilitas): RedirectResponse
    {
        $fasilitas->delete();

        return redirect()->route('admin.fasilitas.index')->with('success', 'Fasilitas berhasil dihapus.');
    }

    public function penyediaIndex(Request $request): View
    {
        $penyedia = $request->user()->penyediaKos()->firstOrFail();

        return view('penyedia.fasilitas.index', [
            'fasilitas' => Fasilitas::withCount('kamars')
                ->visibleForPenyedia($penyedia->id)
                ->orderBy('nama_fasilitas')
                ->paginate(12),
            'penyediaId' => $penyedia->id,
        ]);
    }

    public function penyediaCreate(): View
    {
        return view('penyedia.fasilitas.form', ['fasilitas' => new Fasilitas]);
    }

    public function penyediaStore(Request $request): RedirectResponse
    {
        $penyedia = $request->user()->penyediaKos()->firstOrFail();
        $data = $this->validatedPenyediaData($request, $penyedia->id);
        $data['penyedia_kos_id'] = $penyedia->id;

        Fasilitas::create($data);

        return redirect()->route('penyedia.fasilitas.index')->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function penyediaEdit(Fasilitas $fasilitas): View
    {
        $this->authorizeOwnedFasilitas($fasilitas);

        return view('penyedia.fasilitas.form', compact('fasilitas'));
    }

    public function penyediaUpdate(Request $request, Fasilitas $fasilitas): RedirectResponse
    {
        $this->authorizeOwnedFasilitas($fasilitas);
        $penyediaId = $request->user()->penyediaKos()->value('id');

        $fasilitas->update($this->validatedPenyediaData($request, $penyediaId, $fasilitas));

        return redirect()->route('penyedia.fasilitas.index')->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function penyediaDestroy(Fasilitas $fasilitas): RedirectResponse
    {
        $this->authorizeOwnedFasilitas($fasilitas);

        if ($fasilitas->kamars()->exists()) {
            return back()->with('error', 'Fasilitas sudah dipakai kamar. Hapus dari kamar terlebih dahulu.');
        }

        $fasilitas->delete();

        return redirect()->route('penyedia.fasilitas.index')->with('success', 'Fasilitas berhasil dihapus.');
    }

    private function validatedPenyediaData(Request $request, int $penyediaId, ?Fasilitas $fasilitas = null): array
    {
        $data = $request->validate([
            'nama_fasilitas' => ['required', 'string', 'max:100'],
        ]);

        $alreadyAvailable = Fasilitas::where('nama_fasilitas', $data['nama_fasilitas'])
            ->when($fasilitas, fn ($query) => $query->whereKeyNot($fasilitas->id))
            ->where(fn ($query) => $query->whereNull('penyedia_kos_id')->orWhere('penyedia_kos_id', $penyediaId))
            ->exists();

        if ($alreadyAvailable) {
            throw ValidationException::withMessages([
                'nama_fasilitas' => 'Fasilitas ini sudah tersedia.',
            ]);
        }

        return $data;
    }

    private function authorizeOwnedFasilitas(Fasilitas $fasilitas): void
    {
        abort_unless($fasilitas->penyedia_kos_id === auth()->user()->penyediaKos()->value('id'), 403);
    }
}
