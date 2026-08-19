<?php

namespace App\Http\Controllers;

use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\PenyediaKos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
            'defaultKosName' => $this->kosNameFromRequest(),
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
            'defaultKosName' => $this->kosNameFromKamar($kamar),
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
        $penyedia = auth()->user()->penyediaKos;

        return view('penyedia.kamars.form', [
            'kamar' => new Kamar,
            'fasilitas' => Fasilitas::visibleForPenyedia($penyedia->id)->orderBy('nama_fasilitas')->get(),
            'selectedFasilitas' => [],
            'statuses' => Kamar::STATUSES,
            'defaultKosName' => $this->kosNameFromRequest($penyedia->id),
        ]);
    }

    public function penyediaStore(Request $request): RedirectResponse
    {
        $ownedKosIds = $this->ownedKosIds();
        $data = $this->validatedData($request, allowedKosIds: $ownedKosIds, allowedFasilitasIds: $this->availableFasilitasIds());

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
        $penyedia = auth()->user()->penyediaKos;

        return view('penyedia.kamars.form', [
            'kamar' => $kamar,
            'fasilitas' => Fasilitas::visibleForPenyedia($penyedia->id)->orderBy('nama_fasilitas')->get(),
            'selectedFasilitas' => $kamar->fasilitas()->pluck('fasilitas.id')->toArray(),
            'statuses' => Kamar::STATUSES,
            'defaultKosName' => $this->kosNameFromKamar($kamar),
        ]);
    }

    public function penyediaUpdate(Request $request, Kamar $kamar): RedirectResponse
    {
        $this->authorizeOwnedKamar($kamar);
        $ownedKosIds = $this->ownedKosIds();
        $data = $this->validatedData($request, $kamar, $ownedKosIds, $this->availableFasilitasIds());

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

    private function validatedData(Request $request, ?Kamar $kamar = null, ?array $allowedKosIds = null, ?array $allowedFasilitasIds = null): array
    {
        $kos = $this->resolveKos($request, $kamar, $allowedKosIds);
        $fasilitasRule = Rule::exists('fasilitas', 'id');

        if ($allowedFasilitasIds !== null) {
            $fasilitasRule->where(fn ($query) => $query->whereIn('id', $allowedFasilitasIds));
        }

        $data = $request->validate([
            'nama_kos' => ['nullable', 'string', 'max:255'],
            'nama_kamar' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kamars', 'nama_kamar')
                    ->where(fn ($query) => $query->where('kos_id', $kos->id))
                    ->ignore($kamar),
            ],
            'tipe_kamar' => ['required', 'string', 'max:100'],
            'harga_bulanan' => ['required', 'integer', 'min:1'],
            'deskripsi' => ['required', 'string'],
            'status' => ['required', Rule::in(Kamar::STATUSES)],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'fasilitas' => ['array'],
            'fasilitas.*' => [$fasilitasRule],
        ], [
            'nama_kamar.unique' => 'Nama kamar sudah digunakan pada kos yang dipilih.',
            'nama_kos.required' => 'Nama kos wajib diisi.',
            'kos_id.exists' => 'Kos yang dipilih tidak valid.',
            'fasilitas.*.exists' => 'Fasilitas yang dipilih tidak valid.',
        ]);

        $data['kos_id'] = $kos->id;
        unset($data['nama_kos']);

        return $data;
    }

    private function resolveKos(Request $request, ?Kamar $kamar = null, ?array $allowedKosIds = null): Kos
    {
        $namaKos = trim((string) $request->input('nama_kos', ''));
        $penyedia = auth()->user()->penyediaKos;

        if ($namaKos !== '') {
            if ($allowedKosIds !== null) {
                return $penyedia->kos()->firstOrCreate(
                    ['nama_kos' => $namaKos],
                    [
                        'alamat' => $penyedia->alamat,
                        'kota' => 'Betung',
                        'deskripsi' => 'Kos baru yang dibuat saat menambah data kamar.',
                        'status' => Kos::STATUS_AKTIF,
                    ]
                );
            }

            $adminPenyedia = $penyedia ?: $this->adminPenyediaKos();

            return Kos::firstOrCreate(
                ['nama_kos' => $namaKos],
                [
                    'penyedia_kos_id' => $adminPenyedia->id,
                    'alamat' => $adminPenyedia->alamat,
                    'kota' => 'Betung',
                    'deskripsi' => 'Kos baru yang dibuat saat menambah data kamar.',
                    'status' => Kos::STATUS_AKTIF,
                ]
            );
        }

        $kosId = $request->input('kos_id', $kamar?->kos_id);

        if ($kosId) {
            $query = Kos::query()->whereKey($kosId);

            if ($allowedKosIds !== null) {
                $query->whereIn('id', $allowedKosIds);
            }

            $kos = $query->first();

            if ($kos) {
                return $kos;
            }

            throw ValidationException::withMessages([
                'kos_id' => 'Kos yang dipilih tidak valid.',
            ]);
        }

        throw ValidationException::withMessages([
            'nama_kos' => 'Nama kos wajib diisi.',
        ]);
    }

    private function adminPenyediaKos(): PenyediaKos
    {
        $user = auth()->user();

        return PenyediaKos::firstOrCreate(
            ['user_id' => $user->id],
            [
                'nama_lengkap' => $user->name,
                'no_hp' => '083179749407',
                'alamat' => 'Betung, Banyuasin',
            ]
        );
    }

    private function kosNameFromRequest(?int $penyediaId = null): ?string
    {
        $kosId = request('kos_id');

        if (! $kosId) {
            return null;
        }

        return Kos::query()
            ->when($penyediaId, fn ($query) => $query->where('penyedia_kos_id', $penyediaId))
            ->whereKey($kosId)
            ->value('nama_kos');
    }

    private function kosNameFromKamar(Kamar $kamar): ?string
    {
        $kamar->loadMissing('kos');

        return $kamar->kos?->nama_kos;
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

    private function availableFasilitasIds(): array
    {
        $penyedia = auth()->user()->penyediaKos;

        if (! $penyedia) {
            return [];
        }

        return Fasilitas::visibleForPenyedia($penyedia->id)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }
}
