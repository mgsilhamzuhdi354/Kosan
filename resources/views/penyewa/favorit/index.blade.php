<x-penyewa-layout header="Favorit Saya">
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-black uppercase tracking-wide text-sky-700">Favorit</p>
        <h2 class="mt-1 text-2xl font-black">Kamar yang kamu simpan</h2>
    </section>

    <div class="grid gap-4 md:grid-cols-2">
        @forelse ($favorits as $favorit)
            @php($kamar = $favorit->kamar)
            <article class="compact-room-card overflow-hidden rounded-3xl">
                <div class="grid grid-cols-[132px_1fr] sm:grid-cols-[220px_1fr]">
                    <a href="{{ route('penyewa.kamar.show', $kamar) }}" class="relative min-h-44 sm:min-h-56">
                        <img src="{{ $kamar->foto_url }}" alt="{{ $kamar->nama_kamar }}" class="absolute inset-0 h-full w-full object-cover">
                    </a>
                    <div class="flex min-w-0 flex-col justify-between p-4">
                        <div>
                            <h3 class="line-clamp-1 text-sm font-black sm:text-lg">{{ $kamar->kos?->nama_kos }} - {{ $kamar->nama_kamar }}</h3>
                            <p class="mt-1 line-clamp-1 text-xs font-semibold text-slate-500">{{ $kamar->kos?->alamat }}</p>
                            <p class="mt-3 text-base font-black text-sky-700 sm:text-xl">{{ $kamar->harga_format }} <span class="text-[11px] font-bold text-slate-500">/ bulan</span></p>
                        </div>
                        <form method="POST" action="{{ route('penyewa.favorit.destroy', $kamar) }}" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-2xl border border-slate-200 px-4 py-2 text-xs font-black text-slate-600">Hapus Favorit</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500 md:col-span-2">Belum ada kamar favorit.</div>
        @endforelse
    </div>

    {{ $favorits->links() }}
</x-penyewa-layout>
