<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="grid min-h-screen bg-slate-50 lg:grid-cols-[1fr_520px]">
            <section class="relative hidden overflow-hidden bg-slate-950 lg:block">
                <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1400&q=80" alt="{{ config('app.name') }}" class="absolute inset-0 h-full w-full object-cover opacity-70">
                <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(2,6,23,.84),rgba(3,105,161,.44))]"></div>
                <div class="relative flex h-full flex-col justify-between p-10 text-white">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-600 text-xl font-black shadow-xl">K</span>
                        <span class="text-lg font-black">{{ config('app.name') }}</span>
                    </a>
                    <div class="max-w-2xl">
                        <span class="rounded-full border border-white/25 bg-white/15 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.2em] text-sky-100 backdrop-blur">Smart Boarding House</span>
                        <h1 class="mt-5 text-5xl font-black leading-tight tracking-tight">Masuk, pesan kamar, dan kelola pembayaran dengan pengalaman yang lebih rapi.</h1>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-white/15 p-4 backdrop-blur">
                            <p class="text-2xl font-black">6+</p>
                            <p class="text-xs font-semibold text-slate-200">Kamar demo</p>
                        </div>
                        <div class="rounded-2xl bg-white/15 p-4 backdrop-blur">
                            <p class="text-2xl font-black">PDF</p>
                            <p class="text-xs font-semibold text-slate-200">Laporan</p>
                        </div>
                        <div class="rounded-2xl bg-white/15 p-4 backdrop-blur">
                            <p class="text-2xl font-black">24/7</p>
                            <p class="text-xs font-semibold text-slate-200">Akses web</p>
                        </div>
                    </div>
                </div>
            </section>
            <main class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-6">
                <div class="w-full max-w-md">
                    <a href="{{ route('home') }}" class="mb-6 flex items-center justify-center gap-3 lg:hidden">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-600 text-lg font-black text-white">K</span>
                        <span class="font-black">{{ config('app.name') }}</span>
                    </a>
                    <div class="premium-surface rounded-3xl p-6 sm:p-8">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
