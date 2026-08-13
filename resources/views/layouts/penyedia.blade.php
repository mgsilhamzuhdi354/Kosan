<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Penyedia Kos - '.config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="world-class-shell bg-slate-100 font-sans text-slate-900 antialiased">
    <div class="min-h-screen md:flex">
        <aside class="hidden w-[18rem] shrink-0 border-r border-white/70 bg-white/90 shadow-[18px_0_45px_rgba(15,23,42,0.06)] backdrop-blur-xl md:block">
            <div class="sticky top-0 flex h-screen flex-col p-5">
                <div class="premium-surface rounded-2xl p-4">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-sky-700">Panel Penyedia Kos</p>
                    <p class="mt-1 text-lg font-black">{{ config('app.name') }}</p>
                </div>
                <nav class="mt-5 flex-1 space-y-2">
                    @foreach ([
                        ['label' => 'Dashboard', 'route' => 'penyedia.dashboard', 'icon' => 'home'],
                        ['label' => 'Data Kos', 'route' => 'penyedia.kos.index', 'icon' => 'map-pin'],
                        ['label' => 'Fasilitas', 'route' => 'penyedia.fasilitas.index', 'icon' => 'tag'],
                        ['label' => 'Data Kamar', 'route' => 'penyedia.kamar.index', 'icon' => 'bed'],
                        ['label' => 'Pemesanan', 'route' => 'penyedia.pemesanan.index', 'icon' => 'clipboard'],
                        ['label' => 'Keuangan', 'route' => 'penyedia.keuangan.index', 'icon' => 'credit-card'],
                    ] as $item)
                        <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-black {{ request()->routeIs($item['route'].'*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-100' : 'bg-white text-slate-600 shadow-sm' }}">
                            <x-icon :name="$item['icon']" class="h-5 w-5" />
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full rounded-2xl bg-slate-900 px-4 py-3 text-sm font-bold text-white">Logout</button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-30 border-b border-white/70 bg-white/90 backdrop-blur-xl">
                <div class="flex items-center justify-between gap-3 px-4 py-4 sm:px-6">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.2em] text-sky-700">Penyedia Kos</p>
                        <h1 class="text-xl font-black tracking-tight sm:text-3xl">{{ $header ?? 'Dashboard' }}</h1>
                    </div>
                    <a href="{{ route('home') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm">Lihat Public</a>
                </div>
                <nav class="mobile-safe-scroll flex gap-2 overflow-x-auto border-t border-slate-200/70 px-4 py-3 md:hidden">
                    <a href="{{ route('penyedia.dashboard') }}" class="shrink-0 rounded-2xl px-4 py-2.5 text-xs font-extrabold {{ request()->routeIs('penyedia.dashboard') ? 'bg-sky-600 text-white' : 'bg-white text-slate-700 shadow-sm' }}">Dashboard</a>
                    <a href="{{ route('penyedia.kos.index') }}" class="shrink-0 rounded-2xl px-4 py-2.5 text-xs font-extrabold {{ request()->routeIs('penyedia.kos.*') ? 'bg-sky-600 text-white' : 'bg-white text-slate-700 shadow-sm' }}">Kos</a>
                    <a href="{{ route('penyedia.fasilitas.index') }}" class="shrink-0 rounded-2xl px-4 py-2.5 text-xs font-extrabold {{ request()->routeIs('penyedia.fasilitas.*') ? 'bg-sky-600 text-white' : 'bg-white text-slate-700 shadow-sm' }}">Fasilitas</a>
                    <a href="{{ route('penyedia.kamar.index') }}" class="shrink-0 rounded-2xl px-4 py-2.5 text-xs font-extrabold {{ request()->routeIs('penyedia.kamar.*') ? 'bg-sky-600 text-white' : 'bg-white text-slate-700 shadow-sm' }}">Kamar</a>
                    <a href="{{ route('penyedia.pemesanan.index') }}" class="shrink-0 rounded-2xl px-4 py-2.5 text-xs font-extrabold {{ request()->routeIs('penyedia.pemesanan.*') ? 'bg-sky-600 text-white' : 'bg-white text-slate-700 shadow-sm' }}">Pemesanan</a>
                    <a href="{{ route('penyedia.keuangan.index') }}" class="shrink-0 rounded-2xl px-4 py-2.5 text-xs font-extrabold {{ request()->routeIs('penyedia.keuangan.*') ? 'bg-sky-600 text-white' : 'bg-white text-slate-700 shadow-sm' }}">Keuangan</a>
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
</body>
</html>
