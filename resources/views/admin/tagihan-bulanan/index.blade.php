<x-admin-layout header="Tagihan Bulanan">
    <div class="flex flex-col justify-between gap-3 sm:flex-row">
        <form method="GET" class="flex flex-wrap gap-2">
            <input name="bulan" value="{{ request('bulan') }}" type="number" min="1" max="12" placeholder="Bulan" class="w-28 rounded-lg border-slate-300 text-sm">
            <input name="tahun" value="{{ request('tahun') }}" type="number" placeholder="Tahun" class="w-32 rounded-lg border-slate-300 text-sm">
            <select name="status" class="rounded-lg border-slate-300 text-sm"><option value="">Semua status</option>@foreach ($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_',' ', $status)) }}</option>@endforeach</select>
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Filter</button>
        </form>
        <form method="POST" action="{{ route('admin.tagihan-bulanan.generate') }}">@csrf<button class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-bold text-white">Generate Tagihan</button></form>
    </div>
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-4 py-3">Penghuni</th><th class="px-4 py-3">Periode</th><th class="px-4 py-3">Jumlah</th><th class="px-4 py-3">Jatuh Tempo</th><th class="px-4 py-3">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-100">@foreach ($tagihans as $tagihan)<tr><td class="px-4 py-3 font-bold">{{ $tagihan->penghuni->penyewa->nama_lengkap }}</td><td class="px-4 py-3">{{ $tagihan->periode }}</td><td class="px-4 py-3">{{ $tagihan->jumlah_format }}</td><td class="px-4 py-3">{{ $tagihan->tanggal_jatuh_tempo->format('d/m/Y') }}</td><td class="px-4 py-3">{{ ucfirst(str_replace('_',' ', $tagihan->status_tagihan)) }}</td></tr>@endforeach</tbody>
            </table>
        </div>
    </div>
    {{ $tagihans->links() }}
</x-admin-layout>
