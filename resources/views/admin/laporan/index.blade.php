<x-admin-layout header="Laporan PDF">
    <div class="grid gap-4 lg:grid-cols-[280px_1fr]">
        <aside class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="space-y-1">
                @foreach ($types as $key => $label)
                    <a href="{{ route('admin.laporan.index', $key) }}" class="block rounded-lg px-3 py-2 text-sm font-bold {{ $type === $key ? 'bg-sky-50 text-sky-700' : 'text-slate-600 hover:bg-slate-50' }}">{{ $label }}</a>
                @endforeach
            </div>
        </aside>
        <form method="GET" action="{{ route('admin.laporan.pdf', $type) }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-xl font-extrabold">{{ $title }}</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div><label class="text-sm font-bold">Tanggal Awal</label><input name="tanggal_awal" type="date" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="text-sm font-bold">Tanggal Akhir</label><input name="tanggal_akhir" type="date" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="text-sm font-bold">Bulan</label><input name="bulan" type="number" min="1" max="12" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div><label class="text-sm font-bold">Tahun</label><input name="tahun" type="number" class="mt-1 w-full rounded-lg border-slate-300"></div>
                <div class="sm:col-span-2"><label class="text-sm font-bold">Status</label><input name="status" placeholder="Opsional: lunas, belum_bayar, diterima, dll" class="mt-1 w-full rounded-lg border-slate-300"></div>
            </div>
            <button class="mt-5 rounded-lg bg-sky-600 px-4 py-2 text-sm font-bold text-white">Cetak PDF</button>
        </form>
    </div>
</x-admin-layout>
