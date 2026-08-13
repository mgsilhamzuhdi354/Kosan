<x-penyedia-layout :header="$fasilitas->exists ? 'Edit Fasilitas' : 'Tambah Fasilitas'">
    <form method="POST" action="{{ $fasilitas->exists ? route('penyedia.fasilitas.update', $fasilitas) : route('penyedia.fasilitas.store') }}" class="max-w-xl rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        @if ($fasilitas->exists) @method('PUT') @endif

        <label class="text-sm font-bold">Nama Fasilitas</label>
        <input name="nama_fasilitas" value="{{ old('nama_fasilitas', $fasilitas->nama_fasilitas) }}" class="mt-1 w-full rounded-2xl border-slate-300" required>
        @error('nama_fasilitas') <p class="mt-1 text-sm font-bold text-red-600">{{ $message }}</p> @enderror

        <div class="mt-4 flex gap-3">
            <button class="rounded-2xl bg-sky-600 px-4 py-3 text-sm font-black text-white">Simpan</button>
            <a href="{{ route('penyedia.fasilitas.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black">Batal</a>
        </div>
    </form>
</x-penyedia-layout>
