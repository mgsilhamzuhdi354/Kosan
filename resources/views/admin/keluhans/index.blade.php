<x-admin-layout header="Data Keluhan Penghuni">
    @php
        $statusClass = fn ($status) => match ($status) {
            'selesai' => 'status-badge-success',
            'ditolak' => 'status-badge-danger',
            'diproses' => 'status-badge-warning',
            default => '',
        };

        $statCards = [
            ['label' => 'Total Keluhan', 'value' => $stats['total'], 'hint' => 'Semua laporan masuk'],
            ['label' => 'Baru Dikirim', 'value' => $stats['dikirim'], 'hint' => 'Perlu dicek admin'],
            ['label' => 'Diproses', 'value' => $stats['diproses'], 'hint' => 'Sedang ditangani'],
            ['label' => 'Selesai', 'value' => $stats['selesai'], 'hint' => 'Sudah ditutup'],
            ['label' => 'Ditolak', 'value' => $stats['ditolak'], 'hint' => 'Tidak dilanjutkan'],
            ['label' => 'Dengan Lampiran', 'value' => $stats['lampiran'], 'hint' => 'Foto atau PDF'],
        ];
    @endphp

    <section class="premium-surface rounded-3xl p-5 sm:p-6">
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <span class="premium-pill">Complaint Desk</span>
                <h2 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">Pantau dan tindak lanjuti keluhan penghuni</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Semua laporan formal penghuni dikumpulkan di sini agar admin bisa mencari, memfilter, membuka detail, dan memperbarui status dengan cepat.</p>
            </div>
            <a href="{{ route('admin.laporan.index', 'penyewaan') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-black text-white">
                <x-icon name="clipboard" class="h-5 w-5" />
                <span>Report Penyewaan</span>
            </a>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ($statCards as $card)
            <article class="premium-stat lift-card rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                <p class="mt-3 text-3xl font-black tracking-tight">{{ $card['value'] }}</p>
                <p class="mt-2 text-xs font-bold text-slate-400">{{ $card['hint'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" class="grid gap-3 lg:grid-cols-[1.2fr_220px_220px_auto_auto] lg:items-end">
            <div>
                <label class="text-sm font-bold text-slate-700">Cari keluhan</label>
                <div class="relative mt-1">
                    <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                    <input name="q" value="{{ request('q') }}" placeholder="Judul, penyewa, kamar, atau deskripsi" class="w-full pl-12">
                </div>
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">Status</label>
                <select name="status" class="mt-1 w-full">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">Kategori</label>
                <select name="kategori" class="mt-1 w-full">
                    <option value="">Semua kategori</option>
                    @foreach ($kategori as $item)
                        <option value="{{ $item }}" @selected(request('kategori') === $item)>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
            <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl bg-sky-600 px-5 py-3 text-sm font-black text-white">
                <x-icon name="sliders" class="h-5 w-5" />
                <span>Filter</span>
            </button>
            <a href="{{ route('admin.keluhan.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm">Reset</a>
        </form>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        @forelse ($keluhans as $keluhan)
            <a href="{{ route('admin.keluhan.show', $keluhan) }}" class="lift-card rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="status-badge {{ $statusClass($keluhan->status_keluhan) }}">{{ ucfirst(str_replace('_', ' ', $keluhan->status_keluhan)) }}</span>
                            <span class="status-badge">{{ $keluhan->kategori }}</span>
                        </div>
                        <h3 class="mt-3 text-lg font-black leading-snug text-slate-950">{{ $keluhan->judul }}</h3>
                        <p class="mt-2 overflow-hidden text-sm leading-6 text-slate-600">{{ str($keluhan->deskripsi)->limit(130) }}</p>
                    </div>
                    <span class="app-shortcut-icon app-shortcut-soft shrink-0"><x-icon name="message" class="h-6 w-6" /></span>
                </div>
                <div class="luxury-divider my-4"></div>
                <div class="grid gap-3 text-sm sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-slate-400">Penyewa</p>
                        <p class="mt-1 truncate font-extrabold">{{ $keluhan->penghuni->penyewa->nama_lengkap }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-slate-400">Kamar</p>
                        <p class="mt-1 truncate font-extrabold">{{ $keluhan->penghuni->kamar->nama_kamar }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-slate-400">Tanggal</p>
                        <p class="mt-1 font-extrabold">{{ $keluhan->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
                @if ($keluhan->foto_url)
                    <p class="mt-4 inline-flex items-center gap-2 text-xs font-black text-sky-700"><x-icon name="camera" class="h-4 w-4" /> Ada lampiran</p>
                @endif
            </a>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm lg:col-span-2">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-slate-100 text-slate-500"><x-icon name="message" class="h-7 w-7" /></span>
                <h3 class="mt-4 text-lg font-black">Tidak ada keluhan sesuai filter</h3>
                <p class="mt-2 text-sm text-slate-500">Coba ubah kata kunci, status, atau kategori yang dipilih.</p>
            </div>
        @endforelse
    </section>

    <div>
        {{ $keluhans->links() }}
    </div>
</x-admin-layout>
