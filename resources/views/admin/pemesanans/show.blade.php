<x-admin-layout header="Detail Pemesanan">
    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-2xl font-extrabold">{{ $pemesanan->kamar->nama_kamar }}</h2>
            <p class="text-slate-500">Dipesan oleh {{ $pemesanan->penyewa->nama_lengkap }}</p>
            <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="font-bold">Tanggal Pesan</dt><dd>{{ $pemesanan->tanggal_pesan->format('d/m/Y') }}</dd></div>
                <div><dt class="font-bold">Tanggal Masuk</dt><dd>{{ $pemesanan->tanggal_masuk->format('d/m/Y') }}</dd></div>
                <div><dt class="font-bold">Status</dt><dd>{{ ucfirst(str_replace('_',' ', $pemesanan->status_pemesanan)) }}</dd></div>
                <div><dt class="font-bold">No HP</dt><dd>{{ $pemesanan->penyewa->no_hp }}</dd></div>
            </dl>
            <p class="mt-5 text-sm text-slate-600">{{ $pemesanan->catatan_penyewa ?: 'Tidak ada catatan penyewa.' }}</p>
        </section>
        <aside class="space-y-4">
            @if ($pemesanan->status_pemesanan === 'menunggu_konfirmasi')
                <form method="POST" action="{{ route('admin.pemesanan.approve', $pemesanan) }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    @csrf @method('PATCH')
                    <label class="text-sm font-bold">Catatan Admin</label>
                    <textarea name="catatan_admin" rows="3" class="mt-1 w-full rounded-lg border-slate-300"></textarea>
                    <button class="mt-3 w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white">Terima Pemesanan</button>
                </form>
            @endif
            @if (in_array($pemesanan->status_pemesanan, ['menunggu_konfirmasi','diterima'], true))
                <form method="POST" action="{{ route('admin.pemesanan.reject', $pemesanan) }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    @csrf @method('PATCH')
                    <label class="text-sm font-bold">Alasan Penolakan</label>
                    <textarea name="catatan_admin" rows="3" class="mt-1 w-full rounded-lg border-slate-300" required></textarea>
                    <button class="mt-3 w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white">Tolak Pemesanan</button>
                </form>
            @endif
        </aside>
    </div>
</x-admin-layout>
