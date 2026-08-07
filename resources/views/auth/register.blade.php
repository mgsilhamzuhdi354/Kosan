<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" x-data="{ accountType: '{{ old('account_type', 'penyewa') }}' }">
        @csrf

        <div>
            <x-input-label value="Daftar sebagai" />
            <div class="mt-2 grid grid-cols-2 gap-2">
                <label class="cursor-pointer rounded-2xl border p-3 text-sm font-black" :class="accountType === 'penyewa' ? 'border-sky-400 bg-sky-50 text-sky-700' : 'border-slate-200 bg-white text-slate-600'">
                    <input type="radio" name="account_type" value="penyewa" x-model="accountType" class="sr-only">
                    Pencari Kos
                </label>
                <label class="cursor-pointer rounded-2xl border p-3 text-sm font-black" :class="accountType === 'penyedia_kos' ? 'border-sky-400 bg-sky-50 text-sky-700' : 'border-slate-200 bg-white text-slate-600'">
                    <input type="radio" name="account_type" value="penyedia_kos" x-model="accountType" class="sr-only">
                    Penyedia Kos
                </label>
            </div>
            <x-input-error :messages="$errors->get('account_type')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="no_hp" value="Nomor HP" />
            <x-text-input id="no_hp" class="block mt-1 w-full" type="text" name="no_hp" :value="old('no_hp')" required />
            <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
        </div>

        <div class="mt-4" x-show="accountType === 'penyewa'">
            <x-input-label for="jenis_kelamin" value="Jenis Kelamin" />
            <select id="jenis_kelamin" name="jenis_kelamin" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
                <option value="">Pilih jenis kelamin</option>
                <option value="Perempuan" @selected(old('jenis_kelamin') === 'Perempuan')>Perempuan</option>
                <option value="Laki-laki" @selected(old('jenis_kelamin') === 'Laki-laki')>Laki-laki</option>
            </select>
            <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="alamat" value="Alamat" />
            <textarea id="alamat" name="alamat" rows="3" class="mt-1 block w-full rounded-2xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500" required>{{ old('alamat') }}</textarea>
            <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2" x-show="accountType === 'penyedia_kos'">
            <div>
                <x-input-label for="nama_kos" value="Nama Kos" />
                <x-text-input id="nama_kos" class="block mt-1 w-full" type="text" name="nama_kos" :value="old('nama_kos')" />
                <x-input-error :messages="$errors->get('nama_kos')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="kota" value="Kota/Kecamatan" />
                <x-text-input id="kota" class="block mt-1 w-full" type="text" name="kota" :value="old('kota', 'Betung')" />
                <x-input-error :messages="$errors->get('kota')" class="mt-2" />
            </div>
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Konfirmasi Password" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md" href="{{ route('login') }}">Sudah punya akun?</a>
            <x-primary-button class="ms-4">Daftar</x-primary-button>
        </div>
    </form>
</x-guest-layout>
