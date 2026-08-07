<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\Keluhan;
use App\Models\Kos;
use App\Models\PembayaranAwal;
use App\Models\PembayaranBulanan;
use App\Models\Pemesanan;
use App\Models\Penghuni;
use App\Models\PenyediaKos;
use App\Models\Penyewa;
use App\Models\TagihanBulanan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin Kos',
            'email' => 'admin@kos.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $penyediaUser = User::create([
            'name' => 'Ibu Rina Pemilik Kos',
            'email' => 'penyedia@kos.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENYEDIA_KOS,
        ]);

        $penyedia = PenyediaKos::create([
            'user_id' => $penyediaUser->id,
            'nama_lengkap' => 'Ibu Rina Pemilik Kos',
            'no_hp' => '083179749407',
            'alamat' => 'Jl. Lintas Betung, Banyuasin',
        ]);

        $kosUtama = Kos::create([
            'penyedia_kos_id' => $penyedia->id,
            'nama_kos' => config('app.name'),
            'alamat' => 'Jl. Lintas Betung, Banyuasin',
            'kota' => 'Betung',
            'deskripsi' => 'Kos putri nyaman dengan akses mudah, kamar bersih, dan pengelolaan digital.',
            'latitude' => -2.8836,
            'longitude' => 104.2169,
            'status' => Kos::STATUS_AKTIF,
            'is_promoted' => true,
        ]);

        $kosTambahan = collect([
            ['Kos Putri Damai', 'Jl. Serasi Betung, Banyuasin', -2.8798, 104.2212, true],
            ['Kos Exclusive Gejayan Betung', 'Jl. Palembang Betung No. 22', -2.8912, 104.2098, false],
            ['Kos Nyaman Setu', 'Jl. Setu Raya Betung', -2.8724, 104.2305, true],
        ])->map(fn ($item) => Kos::create([
            'penyedia_kos_id' => $penyedia->id,
            'nama_kos' => $item[0],
            'alamat' => $item[1],
            'kota' => 'Betung',
            'deskripsi' => 'Pilihan kos nyaman dengan fasilitas lengkap untuk demo pencarian lokasi.',
            'latitude' => $item[2],
            'longitude' => $item[3],
            'status' => Kos::STATUS_AKTIF,
            'is_promoted' => $item[4],
        ]));

        $penyewaData = [
            ['Siti Aminah', 'siti@kos.com', '081234567801', 'Jl. Betung Raya No. 1', 'Perempuan'],
            ['Nadia Putri', 'nadia@kos.com', '081234567802', 'Jl. Palembang Betung No. 22', 'Perempuan'],
            ['Rani Lestari', 'rani@kos.com', '081234567803', 'Jl. Serasi Betung', 'Perempuan'],
        ];

        $penyewas = collect($penyewaData)->map(function ($item) {
            $user = User::create([
                'name' => $item[0],
                'email' => $item[1],
                'password' => Hash::make('password'),
                'role' => User::ROLE_PENYEWA,
            ]);

            return Penyewa::create([
                'user_id' => $user->id,
                'nama_lengkap' => $item[0],
                'no_hp' => $item[2],
                'alamat' => $item[3],
                'jenis_kelamin' => $item[4],
            ]);
        });

        $fasilitas = collect(['Kasur', 'Lemari', 'Kipas Angin', 'AC', 'WiFi', 'Kamar Mandi Dalam', 'Listrik', 'Air'])
            ->map(fn ($nama) => Fasilitas::create(['nama_fasilitas' => $nama]));

        $kamarData = [
            ['Kamar A1', 'Standar', 750000, Kamar::STATUS_TERSEDIA],
            ['Kamar A2', 'Standar', 800000, Kamar::STATUS_TERSEDIA],
            ['Kamar A3', 'Deluxe', 950000, Kamar::STATUS_DIPESAN],
            ['Kamar B1', 'Deluxe', 1000000, Kamar::STATUS_TERISI],
            ['Kamar B2', 'Standar', 700000, Kamar::STATUS_MAINTENANCE],
            ['Kamar B3', 'Premium', 1200000, Kamar::STATUS_TERSEDIA],
        ];

        $allKos = collect([$kosUtama])->merge($kosTambahan);

        $kamars = collect($kamarData)->map(function ($item, $index) use ($fasilitas, $allKos) {
            $kamar = Kamar::create([
                'kos_id' => $allKos[$index % $allKos->count()]->id,
                'nama_kamar' => $item[0],
                'tipe_kamar' => $item[1],
                'harga_bulanan' => $item[2],
                'status' => $item[3],
                'deskripsi' => 'Kamar nyaman, bersih, dan cocok untuk penghuni yang membutuhkan lingkungan kos yang tertata.',
            ]);

            $kamar->fasilitas()->sync($fasilitas->random(5)->pluck('id')->all());

            return $kamar;
        });

        $pemesananDipesan = Pemesanan::create([
            'penyewa_id' => $penyewas[1]->id,
            'kamar_id' => $kamars[2]->id,
            'tanggal_pesan' => now()->subDays(2),
            'tanggal_masuk' => now()->addDays(5),
            'status_pemesanan' => Pemesanan::STATUS_DITERIMA,
            'catatan_penyewa' => 'Ingin masuk awal bulan.',
            'catatan_admin' => 'Silakan lakukan pembayaran awal.',
        ]);

        PembayaranAwal::create([
            'pemesanan_id' => $pemesananDipesan->id,
            'jumlah_bayar' => 500000,
            'tanggal_bayar' => now()->subDay(),
            'status_pembayaran' => PembayaranAwal::STATUS_MENUNGGU,
        ]);

        $pemesananSelesai = Pemesanan::create([
            'penyewa_id' => $penyewas[0]->id,
            'kamar_id' => $kamars[3]->id,
            'tanggal_pesan' => now()->subMonths(2),
            'tanggal_masuk' => now()->subMonth(),
            'status_pemesanan' => Pemesanan::STATUS_SELESAI,
            'catatan_penyewa' => 'Membutuhkan kamar yang dekat kamar mandi.',
            'catatan_admin' => 'Pembayaran awal valid.',
        ]);

        PembayaranAwal::create([
            'pemesanan_id' => $pemesananSelesai->id,
            'jumlah_bayar' => 1000000,
            'tanggal_bayar' => now()->subMonth(),
            'status_pembayaran' => PembayaranAwal::STATUS_LUNAS,
        ]);

        $penghuni = Penghuni::create([
            'penyewa_id' => $penyewas[0]->id,
            'kamar_id' => $kamars[3]->id,
            'tanggal_masuk' => now()->subMonth(),
            'harga_bulanan' => $kamars[3]->harga_bulanan,
            'tanggal_jatuh_tempo' => now()->addDays(7),
            'status_penghuni' => Penghuni::STATUS_AKTIF,
        ]);

        $tagihanLunas = TagihanBulanan::create([
            'penghuni_id' => $penghuni->id,
            'bulan' => now()->subMonth()->month,
            'tahun' => now()->subMonth()->year,
            'jumlah_tagihan' => $penghuni->harga_bulanan,
            'tanggal_jatuh_tempo' => now()->subMonth()->day(10),
            'status_tagihan' => TagihanBulanan::STATUS_LUNAS,
        ]);

        PembayaranBulanan::create([
            'tagihan_bulanan_id' => $tagihanLunas->id,
            'tanggal_bayar' => now()->subMonth()->day(8),
            'jumlah_bayar' => $penghuni->harga_bulanan,
            'bukti_bayar' => 'dummy/bukti-lunas.pdf',
            'status_pembayaran' => PembayaranBulanan::STATUS_LUNAS,
        ]);

        TagihanBulanan::create([
            'penghuni_id' => $penghuni->id,
            'bulan' => now()->month,
            'tahun' => now()->year,
            'jumlah_tagihan' => $penghuni->harga_bulanan,
            'tanggal_jatuh_tempo' => now()->addDays(7),
            'status_tagihan' => TagihanBulanan::STATUS_BELUM_BAYAR,
        ]);

        TagihanBulanan::create([
            'penghuni_id' => $penghuni->id,
            'bulan' => now()->subMonths(2)->month,
            'tahun' => now()->subMonths(2)->year,
            'jumlah_tagihan' => $penghuni->harga_bulanan,
            'tanggal_jatuh_tempo' => now()->subDays(10),
            'status_tagihan' => TagihanBulanan::STATUS_TERLAMBAT,
        ]);

        $tagihanMenunggu = TagihanBulanan::create([
            'penghuni_id' => $penghuni->id,
            'bulan' => now()->addMonth()->month,
            'tahun' => now()->addMonth()->year,
            'jumlah_tagihan' => $penghuni->harga_bulanan,
            'tanggal_jatuh_tempo' => now()->addMonth()->day(10),
            'status_tagihan' => TagihanBulanan::STATUS_MENUNGGU,
        ]);

        PembayaranBulanan::create([
            'tagihan_bulanan_id' => $tagihanMenunggu->id,
            'tanggal_bayar' => now(),
            'jumlah_bayar' => $penghuni->harga_bulanan,
            'bukti_bayar' => 'dummy/bukti-menunggu.pdf',
            'status_pembayaran' => PembayaranBulanan::STATUS_MENUNGGU,
        ]);

        Keluhan::create([
            'penghuni_id' => $penghuni->id,
            'kategori' => 'Kebersihan',
            'judul' => 'Area dapur perlu dibersihkan',
            'deskripsi' => 'Mohon jadwal kebersihan area dapur ditambah.',
            'status_keluhan' => Keluhan::STATUS_DIPROSES,
        ]);
    }
}
