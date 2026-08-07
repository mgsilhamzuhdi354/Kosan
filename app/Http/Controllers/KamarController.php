<?php

namespace App\Http\Controllers;

use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KamarController extends Controller
{
    public function index(Request $request): View
    {
        $kamars = Kamar::with('fasilitas')
            ->with('kos')
            ->when($request->filled('q'), fn ($query) => $query->where('nama_kamar', 'like', '%'.$request->q.'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('harga_min'), fn ($query) => $query->where('harga_bulanan', '>=', (int) $request->harga_min))
            ->when($request->filled('harga_max'), fn ($query) => $query->where('harga_bulanan', '<=', (int) $request->harga_max))
            ->orderBy('nama_kamar')
            ->paginate(10)
            ->withQueryString();

        return view('admin.kamars.index', [
            'kamars' => $kamars,
            'statuses' => Kamar::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('admin.kamars.form', [
            'kamar' => new Kamar,
            'fasilitas' => Fasilitas::orderBy('nama_fasilitas')->get(),
            'selectedFasilitas' => [],
            'statuses' => Kamar::STATUSES,
            'kosOptions' => Kos::orderBy('nama_kos')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $fasilitasIds = $data['fasilitas'] ?? [];
        unset($data['fasilitas']);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('kamar', 'public');
        }

        $kamar = Kamar::create($data);
        $kamar->fasilitas()->sync($fasilitasIds);

        return redirect()->route('admin.kamar.index')->with('success', 'Data kamar berhasil ditambahkan.');
    }

    public function show(Kamar $kamar): View
    {
        $kamar->load('fasilitas', 'pemesanans.penyewa', 'penghunis.penyewa');

        return view('admin.kamars.show', compact('kamar'));
    }

    public function edit(Kamar $kamar): View
    {
        return view('admin.kamars.form', [
            'kamar' => $kamar,
            'fasilitas' => Fasilitas::orderBy('nama_fasilitas')->get(),
            'selectedFasilitas' => $kamar->fasilitas()->pluck('fasilitas.id')->toArray(),
            'statuses' => Kamar::STATUSES,
            'kosOptions' => Kos::orderBy('nama_kos')->get(),
        ]);
    }

    public function update(Request $request, Kamar $kamar): RedirectResponse
    {
        $data = $this->validatedData($request, $kamar);
        $fasilitasIds = $data['fasilitas'] ?? [];
        unset($data['fasilitas']);

        if ($request->hasFile('foto')) {
            if ($kamar->foto) {
                Storage::disk('public')->delete($kamar->foto);
            }

            $data['foto'] = $request->file('foto')->store('kamar', 'public');
        }

        $kamar->update($data);
        $kamar->fasilitas()->sync($fasilitasIds);

        return redirect()->route('admin.kamar.index')->with('success', 'Data kamar berhasil diperbarui.');
    }

    public function destroy(Kamar $kamar): RedirectResponse
    {
        if ($kamar->pemesanans()->exists() || $kamar->penghunis()->exists()) {
            return back()->with('error', 'Kamar memiliki riwayat transaksi. Ubah status kamar, jangan hapus datanya.');
        }

        if ($kamar->foto) {
            Storage::disk('public')->delete($kamar->foto);
        }

        $kamar->delete();

        return redirect()->route('admin.kamar.index')->with('success', 'Data kamar berhasil dihapus.');
    }

    public function penyewaIndex(Request $request): View
    {
        $kamars = Kamar::with('fasilitas')
            ->with('kos')
            ->inActiveKos()
            ->when($request->filled('q'), fn ($query) => $query->where('nama_kamar', 'like', '%'.$request->q.'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('tipe'), fn ($query) => $query->where('tipe_kamar', $request->tipe))
            ->when($request->filled('harga_min'), fn ($query) => $query->where('harga_bulanan', '>=', (int) $request->harga_min))
            ->when($request->filled('harga_max'), fn ($query) => $query->where('harga_bulanan', '<=', (int) $request->harga_max))
            ->orderBy('nama_kamar')
            ->paginate(9)
            ->withQueryString();

        $tipes = Kamar::query()
            ->inActiveKos()
            ->select('tipe_kamar')
            ->distinct()
            ->orderBy('tipe_kamar')
            ->pluck('tipe_kamar');

        return view('penyewa.kamar.index', [
            'kamars' => $kamars,
            'statuses' => Kamar::STATUSES,
            'tipes' => $tipes,
        ]);
    }

    public function penyewaShow(Kamar $kamar): View
    {
        abort_unless($kamar->isInActiveKos(), 404);

        $kamar->load('fasilitas');
        $kamar->load('kos');

        return view('penyewa.kamar.show', compact('kamar'));
    }

    public function penyediaIndex(Request $request): View
    {
        $kosIds = $this->ownedKosIds();
        $kamars = Kamar::with(['fasilitas', 'kos'])
            ->whereIn('kos_id', $kosIds)
            ->when($request->filled('q'), fn ($query) => $query->where('nama_kamar', 'like', '%'.$request->q.'%'))
            ->orderBy('nama_kamar')
            ->paginate(10)
            ->withQueryString();

        return view('penyedia.kamars.index', compact('kamars'));
    }

    public function penyediaCreate(): View
    {
        return view('penyedia.kamars.form', [
            'kamar' => new Kamar,
            'fasilitas' => Fasilitas::orderBy('nama_fasilitas')->get(),
            'selectedFasilitas' => [],
            'statuses' => Kamar::STATUSES,
            'kosOptions' => auth()->user()->penyediaKos->kos()->orderBy('nama_kos')->get(),
        ]);
    }

    public function penyediaStore(Request $request): RedirectResponse
    {
        $ownedKosIds = $this->ownedKosIds();
        $data = $this->validatedData($request, allowedKosIds: $ownedKosIds);

        $fasilitasIds = $data['fasilitas'] ?? [];
        unset($data['fasilitas']);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('kamar', 'public');
        }

        $kamar = Kamar::create($data);
        $kamar->fasilitas()->sync($fasilitasIds);

        return redirect()->route('penyedia.kamar.index')->with('success', 'Data kamar berhasil ditambahkan.');
    }

    public function penyediaShow(Kamar $kamar): View
    {
        $this->authorizeOwnedKamar($kamar);
        $kamar->load('fasilitas', 'kos');

        return view('penyedia.kamars.show', compact('kamar'));
    }

    public function penyediaEdit(Kamar $kamar): View
    {
        $this->authorizeOwnedKamar($kamar);

        return view('penyedia.kamars.form', [
            'kamar' => $kamar,
            'fasilitas' => Fasilitas::orderBy('nama_fasilitas')->get(),
            'selectedFasilitas' => $kamar->fasilitas()->pluck('fasilitas.id')->toArray(),
            'statuses' => Kamar::STATUSES,
            'kosOptions' => auth()->user()->penyediaKos->kos()->orderBy('nama_kos')->get(),
        ]);
    }

    public function penyediaUpdate(Request $request, Kamar $kamar): RedirectResponse
    {
        $this->authorizeOwnedKamar($kamar);
        $ownedKosIds = $this->ownedKosIds();
        $data = $this->validatedData($request, $kamar, $ownedKosIds);

        $fasilitasIds = $data['fasilitas'] ?? [];
        unset($data['fasilitas']);

        if ($request->hasFile('foto')) {
            if ($kamar->foto) {
                Storage::disk('public')->delete($kamar->foto);
            }
            $data['foto'] = $request->file('foto')->store('kamar', 'public');
        }

        $kamar->update($data);
        $kamar->fasilitas()->sync($fasilitasIds);

        return redirect()->route('penyedia.kamar.index')->with('success', 'Data kamar berhasil diperbarui.');
    }

    public function penyediaDestroy(Kamar $kamar): RedirectResponse
    {
        $this->authorizeOwnedKamar($kamar);

        if ($kamar->pemesanans()->exists() || $kamar->penghunis()->exists()) {
            return back()->with('error', 'Kamar memiliki riwayat transaksi. Ubah status kamar, jangan hapus datanya.');
        }

        if ($kamar->foto) {
            Storage::disk('public')->delete($kamar->foto);
        }

        $kamar->delete();

        return redirect()->route('penyedia.kamar.index')->with('success', 'Data kamar berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Kamar $kamar = null, ?array $allowedKosIds = null): array
    {
        $kosId = $request->input('kos_id', $kamar?->kos_id);
        $kosRule = Rule::exists('kos', 'id');

        if ($allowedKosIds !== null) {
            $kosRule->where(fn ($query) => $query->whereIn('id', $allowedKosIds));
        }

        return $request->validate([
            'nama_kamar' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kamars', 'nama_kamar')
                    ->where(fn ($query) => $query->where('kos_id', $kosId))
                    ->ignore($kamar),
            ],
            'kos_id' => ['required', $kosRule],
            'tipe_kamar' => ['required', 'string', 'max:100'],
            'harga_bulanan' => ['required', 'integer', 'min:1'],
            'deskripsi' => ['required', 'string'],
            'status' => ['required', Rule::in(Kamar::STATUSES)],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'fasilitas' => ['array'],
            'fasilitas.*' => ['exists:fasilitas,id'],
        ], [
            'nama_kamar.unique' => 'Nama kamar sudah digunakan pada kos yang dipilih.',
            'kos_id.required' => 'Pilih kos terlebih dahulu.',
            'kos_id.exists' => 'Kos yang dipilih tidak valid.',
        ]);
    }

    private function ownedKosIds(): array
    {
        $penyedia = auth()->user()->penyediaKos;

        if (! $penyedia) {
            return [];
        }

        return $penyedia->kos()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function authorizeOwnedKamar(Kamar $kamar): void
    {
        abort_unless(in_array((int) $kamar->kos_id, $this->ownedKosIds(), true), 403);
    }
}
