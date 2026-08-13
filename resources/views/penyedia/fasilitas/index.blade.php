<x-penyedia-layout header="Fasilitas">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <p class="text-sm text-slate-500">Fasilitas bawaan bisa langsung dipakai. Fasilitas tambahan yang Anda buat dapat diedit dan dihapus dari panel ini.</p>
        </div>
        <a href="{{ route('penyedia.fasilitas.create') }}" class="rounded-2xl bg-sky-600 px-4 py-3 text-center text-sm font-black text-white">Tambah Fasilitas</a>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($fasilitas as $item)
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-extrabold">{{ $item->nama_fasilitas }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $item->kamars_count }} kamar memakai fasilitas ini</p>
                    </div>
                    <span class="status-badge">{{ $item->penyedia_kos_id === $penyediaId ? 'Saya' : 'Bawaan' }}</span>
                </div>
                @if ($item->penyedia_kos_id === $penyediaId)
                    <div class="mt-4 flex gap-3 text-sm font-bold">
                        <a class="text-amber-700" href="{{ route('penyedia.fasilitas.edit', $item) }}">Edit</a>
                        <form method="POST" action="{{ route('penyedia.fasilitas.destroy', $item) }}" onsubmit="return confirm('Hapus fasilitas?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-700">Hapus</button>
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{ $fasilitas->links() }}
</x-penyedia-layout>
