<x-admin-layout header="Pembayaran Bulanan">
    <form method="GET" class="flex gap-2"><select name="status" class="rounded-lg border-slate-300 text-sm"><option value="">Semua status</option>@foreach ($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_',' ', $status)) }}</option>@endforeach</select><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Filter</button></form>
    <div class="grid gap-4">
        @foreach ($pembayarans as $pembayaran)
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col justify-between gap-4 lg:flex-row">
                    <div>
                        <p class="font-extrabold">{{ $pembayaran->tagihanBulanan->penghuni->penyewa->nama_lengkap }} - {{ $pembayaran->tagihanBulanan->penghuni->kamar->nama_kamar }}</p>
                        <p class="text-sm text-slate-500">{{ $pembayaran->jumlah_format }} - Periode {{ $pembayaran->tagihanBulanan->periode }} - {{ ucfirst(str_replace('_',' ', $pembayaran->status_pembayaran)) }}</p>
                        <div class="mt-2 flex gap-3 text-sm font-bold"><a class="text-sky-700" target="_blank" href="{{ $pembayaran->bukti_url }}">Lihat Bukti</a>@if ($pembayaran->status_pembayaran === 'lunas')<a class="text-emerald-700" href="{{ route('admin.pembayaran-bulanan.receipt', $pembayaran) }}">Cetak Bukti</a>@endif</div>
                    </div>
                    @if ($pembayaran->status_pembayaran === 'menunggu_konfirmasi')
                        <div class="grid gap-2 sm:min-w-80">
                            <form method="POST" action="{{ route('admin.pembayaran-bulanan.approve', $pembayaran) }}">@csrf @method('PATCH')<button class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white">Setujui</button></form>
                            <form method="POST" action="{{ route('admin.pembayaran-bulanan.reject', $pembayaran) }}" class="flex gap-2">@csrf @method('PATCH')<input name="catatan_admin" placeholder="Catatan tolak" class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm" required><button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white">Tolak</button></form>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    {{ $pembayarans->links() }}
</x-admin-layout>
