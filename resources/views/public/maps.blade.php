<x-public-layout title="Peta Kost">
    <section class="app-home-shell">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-sky-700">Maps</p>
                    <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-5xl">Cari kost lewat peta.</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-600">Lihat lokasi kos aktif, promo, dan jumlah kamar tersedia dari peta interaktif.</p>
                </div>
                <a href="{{ route('public.kamar.index') }}" class="rounded-2xl bg-slate-900 px-5 py-3 text-center text-sm font-black text-white">Lihat List</a>
            </div>

            <div class="mt-6 grid gap-5 lg:grid-cols-[1fr_360px]">
                <div id="kosMap" class="h-[520px] overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm"></div>
                <div class="space-y-3">
                    @foreach ($kos as $item)
                        <a href="{{ route('public.kamar.index', ['lokasi' => $item->nama_kos]) }}" class="block rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex items-start gap-3">
                                <img src="{{ $item->foto_url }}" alt="{{ $item->nama_kos }}" class="h-20 w-20 rounded-2xl object-cover">
                                <div class="min-w-0">
                                    <p class="font-black">{{ $item->nama_kos }}</p>
                                    <p class="mt-1 line-clamp-2 text-xs font-semibold text-slate-500">{{ $item->alamat }}</p>
                                    <p class="mt-2 text-xs font-black text-sky-700">{{ $item->kamars->count() }} kamar tersedia</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        window.addEventListener('load', () => {
            const markers = @json($markers);
            const map = L.map('kosMap').setView([markers[0]?.lat || -2.8836, markers[0]?.lng || 104.2169], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            markers.forEach((item) => {
                L.marker([item.lat, item.lng]).addTo(map)
                    .bindPopup(`<strong>${item.name}</strong><br>${item.address}<br><a href="${item.url}">Lihat kamar</a>`);
            });
        });
    </script>
</x-public-layout>
