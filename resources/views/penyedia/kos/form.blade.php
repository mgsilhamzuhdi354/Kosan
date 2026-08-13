<x-penyedia-layout :header="$kos->exists ? 'Edit Kos' : 'Tambah Kos'">
    <form method="POST" action="{{ $kos->exists ? route('penyedia.kos.update', $kos) : route('penyedia.kos.store') }}" enctype="multipart/form-data" class="grid gap-5 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm lg:grid-cols-2">
        @csrf
        @if ($kos->exists) @method('PUT') @endif

        <div>
            <label class="text-sm font-bold">Nama Kos</label>
            <input name="nama_kos" value="{{ old('nama_kos', $kos->nama_kos) }}" class="mt-1 w-full rounded-2xl border-slate-300" required>
            @error('nama_kos') <p class="mt-1 text-sm font-bold text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-bold">Kota</label>
            <input name="kota" value="{{ old('kota', $kos->kota) }}" class="mt-1 w-full rounded-2xl border-slate-300" required>
            @error('kota') <p class="mt-1 text-sm font-bold text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="lg:col-span-2">
            <label class="text-sm font-bold">Alamat Lengkap</label>
            <textarea name="alamat" rows="3" class="mt-1 w-full rounded-2xl border-slate-300" required>{{ old('alamat', $kos->alamat) }}</textarea>
            @error('alamat') <p class="mt-1 text-sm font-bold text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-bold">Latitude</label>
            <input name="latitude" type="number" step="any" value="{{ old('latitude', $kos->latitude) }}" class="mt-1 w-full rounded-2xl border-slate-300">
            @error('latitude') <p class="mt-1 text-sm font-bold text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-bold">Longitude</label>
            <input name="longitude" type="number" step="any" value="{{ old('longitude', $kos->longitude) }}" class="mt-1 w-full rounded-2xl border-slate-300">
            @error('longitude') <p class="mt-1 text-sm font-bold text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-bold">Status</label>
            <select name="status" class="mt-1 w-full rounded-2xl border-slate-300" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $kos->status ?: 'aktif') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            @error('status') <p class="mt-1 text-sm font-bold text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-bold">Foto Kos</label>
            <input name="foto" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-1 w-full rounded-2xl border border-slate-300 bg-white p-2 text-sm">
            @error('foto') <p class="mt-1 text-sm font-bold text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="lg:col-span-2">
            <label class="text-sm font-bold">Deskripsi</label>
            <textarea name="deskripsi" rows="5" class="mt-1 w-full rounded-2xl border-slate-300" required>{{ old('deskripsi', $kos->deskripsi) }}</textarea>
            @error('deskripsi') <p class="mt-1 text-sm font-bold text-red-600">{{ $message }}</p> @enderror
        </div>
        <label class="flex items-center gap-2 rounded-2xl bg-slate-50 p-3 text-sm font-bold lg:col-span-2">
            <input type="hidden" name="is_promoted" value="0">
            <input type="checkbox" name="is_promoted" value="1" @checked((bool) old('is_promoted', $kos->is_promoted))>
            Tampilkan sebagai rekomendasi
        </label>

        <div class="flex gap-3 lg:col-span-2">
            <button class="rounded-2xl bg-sky-600 px-4 py-3 text-sm font-black text-white">Simpan</button>
            <a href="{{ route('penyedia.kos.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black">Batal</a>
        </div>
    </form>
</x-penyedia-layout>
