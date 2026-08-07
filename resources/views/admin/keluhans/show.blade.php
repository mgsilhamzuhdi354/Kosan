<x-admin-layout header="Detail Keluhan">
    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-2xl font-extrabold">{{ $keluhan->judul }}</h2>
            <p class="text-slate-500">{{ $keluhan->penghuni->penyewa->nama_lengkap }} - {{ $keluhan->kategori }}</p>
            <p class="mt-5">{{ $keluhan->deskripsi }}</p>
            @if ($keluhan->foto_url)<a target="_blank" class="mt-4 inline-block font-bold text-sky-700" href="{{ $keluhan->foto_url }}">Lihat Lampiran</a>@endif
        </section>
        <form method="POST" action="{{ route('admin.keluhan.update-status', $keluhan) }}" class="h-fit rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            @csrf @method('PATCH')
            <label class="text-sm font-bold">Status</label>
            <select name="status_keluhan" class="mt-1 w-full rounded-lg border-slate-300">@foreach (\App\Models\Keluhan::STATUSES as $status)<option value="{{ $status }}" @selected($keluhan->status_keluhan === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
            <label class="mt-4 block text-sm font-bold">Catatan Admin</label>
            <textarea name="catatan_admin" rows="4" class="mt-1 w-full rounded-lg border-slate-300">{{ old('catatan_admin', $keluhan->catatan_admin) }}</textarea>
            <button class="mt-4 w-full rounded-lg bg-sky-600 px-4 py-2 text-sm font-bold text-white">Simpan Status</button>
        </form>
    </div>
</x-admin-layout>
