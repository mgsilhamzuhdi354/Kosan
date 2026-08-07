<x-admin-layout header="Penghuni Aktif">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($penghunis as $penghuni)
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="font-extrabold">{{ $penghuni->penyewa->nama_lengkap }}</p>
                <p class="text-sm text-slate-500">{{ $penghuni->kamar->nama_kamar }} - {{ ucfirst($penghuni->status_penghuni) }}</p>
                <p class="mt-3 text-sm">Masuk: {{ $penghuni->tanggal_masuk->format('d/m/Y') }}</p>
                <p class="text-sm">Jatuh tempo: {{ $penghuni->tanggal_jatuh_tempo->format('d/m/Y') }}</p>
                <div class="mt-4 flex gap-3">
                    <a href="{{ route('admin.penghuni.show', $penghuni) }}" class="text-sm font-bold text-sky-700">Detail</a>
                    @if ($penghuni->status_penghuni === 'aktif')
                        <form method="POST" action="{{ route('admin.penghuni.keluar', $penghuni) }}" onsubmit="return confirm('Tandai penghuni keluar?')">@csrf @method('PATCH')<button class="text-sm font-bold text-red-700">Keluar</button></form>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
    {{ $penghunis->links() }}
</x-admin-layout>
