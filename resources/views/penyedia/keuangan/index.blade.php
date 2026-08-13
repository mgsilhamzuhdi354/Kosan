<x-penyedia-layout header="Keuangan">
    <div class="grid gap-4 md:grid-cols-3">
        @foreach ([
            'Total Uang Masuk' => $stats['total_lunas'],
            'Bulan Ini' => $stats['bulan_ini'],
            'Menunggu Validasi' => $stats['menunggu'],
        ] as $label => $value)
            <div class="premium-stat rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">{{ $label }}</p>
                <p class="mt-3 text-2xl font-black">
                    {{ is_numeric($value) && $label !== 'Menunggu Validasi' ? 'Rp '.number_format($value, 0, ',', '.') : $value }}
                </p>
            </div>
        @endforeach
    </div>

    <form method="GET" class="flex gap-2">
        <select name="status" class="rounded-2xl border-slate-300 text-sm">
            <option value="">Semua status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <button class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-black text-white">Filter</button>
    </form>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-xl font-black">Pembayaran Awal</h2>
        <div class="mt-4 grid gap-4">
            @forelse ($pembayaranAwal as $pembayaran)
                <article class="rounded-2xl bg-slate-50 p-4">
                    <div class="flex flex-col justify-between gap-4 lg:flex-row">
                        <div>
                            <p class="font-black">{{ $pembayaran->pemesanan->penyewa->nama_lengkap }} - {{ $pembayaran->pemesanan->kamar->nama_kamar }}</p>
                            <p class="text-sm text-slate-500">{{ $pembayaran->pemesanan->kamar->kos?->nama_kos }} - {{ $pembayaran->jumlah_format }} - {{ ucfirst(str_replace('_', ' ', $pembayaran->status_pembayaran)) }}</p>
                            @if ($pembayaran->bukti_url)
                                <a target="_blank" class="mt-2 inline-block text-sm font-bold text-sky-700" href="{{ $pembayaran->bukti_url }}">Lihat Bukti</a>
                            @endif
                        </div>
                        @if ($pembayaran->status_pembayaran === 'menunggu_konfirmasi')
                            <div class="grid gap-2 sm:min-w-80">
                                <form method="POST" action="{{ route('penyedia.pembayaran-awal.approve', $pembayaran) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="w-full rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-black text-white">Setujui</button>
                                </form>
                                <form method="POST" action="{{ route('penyedia.pembayaran-awal.reject', $pembayaran) }}" class="flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input name="catatan_admin" placeholder="Catatan tolak" class="min-w-0 flex-1 rounded-2xl border-slate-300 text-sm" required>
                                    <button class="rounded-2xl bg-red-600 px-4 py-2 text-sm font-black text-white">Tolak</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <p class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">Belum ada pembayaran awal.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $pembayaranAwal->links() }}</div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-xl font-black">Pembayaran Bulanan</h2>
        <div class="mt-4 grid gap-4">
            @forelse ($pembayaranBulanan as $pembayaran)
                <article class="rounded-2xl bg-slate-50 p-4">
                    <div class="flex flex-col justify-between gap-4 lg:flex-row">
                        <div>
                            <p class="font-black">{{ $pembayaran->tagihanBulanan->penghuni->penyewa->nama_lengkap }} - {{ $pembayaran->tagihanBulanan->penghuni->kamar->nama_kamar }}</p>
                            <p class="text-sm text-slate-500">{{ $pembayaran->tagihanBulanan->penghuni->kamar->kos?->nama_kos }} - {{ $pembayaran->jumlah_format }} - Periode {{ $pembayaran->tagihanBulanan->periode }} - {{ ucfirst(str_replace('_', ' ', $pembayaran->status_pembayaran)) }}</p>
                            <div class="mt-2 flex gap-3 text-sm font-bold">
                                <a class="text-sky-700" target="_blank" href="{{ $pembayaran->bukti_url }}">Lihat Bukti</a>
                                @if ($pembayaran->status_pembayaran === 'lunas')
                                    <a class="text-emerald-700" href="{{ route('penyedia.pembayaran-bulanan.receipt', $pembayaran) }}">Cetak Bukti</a>
                                @endif
                            </div>
                        </div>
                        @if ($pembayaran->status_pembayaran === 'menunggu_konfirmasi')
                            <div class="grid gap-2 sm:min-w-80">
                                <form method="POST" action="{{ route('penyedia.pembayaran-bulanan.approve', $pembayaran) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="w-full rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-black text-white">Setujui</button>
                                </form>
                                <form method="POST" action="{{ route('penyedia.pembayaran-bulanan.reject', $pembayaran) }}" class="flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input name="catatan_admin" placeholder="Catatan tolak" class="min-w-0 flex-1 rounded-2xl border-slate-300 text-sm" required>
                                    <button class="rounded-2xl bg-red-600 px-4 py-2 text-sm font-black text-white">Tolak</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <p class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">Belum ada pembayaran bulanan.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $pembayaranBulanan->links() }}</div>
    </section>
</x-penyedia-layout>
