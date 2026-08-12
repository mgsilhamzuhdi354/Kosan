<x-admin-layout header="Report & Laporan">
    @php
        $pdfParams = array_merge(['type' => $type], request()->query());
        $showDateFilter = in_array($type, ['penyewaan', 'pemesanan', 'pembayaran-awal', 'pembayaran-bulanan', 'pendapatan'], true);
        $showMonthFilter = $type === 'tagihan-bulanan';
    @endphp

    <section class="premium-surface rounded-3xl p-5 sm:p-6">
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <span class="premium-pill">Rental Report</span>
                <h2 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">{{ $title }}</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Lihat ringkasan data penyewaan, cek hasil filter di layar, lalu cetak PDF saat data sudah sesuai.</p>
            </div>
            <a href="{{ route('admin.laporan.pdf', $pdfParams) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white">
                <x-icon name="clipboard" class="h-5 w-5" />
                <span>Cetak PDF</span>
            </a>
        </div>
    </section>

    <div class="grid gap-4 lg:grid-cols-[300px_1fr]">
        <aside class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="px-3 text-xs font-black uppercase tracking-wide text-slate-400">Jenis Laporan</p>
            <div class="mt-3 space-y-1">
                @foreach ($types as $key => $label)
                    <a href="{{ route('admin.laporan.index', $key) }}" class="flex items-center justify-between rounded-2xl px-3 py-3 text-sm font-bold {{ $type === $key ? 'bg-sky-600 text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">
                        <span>{{ $label }}</span>
                        @if ($type === $key)
                            <span class="h-2 w-2 rounded-full bg-white/80"></span>
                        @endif
                    </a>
                @endforeach
            </div>
        </aside>

        <div class="space-y-4">
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($summary as $item)
                    <article class="premium-stat rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-black uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                        <p class="mt-3 text-2xl font-black tracking-tight">{{ $item['value'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <form method="GET" action="{{ route('admin.laporan.index', $type) }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-5 xl:items-end">
                    @if ($showDateFilter)
                        <div>
                            <label class="text-sm font-bold text-slate-700">Tanggal Awal</label>
                            <input name="tanggal_awal" type="date" value="{{ request('tanggal_awal') }}" class="mt-1 w-full">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-700">Tanggal Akhir</label>
                            <input name="tanggal_akhir" type="date" value="{{ request('tanggal_akhir') }}" class="mt-1 w-full">
                        </div>
                    @endif

                    @if ($showMonthFilter)
                        <div>
                            <label class="text-sm font-bold text-slate-700">Bulan</label>
                            <input name="bulan" type="number" min="1" max="12" value="{{ request('bulan') }}" class="mt-1 w-full">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-slate-700">Tahun</label>
                            <input name="tahun" type="number" value="{{ request('tahun') }}" class="mt-1 w-full">
                        </div>
                    @endif

                    @if ($statusOptions)
                        <div>
                            <label class="text-sm font-bold text-slate-700">Status</label>
                            <select name="status" class="mt-1 w-full">
                                <option value="">Semua status</option>
                                @foreach ($statusOptions as $status)
                                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl bg-sky-600 px-5 py-3 text-sm font-black text-white">
                        <x-icon name="sliders" class="h-5 w-5" />
                        <span>Tampilkan</span>
                    </button>
                    <a href="{{ route('admin.laporan.index', $type) }}" class="inline-flex min-h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm">Reset</a>
                </form>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col justify-between gap-2 border-b border-slate-200 p-5 sm:flex-row sm:items-end">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-slate-400">Preview Data</p>
                        <h3 class="mt-1 text-xl font-black">{{ $rows->count() }} data ditemukan</h3>
                    </div>
                    @if ($totalPendapatan > 0)
                        <p class="rounded-2xl bg-slate-50 px-4 py-2 text-sm font-black text-slate-700">Total: Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
                    @endif
                </div>

                <div class="mobile-safe-scroll overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead>
                            <tr class="text-xs font-black uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-4">No</th>
                                <th class="px-5 py-4">Data Utama</th>
                                <th class="px-5 py-4">Keterangan</th>
                                <th class="px-5 py-4">Status / Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td class="px-5 py-4 font-bold">{{ $loop->iteration }}</td>
                                    <td class="px-5 py-4">
                                        @if ($type === 'penyewaan')
                                            <p class="font-extrabold">{{ $row->penyewa->nama_lengkap }}</p>
                                            <p class="text-slate-500">{{ $row->kamar->nama_kamar }} - {{ $row->kamar->kos?->nama_kos }}</p>
                                        @elseif ($type === 'kamar')
                                            <p class="font-extrabold">{{ $row->nama_kamar }}</p>
                                            <p class="text-slate-500">{{ $row->tipe_kamar }}</p>
                                        @elseif ($type === 'penyewa')
                                            <p class="font-extrabold">{{ $row->nama_lengkap }}</p>
                                            <p class="text-slate-500">{{ $row->user->email }}</p>
                                        @elseif ($type === 'penghuni')
                                            <p class="font-extrabold">{{ $row->penyewa->nama_lengkap }}</p>
                                            <p class="text-slate-500">{{ $row->kamar->nama_kamar }}</p>
                                        @elseif ($type === 'pemesanan')
                                            <p class="font-extrabold">{{ $row->penyewa->nama_lengkap }}</p>
                                            <p class="text-slate-500">{{ $row->kamar->nama_kamar }}</p>
                                        @elseif ($type === 'pembayaran-awal')
                                            <p class="font-extrabold">{{ $row->pemesanan->penyewa->nama_lengkap }}</p>
                                            <p class="text-slate-500">{{ $row->pemesanan->kamar->nama_kamar }}</p>
                                        @elseif (in_array($type, ['tagihan-bulanan', 'terlambat'], true))
                                            <p class="font-extrabold">{{ $row->penghuni->penyewa->nama_lengkap }}</p>
                                            <p class="text-slate-500">{{ $row->penghuni->kamar->nama_kamar }}</p>
                                        @else
                                            <p class="font-extrabold">{{ $row->tagihanBulanan->penghuni->penyewa->nama_lengkap }}</p>
                                            <p class="text-slate-500">{{ $row->tagihanBulanan->penghuni->kamar->nama_kamar }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">
                                        @if ($type === 'penyewaan')
                                            Masuk {{ $row->tanggal_masuk->format('d/m/Y') }}<br>Jatuh tempo {{ $row->tanggal_jatuh_tempo->format('d/m/Y') }}
                                        @elseif ($type === 'kamar')
                                            {{ $row->fasilitas->pluck('nama_fasilitas')->join(', ') ?: '-' }}
                                        @elseif ($type === 'penyewa')
                                            {{ $row->no_hp }}<br>{{ $row->alamat }}
                                        @elseif ($type === 'penghuni')
                                            Masuk {{ $row->tanggal_masuk->format('d/m/Y') }}
                                        @elseif ($type === 'pemesanan')
                                            Pesan {{ $row->tanggal_pesan->format('d/m/Y') }}<br>Masuk {{ $row->tanggal_masuk->format('d/m/Y') }}
                                        @elseif ($type === 'pembayaran-awal')
                                            Bayar {{ optional($row->tanggal_bayar)->format('d/m/Y') ?: '-' }}
                                        @elseif (in_array($type, ['tagihan-bulanan', 'terlambat'], true))
                                            Periode {{ $row->periode }}<br>Jatuh tempo {{ $row->tanggal_jatuh_tempo->format('d/m/Y') }}
                                        @else
                                            Periode {{ $row->tagihanBulanan->periode }}<br>Bayar {{ $row->tanggal_bayar->format('d/m/Y') }}
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($type === 'penyewaan')
                                            <span class="status-badge">{{ ucfirst($row->status_penghuni) }}</span><br>
                                            <span class="mt-2 inline-block font-black">{{ $row->harga_format }}</span>
                                        @elseif ($type === 'kamar')
                                            <span class="status-badge">{{ ucfirst($row->status) }}</span><br>
                                            <span class="mt-2 inline-block font-black">{{ $row->harga_format }}</span>
                                        @elseif ($type === 'penghuni')
                                            <span class="status-badge">{{ ucfirst($row->status_penghuni) }}</span><br>
                                            <span class="mt-2 inline-block font-black">{{ $row->harga_format }}</span>
                                        @elseif ($type === 'pemesanan')
                                            <span class="status-badge">{{ ucfirst(str_replace('_', ' ', $row->status_pemesanan)) }}</span>
                                        @elseif ($type === 'pembayaran-awal')
                                            <span class="status-badge">{{ ucfirst(str_replace('_', ' ', $row->status_pembayaran)) }}</span><br>
                                            <span class="mt-2 inline-block font-black">{{ $row->jumlah_format }}</span>
                                        @elseif (in_array($type, ['tagihan-bulanan', 'terlambat'], true))
                                            <span class="status-badge">{{ ucfirst(str_replace('_', ' ', $row->status_tagihan)) }}</span><br>
                                            <span class="mt-2 inline-block font-black">{{ $row->jumlah_format }}</span>
                                        @elseif (in_array($type, ['pembayaran-bulanan', 'pendapatan'], true))
                                            <span class="status-badge">{{ ucfirst(str_replace('_', ' ', $row->status_pembayaran)) }}</span><br>
                                            <span class="mt-2 inline-block font-black">{{ $row->jumlah_format }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-slate-500">Tidak ada data sesuai filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-admin-layout>
