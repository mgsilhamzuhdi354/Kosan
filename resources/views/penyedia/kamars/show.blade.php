<x-penyedia-layout header="Detail Kamar">
    <section class="grid gap-5 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm lg:grid-cols-[320px_1fr]">
        <img src="{{ $kamar->foto_url }}" alt="{{ $kamar->nama_kamar }}" class="h-64 w-full rounded-3xl object-cover">
        <div>
            <p class="text-sm font-bold text-slate-500">{{ $kamar->kos?->nama_kos }}</p>
            <h2 class="mt-1 text-3xl font-black">{{ $kamar->nama_kamar }}</h2>
            <p class="mt-2 text-xl font-black text-sky-700">{{ $kamar->harga_format }}</p>
            <p class="mt-4 leading-7 text-slate-600">{{ $kamar->deskripsi }}</p>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($kamar->fasilitas as $item)
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">{{ $item->nama_fasilitas }}</span>
                @endforeach
            </div>
        </div>
    </section>
</x-penyedia-layout>
