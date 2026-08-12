<x-admin-layout header="Detail Keluhan">
    @php
        $statusClass = match ($keluhan->status_keluhan) {
            'selesai' => 'status-badge-success',
            'ditolak' => 'status-badge-danger',
            'diproses' => 'status-badge-warning',
            default => '',
        };
    @endphp

    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="status-badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $keluhan->status_keluhan)) }}</span>
                        <span class="status-badge">{{ $keluhan->kategori }}</span>
                    </div>
                    <h2 class="mt-4 text-2xl font-black tracking-tight sm:text-3xl">{{ $keluhan->judul }}</h2>
                </div>
                @if ($keluhan->foto_url)
                    <a target="_blank" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-sky-700 shadow-sm" href="{{ $keluhan->foto_url }}">
                        <x-icon name="camera" class="h-5 w-5" />
                        <span>Lampiran</span>
                    </a>
                @endif
            </div>

            <div class="mt-6 grid gap-3 rounded-3xl bg-slate-50 p-4 text-sm sm:grid-cols-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-wide text-slate-400">Penyewa</p>
                    <p class="mt-1 font-extrabold">{{ $keluhan->penghuni->penyewa->nama_lengkap }}</p>
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-wide text-slate-400">Kamar</p>
                    <p class="mt-1 font-extrabold">{{ $keluhan->penghuni->kamar->nama_kamar }}</p>
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-wide text-slate-400">Tanggal Masuk</p>
                    <p class="mt-1 font-extrabold">{{ $keluhan->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="mt-6">
                <p class="text-sm font-black uppercase tracking-wide text-slate-400">Deskripsi Keluhan</p>
                <p class="mt-3 whitespace-pre-line text-base leading-8 text-slate-700">{{ $keluhan->deskripsi }}</p>
            </div>

            @if ($keluhan->catatan_admin)
                <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-black uppercase tracking-wide text-slate-400">Catatan Admin</p>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $keluhan->catatan_admin }}</p>
                </div>
            @endif
        </section>

        <form method="POST" action="{{ route('admin.keluhan.update-status', $keluhan) }}" class="h-fit rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            @method('PATCH')
            <div>
                <label class="text-sm font-bold text-slate-700">Status penanganan</label>
                <select name="status_keluhan" class="mt-1 w-full">
                    @foreach (\App\Models\Keluhan::STATUSES as $status)
                        <option value="{{ $status }}" @selected($keluhan->status_keluhan === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-4">
                <label class="text-sm font-bold text-slate-700">Catatan Admin</label>
                <textarea name="catatan_admin" rows="5" class="mt-1 w-full" placeholder="Tulis tindak lanjut atau alasan status.">{{ old('catatan_admin', $keluhan->catatan_admin) }}</textarea>
            </div>
            <button class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-sky-600 px-4 py-3 text-sm font-black text-white">
                <x-icon name="clipboard" class="h-5 w-5" />
                <span>Simpan Status</span>
            </button>
            <a href="{{ route('admin.keluhan.index') }}" class="mt-3 inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm">Kembali</a>
        </form>
    </div>
</x-admin-layout>
