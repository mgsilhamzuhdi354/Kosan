<x-penyewa-layout header="Profil Penyewa">
    <div class="grid gap-5 lg:grid-cols-2">
        <form method="POST" action="{{ route('penyewa.profil.update') }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
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

        <form method="POST" action="{{ route('password.update') }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            @csrf @method('PUT')
            <h2 class="text-lg font-black">Ubah Password</h2>
            <p class="mt-1 text-sm text-slate-500">Penyewa bisa mengganti password sendiri kapan saja dari halaman ini.</p>

            <div class="mt-4">
                <label class="text-sm font-bold">Password Saat Ini</label>
                <input name="current_password" type="password" autocomplete="current-password" class="mt-1 w-full rounded-lg border-slate-300" required>
                @error('current_password', 'updatePassword') <p class="mt-1 text-sm font-bold text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="mt-4">
                <label class="text-sm font-bold">Password Baru</label>
                <input name="password" type="password" autocomplete="new-password" class="mt-1 w-full rounded-lg border-slate-300" required>
                @error('password', 'updatePassword') <p class="mt-1 text-sm font-bold text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="mt-4">
                <label class="text-sm font-bold">Konfirmasi Password Baru</label>
                <input name="password_confirmation" type="password" autocomplete="new-password" class="mt-1 w-full rounded-lg border-slate-300" required>
            </div>

            <div class="mt-5 flex items-center gap-3">
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Simpan Password</button>
                @if (session('status') === 'password-updated')
                    <span class="text-sm font-bold text-emerald-700">Password berhasil diubah.</span>
                @endif
            </div>
        </form>
    </div>
</x-penyewa-layout>
