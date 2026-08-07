<x-admin-layout header="Dashboard Admin">
    <section class="premium-surface overflow-hidden rounded-3xl p-6">
        <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr] lg:items-end">
            <div>
                <span class="premium-pill">Executive Overview</span>
                <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">Ringkasan operasional {{ config('app.name') }}</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">Pantau status kamar, pembayaran masuk, tagihan aktif, dan pendapatan dari satu dashboard yang siap dipakai untuk presentasi Semhas maupun operasional harian.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-3xl bg-slate-950 p-5 text-white shadow-xl">
                    <p class="text-xs font-black uppercase tracking-wide text-sky-200">Pendapatan Bulan Ini</p>
                    <p class="mt-3 text-3xl font-black">Rp {{ number_format($stats['pendapatan_bulan_ini'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-3xl bg-white p-5 shadow-xl">
                    <p class="text-xs font-black uppercase tracking-wide text-teal-700">Total Pendapatan</p>
                    <p class="mt-3 text-3xl font-black text-slate-950">Rp {{ number_format($stats['pendapatan_total'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="booking-search-card rounded-[2rem] p-4">
        <div class="mobile-safe-scroll flex gap-2 overflow-x-auto">
            @foreach ([
                ['label' => 'Kamar', 'route' => 'admin.kamar.index', 'icon' => 'K'],
                ['label' => 'Pemesanan', 'route' => 'admin.pemesanan.index', 'icon' => 'P'],
                ['label' => 'DP', 'route' => 'admin.pembayaran-awal.index', 'icon' => 'D'],
                ['label' => 'Tagihan', 'route' => 'admin.tagihan-bulanan.index', 'icon' => 'T'],
                ['label' => 'Laporan', 'route' => 'admin.laporan.index', 'icon' => 'R'],
            ] as $item)
                <a href="{{ route($item['route']) }}" class="service-tab {{ request()->routeIs($item['route'].'*') ? 'service-tab-active' : '' }}">
                    <span class="service-icon">{{ $item['icon'] }}</span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            'Total Kamar' => $stats['total_kamar'],
            'Kamar Tersedia' => $stats['kamar_tersedia'],
            'Kamar Dipesan' => $stats['kamar_dipesan'],
            'Kamar Terisi' => $stats['kamar_terisi'],
            'Maintenance' => $stats['kamar_maintenance'],
            'Penyewa Terdaftar' => $stats['total_penyewa'],
            'Penghuni Aktif' => $stats['penghuni_aktif'],
            'Pemesanan Masuk' => $stats['pemesanan_masuk'],
            'DP Menunggu' => $stats['dp_menunggu'],
            'Bulanan Menunggu' => $stats['bulanan_menunggu'],
            'Tagihan Belum Bayar' => $stats['tagihan_belum_bayar'],
            'Tagihan Terlambat' => $stats['tagihan_terlambat'],
        ] as $label => $value)
            <div class="premium-stat lift-card rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-slate-500">{{ $label }}</p>
                        <p class="mt-3 text-3xl font-black tracking-tight">{{ $value }}</p>
                    </div>
                    <span class="mt-1 h-3 w-3 rounded-full bg-sky-300 shadow-[0_0_0_6px_rgba(186,230,253,.45)]"></span>
                </div>
                <div class="luxury-divider mt-4"></div>
                <p class="mt-3 text-xs font-bold text-slate-400">Terhubung ke data realtime sistem</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="lift-card rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-bold text-slate-500">Grafik Pendapatan Bulanan</p>
                    <p class="mt-1 text-sm text-slate-400">6 bulan terakhir</p>
                </div>
                <span class="premium-pill">Revenue</span>
            </div>
            <div class="mt-5 h-72"><canvas id="incomeChart"></canvas></div>
        </div>
        <div class="lift-card rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-500">Grafik Status Kamar</p>
                    <p class="mt-1 text-sm text-slate-400">Tersedia, dipesan, terisi, maintenance</p>
                </div>
                <span class="premium-pill">Rooms</span>
            </div>
            <div class="mt-5 h-72"><canvas id="roomChart"></canvas></div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black">Tagihan Belum Bayar</h2>
                <a href="{{ route('admin.tagihan-bulanan.index', ['status' => 'belum_bayar']) }}" class="text-sm font-black text-sky-700">Detail</a>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($reminders['belum_bayar'] as $tagihan)
                    <div class="rounded-2xl bg-slate-50 p-4 text-sm">
                        <p class="font-bold">{{ $tagihan->penghuni->penyewa->nama_lengkap }}</p>
                        <p class="text-slate-500">Periode {{ $tagihan->periode }} - {{ $tagihan->jumlah_format }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada tagihan belum bayar.</p>
                @endforelse
            </div>
        </section>
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black">Mendekati Jatuh Tempo</h2>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-black text-amber-700">Reminder</span>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($reminders['mendekati_jatuh_tempo'] as $tagihan)
                    <div class="rounded-2xl bg-amber-50 p-4 text-sm">
                        <p class="font-bold">{{ $tagihan->penghuni->penyewa->nama_lengkap }}</p>
                        <p class="text-amber-700">Jatuh tempo {{ $tagihan->tanggal_jatuh_tempo->format('d/m/Y') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada reminder jatuh tempo dekat.</p>
                @endforelse
            </div>
        </section>
    </div>

    <script>
        window.addEventListener('load', () => {
            new Chart(document.getElementById('incomeChart'), {
                type: 'line',
                data: {
                    labels: @json($pendapatanBulanan->pluck('label')),
                    datasets: [{ label: 'Pendapatan', data: @json($pendapatanBulanan->pluck('value')), borderColor: '#0b74de', backgroundColor: 'rgba(2,184,166,.14)', fill: true, tension: .38, pointRadius: 5, pointBackgroundColor: '#02b8a6' }]
                }
            });
            new Chart(document.getElementById('roomChart'), {
                type: 'doughnut',
                data: {
                    labels: @json($statusKamar->keys()),
                    datasets: [{ data: @json($statusKamar->values()), backgroundColor: ['#10b981','#0ea5e9','#6366f1','#f59e0b'] }]
                }
            });
        });
    </script>
</x-admin-layout>
