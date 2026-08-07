<x-penyedia-layout header="Data Kamar">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <form method="GET" class="flex min-w-0 flex-1 gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari kamar" class="w-full rounded-2xl border-slate-300 px-4 text-sm">
            <button class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-black text-white">Cari</button>
        </form>
        <a href="{{ route('penyedia.kamar.create') }}" class="rounded-2xl bg-sky-600 px-4 py-3 text-center text-sm font-black text-white">Tambah Kamar</a>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Kamar</th>
                    <th class="px-4 py-3">Kos</th>
                    <th class="px-4 py-3">Harga</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kamars as $kamar)
                    <tr>
                        <td class="px-4 py-3 font-black">{{ $kamar->nama_kamar }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $kamar->kos?->nama_kos }}</td>
                        <td class="px-4 py-3 text-sky-700 font-black">{{ $kamar->harga_format }}</td>
                        <td class="px-4 py-3"><span class="status-badge">{{ ucfirst($kamar->status) }}</span></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('penyedia.kamar.edit', $kamar) }}" class="font-black text-sky-700">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $kamars->links() }}
</x-penyedia-layout>
