<x-penyewa-layout header="Daftar Kamar">
    <form method="GET" class="app-search-panel overflow-hidden rounded-[1.75rem]">
        <label class="app-search-row">
            <span class="app-mini-icon"><x-icon name="search" class="h-5 w-5" /></span>
            <span class="min-w-0 flex-1">
                <span class="block text-xs font-bold text-slate-400">Cari kamar</span>
                <input name="q" value="{{ request('q') }}" placeholder="Nama kamar" class="min-h-0 w-full border-0 bg-transparent p-0 text-sm font-black text-slate-900 shadow-none focus:ring-0">
            </span>
        </label>
        <div class="app-search-grid grid grid-cols-2 lg:grid-cols-4">
            <label class="app-search-row border-r border-slate-200">
                <span class="min-w-0 flex-1">
                    <span class="block text-xs font-bold text-slate-400">Tipe</span>
                    <select name="tipe" class="min-h-0 w-full border-0 bg-transparent p-0 text-xs font-black text-slate-900 shadow-none focus:ring-0">
                        <option value="">Semua Tipe</option>
                        @foreach ($tipes as $tipe)
                            <option value="{{ $tipe }}" @selected(request('tipe') === $tipe)>{{ $tipe }}</option>
                        @endforeach
                    </select>
                </span>
            </label>
            <label class="app-search-row lg:border-r border-slate-200">
                <span class="min-w-0 flex-1">
                    <span class="block text-xs font-bold text-slate-400">Status</span>
                    <select name="status" class="min-h-0 w-full border-0 bg-transparent p-0 text-xs font-black text-slate-900 shadow-none focus:ring-0">
                        <option value="">Semua</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </span>
            </label>
            <label class="app-search-row border-r border-t border-slate-200 lg:border-t-0">
                <span class="min-w-0 flex-1">
                    <span class="block text-xs font-bold text-slate-400">Harga min</span>
                    <input name="harga_min" value="{{ request('harga_min') }}" type="number" placeholder="Rp" class="min-h-0 w-full border-0 bg-transparent p-0 text-xs font-black text-slate-900 shadow-none focus:ring-0">
                </span>
            </label>
            <label class="app-search-row border-t border-slate-200 lg:border-t-0">
                <span class="min-w-0 flex-1">
                    <span class="block text-xs font-bold text-slate-400">Harga max</span>
                    <input name="harga_max" value="{{ request('harga_max') }}" type="number" placeholder="Rp" class="min-h-0 w-full border-0 bg-transparent p-0 text-xs font-black text-slate-900 shadow-none focus:ring-0">
                </span>
            </label>
        </div>
        <div class="p-4 pt-2">
            <button class="w-full rounded-2xl bg-sky-600 px-5 py-3.5 text-sm font-black text-white">Terapkan Filter</button>
        </div>
    </form>

    <div class="mobile-safe-scroll flex gap-2 overflow-x-auto pb-2">
        <a href="{{ route('penyewa.kamar.index') }}" class="filter-chip {{ request()->query() ? '' : 'filter-chip-active' }}">Urutkan</a>
        <a href="{{ route('penyewa.kamar.index', array_merge(request()->except('harga_max'), ['harga_max' => 1500000])) }}" class="filter-chip {{ request('harga_max') ? 'filter-chip-active' : '' }}">Harga</a>
        @foreach ($tipes->take(3) as $tipe)
            <a href="{{ route('penyewa.kamar.index', array_merge(request()->except('tipe'), ['tipe' => $tipe])) }}" class="filter-chip {{ request('tipe') === $tipe ? 'filter-chip-active' : '' }}">{{ $tipe }}</a>
        @endforeach
        <a href="{{ route('penyewa.kamar.index', array_merge(request()->except('status'), ['status' => 'tersedia'])) }}" class="filter-chip {{ request('status') === 'tersedia' ? 'filter-chip-active' : '' }}">Status</a>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        @forelse ($kamars as $kamar)
            <article class="compact-room-card overflow-hidden rounded-3xl">
                <div class="grid grid-cols-[132px_1fr] sm:grid-cols-[220px_1fr]">
                    <a href="{{ route('penyewa.kamar.show', $kamar) }}" class="relative min-h-44 sm:min-h-56">
                        <img src="{{ $kamar->foto_url }}" alt="{{ $kamar->nama_kamar }}" class="absolute inset-0 h-full w-full object-cover">
                        <span class="absolute left-2 top-2 {{ $kamar->status === 'tersedia' ? 'status-badge status-badge-success' : ($kamar->status === 'maintenance' ? 'status-badge status-badge-danger' : 'status-badge status-badge-warning') }}">{{ ucfirst($kamar->status) }}</span>
                    </a>
                    <div class="flex min-w-0 flex-col justify-between p-4">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h2 class="line-clamp-1 text-sm font-black sm:text-lg">{{ $kamar->nama_kamar }}</h2>
                                    <p class="mt-1 line-clamp-1 text-xs font-semibold text-slate-500">Betung, Banyuasin</p>
                                </div>
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-50 text-slate-500"><x-icon name="heart" class="h-4 w-4" /></span>
                            </div>
                            <p class="mt-3 text-base font-black text-sky-700 sm:text-xl">{{ $kamar->harga_format }} <span class="text-[11px] font-bold text-slate-500">/ bulan</span></p>
                            <p class="mt-2 inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-black text-emerald-700">Bebas biaya servis</p>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($kamar->fasilitas->take(3) as $item)
                                <span class="rounded-lg bg-slate-50 px-2 py-1 text-[10px] font-bold text-slate-500">{{ $item->nama_fasilitas }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500 lg:col-span-2">Tidak ada kamar yang cocok dengan filter.</div>
        @endforelse
    </div>

    {{ $kamars->links() }}
</x-penyewa-layout>
