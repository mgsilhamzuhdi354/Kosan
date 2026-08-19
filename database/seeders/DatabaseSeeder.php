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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->ensureDemoPaymentProofs();

        User::updateOrCreate(
            ['email' => 'admin@kos.com'],
            [
                'name' => 'Admin Kos',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        $penyediaUser = User::updateOrCreate(
            ['email' => 'penyedia@kos.com'],
            [
                'name' => 'Ibu Rina Pemilik Kos',
                'password' => Hash::make('password'),
                'role' => User::ROLE_PENYEDIA_KOS,
            ]
        );

        $penyedia = PenyediaKos::updateOrCreate(
            ['user_id' => $penyediaUser->id],
            [
                'nama_lengkap' => 'Ibu Rina Pemilik Kos',
                'no_hp' => '083179749407',
                'alamat' => 'Jl. Lintas Betung, Banyuasin',
            ]
        );

        $penyedia->kos()
            ->whereIn('nama_kos', [
                'kost banyuasin',
                'Kos Putri Damai',
                'Kos Exclusive Gejayan Betung',
                'Kos Nyaman Setu',
            ])
            ->update(['status' => Kos::STATUS_NONAKTIF]);

        $kosUtama = Kos::updateOrCreate(
            ['penyedia_kos_id' => $penyedia->id, 'nama_kos' => config('app.name')],
            [
                'alamat' => 'Jl. Lintas Betung, Banyuasin',
                'kota' => 'Betung',
                'deskripsi' => 'Kos putri nyaman dengan akses mudah, kamar bersih, dan pengelolaan digital.',
                'foto' => 'assets/kos-putri/utama-betung.jpeg',
                'latitude' => -2.8836,
                'longitude' => 104.2169,
                'status' => Kos::STATUS_AKTIF,
                'is_promoted' => true,
            ]
        );

        $kosTambahan = collect([
            ['Kos Putri Harmoni', 'Jl. Serasi Betung, Banyuasin', -2.8798, 104.2212, true, 'assets/kos-putri/harmoni.jpeg'],
            ['Kos Putri Exclusive Betung', 'Jl. Palembang Betung No. 22', -2.8912, 104.2098, false, 'assets/kos-putri/exclusive-betung.jpeg'],
            ['Kos Putri Nyaman Setu', 'Jl. Setu Raya Betung', -2.8724, 104.2305, true, 'assets/kos-putri/nyaman-setu.jpeg'],
        ])->map(fn ($item) => Kos::updateOrCreate(
            ['penyedia_kos_id' => $penyedia->id, 'nama_kos' => $item[0]],
            [
                'alamat' => $item[1],
                'kota' => 'Betung',
                'deskripsi' => 'Pilihan kos putri nyaman dengan fasilitas lengkap untuk demo pencarian lokasi.',
                'foto' => $item[5],
                'latitude' => $item[2],
                'longitude' => $item[3],
                'status' => Kos::STATUS_AKTIF,
                'is_promoted' => $item[4],
            ]
        ));

        $penyewaData = [
            ['Siti Aminah', 'siti@kos.com', '081234567801', 'Jl. Betung Raya No. 1', 'Perempuan'],
            ['Nadia Putri', 'nadia@kos.com', '081234567802', 'Jl. Palembang Betung No. 22', 'Perempuan'],
            ['Rani Lestari', 'rani@kos.com', '081234567803', 'Jl. Serasi Betung', 'Perempuan'],
        ];

        $penyewas = collect($penyewaData)->map(function ($item) {
            $user = User::updateOrCreate(
                ['email' => $item[1]],
                [
                    'name' => $item[0],
                    'password' => Hash::make('penyewa123'),
                    'role' => User::ROLE_PENYEWA,
                ]
            );

            return Penyewa::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama_lengkap' => $item[0],
                    'no_hp' => $item[2],
                    'alamat' => $item[3],
                    'jenis_kelamin' => $item[4],
                ]
            );
        });

        $fasilitas = collect(['Kasur', 'Lemari', 'Kipas Angin', 'AC', 'WiFi', 'Kamar Mandi Dalam', 'Listrik', 'Air'])
            ->map(fn ($nama) => Fasilitas::firstOrCreate(['penyedia_kos_id' => null, 'nama_fasilitas' => $nama]));

        $kamarData = [
            ['Kamar A1', 'Standar', 750000, Kamar::STATUS_TERSEDIA, 'assets/kamar/utama-a1.jpeg'],
            ['Kamar A2', 'Standar', 800000, Kamar::STATUS_TERSEDIA, 'assets/kamar/utama-a2.jpeg'],
            ['Kamar A3', 'Deluxe', 950000, Kamar::STATUS_DIPESAN, 'assets/kamar/utama-a3.jpeg'],
            ['Kamar B1', 'Deluxe', 1000000, Kamar::STATUS_TERISI, 'assets/kamar/utama-b1.jpeg'],
            ['Kamar B2', 'Standar', 700000, Kamar::STATUS_MAINTENANCE, 'assets/kamar/utama-b2.jpeg'],
            ['Kamar B3', 'Premium', 1200000, Kamar::STATUS_TERSEDIA, 'assets/kamar/utama-b3.jpeg'],
        ];

        $allKos = collect([$kosUtama])->merge($kosTambahan);

        $kamars = collect($kamarData)->map(function ($item, $index) use ($fasilitas, $allKos) {
            $kos = $allKos[$index % $allKos->count()];
            $kamar = Kamar::updateOrCreate(
                ['kos_id' => $kos->id, 'nama_kamar' => $item[0]],
                [
                    'tipe_kamar' => $item[1],
                    'harga_bulanan' => $item[2],
                    'status' => $item[3],
                    'foto' => $item[4],
                    'deskripsi' => 'Kamar nyaman, bersih, dan cocok untuk penghuni yang membutuhkan lingkungan kos yang tertata.',
                ]
            );

            $kamar->fasilitas()->sync($fasilitas->random(5)->pluck('id')->all());

            return $kamar;
        });

        $pemesananDipesan = Pemesanan::updateOrCreate(
            ['penyewa_id' => $penyewas[1]->id, 'kamar_id' => $kamars[2]->id],
            [
                'tanggal_pesan' => now()->subDays(2),
                'tanggal_masuk' => now()->addDays(5),
                'status_pemesanan' => Pemesanan::STATUS_DITERIMA,
                'catatan_penyewa' => 'Ingin masuk awal bulan.',
                'catatan_admin' => 'Silakan lakukan pembayaran awal.',
            ]
        );

        PembayaranAwal::updateOrCreate(
            ['pemesanan_id' => $pemesananDipesan->id],
            [
                'jumlah_bayar' => 500000,
                'tanggal_bayar' => now()->subDay(),
                'status_pembayaran' => PembayaranAwal::STATUS_MENUNGGU,
            ]
        );

        $pemesananSelesai = Pemesanan::updateOrCreate(
            ['penyewa_id' => $penyewas[0]->id, 'kamar_id' => $kamars[3]->id],
            [
                'tanggal_pesan' => now()->subMonths(2),
                'tanggal_masuk' => now()->subMonth(),
                'status_pemesanan' => Pemesanan::STATUS_SELESAI,
                'catatan_penyewa' => 'Membutuhkan kamar yang dekat kamar mandi.',
                'catatan_admin' => 'Pembayaran awal valid.',
            ]
        );

        PembayaranAwal::updateOrCreate(
            ['pemesanan_id' => $pemesananSelesai->id],
            [
                'jumlah_bayar' => 1000000,
                'tanggal_bayar' => now()->subMonth(),
                'status_pembayaran' => PembayaranAwal::STATUS_LUNAS,
            ]
        );

        $penghuni = Penghuni::updateOrCreate(
            ['penyewa_id' => $penyewas[0]->id, 'kamar_id' => $kamars[3]->id],
            [
                'tanggal_masuk' => now()->subMonth(),
                'harga_bulanan' => $kamars[3]->harga_bulanan,
                'tanggal_jatuh_tempo' => now()->addDays(7),
                'status_penghuni' => Penghuni::STATUS_AKTIF,
            ]
        );

        $tagihanLunas = TagihanBulanan::updateOrCreate(
            ['penghuni_id' => $penghuni->id, 'bulan' => now()->subMonth()->month, 'tahun' => now()->subMonth()->year],
            [
                'jumlah_tagihan' => $penghuni->harga_bulanan,
                'tanggal_jatuh_tempo' => now()->subMonth()->day(10),
                'status_tagihan' => TagihanBulanan::STATUS_LUNAS,
            ]
        );

        PembayaranBulanan::updateOrCreate(
            ['tagihan_bulanan_id' => $tagihanLunas->id],
            [
                'tanggal_bayar' => now()->subMonth()->day(8),
                'jumlah_bayar' => $penghuni->harga_bulanan,
                'bukti_bayar' => 'dummy/bukti-lunas.pdf',
                'status_pembayaran' => PembayaranBulanan::STATUS_LUNAS,
            ]
        );

        TagihanBulanan::updateOrCreate(
            ['penghuni_id' => $penghuni->id, 'bulan' => now()->month, 'tahun' => now()->year],
            [
                'jumlah_tagihan' => $penghuni->harga_bulanan,
                'tanggal_jatuh_tempo' => now()->addDays(7),
                'status_tagihan' => TagihanBulanan::STATUS_BELUM_BAYAR,
            ]
        );

        TagihanBulanan::updateOrCreate(
            ['penghuni_id' => $penghuni->id, 'bulan' => now()->subMonths(2)->month, 'tahun' => now()->subMonths(2)->year],
            [
                'jumlah_tagihan' => $penghuni->harga_bulanan,
                'tanggal_jatuh_tempo' => now()->subDays(10),
                'status_tagihan' => TagihanBulanan::STATUS_TERLAMBAT,
            ]
        );

        $tagihanMenunggu = TagihanBulanan::updateOrCreate(
            ['penghuni_id' => $penghuni->id, 'bulan' => now()->addMonth()->month, 'tahun' => now()->addMonth()->year],
            [
                'jumlah_tagihan' => $penghuni->harga_bulanan,
                'tanggal_jatuh_tempo' => now()->addMonth()->day(10),
                'status_tagihan' => TagihanBulanan::STATUS_MENUNGGU,
            ]
        );

        PembayaranBulanan::updateOrCreate(
            ['tagihan_bulanan_id' => $tagihanMenunggu->id],
            [
                'tanggal_bayar' => now(),
                'jumlah_bayar' => $penghuni->harga_bulanan,
                'bukti_bayar' => 'dummy/bukti-menunggu.pdf',
                'status_pembayaran' => PembayaranBulanan::STATUS_MENUNGGU,
            ]
        );

        $this->call(KostAssetSeeder::class);
        $this->seedComplaintReportData();
    }

    private function ensureDemoPaymentProofs(): void
    {
        collect([
            'dummy/bukti-lunas.pdf' => 'Bukti Pembayaran Lunas',
            'dummy/bukti-menunggu.pdf' => 'Bukti Pembayaran Menunggu Konfirmasi',
        ])->each(function (string $title, string $path) {
            Storage::disk('public')->put($path, Pdf::loadHTML(
                '<html><body style="font-family: DejaVu Sans, sans-serif;">'
                .'<h1>'.$title.'</h1>'
                .'<p>Dokumen demo untuk validasi bukti pembayaran Kos Putri Betung.</p>'
                .'<p>Tanggal dibuat: '.now()->format('d/m/Y H:i').'</p>'
                .'</body></html>'
            )->output());
        });
    }

    private function seedComplaintReportData(): void
    {
        Keluhan::whereNull('kode_keluhan')
            ->where('judul', 'Area dapur perlu dibersihkan')
            ->where('deskripsi', 'Mohon jadwal kebersihan area dapur ditambah.')
            ->delete();

        $providerUser = User::updateOrCreate(
            ['email' => 'laporan.keluhan@kos.com'],
            [
                'name' => 'Admin Data Keluhan Kos',
                'password' => Hash::make('password'),
                'role' => User::ROLE_PENYEDIA_KOS,
            ]
        );

        $provider = PenyediaKos::updateOrCreate(
            ['user_id' => $providerUser->id],
            [
                'nama_lengkap' => 'Admin Data Keluhan Kos',
                'no_hp' => '083179749420',
                'alamat' => 'Jl. Laporan Keluhan Kos, Betung',
            ]
        );

        collect($this->complaintReportRows())->each(function (array $row) use ($provider) {
            $user = User::updateOrCreate(
                ['email' => strtolower($row['kode_penyewa']).'@kos.com'],
                [
                    'name' => $row['nama_penyewa'],
                    'password' => Hash::make('penyewa123'),
                    'role' => User::ROLE_PENYEWA,
                ]
            );

            $penyewa = Penyewa::updateOrCreate(
                ['kode_penyewa' => $row['kode_penyewa']],
                [
                    'user_id' => $user->id,
                    'nama_lengkap' => $row['nama_penyewa'],
                    'no_hp' => $row['no_hp'],
                    'alamat' => 'Alamat '.$row['nama_penyewa'].' - Betung',
                    'jenis_kelamin' => $row['jenis_kelamin'],
                ]
            );

            $kos = Kos::updateOrCreate(
                ['penyedia_kos_id' => $provider->id, 'nama_kos' => $row['nama_kos']],
                [
                    'alamat' => 'Jl. '.$row['nama_kos'].' Betung, Banyuasin',
                    'kota' => 'Betung',
                    'deskripsi' => 'Data kos untuk laporan keluhan sistem.',
                    'foto' => null,
                    'latitude' => -2.88,
                    'longitude' => 104.22,
                    'status' => Kos::STATUS_NONAKTIF,
                    'is_promoted' => false,
                ]
            );

            $kamar = Kamar::updateOrCreate(
                ['kos_id' => $kos->id, 'nama_kamar' => $row['kamar']],
                [
                    'tipe_kamar' => 'Standar',
                    'harga_bulanan' => 850000,
                    'deskripsi' => 'Kamar '.$row['kamar'].' pada '.$row['nama_kos'].' untuk data laporan keluhan.',
                    'foto' => null,
                    'status' => Kamar::STATUS_TERISI,
                ]
            );

            $penghuni = Penghuni::updateOrCreate(
                ['penyewa_id' => $penyewa->id, 'kamar_id' => $kamar->id],
                [
                    'tanggal_masuk' => Carbon::create(2026, 5, 1),
                    'harga_bulanan' => $kamar->harga_bulanan,
                    'tanggal_jatuh_tempo' => Carbon::create(2026, 8, 25),
                    'status_penghuni' => Penghuni::STATUS_AKTIF,
                ]
            );

            $keluhan = Keluhan::updateOrCreate(
                ['kode_keluhan' => $row['kode_keluhan']],
                [
                    'penghuni_id' => $penghuni->id,
                    'kategori' => $row['kategori'],
                    'judul' => $row['keluhan'],
                    'deskripsi' => $row['keluhan'],
                    'status_keluhan' => $row['status'],
                    'catatan_admin' => null,
                ]
            );

            $tanggal = Carbon::createFromFormat('d/m/Y', $row['tanggal'])->startOfDay();

            Keluhan::withoutTimestamps(fn () => $keluhan->forceFill([
                'created_at' => $tanggal,
                'updated_at' => $tanggal,
            ])->saveQuietly());
        });
    }

    private function complaintReportRows(): array
    {
        return [
            ['kode_keluhan' => 'K001', 'kode_penyewa' => 'P001', 'nama_penyewa' => 'Nadia Putri', 'nama_kos' => 'Kos Pondok Aer', 'kamar' => 'A3', 'tanggal' => '12/05/2026', 'kategori' => 'Kebersihan', 'keluhan' => 'Area dapur perlu dibersihkan', 'status' => Keluhan::STATUS_DIPROSES, 'jenis_kelamin' => 'Perempuan', 'no_hp' => '081234560001'],
            ['kode_keluhan' => 'K002', 'kode_penyewa' => 'P002', 'nama_penyewa' => 'Siti Aminah', 'nama_kos' => 'Permata Kos', 'kamar' => 'B1', 'tanggal' => '14/05/2026', 'kategori' => 'Fasilitas', 'keluhan' => 'Lampu kamar mati', 'status' => Keluhan::STATUS_SELESAI, 'jenis_kelamin' => 'Perempuan', 'no_hp' => '081234560002'],
            ['kode_keluhan' => 'K003', 'kode_penyewa' => 'P003', 'nama_penyewa' => 'Andi Saputra', 'nama_kos' => 'Asri Kos', 'kamar' => 'A2', 'tanggal' => '18/05/2026', 'kategori' => 'Air', 'keluhan' => 'Air kamar mandi tidak mengalir', 'status' => Keluhan::STATUS_SELESAI, 'jenis_kelamin' => 'Laki-laki', 'no_hp' => '081234560003'],
            ['kode_keluhan' => 'K004', 'kode_penyewa' => 'P004', 'nama_penyewa' => 'Rina Oktavia', 'nama_kos' => 'Citra Kos', 'kamar' => 'B2', 'tanggal' => '22/05/2026', 'kategori' => 'Fasilitas', 'keluhan' => 'Kipas angin tidak berfungsi', 'status' => Keluhan::STATUS_DIPROSES, 'jenis_kelamin' => 'Perempuan', 'no_hp' => '081234560004'],
            ['kode_keluhan' => 'K005', 'kode_penyewa' => 'P005', 'nama_penyewa' => 'Dimas Pratama', 'nama_kos' => 'D Kost', 'kamar' => 'C1', 'tanggal' => '03/06/2026', 'kategori' => 'Keamanan', 'keluhan' => 'Kunci pintu kamar sulit digunakan', 'status' => Keluhan::STATUS_SELESAI, 'jenis_kelamin' => 'Laki-laki', 'no_hp' => '081234560005'],
            ['kode_keluhan' => 'K006', 'kode_penyewa' => 'P006', 'nama_penyewa' => 'Fitri Lestari', 'nama_kos' => 'Eka Kost', 'kamar' => 'C2', 'tanggal' => '07/06/2026', 'kategori' => 'Kebersihan', 'keluhan' => 'Sampah di area belakang belum diangkut', 'status' => Keluhan::STATUS_SELESAI, 'jenis_kelamin' => 'Perempuan', 'no_hp' => '081234560006'],
            ['kode_keluhan' => 'K007', 'kode_penyewa' => 'P007', 'nama_penyewa' => 'Reza Maulana', 'nama_kos' => 'Cahaya Kost', 'kamar' => 'A1', 'tanggal' => '11/06/2026', 'kategori' => 'Fasilitas', 'keluhan' => 'Wi-Fi tidak dapat digunakan', 'status' => Keluhan::STATUS_DIPROSES, 'jenis_kelamin' => 'Laki-laki', 'no_hp' => '081234560007'],
            ['kode_keluhan' => 'K008', 'kode_penyewa' => 'P008', 'nama_penyewa' => 'Ayu Permata', 'nama_kos' => 'Mulia Kos', 'kamar' => 'B3', 'tanggal' => '16/06/2026', 'kategori' => 'Air', 'keluhan' => 'Keran kamar mandi bocor', 'status' => Keluhan::STATUS_SELESAI, 'jenis_kelamin' => 'Perempuan', 'no_hp' => '081234560008'],
            ['kode_keluhan' => 'K009', 'kode_penyewa' => 'P009', 'nama_penyewa' => 'Fajar Ramadhan', 'nama_kos' => 'Kos Damai', 'kamar' => 'C3', 'tanggal' => '21/06/2026', 'kategori' => 'Kebersihan', 'keluhan' => 'Kamar mandi bersama kurang bersih', 'status' => Keluhan::STATUS_DIPROSES, 'jenis_kelamin' => 'Laki-laki', 'no_hp' => '081234560009'],
            ['kode_keluhan' => 'K010', 'kode_penyewa' => 'P010', 'nama_penyewa' => 'Salsabila Putri', 'nama_kos' => 'Indah Kos', 'kamar' => 'D1', 'tanggal' => '02/07/2026', 'kategori' => 'Fasilitas', 'keluhan' => 'Stop kontak kamar rusak', 'status' => Keluhan::STATUS_SELESAI, 'jenis_kelamin' => 'Perempuan', 'no_hp' => '081234560010'],
            ['kode_keluhan' => 'K011', 'kode_penyewa' => 'P011', 'nama_penyewa' => 'Muhammad Rizky', 'nama_kos' => 'Rans Kos', 'kamar' => 'D2', 'tanggal' => '06/07/2026', 'kategori' => 'Keamanan', 'keluhan' => 'Lampu halaman depan mati', 'status' => Keluhan::STATUS_SELESAI, 'jenis_kelamin' => 'Laki-laki', 'no_hp' => '081234560011'],
            ['kode_keluhan' => 'K012', 'kode_penyewa' => 'P012', 'nama_penyewa' => 'Intan Permata', 'nama_kos' => 'Kos Pondok Aer', 'kamar' => 'A1', 'tanggal' => '11/07/2026', 'kategori' => 'Kebersihan', 'keluhan' => 'Tempat sampah penuh', 'status' => Keluhan::STATUS_SELESAI, 'jenis_kelamin' => 'Perempuan', 'no_hp' => '081234560012'],
            ['kode_keluhan' => 'K013', 'kode_penyewa' => 'P013', 'nama_penyewa' => 'Yoga Pratama', 'nama_kos' => 'Permata Kos', 'kamar' => 'B2', 'tanggal' => '16/07/2026', 'kategori' => 'Fasilitas', 'keluhan' => 'Lemari kamar rusak', 'status' => Keluhan::STATUS_DIPROSES, 'jenis_kelamin' => 'Laki-laki', 'no_hp' => '081234560013'],
            ['kode_keluhan' => 'K014', 'kode_penyewa' => 'P014', 'nama_penyewa' => 'Desi Anggraini', 'nama_kos' => 'Asri Kos', 'kamar' => 'A3', 'tanggal' => '21/07/2026', 'kategori' => 'Air', 'keluhan' => 'Air keran kecil', 'status' => Keluhan::STATUS_SELESAI, 'jenis_kelamin' => 'Perempuan', 'no_hp' => '081234560014'],
            ['kode_keluhan' => 'K015', 'kode_penyewa' => 'P015', 'nama_penyewa' => 'Bagas Aditya', 'nama_kos' => 'Citra Kos', 'kamar' => 'C1', 'tanggal' => '26/07/2026', 'kategori' => 'Kebersihan', 'keluhan' => 'Area parkir kotor', 'status' => Keluhan::STATUS_SELESAI, 'jenis_kelamin' => 'Laki-laki', 'no_hp' => '081234560015'],
            ['kode_keluhan' => 'K016', 'kode_penyewa' => 'P016', 'nama_penyewa' => 'Putri Amelia', 'nama_kos' => 'D Kost', 'kamar' => 'D2', 'tanggal' => '02/08/2026', 'kategori' => 'Fasilitas', 'keluhan' => 'AC kurang dingin', 'status' => Keluhan::STATUS_DIPROSES, 'jenis_kelamin' => 'Perempuan', 'no_hp' => '081234560016'],
            ['kode_keluhan' => 'K017', 'kode_penyewa' => 'P017', 'nama_penyewa' => 'Aldi Kurniawan', 'nama_kos' => 'Eka Kost', 'kamar' => 'A2', 'tanggal' => '06/08/2026', 'kategori' => 'Keamanan', 'keluhan' => 'Pintu gerbang sulit dikunci', 'status' => Keluhan::STATUS_DIPROSES, 'jenis_kelamin' => 'Laki-laki', 'no_hp' => '081234560017'],
            ['kode_keluhan' => 'K018', 'kode_penyewa' => 'P018', 'nama_penyewa' => 'Nabila Sari', 'nama_kos' => 'Cahaya Kost', 'kamar' => 'B1', 'tanggal' => '11/08/2026', 'kategori' => 'Internet', 'keluhan' => 'Koneksi Wi-Fi lambat', 'status' => Keluhan::STATUS_SELESAI, 'jenis_kelamin' => 'Perempuan', 'no_hp' => '081234560018'],
            ['kode_keluhan' => 'K019', 'kode_penyewa' => 'P019', 'nama_penyewa' => 'Arif Hidayat', 'nama_kos' => 'Mulia Kos', 'kamar' => 'C2', 'tanggal' => '16/08/2026', 'kategori' => 'Kebersihan', 'keluhan' => 'Saluran pembuangan tersumbat', 'status' => Keluhan::STATUS_DIPROSES, 'jenis_kelamin' => 'Laki-laki', 'no_hp' => '081234560019'],
            ['kode_keluhan' => 'K020', 'kode_penyewa' => 'P020', 'nama_penyewa' => 'Tiara Anjani', 'nama_kos' => 'Kos Damai', 'kamar' => 'D1', 'tanggal' => '18/08/2026', 'kategori' => 'Fasilitas', 'keluhan' => 'Lampu kamar berkedip', 'status' => Keluhan::STATUS_DIKIRIM, 'jenis_kelamin' => 'Perempuan', 'no_hp' => '081234560020'],
        ];
    }
}
