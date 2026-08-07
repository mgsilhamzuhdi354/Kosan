<x-admin-layout :header="$fasilitas->exists ? 'Edit Fasilitas' : 'Tambah Fasilitas'">
    <form method="POST" action="{{ $fasilitas->exists ? route('admin.fasilitas.update', $fasilitas) : route('admin.fasilitas.store') }}" class="max-w-xl rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        @if ($fasilitas->exists) @method('PUT') @endif
        <label class="text-sm font-bold">Nama Fasilitas</label>
        <input name="nama_fasilitas" value="{{ old('nama_fasilitas', $fasilitas->nama_fasilitas) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
        <div class="mt-4 flex gap-3">
            <button class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-bold text-white">Simpan</button>
            <a href="{{ route('admin.fasilitas.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold">Batal</a>
        </div>
    </form>
</x-admin-layout>
