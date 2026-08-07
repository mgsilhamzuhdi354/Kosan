<x-penyewa-layout header="Buat Keluhan">
    <div class="grid gap-4 lg:grid-cols-[1fr_320px]">
    <form method="POST" action="{{ route('penyewa.keluhan.store') }}" enctype="multipart/form-data" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        <div>
            <label class="text-sm font-bold">Kategori</label>
            <select name="kategori" class="mt-1 w-full rounded-2xl border-slate-300" required>
                @foreach ($kategori as $item)
                    <option value="{{ $item }}" @selected(old('kategori') === $item)>{{ $item }}</option>
                @endforeach
            </select>
        </div>
        <div class="mt-4">
            <label class="text-sm font-bold">Judul</label>
            <input name="judul" value="{{ old('judul') }}" class="mt-1 w-full rounded-2xl border-slate-300" required>
        </div>
        <div class="mt-4">
            <label class="text-sm font-bold">Deskripsi</label>
            <textarea name="deskripsi" rows="5" class="mt-1 w-full rounded-2xl border-slate-300" required>{{ old('deskripsi') }}</textarea>
        </div>
        <div class="mt-4">
            <label class="text-sm font-bold">Foto/Lampiran</label>
            <input name="foto" type="file" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 w-full rounded-2xl border border-slate-300 bg-white p-2 text-sm">
        </div>
        <button class="mt-5 rounded-2xl bg-sky-600 px-5 py-3 text-sm font-black text-white">Kirim Keluhan</button>
    </form>

    <aside class="complaint-option-card h-fit rounded-3xl p-5">
        <span class="app-shortcut-icon app-shortcut-soft"><x-icon name="bell" class="h-7 w-7" /></span>
        <h2 class="mt-4 text-xl font-black">Butuh respon cepat?</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan live chat untuk keluhan mendesak. Keluhan formal tetap bisa dikirim agar tercatat di sistem.</p>
        <a href="https://wa.me/6283179749407?text=Halo%20Admin%2C%20saya%20ingin%20melaporkan%20keluhan%20kos." target="_blank" class="mt-5 flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm">
            <x-icon name="message" class="h-5 w-5 text-sky-700" />
            <span>Live Chat Admin</span>
        </a>
    </aside>
    </div>
</x-penyewa-layout>
