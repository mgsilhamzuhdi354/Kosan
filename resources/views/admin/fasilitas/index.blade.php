<x-admin-layout header="Fasilitas">
    <div class="flex justify-end"><a href="{{ route('admin.fasilitas.create') }}" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-bold text-white">Tambah Fasilitas</a></div>
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($fasilitas as $item)
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="font-extrabold">{{ $item->nama_fasilitas }}</p>
                <p class="text-sm text-slate-500">{{ $item->kamars_count }} kamar memakai fasilitas ini</p>
                <div class="mt-3 flex gap-3 text-sm font-bold">
                    <a class="text-amber-700" href="{{ route('admin.fasilitas.edit', $item) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.fasilitas.destroy', $item) }}" onsubmit="return confirm('Hapus fasilitas?')">@csrf @method('DELETE')<button class="text-red-700">Hapus</button></form>
                </div>
            </div>
        @endforeach
    </div>
    {{ $fasilitas->links() }}
</x-admin-layout>
