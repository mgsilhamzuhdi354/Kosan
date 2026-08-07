<x-penyewa-layout header="Riwayat Pembayaran">
    <div class="grid gap-4">
        @forelse ($pembayarans as $pembayaran)
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col justify-between gap-3 sm:flex-row">
                    <div>
                        <p class="font-extrabold">{{ $pembayaran->jumlah_format }} - Periode {{ $pembayaran->tagihanBulanan->periode }}</p>
                        <p class="text-sm text-slate-500">{{ $pembayaran->tanggal_bayar->format('d/m/Y') }} - {{ ucfirst(str_replace('_',' ', $pembayaran->status_pembayaran)) }}</p>
                    </div>
                    @if ($pembayaran->status_pembayaran === 'lunas')
                        <a href="{{ route('penyewa.riwayat-pembayaran.receipt', $pembayaran) }}" class="rounded-lg border border-slate-200 px-4 py-2 text-center text-sm font-bold">Cetak Bukti</a>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-lg border border-slate-200 bg-white p-6 text-center text-slate-500">Belum ada riwayat pembayaran.</div>
        @endforelse
    </div>
    {{ $pembayarans->links() }}
</x-penyewa-layout>
