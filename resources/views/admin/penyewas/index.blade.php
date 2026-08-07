<x-admin-layout header="Data Penyewa">
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Kontak</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($penyewas as $penyewa)
                        <tr>
                            <td class="px-4 py-3 font-bold">{{ $penyewa->nama_lengkap }}<p class="font-normal text-slate-500">{{ $penyewa->user->email }}</p></td>
                            <td class="px-4 py-3">{{ $penyewa->no_hp }}</td>
                            <td class="px-4 py-3">{{ $penyewa->penghuniAktif ? 'Penghuni Aktif' : 'Penyewa' }}</td>
                            <td class="px-4 py-3"><a class="font-bold text-sky-700" href="{{ route('admin.penyewa.show', $penyewa) }}">Detail</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{ $penyewas->links() }}
</x-admin-layout>
