<x-penyewa-layout header="Upload Pembayaran Awal">
    <form method="POST" action="{{ route('penyewa.pembayaran-awal.store', $pemesanan) }}" enctype="multipart/form-data" class="max-w-2xl rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @csrf
        <h2 class="text-xl font-extrabold">{{ $pemesanan->kamar->nama_kamar }}</h2>
        <p class="text-sm text-slate-500">Status saat ini: {{ ucfirst(str_replace('_',' ', $pembayaran->status_pembayaran)) }}</p>
        <div class="mt-5">
            <label class="text-sm font-bold">Jumlah Bayar</label>
            <input name="jumlah_bayar" type="number" min="1" value="{{ old('jumlah_bayar', $pembayaran->jumlah_bayar ?: $pemesanan->kamar->harga_bulanan) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
        </div>
        <div class="mt-4">
            <label class="text-sm font-bold">Tanggal Bayar</label>
            <input name="tanggal_bayar" type="date" value="{{ old('tanggal_bayar', today()->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
        </div>
        <div class="mt-4">
            <label class="text-sm font-bold">Bukti Bayar</label>
            <input name="bukti_bayar" type="file" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 w-full rounded-lg border border-slate-300 bg-white p-2 text-sm" required>
            <p class="mt-1 text-xs text-slate-500">Format JPG, JPEG, PNG, atau PDF. Maksimal 2 MB.</p>
        </div>
        <button class="mt-5 rounded-lg bg-sky-600 px-4 py-2 text-sm font-bold text-white">Upload Bukti</button>
    </form>
</x-penyewa-layout>
