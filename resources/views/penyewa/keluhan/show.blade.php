<x-penyewa-layout header="Detail Keluhan">
    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-2xl font-extrabold">{{ $keluhan->judul }}</h2>
        <p class="text-slate-500">{{ $keluhan->kategori }} - {{ ucfirst($keluhan->status_keluhan) }}</p>
        <p class="mt-5">{{ $keluhan->deskripsi }}</p>
        @if ($keluhan->catatan_admin)
            <div class="mt-5 rounded-lg bg-slate-50 p-4 text-sm"><p class="font-bold">Catatan Admin</p><p>{{ $keluhan->catatan_admin }}</p></div>
        @endif
        @if ($keluhan->foto_url)<a target="_blank" class="mt-4 inline-block font-bold text-sky-700" href="{{ $keluhan->foto_url }}">Lihat Lampiran</a>@endif
    </section>
</x-penyewa-layout>
