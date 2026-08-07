<x-admin-layout header="Data Kamar">
    <div class="premium-surface flex flex-col justify-between gap-3 rounded-3xl p-4 sm:flex-row sm:items-center">
        <form method="GET" class="grid flex-1 gap-2 sm:grid-cols-4">
            <input name="q" value="{{ request('q') }}" placeholder="Cari kamar" class="rounded-2xl border-slate-300 px-4 text-sm">
            <select name="status" class="rounded-2xl border-slate-300 px-4 text-sm">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <input name="harga_min" value="{{ request('harga_min') }}" type="number" placeholder="Harga min" class="rounded-2xl border-slate-300 px-4 text-sm">
            <button class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-black text-white">Filter</button>
        </form>
        <a href="{{ route('admin.kamar.create') }}" class="rounded-2xl bg-sky-600 px-5 py-3 text-center text-sm font-black text-white">Tambah Kamar</a>
    </div>
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                    <tr><th class="px-4 py-3">Kamar</th><th class="px-4 py-3">Harga</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Fasilitas</th><th class="px-4 py-3">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($kamars as $kamar)
                        <tr>
                            <td class="px-4 py-3 font-bold">{{ $kamar->nama_kamar }}<p class="font-normal text-slate-500">{{ $kamar->tipe_kamar }}</p></td>
                            <td class="px-4 py-3">{{ $kamar->harga_format }}</td>
                            <td class="px-4 py-3"><span class="{{ $kamar->status === 'tersedia' ? 'status-badge status-badge-success' : ($kamar->status === 'maintenance' ? 'status-badge status-badge-warning' : 'status-badge') }}">{{ ucfirst($kamar->status) }}</span></td>
                            <td class="px-4 py-3">{{ $kamar->fasilitas->pluck('nama_fasilitas')->take(3)->join(', ') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a class="font-bold text-sky-700" href="{{ route('admin.kamar.show', $kamar) }}">Detail</a>
                                    <a class="font-bold text-amber-700" href="{{ route('admin.kamar.edit', $kamar) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.kamar.destroy', $kamar) }}" onsubmit="return confirm('Hapus kamar ini?')">@csrf @method('DELETE')<button class="font-bold text-red-700">Hapus</button></form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{ $kamars->links() }}
</x-admin-layout>
