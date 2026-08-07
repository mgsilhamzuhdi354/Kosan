<x-penyewa-layout header="Pesan Kamar">
    <form method="POST" action="{{ route('penyewa.pemesanan.store', $kamar) }}" class="max-w-2xl rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        <h2 class="text-xl font-extrabold">{{ $kamar->nama_kamar }}</h2>
        <p class="text-slate-500">{{ $kamar->harga_format }}/bulan</p>
        <div class="mt-5">
            <label class="text-sm font-bold">Tanggal Masuk</label>
            <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk') }}" class="mt-1 w-full rounded-lg border-slate-300" required>
        </div>
        <div class="mt-4">
            <label class="text-sm font-bold">Catatan</label>
            <textarea name="catatan_penyewa" rows="4" class="mt-1 w-full rounded-lg border-slate-300">{{ old('catatan_penyewa') }}</textarea>
        </div>
        <button class="mt-5 rounded-lg bg-sky-600 px-4 py-2 text-sm font-bold text-white">Kirim Pemesanan</button>
    </form>
</x-penyewa-layout>
