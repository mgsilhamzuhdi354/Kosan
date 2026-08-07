<x-public-layout title="Detail {{ $kamar->nama_kamar }}">
    <section class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
        <div class="grid gap-7 lg:grid-cols-[1fr_400px]">
            <div class="space-y-5">
                <div class="relative overflow-hidden rounded-[2rem] shadow-2xl">
                    <img src="{{ $kamar->foto_url }}" alt="{{ $kamar->nama_kamar }}" class="h-[420px] w-full object-cover sm:h-[520px]">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/78 via-transparent to-slate-950/25"></div>
                    <div class="absolute left-4 right-4 top-4 flex items-center justify-between">
                        <a href="{{ route('public.kamar.index') }}" class="detail-glass-button">&lt;</a>
                        <div class="flex gap-2">
                            <a href="https://wa.me/6283179749407" target="_blank" class="detail-glass-button"><x-icon name="message" class="h-5 w-5" /></a>
                            <span class="detail-glass-button"><x-icon name="heart" class="h-5 w-5" /></span>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 p-5 text-white sm:p-7">
                        <span class="{{ $kamar->status === 'tersedia' ? 'status-badge status-badge-success' : 'status-badge' }}">{{ ucfirst($kamar->status) }}</span>
                        <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-5xl">{{ $kamar->kos?->nama_kos }} - {{ $kamar->nama_kamar }}</h1>
                        <p class="mt-2 text-sm font-semibold text-slate-200">{{ $kamar->kos?->alamat ?? 'Betung, Banyuasin' }} - {{ $kamar->tipe_kamar }}</p>
                    </div>
                </div>

                <div class="compact-room-card rounded-3xl p-5">
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                        <div>
                            <p class="text-sm font-bold text-slate-500">Harga bulanan</p>
                            <p class="mt-1 text-3xl font-black text-sky-700">{{ $kamar->harga_format }} <span class="text-sm font-bold text-slate-500">/ bulan</span></p>
                            <p class="mt-2 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">Bebas biaya servis</p>
                        </div>
                        <span class="{{ $kamar->status === 'tersedia' ? 'status-badge status-badge-success' : ($kamar->status === 'maintenance' ? 'status-badge status-badge-danger' : 'status-badge status-badge-warning') }}">{{ ucfirst($kamar->status) }}</span>
                    </div>
                </div>

                <div class="compact-room-card rounded-3xl p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-black">Fasilitas</h2>
                        <span class="text-xs font-black text-sky-700">{{ $kamar->fasilitas->count() }} item</span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($kamar->fasilitas as $item)
                            <div class="facility-tile">
                                <span class="app-mini-icon h-8 w-8 text-xs"><x-icon :name="$loop->index === 0 ? 'bed' : ($loop->index === 1 ? 'wifi' : 'shield')" class="h-4 w-4" /></span>
                                <span>{{ $item->nama_fasilitas }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="compact-room-card rounded-3xl p-5">
                    <h2 class="text-lg font-black">Deskripsi</h2>
                    <p class="mt-3 leading-7 text-slate-600">{{ $kamar->deskripsi }}</p>
                </div>
            </div>

            <aside class="premium-surface hidden h-fit rounded-3xl p-5 shadow-sm lg:sticky lg:top-28 lg:block">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400">Ringkasan Booking</p>
                <p class="mt-3 text-sm text-slate-500">Harga bulanan mulai dari</p>
                <p class="text-3xl font-black text-sky-700">{{ $kamar->harga_format }}</p>
                <p class="mt-1 text-xs font-bold text-slate-400">Belum termasuk ketentuan DP yang divalidasi admin.</p>
                <div class="luxury-divider my-5"></div>
                <dl class="grid gap-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="font-bold text-slate-500">Tipe</dt><dd class="font-black">{{ $kamar->tipe_kamar }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="font-bold text-slate-500">Status</dt><dd class="font-black">{{ ucfirst($kamar->status) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="font-bold text-slate-500">Fasilitas</dt><dd class="font-black">{{ $kamar->fasilitas->count() }} item</dd></div>
                </dl>
                <div class="mt-6 grid gap-3">
                    @if ($kamar->status === 'tersedia')
                        <a href="{{ auth()->check() ? route('penyewa.pemesanan.create', $kamar) : route('login') }}" class="rounded-2xl bg-sky-600 px-4 py-3.5 text-center text-sm font-black text-white">Pesan Kamar</a>
                    @else
                        <button disabled class="rounded-2xl bg-slate-200 px-4 py-3.5 text-sm font-black text-slate-500">Kamar Tidak Tersedia</button>
                    @endif
                    <a href="https://wa.me/6283179749407" target="_blank" class="rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-center text-sm font-black text-slate-700 shadow-sm">Hubungi Admin</a>
                </div>
            </aside>
        </div>
    </section>

    <div class="sticky-mobile-cta fixed inset-x-0 bottom-[5.7rem] z-50 border-t border-white/70 bg-white/95 px-4 py-3 shadow-[0_-18px_45px_rgba(15,23,42,0.1)] backdrop-blur-xl md:hidden">
        <div class="mx-auto flex max-w-md items-center justify-between gap-3">
            <div>
                <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Total per bulan</p>
                <p class="text-sm font-black text-sky-700">{{ $kamar->harga_format }}</p>
            </div>
            @if ($kamar->status === 'tersedia')
                <a href="{{ auth()->check() ? route('penyewa.pemesanan.create', $kamar) : route('login') }}" class="rounded-2xl bg-sky-600 px-5 py-3 text-sm font-black text-white">Pilih Kamar</a>
            @else
                <button disabled class="rounded-2xl bg-slate-200 px-5 py-3 text-sm font-black text-slate-500">Tidak tersedia</button>
            @endif
        </div>
    </div>
</x-public-layout>
