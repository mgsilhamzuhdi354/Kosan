<x-penyedia-layout header="Pemesanan Masuk">
    <form method="GET" class="flex gap-2">
        <select name="status" class="rounded-2xl border-slate-300 text-sm">
            <option value="">Semua status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <button class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-black text-white">Filter</button>
    </form>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                    <tr><th class="px-4 py-3">Penyewa</th><th class="px-4 py-3">Kos/Kamar</th><th class="px-4 py-3">Tanggal Masuk</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pemesanans as $pemesanan)
                        <tr>
                            <td class="px-4 py-3 font-bold">{{ $pemesanan->penyewa->nama_lengkap }}</td>
                            <td class="px-4 py-3">{{ $pemesanan->kamar->kos?->nama_kos }} / {{ $pemesanan->kamar->nama_kamar }}</td>
                            <td class="px-4 py-3">{{ $pemesanan->tanggal_masuk->format('d/m/Y') }}</td>
                            <td class="px-4 py-3"><span class="status-badge">{{ ucfirst(str_replace('_', ' ', $pemesanan->status_pemesanan)) }}</span></td>
                            <td class="px-4 py-3"><a class="font-black text-sky-700" href="{{ route('penyedia.pemesanan.show', $pemesanan) }}">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada pemesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $pemesanans->links() }}
</x-penyedia-layout>
