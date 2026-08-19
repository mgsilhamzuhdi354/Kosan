<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\PenyediaKos;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KostAssetSeeder extends Seeder
{
    public function run(): void
    {
        $fasilitas = collect(['Kasur', 'Lemari', 'Kipas Angin', 'AC', 'WiFi', 'Kamar Mandi Dalam', 'Listrik', 'Air', 'Parkir Motor', 'Dapur Bersama'])
            ->mapWithKeys(fn (string $nama) => [$nama => Fasilitas::firstOrCreate(['penyedia_kos_id' => null, 'nama_fasilitas' => $nama])]);

        collect($this->kostData())->each(function (array $item, int $index) use ($fasilitas) {
            $user = User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['penyedia'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_PENYEDIA_KOS,
                ]
            );

            $penyedia = PenyediaKos::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama_lengkap' => $item['penyedia'],
                    'no_hp' => $item['no_hp'],
                    'alamat' => $item['alamat_penyedia'],
                ]
            );

            $this->deactivateLegacyKos($penyedia, $item);

            $kos = $penyedia->kos()->updateOrCreate(
                ['nama_kos' => $item['nama_kos']],
                [
                    'alamat' => $item['alamat'],
                    'kota' => 'Betung',
                    'deskripsi' => $item['deskripsi'],
                    'foto' => $item['foto'],
                    'latitude' => $item['latitude'],
                    'longitude' => $item['longitude'],
                    'status' => Kos::STATUS_AKTIF,
                    'is_promoted' => $item['is_promoted'],
                ]
            );

            collect($item['kamars'])->each(function (array $kamar, int $roomIndex) use ($fasilitas, $index, $item, $kos) {
                $createdKamar = Kamar::updateOrCreate(
                    [
                        'kos_id' => $kos->id,
                        'nama_kamar' => $kamar['nama'],
                    ],
                    [
                        'tipe_kamar' => $kamar['tipe'],
                        'harga_bulanan' => $kamar['harga'],
                        'deskripsi' => 'Kamar '.$kamar['tipe'].' di '.$item['nama_kos'].' dengan suasana nyaman, akses mudah, dan fasilitas siap huni.',
                        'foto' => $kamar['foto'],
                        'status' => $roomIndex === 2 && $index % 2 === 0 ? Kamar::STATUS_DIPESAN : Kamar::STATUS_TERSEDIA,
                    ]
                );

                $createdKamar->fasilitas()->sync(
                    $fasilitas
                        ->only($kamar['fasilitas'])
                        ->pluck('id')
                        ->values()
                        ->all()
                );
            });
        });
    }

    private function deactivateLegacyKos(PenyediaKos $penyedia, array $item): void
    {
        if (empty($item['legacy_names'])) {
            return;
        }

        $penyedia->kos()
            ->whereIn('nama_kos', $item['legacy_names'])
            ->update(['status' => Kos::STATUS_NONAKTIF]);
    }

    private function kostData(): array
    {
        return [
            [
                'nama_kos' => 'Kos Putri Asri',
                'penyedia' => 'Ibu Melati Asri',
                'email' => 'asri.kost@demo.local',
                'no_hp' => '083179749411',
                'alamat_penyedia' => 'Jl. Asri Betung, Banyuasin',
                'alamat' => 'Jl. Asri Raya No. 12, Betung, Banyuasin',
                'deskripsi' => 'Kos putri dengan halaman rapi, kamar terang, dan lingkungan tenang untuk mahasiswi maupun pekerja.',
                'foto' => 'assets/kos/asri-kost.jpeg',
                'legacy_names' => ['Asri Kost'],
                'latitude' => -2.8817,
                'longitude' => 104.2178,
                'is_promoted' => true,
                'kamars' => [
                    ['nama' => 'Putri Asri A1', 'tipe' => 'Standar', 'harga' => 750000, 'foto' => 'assets/kamar/putri-asri-a1.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'Kipas Angin', 'WiFi']],
                    ['nama' => 'Putri Asri A2', 'tipe' => 'Deluxe', 'harga' => 950000, 'foto' => 'assets/kamar/putri-asri-a2.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'AC', 'WiFi', 'Kamar Mandi Dalam']],
                    ['nama' => 'Putri Asri A3', 'tipe' => 'Premium', 'harga' => 1200000, 'foto' => 'assets/kamar/putri-asri-a3.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'AC', 'WiFi', 'Kamar Mandi Dalam', 'Dapur Bersama']],
                ],
            ],
            [
                'nama_kos' => 'Kos Putri Citra',
                'penyedia' => 'Ibu Citra Lestari',
                'email' => 'citra.kost@demo.local',
                'no_hp' => '083179749412',
                'alamat_penyedia' => 'Jl. Citra Betung, Banyuasin',
                'alamat' => 'Jl. Citra Mandiri No. 8, Betung, Banyuasin',
                'deskripsi' => 'Kos putri strategis dekat jalan utama dengan pilihan kamar ekonomis dan fasilitas harian lengkap.',
                'foto' => 'assets/kos/citra-kost.jpeg',
                'legacy_names' => ['Citra Kost'],
                'latitude' => -2.8864,
                'longitude' => 104.2241,
                'is_promoted' => false,
                'kamars' => [
                    ['nama' => 'Putri Citra B1', 'tipe' => 'Ekonomis', 'harga' => 650000, 'foto' => 'assets/kamar/putri-citra-b1.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'Kipas Angin', 'Listrik']],
                    ['nama' => 'Putri Citra B2', 'tipe' => 'Standar', 'harga' => 780000, 'foto' => 'assets/kamar/putri-citra-b2.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'Kipas Angin', 'WiFi']],
                    ['nama' => 'Putri Citra B3', 'tipe' => 'Deluxe', 'harga' => 980000, 'foto' => 'assets/kamar/putri-citra-b3.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'AC', 'WiFi']],
                ],
            ],
            [
                'nama_kos' => 'Kos Putri Dani',
                'penyedia' => 'Ibu Dani Pratama',
                'email' => 'dani.kost@demo.local',
                'no_hp' => '083179749413',
                'alamat_penyedia' => 'Jl. Pratama Betung, Banyuasin',
                'alamat' => 'Jl. Pratama No. 19, Betung, Banyuasin',
                'deskripsi' => 'Kos putri nyaman dengan akses parkir motor dan pengelolaan kamar yang tertata.',
                'foto' => 'assets/kos/dani-kost.jpeg',
                'legacy_names' => ['Dani Kost'],
                'latitude' => -2.8911,
                'longitude' => 104.2132,
                'is_promoted' => true,
                'kamars' => [
                    ['nama' => 'Putri Dani C1', 'tipe' => 'Standar', 'harga' => 720000, 'foto' => 'assets/kamar/putri-dani-c1.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'Kipas Angin', 'Parkir Motor']],
                    ['nama' => 'Putri Dani C2', 'tipe' => 'Deluxe', 'harga' => 900000, 'foto' => 'assets/kamar/putri-dani-c2.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'AC', 'Parkir Motor', 'WiFi']],
                    ['nama' => 'Putri Dani C3', 'tipe' => 'Premium', 'harga' => 1150000, 'foto' => 'assets/kamar/putri-dani-c3.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'AC', 'WiFi', 'Kamar Mandi Dalam']],
                ],
            ],
            [
                'nama_kos' => 'Kos Putri Damai',
                'penyedia' => 'Ibu Rani Damai',
                'email' => 'kost.damai@demo.local',
                'no_hp' => '083179749414',
                'alamat_penyedia' => 'Jl. Damai Betung, Banyuasin',
                'alamat' => 'Jl. Damai Indah No. 5, Betung, Banyuasin',
                'deskripsi' => 'Kos putri bernuansa tenang dengan dapur bersama, air lancar, dan suasana kekeluargaan.',
                'foto' => 'assets/kos/kost-damai.jpeg',
                'legacy_names' => ['Kost Damai'],
                'latitude' => -2.8769,
                'longitude' => 104.2293,
                'is_promoted' => true,
                'kamars' => [
                    ['nama' => 'Putri Damai D1', 'tipe' => 'Standar', 'harga' => 700000, 'foto' => 'assets/kamar/putri-damai-d1.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'Kipas Angin', 'Dapur Bersama']],
                    ['nama' => 'Putri Damai D2', 'tipe' => 'Deluxe', 'harga' => 920000, 'foto' => 'assets/kamar/putri-damai-d2.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'AC', 'WiFi', 'Dapur Bersama']],
                    ['nama' => 'Putri Damai D3', 'tipe' => 'Premium', 'harga' => 1100000, 'foto' => 'assets/kamar/putri-damai-d3.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'AC', 'Kamar Mandi Dalam', 'Air']],
                ],
            ],
            [
                'nama_kos' => 'Kos Putri Pondok Aer',
                'penyedia' => 'Ibu Sulastri Aer',
                'email' => 'pondok.aer@demo.local',
                'no_hp' => '083179749415',
                'alamat_penyedia' => 'Jl. Pondok Aer Betung, Banyuasin',
                'alamat' => 'Jl. Pondok Aer No. 3, Betung, Banyuasin',
                'deskripsi' => 'Kos putri dengan area bersih, air lancar, dan pilihan kamar hemat untuk penghuni baru.',
                'foto' => 'assets/kos/kost-pondok-aer.jpeg',
                'legacy_names' => ['Kost Pondok Aer'],
                'latitude' => -2.8705,
                'longitude' => 104.2205,
                'is_promoted' => false,
                'kamars' => [
                    ['nama' => 'Putri Pondok E1', 'tipe' => 'Ekonomis', 'harga' => 600000, 'foto' => 'assets/kamar/putri-pondok-e1.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'Kipas Angin', 'Air']],
                    ['nama' => 'Putri Pondok E2', 'tipe' => 'Standar', 'harga' => 760000, 'foto' => 'assets/kamar/putri-pondok-e2.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'WiFi', 'Air']],
                    ['nama' => 'Putri Pondok E3', 'tipe' => 'Deluxe', 'harga' => 940000, 'foto' => 'assets/kamar/putri-pondok-e3.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'AC', 'WiFi', 'Air']],
                ],
            ],
            [
                'nama_kos' => 'Kos Putri Permata',
                'penyedia' => 'Ibu Fitri Permata',
                'email' => 'permata.kost@demo.local',
                'no_hp' => '083179749416',
                'alamat_penyedia' => 'Jl. Permata Betung, Banyuasin',
                'alamat' => 'Jl. Permata Baru No. 21, Betung, Banyuasin',
                'deskripsi' => 'Kos putri premium dengan kamar nyaman, fasilitas lengkap, dan tampilan bangunan representatif.',
                'foto' => 'assets/kos/permata-kost.jpeg',
                'legacy_names' => ['Permata Kost'],
                'latitude' => -2.8952,
                'longitude' => 104.2277,
                'is_promoted' => true,
                'kamars' => [
                    ['nama' => 'Putri Permata F1', 'tipe' => 'Deluxe', 'harga' => 980000, 'foto' => 'assets/kamar/putri-permata-f1.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'AC', 'WiFi']],
                    ['nama' => 'Putri Permata F2', 'tipe' => 'Premium', 'harga' => 1250000, 'foto' => 'assets/kamar/putri-permata-f2.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'AC', 'WiFi', 'Kamar Mandi Dalam']],
                    ['nama' => 'Putri Permata F3', 'tipe' => 'Exclusive', 'harga' => 1450000, 'foto' => 'assets/kamar/putri-permata-f3.jpeg', 'fasilitas' => ['Kasur', 'Lemari', 'AC', 'WiFi', 'Kamar Mandi Dalam', 'Parkir Motor']],
                ],
            ],
        ];
    }
}
