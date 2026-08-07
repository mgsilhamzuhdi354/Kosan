<x-penyewa-layout header="Dashboard Penyewa">
    @php
        $hour = (int) now()->format('H');
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
    @endphp

    <section class="premium-surface overflow-hidden rounded-3xl p-6">
        <div class="grid gap-6 lg:grid-cols-[1fr_360px] lg:items-center">
            <div>
                <span class="premium-pill">{{ $greeting }}, {{ strtok($penyewa->nama_lengkap, ' ') }}</span>
                <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">Mau kelola kos kamu dari mana?</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">Pantau kamar, pemesanan, pembayaran awal, tagihan bulanan, riwayat, dan keluhan dengan tampilan yang enak dibaca di HP.</p>
                <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('penyewa.kamar.index') }}" class="rounded-2xl bg-sky-600 px-5 py-3 text-center text-sm font-black text-white">Cari Kamar</a>
                    <a href="{{ route('penyewa.tagihan.index') }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-center text-sm font-black text-slate-700 shadow-sm">Lihat Tagihan</a>
                </div>
            </div>
            <div class="rounded-3xl bg-slate-950 p-5 text-white shadow-2xl">
                <p class="text-xs font-black uppercase tracking-wide text-sky-200">Status Hunian</p>
                @if ($penghuniAktif)
                    <p class="mt-3 text-3xl font-black">{{ $penghuniAktif->kamar->nama_kamar }}</p>
                    <p class="mt-2 text-sm text-slate-300">Jatuh tempo {{ $penghuniAktif->tanggal_jatuh_tempo->format('d/m/Y') }}</p>
                @else
                    <p class="mt-3 text-3xl font-black">Belum Aktif</p>
                    <p class="mt-2 text-sm text-slate-300">Silakan pilih kamar dan lakukan pemesanan.</p>
                @endif
            </div>
        </div>
    </section>

    <section class="booking-search-card rounded-[2rem] p-4">
        <div class="mobile-safe-scroll grid grid-cols-4 gap-4 sm:grid-cols-5 lg:grid-cols-9">
            @foreach ([
                ['label' => 'Dashboard', 'route' => 'penyewa.dashboard', 'icon' => 'home'],
                ['label' => 'Cari Kamar', 'route' => 'penyewa.kamar.index', 'icon' => 'search'],
                ['label' => 'Pemesanan', 'route' => 'penyewa.pemesanan.index', 'icon' => 'clipboard'],
                ['label' => 'Pembayaran', 'route' => 'penyewa.pembayaran-awal.index', 'icon' => 'credit-card'],
                ['label' => 'Tagihan', 'route' => 'penyewa.tagihan.index', 'icon' => 'tag'],
                ['label' => 'Riwayat', 'route' => 'penyewa.riwayat-pembayaran.index', 'icon' => 'calendar'],
                ['label' => 'Keluhan', 'route' => 'penyewa.keluhan.index', 'icon' => 'message'],
                ['label' => 'Live Chat', 'href' => 'https://wa.me/6283179749407?text=Halo%20Admin%2C%20saya%20ingin%20bertanya%20tentang%20kos.', 'icon' => 'bell'],
                ['label' => 'Profil', 'route' => 'penyewa.profil.edit', 'icon' => 'user'],
            ] as $item)
                @php
                    $href = $item['href'] ?? route($item['route']);
                    $active = isset($item['route']) && request()->routeIs($item['route'].'*');
                @endphp
                <a href="{{ $href }}" @isset($item['href']) target="_blank" @endisset class="app-shortcut">
                    <span class="app-shortcut-icon {{ $active ? '' : 'app-shortcut-soft' }}"><x-icon :name="$item['icon']" class="h-7 w-7" /></span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <div class="grid gap-4 lg:grid-cols-[1fr_360px]">
        <section class="lift-card rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-bold text-slate-500">Profil Penyewa</p>
            <h2 class="mt-1 text-2xl font-black">{{ $penyewa->nama_lengkap }}</h2>
            <p class="mt-2 text-sm text-slate-600">{{ $penyewa->no_hp }} - {{ $penyewa->alamat }}</p>
            <a href="{{ route('penyewa.profil.edit') }}" class="mt-4 inline-block rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black shadow-sm">Edit Profil</a>
        </section>
        <section class="lift-card rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-bold text-slate-500">Kamar Ditempati</p>
            @if ($penghuniAktif)
                <h2 class="mt-1 text-xl font-black">{{ $penghuniAktif->kamar->nama_kamar }}</h2>
                <p class="mt-2 text-sm text-slate-600">Jatuh tempo {{ $penghuniAktif->tanggal_jatuh_tempo->format('d/m/Y') }}</p>
            @else
                <p class="mt-3 text-sm text-slate-500">Belum menjadi penghuni aktif.</p>
            @endif
        </section>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="premium-stat lift-card rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-bold text-slate-500">Status Pemesanan</p>
            <p class="mt-3 text-xl font-black">{{ $pemesananAktif ? ucfirst(str_replace('_',' ', $pemesananAktif->status_pemesanan)) : 'Belum Ada' }}</p>
            @if ($pemesananAktif)
                <p class="mt-1 text-sm text-slate-500">{{ $pemesananAktif->kamar->nama_kamar }}</p>
            @endif
        </section>
        <section class="premium-stat lift-card rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-bold text-slate-500">Pembayaran Awal</p>
            <p class="mt-3 text-xl font-black">{{ $pembayaranAwal ? ucfirst(str_replace('_',' ', $pembayaranAwal->status_pembayaran)) : 'Belum Ada' }}</p>
            @if ($pembayaranAwal)
                <p class="mt-1 text-sm text-slate-500">{{ $pembayaranAwal->jumlah_format }}</p>
            @endif
        </section>
        <section class="premium-stat lift-card rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-bold text-slate-500">Tagihan Aktif</p>
            <p class="mt-3 text-xl font-black">{{ $tagihanAktif->count() }}</p>
            <a href="{{ route('penyewa.tagihan.index') }}" class="mt-2 inline-block text-sm font-black text-sky-700">Lihat tagihan</a>
        </section>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black">Reminder Tagihan</h2>
                <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-black text-sky-700">Aktif</span>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($tagihanAktif as $tagihan)
                    <div class="rounded-2xl bg-slate-50 p-4 text-sm">
                        <p class="font-bold">Periode {{ $tagihan->periode }} - {{ $tagihan->jumlah_format }}</p>
                        <p class="text-slate-500">Jatuh tempo {{ $tagihan->tanggal_jatuh_tempo->format('d/m/Y') }} - {{ ucfirst(str_replace('_',' ', $tagihan->status_tagihan)) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada tagihan aktif.</p>
                @endforelse
            </div>
        </section>
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black">Riwayat Pembayaran Terakhir</h2>
                <a href="{{ route('penyewa.riwayat-pembayaran.index') }}" class="text-sm font-black text-sky-700">Semua</a>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($riwayatPembayaran as $pembayaran)
                    <div class="rounded-2xl bg-slate-50 p-4 text-sm">
                        <p class="font-bold">{{ $pembayaran->jumlah_format }} - {{ ucfirst(str_replace('_',' ', $pembayaran->status_pembayaran)) }}</p>
                        <p class="text-slate-500">Periode {{ $pembayaran->tagihanBulanan->periode }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada riwayat pembayaran.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-penyewa-layout>
