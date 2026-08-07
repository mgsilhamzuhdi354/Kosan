<x-admin-layout header="Detail Kamar">
    <div class="grid gap-6 lg:grid-cols-[380px_1fr]">
        <img src="{{ $kamar->foto_url }}" class="h-72 w-full rounded-lg object-cover" alt="{{ $kamar->nama_kamar }}">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-2xl font-extrabold">{{ $kamar->nama_kamar }}</h2>
            <p class="text-slate-500">{{ $kamar->tipe_kamar }} - {{ ucfirst($kamar->status) }}</p>
            <p class="mt-4 text-2xl font-extrabold text-sky-700">{{ $kamar->harga_format }}</p>
            <p class="mt-4 text-slate-600">{{ $kamar->deskripsi }}</p>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($kamar->fasilitas as $item)
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold">{{ $item->nama_fasilitas }}</span>
                @endforeach
            </div>
        </div>
    </div>
</x-admin-layout>
