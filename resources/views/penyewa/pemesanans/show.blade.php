<x-penyewa-layout header="Detail Pemesanan">
    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-2xl font-extrabold">{{ $pemesanan->kamar->nama_kamar }}</h2>
        <p class="text-slate-500">{{ ucfirst(str_replace('_',' ', $pemesanan->status_pemesanan)) }}</p>
        <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
            <div><dt class="font-bold">Tanggal Pesan</dt><dd>{{ $pemesanan->tanggal_pesan->format('d/m/Y') }}</dd></div>
            <div><dt class="font-bold">Tanggal Masuk</dt><dd>{{ $pemesanan->tanggal_masuk->format('d/m/Y') }}</dd></div>
            <div><dt class="font-bold">Pembayaran Awal</dt><dd>{{ $pemesanan->pembayaranAwal ? ucfirst(str_replace('_',' ', $pemesanan->pembayaranAwal->status_pembayaran)) : 'Belum Bayar' }}</dd></div>
            <div><dt class="font-bold">Catatan Admin</dt><dd>{{ $pemesanan->catatan_admin ?: '-' }}</dd></div>
        </dl>
        <div class="mt-5 flex flex-wrap gap-3">
            @if ($pemesanan->status_pemesanan === 'diterima')
                <a href="{{ route('penyewa.pembayaran-awal.create', $pemesanan) }}" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-bold text-white">Upload DP</a>
            @endif
            @if (in_array($pemesanan->status_pemesanan, ['menunggu_konfirmasi','diterima'], true))
                <form method="POST" action="{{ route('penyewa.pemesanan.cancel', $pemesanan) }}" onsubmit="return confirm('Batalkan pemesanan?')">@csrf @method('PATCH')<button class="rounded-lg border border-red-200 px-4 py-2 text-sm font-bold text-red-700">Batalkan</button></form>
            @endif
        </div>
    </section>
</x-penyewa-layout>
