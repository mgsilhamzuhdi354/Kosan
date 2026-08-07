<x-penyedia-layout header="Dashboard Penyedia">
    <section class="premium-surface rounded-3xl p-6">
        <span class="premium-pill">Panel Penyedia Kos</span>
        <h2 class="mt-4 text-3xl font-black tracking-tight">Kelola kos dan kamar dari satu dashboard.</h2>
        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">Pantau data kos, kamar tersedia, pemesanan masuk, dan pembayaran awal yang menunggu validasi.</p>
    </section>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            'Total Kos' => $stats['total_kos'],
            'Total Kamar' => $stats['total_kamar'],
            'Kamar Tersedia' => $stats['kamar_tersedia'],
            'Pemesanan Masuk' => $stats['pemesanan_masuk'],
            'DP Menunggu' => $stats['dp_menunggu'],
        ] as $label => $value)
            <div class="premium-stat rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">{{ $label }}</p>
                <p class="mt-3 text-3xl font-black">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-black">Kos Saya</h2>
            <a href="{{ route('penyedia.kamar.create') }}" class="rounded-2xl bg-sky-600 px-4 py-3 text-sm font-black text-white">Tambah Kamar</a>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            @foreach ($kos as $item)
                <article class="rounded-2xl bg-slate-50 p-4">
                    <p class="font-black">{{ $item->nama_kos }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $item->alamat }}</p>
                    <p class="mt-3 text-xs font-black text-sky-700">{{ $item->kamars_count ?? $item->kamars()->count() }} kamar</p>
                </article>
            @endforeach
        </div>
    </section>
</x-penyedia-layout>
