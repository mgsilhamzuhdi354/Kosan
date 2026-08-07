<x-penyewa-layout header="Tagihan Bulanan">
    <div class="grid gap-4">
        @forelse ($tagihans as $tagihan)
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col justify-between gap-3 sm:flex-row">
                    <div>
                        <p class="font-extrabold">Periode {{ $tagihan->periode }} - {{ $tagihan->penghuni->kamar->nama_kamar }}</p>
                        <p class="text-sm text-slate-500">{{ $tagihan->jumlah_format }} - Jatuh tempo {{ $tagihan->tanggal_jatuh_tempo->format('d/m/Y') }}</p>
                        <p class="mt-1 text-sm font-bold">{{ ucfirst(str_replace('_',' ', $tagihan->status_tagihan)) }}</p>
                    </div>
                    @if (in_array($tagihan->status_tagihan, ['belum_bayar','terlambat','ditolak'], true))
                        <a href="{{ route('penyewa.tagihan.bayar', $tagihan) }}" class="rounded-lg bg-sky-600 px-4 py-2 text-center text-sm font-bold text-white">Bayar</a>
                    @elseif ($tagihan->status_tagihan === 'lunas' && $tagihan->pembayaranBulanan)
                        <a href="{{ route('penyewa.riwayat-pembayaran.receipt', $tagihan->pembayaranBulanan) }}" class="rounded-lg border border-slate-200 px-4 py-2 text-center text-sm font-bold">Cetak Bukti</a>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-lg border border-slate-200 bg-white p-6 text-center text-slate-500">Belum ada tagihan.</div>
        @endforelse
    </div>
    {{ $tagihans->links() }}
</x-penyewa-layout>
