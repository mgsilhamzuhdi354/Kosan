<x-admin-layout header="Pembayaran Awal">
    <form method="GET" class="flex gap-2">
        <select name="status" class="rounded-lg border-slate-300 text-sm">
            <option value="">Semua status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_',' ', $status)) }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Filter</button>
    </form>
    <div class="grid gap-4">
        @foreach ($pembayarans as $pembayaran)
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col justify-between gap-4 lg:flex-row">
                    <div>
                        <p class="font-extrabold">{{ $pembayaran->pemesanan->penyewa->nama_lengkap }} - {{ $pembayaran->pemesanan->kamar->nama_kamar }}</p>
                        <p class="text-sm text-slate-500">{{ $pembayaran->jumlah_format }} pada {{ optional($pembayaran->tanggal_bayar)->format('d/m/Y') ?: '-' }} - {{ ucfirst(str_replace('_',' ', $pembayaran->status_pembayaran)) }}</p>
                        @if ($pembayaran->bukti_url)
                            <a target="_blank" class="mt-2 inline-block text-sm font-bold text-sky-700" href="{{ $pembayaran->bukti_url }}">Lihat Bukti</a>
                        @endif
                    </div>
                    @if ($pembayaran->status_pembayaran === 'menunggu_konfirmasi')
                        <div class="grid gap-2 sm:min-w-80">
                            <form method="POST" action="{{ route('admin.pembayaran-awal.approve', $pembayaran) }}">@csrf @method('PATCH')<button class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white">Setujui</button></form>
                            <form method="POST" action="{{ route('admin.pembayaran-awal.reject', $pembayaran) }}" class="flex gap-2">@csrf @method('PATCH')<input name="catatan_admin" placeholder="Catatan tolak" class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm" required><button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white">Tolak</button></form>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    {{ $pembayarans->links() }}
</x-admin-layout>
