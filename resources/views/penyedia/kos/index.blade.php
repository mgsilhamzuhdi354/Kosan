<x-penyedia-layout header="Data Kos">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <form method="GET" class="flex min-w-0 flex-1 gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari kos" class="w-full rounded-2xl border-slate-300 px-4 text-sm">
            <button class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-black text-white">Cari</button>
        </form>
        <a href="{{ route('penyedia.kos.create') }}" class="rounded-2xl bg-sky-600 px-4 py-3 text-center text-sm font-black text-white">Tambah Kos</a>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        @forelse ($kos as $item)
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="grid gap-0 sm:grid-cols-[12rem,1fr]">
                    <img src="{{ $item->foto_url }}" alt="{{ $item->nama_kos }}" class="h-48 w-full object-cover sm:h-full">
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-lg font-black">{{ $item->nama_kos }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $item->alamat }}</p>
                            </div>
                            <span class="status-badge">{{ ucfirst($item->status) }}</span>
                        </div>
                        <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">{{ $item->deskripsi }}</p>
                        <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs font-black">
                            <div class="rounded-2xl bg-slate-50 p-3"><span class="block text-lg">{{ $item->kamars_count }}</span>Kamar</div>
                            <div class="rounded-2xl bg-slate-50 p-3"><span class="block text-lg">{{ $item->kamar_tersedia_count }}</span>Tersedia</div>
                            <div class="rounded-2xl bg-slate-50 p-3"><span class="block text-lg">{{ $item->kamar_terisi_count }}</span>Terisi</div>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('penyedia.kos.edit', $item) }}" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700">Edit</a>
                            <a href="{{ route('penyedia.kamar.create', ['kos_id' => $item->id]) }}" class="rounded-2xl bg-sky-600 px-4 py-2 text-sm font-black text-white">Tambah Kamar</a>
                            <form method="POST" action="{{ route('penyedia.kos.destroy', $item) }}" onsubmit="return confirm('Hapus kos ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-2xl bg-red-600 px-4 py-2 text-sm font-black text-white">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <p class="text-lg font-black">Belum ada kos.</p>
                <a href="{{ route('penyedia.kos.create') }}" class="mt-4 inline-flex rounded-2xl bg-sky-600 px-4 py-3 text-sm font-black text-white">Tambah Kos Pertama</a>
            </div>
        @endforelse
    </div>

    {{ $kos->links() }}
</x-penyedia-layout>
