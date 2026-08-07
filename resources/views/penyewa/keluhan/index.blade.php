<x-penyewa-layout header="Keluhan">
    <section class="grid gap-4 md:grid-cols-2">
        <article class="complaint-option-card rounded-3xl p-5">
            <div class="flex items-start gap-4">
                <span class="app-shortcut-icon"><x-icon name="message" class="h-7 w-7" /></span>
                <div class="min-w-0">
                    <p class="text-xs font-black uppercase tracking-wide text-sky-700">Keluhan Formal</p>
                    <h2 class="mt-1 text-xl font-black">Buat laporan keluhan</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan fitur ini untuk keluhan yang perlu dicatat, diproses, dan dipantau statusnya oleh admin.</p>
                </div>
            </div>
            @if ($penghuni)
                <a href="{{ route('penyewa.keluhan.create') }}" class="mt-5 flex items-center justify-center gap-2 rounded-2xl bg-sky-600 px-4 py-3 text-sm font-black text-white">
                    <x-icon name="message" class="h-5 w-5" />
                    <span>Buat Keluhan</span>
                </a>
            @else
                <button disabled class="mt-5 w-full rounded-2xl bg-slate-200 px-4 py-3 text-sm font-black text-slate-500">Tersedia setelah menjadi penghuni aktif</button>
            @endif
        </article>

        <article class="complaint-option-card live-chat-card rounded-3xl p-5">
            <div class="flex items-start gap-4">
                <span class="app-shortcut-icon"><x-icon name="bell" class="h-7 w-7" /></span>
                <div class="min-w-0">
                    <p class="text-xs font-black uppercase tracking-wide text-emerald-700">Live Chat</p>
                    <h2 class="mt-1 text-xl font-black">Chat langsung admin</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Untuk kondisi mendesak, hubungi admin melalui WhatsApp agar lebih cepat ditanggapi.</p>
                </div>
            </div>
            <a href="https://wa.me/6283179749407?text=Halo%20Admin%2C%20saya%20ingin%20melaporkan%20keluhan%20kos." target="_blank" class="mt-5 flex items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-100">
                <x-icon name="message" class="h-5 w-5" />
                <span>Live Chat Admin</span>
            </a>
        </article>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-black uppercase tracking-wide text-sky-700">Riwayat Keluhan</p>
                <h2 class="mt-1 text-2xl font-black">Status laporan kamu</h2>
            </div>
            @if ($penghuni)
                <a href="{{ route('penyewa.keluhan.create') }}" class="rounded-2xl bg-slate-900 px-4 py-3 text-center text-sm font-black text-white">Tambah Laporan</a>
            @endif
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2">
            @forelse ($keluhans as $keluhan)
                <a href="{{ route('penyewa.keluhan.show', $keluhan) }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm transition hover:border-sky-300 hover:bg-white">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-extrabold">{{ $keluhan->judul }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $keluhan->kategori }}</p>
                        </div>
                        <span class="status-badge {{ $keluhan->status_keluhan === 'selesai' ? 'status-badge-success' : ($keluhan->status_keluhan === 'ditolak' ? 'status-badge-danger' : 'status-badge-warning') }}">{{ ucfirst($keluhan->status_keluhan) }}</span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-slate-500 md:col-span-2">{{ $penghuni ? 'Belum ada keluhan.' : 'Keluhan formal hanya tersedia setelah menjadi penghuni aktif.' }}</div>
            @endforelse
        </div>

        <div class="mt-5">
            {{ $keluhans->links() }}
        </div>
    </section>
</x-penyewa-layout>
