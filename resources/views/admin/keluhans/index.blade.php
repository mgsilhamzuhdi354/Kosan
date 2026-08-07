<x-admin-layout header="Keluhan Penghuni">
    <form method="GET" class="flex gap-2"><select name="status" class="rounded-lg border-slate-300 text-sm"><option value="">Semua status</option>@foreach ($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Filter</button></form>
    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($keluhans as $keluhan)
            <a href="{{ route('admin.keluhan.show', $keluhan) }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm hover:border-sky-300">
                <p class="font-extrabold">{{ $keluhan->judul }}</p>
                <p class="text-sm text-slate-500">{{ $keluhan->penghuni->penyewa->nama_lengkap }} - {{ $keluhan->kategori }}</p>
                <p class="mt-3 text-sm">{{ ucfirst($keluhan->status_keluhan) }}</p>
            </a>
        @endforeach
    </div>
    {{ $keluhans->links() }}
</x-admin-layout>
