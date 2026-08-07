<x-penyewa-layout header="Pemesanan Saya">
    <div class="grid gap-4">
        @forelse ($pemesanans as $pemesanan)
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col justify-between gap-3 sm:flex-row">
                    <div>
                        <p class="font-extrabold">{{ $pemesanan->kamar->nama_kamar }}</p>
                        <p class="text-sm text-slate-500">Masuk {{ $pemesanan->tanggal_masuk->format('d/m/Y') }} - {{ ucfirst(str_replace('_',' ', $pemesanan->status_pemesanan)) }}</p>
                    </div>
                    <div class="flex flex-wrap gap-3 text-sm font-bold">
                        <a class="text-sky-700" href="{{ route('penyewa.pemesanan.show', $pemesanan) }}">Detail</a>
                        @if ($pemesanan->status_pemesanan === 'diterima')
                            <a class="text-emerald-700" href="{{ route('penyewa.pembayaran-awal.create', $pemesanan) }}">Upload DP</a>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-lg border border-slate-200 bg-white p-6 text-center text-slate-500">Belum ada pemesanan.</div>
        @endforelse
    </div>
    {{ $pemesanans->links() }}
</x-penyewa-layout>
