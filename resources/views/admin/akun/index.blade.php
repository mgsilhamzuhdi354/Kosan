<x-admin-layout header="Akun Pengguna">
    @if (session('temporary_password'))
        @php
            $temporary = session('temporary_password');
        @endphp
        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900 shadow-sm">
            <p class="text-xs font-black uppercase tracking-wide">Password Sementara</p>
            <div class="mt-3 grid gap-3 md:grid-cols-3">
                <div>
                    <p class="text-xs font-bold text-amber-700">Nama</p>
                    <p class="font-black">{{ $temporary['name'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-amber-700">Email Login</p>
                    <p class="font-black">{{ $temporary['email'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-amber-700">Password Baru</p>
                    <p class="font-mono text-lg font-black">{{ $temporary['password'] }}</p>
                </div>
            </div>
        </section>
    @endif

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" class="grid gap-3 md:grid-cols-[1fr_220px_auto] md:items-end">
            <div>
                <label class="text-sm font-bold text-slate-700">Cari Akun</label>
                <input name="q" value="{{ request('q') }}" placeholder="Nama atau email" class="mt-1 w-full rounded-2xl border-slate-300">
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700">Role</label>
                <select name="role" class="mt-1 w-full rounded-2xl border-slate-300">
                    <option value="">Semua role</option>
                    <option value="{{ App\Models\User::ROLE_PENYEWA }}" @selected(request('role') === App\Models\User::ROLE_PENYEWA)>Penyewa</option>
                    <option value="{{ App\Models\User::ROLE_PENYEDIA_KOS }}" @selected(request('role') === App\Models\User::ROLE_PENYEDIA_KOS)>Penyedia Kos</option>
                </select>
            </div>
            <button class="rounded-2xl bg-sky-600 px-5 py-3 text-sm font-black text-white">Tampilkan</button>
        </form>
    </section>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5">
            <p class="text-xs font-black uppercase tracking-wide text-slate-400">Data Login</p>
            <h2 class="mt-1 text-xl font-black">{{ $users->total() }} akun ditemukan</h2>
        </div>
        <div class="mobile-safe-scroll overflow-x-auto">
            <table class="w-full min-w-[920px] text-left text-sm">
                <thead class="bg-slate-50 text-xs font-black uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">ID User</th>
                        <th class="px-5 py-4">ID Profil</th>
                        <th class="px-5 py-4">Nama</th>
                        <th class="px-5 py-4">Email Login</th>
                        <th class="px-5 py-4">Role</th>
                        <th class="px-5 py-4">Password</th>
                        <th class="px-5 py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        @php
                            $isPenyewa = $user->role === App\Models\User::ROLE_PENYEWA;
                            $profile = $isPenyewa ? $user->penyewa : $user->penyediaKos;
                        @endphp
                        <tr>
                            <td class="px-5 py-4 font-black">#{{ $user->id }}</td>
                            <td class="px-5 py-4 font-black">{{ $profile ? '#'.$profile->id : '-' }}</td>
                            <td class="px-5 py-4">
                                <p class="font-black">{{ $profile?->nama_lengkap ?? $user->name }}</p>
                                <p class="text-xs font-semibold text-slate-500">{{ $profile?->no_hp ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-600">{{ $user->email }}</td>
                            <td class="px-5 py-4">
                                <span class="status-badge">{{ $isPenyewa ? 'Penyewa' : 'Penyedia Kos' }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-500">
                                Terenkripsi
                                <p class="text-xs">Reset untuk membuat password baru.</p>
                            </td>
                            <td class="px-5 py-4">
                                <form method="POST" action="{{ route('admin.akun.reset-password', $user) }}" onsubmit="return confirm('Reset password akun ini? Password lama tidak bisa dikembalikan.')">
                                    @csrf
                                    @method('PATCH')
                                    <button class="rounded-2xl bg-slate-900 px-4 py-2 text-xs font-black text-white">Reset Password</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-500">Tidak ada akun penyewa atau penyedia kos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{ $users->links() }}
</x-admin-layout>
