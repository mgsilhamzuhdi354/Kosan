<x-penyedia-layout header="Detail Pemesanan">
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col justify-between gap-4 lg:flex-row">
            <div>
                <p class="text-2xl font-black">{{ $pemesanan->penyewa->nama_lengkap }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $pemesanan->kamar->kos?->nama_kos }} / {{ $pemesanan->kamar->nama_kamar }}</p>
                <p class="mt-3 text-sm text-slate-600">Tanggal masuk: {{ $pemesanan->tanggal_masuk->format('d/m/Y') }}</p>
                <p class="mt-1 text-sm text-slate-600">Status: {{ ucfirst(str_replace('_', ' ', $pemesanan->status_pemesanan)) }}</p>
                @if ($pemesanan->catatan_penyewa)
                    <p class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">{{ $pemesanan->catatan_penyewa }}</p>
                @endif
            </div>
            @if ($pemesanan->status_pemesanan === 'menunggu_konfirmasi')
                <div class="grid gap-2 sm:min-w-80">
                    <form method="POST" action="{{ route('penyedia.pemesanan.approve', $pemesanan) }}">
                        @csrf
                        @method('PATCH')
                        <textarea name="catatan_admin" rows="2" placeholder="Catatan untuk penyewa" class="mb-2 w-full rounded-2xl border-slate-300 text-sm"></textarea>
                        <button class="w-full rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-black text-white">Terima</button>
                    </form>
                    <form method="POST" action="{{ route('penyedia.pemesanan.reject', $pemesanan) }}" class="grid gap-2">
                        @csrf
                        @method('PATCH')
                        <input name="catatan_admin" placeholder="Alasan ditolak" class="rounded-2xl border-slate-300 text-sm" required>
                        <button class="rounded-2xl bg-red-600 px-4 py-3 text-sm font-black text-white">Tolak</button>
                    </form>
                </div>
            @endif
        </div>
    </section>
</x-penyedia-layout>
