<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="world-class-shell bg-slate-50 font-sans text-slate-900 antialiased">
    <header class="{{ request()->routeIs('home') ? 'hidden md:block' : '' }} sticky top-0 z-40 border-b border-white/70 bg-white/95 shadow-sm backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 sm:py-4 lg:px-8">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-sky-600 text-lg font-black text-white shadow-lg sm:h-11 sm:w-11">K</span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-black leading-tight sm:text-base">{{ config('app.name') }}</span>
                    <span class="hidden text-[11px] font-bold text-slate-400 sm:block">Booking kos, tagihan, keluhan</span>
                </span>
            </a>
            <nav class="hidden items-center gap-6 text-sm font-semibold text-slate-600 md:flex">
                <a href="{{ route('home') }}" class="hover:text-sky-700">Home</a>
                <a href="{{ route('public.kamar.index') }}" class="hover:text-sky-700">Kamar</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-2xl bg-slate-900 px-5 py-3 text-white">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-sky-700">Login</a>
                    <a href="{{ route('register') }}" class="rounded-2xl bg-sky-600 px-5 py-3 text-white hover:bg-sky-700">Daftar</a>
                @endauth
            </nav>
            <div class="flex items-center gap-2 md:hidden">
                <a href="{{ route('public.kamar.index') }}" class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold shadow-sm">Cari</a>
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="rounded-2xl bg-sky-600 px-3 py-2 text-xs font-bold text-white">
                    {{ auth()->check() ? 'Panel' : 'Login' }}
                </a>
            </div>
        </div>
    </header>

    <main class="public-bottom-safe">
        @if (session('success') || session('error') || $errors->any())
            <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
                <x-flash />
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="border-t border-white/70 bg-white/90">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 py-8 text-sm text-slate-600 sm:px-6 md:grid-cols-3 lg:px-8">
            <div>
                <p class="font-bold text-slate-900">Sistem Informasi Manajemen Pengelolaan Kos</p>
                <p class="mt-2">{{ config('app.name') }}, aplikasi web pengelolaan kamar, penghuni, pembayaran, dan keluhan.</p>
            </div>
            <div>
                <p class="font-bold text-slate-900">Kontak Admin</p>
                <a class="mt-2 inline-block text-sky-700 hover:underline" href="https://wa.me/6283179749407" target="_blank">WhatsApp Admin</a>
            </div>
            <div>
                <p class="font-bold text-slate-900">Batasan V1</p>
                <p class="mt-2">Pembayaran dilakukan melalui upload bukti transfer dan divalidasi admin.</p>
            </div>
        </div>
    </footer>

    <nav class="bottom-app-nav md:hidden">
        <div class="mx-auto grid max-w-md grid-cols-5 gap-1">
            @foreach ([
                ['label' => 'Beranda', 'route' => 'home', 'icon' => 'home', 'active' => 'home'],
                ['label' => 'Cari', 'route' => 'public.kamar.index', 'icon' => 'search', 'active' => 'public.kamar.*'],
                ['label' => 'Favorit', 'route' => auth()->check() && auth()->user()->isPenyewa() ? 'penyewa.favorit.index' : 'login', 'icon' => 'heart', 'active' => 'penyewa.favorit.*'],
                ['label' => 'Pesanan', 'route' => auth()->check() ? 'dashboard' : 'login', 'icon' => 'clipboard', 'active' => auth()->check() ? 'dashboard' : 'login'],
                ['label' => 'Akun', 'route' => auth()->check() ? 'dashboard' : 'login', 'icon' => 'user', 'active' => auth()->check() ? 'dashboard' : 'login'],
            ] as $item)
                @php
                    $isActive = request()->routeIs($item['active']);
                @endphp
                <a href="{{ route($item['route']) }}" class="bottom-app-link {{ $isActive ? 'bottom-app-link-active' : '' }}">
                    <span class="bottom-app-icon"><x-icon :name="$item['icon']" class="h-5 w-5" /></span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>
</body>
</html>
