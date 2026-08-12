<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Penyewa - '.config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="world-class-shell bg-slate-50 font-sans text-slate-900 antialiased">
    @php
        $menus = [];
    @endphp
    <header class="sticky top-0 z-40 border-b border-white/70 bg-white/95 shadow-sm backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-6 sm:py-4 lg:px-8">
            <a href="{{ route('penyewa.dashboard') }}" class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-sky-600 text-lg font-black text-white shadow-lg sm:h-11 sm:w-11">K</span>
                <span class="min-w-0">
                    <span class="block text-[11px] font-extrabold uppercase tracking-[0.18em] text-sky-700">Penyewa / Penghuni</span>
                    <span class="block truncate text-lg font-black">{{ config('app.name') }}</span>
                </span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-2xl bg-slate-900 px-4 py-3 text-xs font-bold text-white sm:text-sm">Logout</button>
            </form>
        </div>
    </header>

    <main class="app-bottom-safe mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <div class="premium-surface rounded-3xl p-5 sm:p-6">
            <p class="text-[11px] font-extrabold uppercase tracking-[0.2em] text-sky-700">Area Penyewa</p>
            <div class="mt-2 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                <h1 class="text-2xl font-black tracking-tight sm:text-3xl">{{ $header ?? 'Dashboard' }}</h1>
                <p class="text-sm font-semibold text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
        <x-flash />
        {{ $slot }}
    </main>

    <nav class="bottom-app-nav md:hidden">
        <div class="mx-auto grid max-w-md grid-cols-5 gap-1">
            @foreach ([
                ['label' => 'Beranda', 'route' => 'penyewa.dashboard', 'icon' => 'home'],
                ['label' => 'Cari', 'route' => 'penyewa.kamar.index', 'icon' => 'search'],
                ['label' => 'Favorit', 'route' => 'penyewa.favorit.index', 'icon' => 'heart'],
                ['label' => 'Pesanan', 'route' => 'penyewa.pemesanan.index', 'icon' => 'clipboard'],
                ['label' => 'Akun', 'route' => 'penyewa.profil.edit', 'icon' => 'user'],
            ] as $item)
                <a href="{{ route($item['route']) }}" class="bottom-app-link {{ request()->routeIs($item['route'].'*') ? 'bottom-app-link-active' : '' }}">
                    <span class="bottom-app-icon"><x-icon :name="$item['icon']" class="h-5 w-5" /></span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>
    <x-theme-switcher />
</body>
</html>
