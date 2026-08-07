<x-admin-layout header="Pemesanan Kamar">
    <form method="GET" class="flex gap-2">
        <select name="status" class="rounded-lg border-slate-300 text-sm">
            <option value="">Semua status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_',' ', $status)) }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Filter</button>
    </form>
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-4 py-3">Penyewa</th><th class="px-4 py-3">Kamar</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($pemesanans as $pemesanan)
                        <tr>
                            <td class="px-4 py-3 font-bold">{{ $pemesanan->penyewa->nama_lengkap }}</td>
                            <td class="px-4 py-3">{{ $pemesanan->kamar->nama_kamar }}</td>
                            <td class="px-4 py-3">{{ $pemesanan->tanggal_masuk->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ ucfirst(str_replace('_',' ', $pemesanan->status_pemesanan)) }}</td>
                            <td class="px-4 py-3"><a class="font-bold text-sky-700" href="{{ route('admin.pemesanan.show', $pemesanan) }}">Detail</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{ $pemesanans->links() }}
</x-admin-layout>
