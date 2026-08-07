<x-penyewa-layout header="Pembayaran Awal">
    <div class="grid gap-4">
        @forelse ($pembayarans as $pembayaran)
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col justify-between gap-3 sm:flex-row">
                    <div>
                        <p class="font-extrabold">{{ $pembayaran->pemesanan->kamar->nama_kamar }}</p>
                        <p class="text-sm text-slate-500">{{ $pembayaran->jumlah_format }} - {{ ucfirst(str_replace('_',' ', $pembayaran->status_pembayaran)) }}</p>
                        @if ($pembayaran->catatan_admin)
                            <p class="mt-2 text-sm text-red-700">{{ $pembayaran->catatan_admin }}</p>
                        @endif
                    </div>
                    @if (in_array($pembayaran->status_pembayaran, ['belum_bayar','ditolak'], true))
                        <a href="{{ route('penyewa.pembayaran-awal.create', $pembayaran->pemesanan) }}" class="rounded-lg bg-sky-600 px-4 py-2 text-center text-sm font-bold text-white">Upload Bukti</a>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-lg border border-slate-200 bg-white p-6 text-center text-slate-500">Belum ada pembayaran awal.</div>
        @endforelse
    </div>
    {{ $pembayarans->links() }}
</x-penyewa-layout>
