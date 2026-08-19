<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin - '.config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="world-class-shell bg-slate-100 font-sans text-slate-900 antialiased">
    @php
        $menus = [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard'],
            ['label' => 'Data Kamar', 'route' => 'admin.kamar.index'],
            ['label' => 'Fasilitas', 'route' => 'admin.fasilitas.index'],
            ['label' => 'Akun Pengguna', 'route' => 'admin.akun.index'],
            ['label' => 'Data Penyewa', 'route' => 'admin.penyewa.index'],
            ['label' => 'Pemesanan', 'route' => 'admin.pemesanan.index'],
            ['label' => 'Pembayaran Awal', 'route' => 'admin.pembayaran-awal.index'],
            ['label' => 'Penghuni Aktif', 'route' => 'admin.penghuni.index'],
            ['label' => 'Tagihan Bulanan', 'route' => 'admin.tagihan-bulanan.index'],
            ['label' => 'Pembayaran Bulanan', 'route' => 'admin.pembayaran-bulanan.index'],
            ['label' => 'Keluhan', 'route' => 'admin.keluhan.index'],
            ['label' => 'Laporan', 'route' => 'admin.laporan.index'],
        ];
    @endphp
    <div class="min-h-screen md:flex">
        <aside class="hidden w-[19rem] shrink-0 border-r border-white/70 bg-white/90 shadow-[18px_0_45px_rgba(15,23,42,0.06)] backdrop-blur-xl md:block">
            <div class="sticky top-0 flex h-screen flex-col">
                <div class="p-5">
                    <div class="premium-surface rounded-2xl p-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-600 text-xl font-black text-white shadow-lg">K</span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-sky-700">Admin Suite</p>
                                <p class="truncate text-base font-black leading-tight">{{ config('app.name') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <nav class="mobile-safe-scroll flex-1 space-y-1 overflow-y-auto px-4 pb-4">
                    @foreach ($menus as $menu)
                        <a href="{{ route($menu['route']) }}" class="group flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-bold {{ request()->routeIs($menu['route'].'*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-200' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">
                            <span>{{ $menu['label'] }}</span>
                            <span class="{{ request()->routeIs($menu['route'].'*') ? 'bg-white/25' : 'bg-slate-200 group-hover:bg-slate-300' }} h-2 w-2 rounded-full"></span>
                        </a>
                    @endforeach
                </nav>
                <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-200/70 p-4">
                    @csrf
                    <button class="w-full rounded-2xl bg-slate-900 px-4 py-3 text-sm font-bold text-white">Logout</button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-30 border-b border-white/70 bg-white/90 backdrop-blur-xl">
                <div class="flex items-center justify-between gap-3 px-4 py-4 sm:px-6">
                    <div class="min-w-0">
                        <p class="truncate text-[11px] font-extrabold uppercase tracking-[0.2em] text-sky-700">Admin / Pemilik Kos</p>
                        <h1 class="truncate text-xl font-black tracking-tight sm:text-3xl">{{ $header ?? 'Dashboard' }}</h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="hidden rounded-2xl border border-slate-200 bg-white px-4 py-2 text-right sm:block">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Masuk sebagai</p>
                            <p class="text-sm font-extrabold">{{ auth()->user()->name }}</p>
                        </div>
                        <a href="{{ route('home') }}" class="hidden rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm sm:inline-block">Lihat Home</a>
                        <form method="POST" action="{{ route('logout') }}" class="md:hidden">
                            @csrf
                            <button class="rounded-2xl bg-slate-900 px-4 py-3 text-xs font-bold text-white">Logout</button>
                        </form>
                    </div>
                </div>
                <nav class="mobile-safe-scroll flex gap-2 overflow-x-auto border-t border-slate-200/70 px-4 py-3 md:hidden">
                    @foreach ($menus as $menu)
                        <a href="{{ route($menu['route']) }}" class="shrink-0 rounded-2xl px-4 py-2.5 text-xs font-extrabold {{ request()->routeIs($menu['route'].'*') ? 'bg-sky-600 text-white shadow-md shadow-sky-200' : 'bg-white text-slate-700 shadow-sm' }}">{{ $menu['label'] }}</a>
                    @endforeach
                </nav>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                <div class="mx-auto max-w-7xl space-y-6">
                    <x-flash />
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
    <x-theme-switcher />
</body>
</html>
