<x-admin-layout header="Detail Penghuni">
    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-2xl font-extrabold">{{ $penghuni->penyewa->nama_lengkap }}</h2>
        <p class="text-slate-500">{{ $penghuni->kamar->nama_kamar }} - {{ $penghuni->harga_format }}/bulan</p>
    </section>
    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="font-extrabold">Riwayat Tagihan</h3>
            <div class="mt-3 space-y-2">
                @foreach ($penghuni->tagihanBulanans as $tagihan)
                    <p class="rounded-lg bg-slate-50 p-3 text-sm">{{ $tagihan->periode }} - {{ $tagihan->jumlah_format }} - {{ ucfirst(str_replace('_',' ', $tagihan->status_tagihan)) }}</p>
                @endforeach
            </div>
        </section>
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="font-extrabold">Riwayat Keluhan</h3>
            <div class="mt-3 space-y-2">
                @foreach ($penghuni->keluhans as $keluhan)
                    <p class="rounded-lg bg-slate-50 p-3 text-sm">{{ $keluhan->judul }} - {{ ucfirst($keluhan->status_keluhan) }}</p>
                @endforeach
            </div>
        </section>
    </div>
</x-admin-layout>
