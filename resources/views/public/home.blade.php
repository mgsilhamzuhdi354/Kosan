<x-public-layout :title="config('app.name')">
    @php
        $hour = (int) now()->format('H');
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
        $tipes = $kamars->pluck('tipe_kamar')->unique()->values();
    @endphp

    <section class="app-home-shell">
        <div class="mx-auto max-w-7xl px-4 pb-8 pt-4 sm:px-6 lg:px-8">
            <div class="mb-5 flex items-center justify-between md:hidden">
                <span class="text-sm font-black text-slate-900">{{ now()->format('H:i') }}</span>
                <div class="flex items-center gap-2 text-slate-900">
                    <span class="text-xs font-black">LTE</span>
                    <span class="h-3 w-5 rounded-sm border border-slate-900 after:ml-[2px] after:block after:h-full after:w-3 after:bg-slate-900"></span>
                </div>
            </div>
            <div class="grid gap-8 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                <div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">{{ $greeting }},</p>
                            <h1 class="mt-2 max-w-xl text-2xl font-black tracking-tight text-slate-950 sm:text-5xl">Mau cari kost ke mana?</h1>
                            <p class="mt-3 hidden max-w-2xl text-sm leading-7 text-slate-600 sm:block sm:text-base">Cari kamar, lihat fasilitas, pesan, dan pantau pembayaran dari satu aplikasi web kos.</p>
                        </div>
                        <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm">
                            <x-icon name="bell" class="h-6 w-6" />
                            <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-red-500"></span>
                        </a>
                    </div>

                    <form method="GET" action="{{ route('public.kamar.index') }}" class="app-search-panel mt-6 overflow-hidden rounded-[1.75rem]">
                        <label class="app-search-row">
                            <span class="app-mini-icon"><x-icon name="map-pin" class="h-5 w-5" /></span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-xs font-bold text-slate-400">Lokasi</span>
                                <span class="display-field">Betung, Banyuasin</span>
                                <input name="q" type="hidden" value="">
                            </span>
                            <x-icon name="chevron-right" class="h-5 w-5 text-slate-300" />
                        </label>

                        <div class="app-search-grid grid grid-cols-2">
                            <label class="app-search-row border-r border-slate-200">
                                <span class="app-mini-icon"><x-icon name="calendar" class="h-5 w-5" /></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-xs font-bold text-slate-400">Check-in</span>
                                    <span class="display-field text-xs">{{ now()->translatedFormat('d F Y') }}</span>
                                    <input name="tanggal_masuk" type="hidden" value="{{ now()->format('Y-m-d') }}">
                                </span>
                            </label>
                            <label class="app-search-row">
                                <span class="app-mini-icon"><x-icon name="calendar" class="h-5 w-5" /></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-xs font-bold text-slate-400">Check-out</span>
                                    <span class="display-field text-xs">Tanpa Batas</span>
                                    <input name="check_out" type="hidden" value="">
                                </span>
                                <x-icon name="chevron-right" class="h-5 w-5 text-slate-300" />
                            </label>
                        </div>

                        <label class="app-search-row">
                            <span class="app-mini-icon"><x-icon name="users" class="h-5 w-5" /></span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-xs font-bold text-slate-400">Tipe Kamar</span>
                                <select name="tipe" class="min-h-0 w-full border-0 bg-transparent p-0 text-sm font-black text-slate-900 shadow-none focus:ring-0">
                                    <option value="">Semua Tipe</option>
                                    @foreach ($tipes as $tipe)
                                        <option value="{{ $tipe }}">{{ $tipe }}</option>
                                    @endforeach
                                </select>
                            </span>
                            <x-icon name="chevron-right" class="h-5 w-5 text-slate-300" />
                        </label>

                        <div class="p-4 pt-2">
                            <button class="flex w-full items-center justify-center gap-2 rounded-2xl bg-sky-600 px-5 py-4 text-sm font-black text-white shadow-lg shadow-sky-200">
                                <x-icon name="search" class="h-5 w-5" />
                                <span>Cari Kost</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="hidden lg:block">
                    <div class="relative overflow-hidden rounded-[2rem] shadow-2xl">
                        <img class="h-[520px] w-full object-cover" src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1600&q=80" alt="Foto kos">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
                        <div class="absolute bottom-0 p-8 text-white">
                            <span class="rounded-full bg-white/15 px-4 py-2 text-xs font-black backdrop-blur">{{ config('app.name') }}</span>
                            <h2 class="mt-4 text-4xl font-black">Booking kos lebih mudah dan transparan.</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mobile-safe-scroll mt-7 flex justify-between gap-4 overflow-x-auto pb-2">
                @foreach ([
                    ['label' => 'Semua Kost', 'href' => route('public.kamar.index'), 'icon' => 'home', 'soft' => false],
                    ['label' => 'Promo', 'href' => route('public.kamar.index', ['promo' => 1]), 'icon' => 'tag', 'soft' => true, 'badge' => 'Promo'],
                    ['label' => 'Kost Dekat Kampus', 'href' => route('public.maps', ['dekat' => 'kampus']), 'icon' => 'map-pin', 'soft' => true],
                    ['label' => 'Favorit Saya', 'href' => auth()->check() && auth()->user()->isPenyewa() ? route('penyewa.favorit.index') : route('login'), 'icon' => 'heart', 'soft' => true],
                    ['label' => 'Filter Lainnya', 'href' => route('public.kamar.index'), 'icon' => 'sliders', 'soft' => true],
                ] as $item)
                    <a href="{{ $item['href'] }}" class="app-shortcut">
                        <span class="relative app-shortcut-icon {{ $item['soft'] ? 'app-shortcut-soft' : '' }}">
                            @isset($item['badge'])
                                <span class="absolute -right-2 -top-2 rounded-full bg-pink-500 px-2 py-0.5 text-[10px] font-black text-white">{{ $item['badge'] }}</span>
                            @endisset
                            <x-icon :name="$item['icon']" class="h-7 w-7" />
                        </span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
        <div class="mb-8 hidden grid-cols-3 gap-4 lg:grid">
            @foreach ([
                ['label' => 'Kos aktif', 'value' => $stats['total_kos'] ?? 0],
                ['label' => 'Kamar terdaftar', 'value' => $stats['total_kamar'] ?? 0],
                ['label' => 'Kos promo', 'value' => $stats['promo'] ?? 0],
            ] as $item)
                <div class="premium-stat rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-black text-slate-500">{{ $item['label'] }}</p>
                    <p class="mt-3 text-4xl font-black text-slate-950">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-sky-700">Penyedia Kost Lain</p>
                <h2 class="mt-1 text-xl font-black tracking-tight sm:text-3xl">Pilihan kost dari beberapa penyedia</h2>
                <p class="mt-1 text-xs font-bold text-slate-500 sm:text-sm">Data kost dari aset lokal ditampilkan sebagai katalog multi-penyedia.</p>
            </div>
            <a href="{{ route('public.maps') }}" class="shrink-0 text-sm font-black text-sky-700">Lihat peta &gt;</a>
        </div>

        <div class="mobile-safe-scroll mt-5 flex gap-4 overflow-x-auto pb-4 lg:grid lg:grid-cols-3 lg:overflow-visible">
            @forelse ($featuredKos as $kos)
                @php
                    $startingPrice = $kos->kamars->min('harga_bulanan');
                @endphp
                <article class="compact-room-card w-[290px] shrink-0 overflow-hidden rounded-3xl lg:w-auto">
                    <a href="{{ route('public.kamar.index', ['lokasi' => $kos->nama_kos]) }}" class="block">
                        <div class="relative h-44">
                            <img src="{{ $kos->foto_url }}" alt="{{ $kos->nama_kos }}" class="h-full w-full object-cover">
                            @if ($kos->is_promoted)
                                <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1 text-xs font-black text-sky-700 shadow-sm">Promo</span>
                            @endif
                            <span class="absolute bottom-3 right-3 rounded-full bg-slate-950/80 px-3 py-1 text-xs font-black text-white">{{ $kos->kamars_count }} kamar tersedia</span>
                        </div>
                        <div class="p-4">
                            <h3 class="line-clamp-1 text-lg font-black">{{ $kos->nama_kos }}</h3>
                            <p class="mt-1 line-clamp-1 text-xs font-semibold text-slate-500">{{ $kos->alamat }}</p>
                            <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                                <div class="rounded-2xl bg-slate-50 p-3">
                                    <p class="font-bold text-slate-400">Penyedia</p>
                                    <p class="mt-1 truncate font-black text-slate-800">{{ $kos->penyediaKos?->nama_lengkap }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-3">
                                    <p class="font-bold text-slate-400">Mulai dari</p>
                                    <p class="mt-1 font-black text-sky-700">{{ $startingPrice ? 'Rp '.number_format($startingPrice, 0, ',', '.') : '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500 lg:col-span-3">Belum ada data kos aktif.</div>
            @endforelse
        </div>

        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-black tracking-tight sm:text-3xl">Rekomendasi Kost untukmu</h2>
                <p class="mt-1 text-xs font-bold text-slate-500 sm:text-sm">Kamar tersedia dengan informasi harga dan fasilitas.</p>
            </div>
            <a href="{{ route('public.kamar.index') }}" class="shrink-0 text-sm font-black text-sky-700">Lihat semua &gt;</a>
        </div>

        <div class="mobile-safe-scroll mt-5 flex gap-4 overflow-x-auto pb-3 lg:grid lg:grid-cols-3 lg:overflow-visible">
            @forelse ($kamars as $kamar)
                <article class="compact-room-card w-[265px] shrink-0 overflow-hidden rounded-3xl lg:w-auto">
                    <div class="relative compact-room-image">
                        <img src="{{ $kamar->foto_url }}" alt="{{ $kamar->nama_kamar }}" class="h-full w-full object-cover">
                        <span class="absolute left-3 top-3 status-badge status-badge-success">{{ ucfirst($kamar->status) }}</span>
                        <span class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/95 text-slate-600 shadow"><x-icon name="heart" class="h-5 w-5" /></span>
                    </div>
                    <div class="p-4">
                        <h3 class="line-clamp-1 text-sm font-black">{{ $kamar->kos?->nama_kos }} - {{ $kamar->nama_kamar }}</h3>
                        <p class="mt-1 line-clamp-1 text-xs font-semibold text-slate-500">{{ $kamar->kos?->alamat ?? 'Betung, Banyuasin' }}</p>
                        <p class="mt-3 text-lg font-black text-sky-700">{{ $kamar->harga_format }} <span class="text-xs font-bold text-slate-500">/ bulan</span></p>
                        <p class="mt-2 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-black text-emerald-700">Bebas biaya servis</p>
                        <div class="mt-4 grid grid-cols-3 gap-2 text-center text-[11px] font-bold text-slate-500">
                            @foreach ($kamar->fasilitas->take(3) as $item)
                                <span class="flex flex-col items-center gap-1 rounded-xl bg-slate-50 px-2 py-2">
                                    <x-icon :name="$loop->index === 0 ? 'bed' : ($loop->index === 1 ? 'wifi' : 'credit-card')" class="h-4 w-4 text-slate-500" />
                                    <span>{{ $item->nama_fasilitas }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">Belum ada kamar tersedia.</div>
            @endforelse
        </div>
    </section>

    <section class="mx-auto hidden max-w-7xl px-4 pb-10 sm:px-6 lg:block lg:px-8">
        <div class="grid gap-5 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-sky-700">Cari di Peta</p>
                <h2 class="mt-2 text-4xl font-black tracking-tight">Lihat lokasi kost aktif dari maps.</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">Peta membantu pencari kos melihat posisi kos, alamat, dan akses ke daftar kamar dari marker.</p>
                <a href="{{ route('public.maps') }}" class="mt-5 inline-flex rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white">Buka Peta Kost</a>
            </div>
            <div id="homeKosMap" class="h-[360px] overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm"></div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-16 pt-2 sm:px-6 lg:px-8">
        <div class="trust-banner overflow-hidden rounded-3xl px-5 py-5 sm:px-7 sm:py-6">
            <div class="trust-banner-grid sm:grid sm:grid-cols-[1fr_210px] sm:items-center sm:gap-4">
                <div>
                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-sky-700">Pasti Nyaman</span>
                    <h2 class="mt-3 max-w-md text-xl font-black leading-tight tracking-tight sm:text-2xl">Booking kost lebih mudah dengan jaminan terpercaya</h2>
                    <div class="mt-5 grid grid-cols-3 gap-2 sm:max-w-lg">
                        @foreach (['Harga Transparan', 'Foto & Info Lengkap', 'Proses Aman'] as $item)
                            <div class="flex flex-col gap-2 text-[11px] font-black leading-tight text-slate-700 sm:flex-row sm:items-center sm:text-sm">
                                <span class="app-mini-icon h-9 w-9"><x-icon :name="$loop->index === 0 ? 'tag' : ($loop->index === 1 ? 'camera' : 'shield')" class="h-5 w-5" /></span>
                                <span class="max-w-24">{{ $item }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="trust-illustration">
                    <span class="trust-bird"></span>
                    <span class="trust-plant"><span class="trust-pot"></span></span>
                    <span class="trust-house"><span class="trust-door"></span><span class="trust-window"></span></span>
                </div>
            </div>
        </div>
    </section>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        window.addEventListener('load', () => {
            const target = document.getElementById('homeKosMap');
            if (!target || typeof L === 'undefined') return;
            const markers = @json($kosMarkers ?? []);
            const map = L.map(target).setView([markers[0]?.lat || -2.8836, markers[0]?.lng || 104.2169], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
            markers.forEach((item) => {
                L.marker([item.lat, item.lng]).addTo(map).bindPopup(`<strong>${item.name}</strong><br>${item.address}<br><a href="${item.url}">Lihat kamar</a>`);
            });
        });
    </script>
</x-public-layout>
