<x-admin-layout header="Detail Penyewa">
    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-2xl font-extrabold">{{ $penyewa->nama_lengkap }}</h2>
        <p class="text-slate-500">{{ $penyewa->user->email }} - {{ $penyewa->no_hp }}</p>
        <p class="mt-3">{{ $penyewa->alamat }}</p>
    </div>
    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="font-extrabold">Riwayat Pemesanan</h3>
            <div class="mt-3 space-y-2">
                @foreach ($penyewa->pemesanans as $pemesanan)
                    <p class="rounded-lg bg-slate-50 p-3 text-sm">{{ $pemesanan->kamar->nama_kamar }} - {{ ucfirst(str_replace('_',' ', $pemesanan->status_pemesanan)) }}</p>
                @endforeach
            </div>
        </section>
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="font-extrabold">Hunian</h3>
            @if ($penyewa->penghuni)
                <p class="mt-3 text-sm">{{ $penyewa->penghuni->kamar->nama_kamar }} - {{ ucfirst($penyewa->penghuni->status_penghuni) }}</p>
            @else
                <p class="mt-3 text-sm text-slate-500">Belum menjadi penghuni aktif.</p>
            @endif
        </section>
    </div>
</x-admin-layout>
