<x-admin-layout :header="$kamar->exists ? 'Edit Kamar' : 'Tambah Kamar'">
    <form method="POST" action="{{ $kamar->exists ? route('admin.kamar.update', $kamar) : route('admin.kamar.store') }}" enctype="multipart/form-data" class="grid gap-5 rounded-lg border border-slate-200 bg-white p-5 shadow-sm lg:grid-cols-2">
        @csrf
        @if ($kamar->exists) @method('PUT') @endif
        <div>
            <label class="text-sm font-bold">Nama/Nomor Kamar</label>
            <input name="nama_kamar" value="{{ old('nama_kamar', $kamar->nama_kamar) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
        </div>
        <div>
            <label class="text-sm font-bold">Kos</label>
            <input name="nama_kos" value="{{ old('nama_kos', $defaultKosName ?? '') }}" class="mt-1 w-full rounded-lg border-slate-300" required>
            @error('nama_kos') <p class="mt-1 text-sm font-bold text-red-600">{{ $message }}</p> @enderror
            @error('kos_id') <p class="mt-1 text-sm font-bold text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-bold">Tipe Kamar</label>
            <input name="tipe_kamar" value="{{ old('tipe_kamar', $kamar->tipe_kamar) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
        </div>
        <div>
            <label class="text-sm font-bold">Harga Bulanan</label>
            <input name="harga_bulanan" type="number" min="1" value="{{ old('harga_bulanan', $kamar->harga_bulanan) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
        </div>
        <div>
            <label class="text-sm font-bold">Status</label>
            <select name="status" class="mt-1 w-full rounded-lg border-slate-300" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $kamar->status ?: 'tersedia') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="lg:col-span-2">
            <label class="text-sm font-bold">Deskripsi</label>
            <textarea name="deskripsi" rows="4" class="mt-1 w-full rounded-lg border-slate-300" required>{{ old('deskripsi', $kamar->deskripsi) }}</textarea>
        </div>
        <div>
            <label class="text-sm font-bold">Foto Kamar</label>
            <input name="foto" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2 text-sm">
        </div>
        <div>
            <label class="text-sm font-bold">Fasilitas</label>
            <div class="mt-2 grid grid-cols-2 gap-2">
                @foreach ($fasilitas as $item)
                    <label class="flex items-center gap-2 rounded-lg bg-slate-50 p-2 text-sm">
                        <input type="checkbox" name="fasilitas[]" value="{{ $item->id }}" @checked(in_array($item->id, old('fasilitas', $selectedFasilitas), true))>
                        {{ $item->nama_fasilitas }}
                    </label>
                @endforeach
            </div>
        </div>
        <div class="flex gap-3 lg:col-span-2">
            <button class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-bold text-white">Simpan</button>
            <a href="{{ route('admin.kamar.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold">Batal</a>
        </div>
    </form>
</x-admin-layout>
