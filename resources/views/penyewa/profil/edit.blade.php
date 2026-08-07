<x-penyewa-layout header="Profil Penyewa">
    <form method="POST" action="{{ route('penyewa.profil.update') }}" class="max-w-2xl rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        @csrf @method('PATCH')
        <div>
            <label class="text-sm font-bold">Nama Lengkap</label>
            <input name="nama_lengkap" value="{{ old('nama_lengkap', $penyewa->nama_lengkap) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
        </div>
        <div class="mt-4">
            <label class="text-sm font-bold">Nomor HP</label>
            <input name="no_hp" value="{{ old('no_hp', $penyewa->no_hp) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
        </div>
        <div class="mt-4">
            <label class="text-sm font-bold">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="mt-1 w-full rounded-lg border-slate-300" required>
                <option value="Perempuan" @selected(old('jenis_kelamin', $penyewa->jenis_kelamin) === 'Perempuan')>Perempuan</option>
                <option value="Laki-laki" @selected(old('jenis_kelamin', $penyewa->jenis_kelamin) === 'Laki-laki')>Laki-laki</option>
            </select>
        </div>
        <div class="mt-4">
            <label class="text-sm font-bold">Alamat</label>
            <textarea name="alamat" rows="4" class="mt-1 w-full rounded-lg border-slate-300" required>{{ old('alamat', $penyewa->alamat) }}</textarea>
        </div>
        <button class="mt-5 rounded-lg bg-sky-600 px-4 py-2 text-sm font-bold text-white">Simpan Profil</button>
    </form>
</x-penyewa-layout>
