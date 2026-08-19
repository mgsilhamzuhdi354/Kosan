<?php

namespace Tests\Feature;

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
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\KostAssetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KosManagementFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_penyewa_registration_creates_profile_and_redirects_to_dashboard(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Dewi Sartika',
            'email' => 'dewi@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'account_type' => 'penyewa',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Betung',
            'jenis_kelamin' => 'Perempuan',
        ]);

        $response->assertRedirect(route('penyewa.dashboard', absolute: false));
        $this->assertDatabaseHas('users', ['email' => 'dewi@example.com', 'role' => User::ROLE_PENYEWA]);
        $this->assertDatabaseHas('penyewas', ['nama_lengkap' => 'Dewi Sartika']);
    }

    public function test_penyedia_registration_creates_provider_profile_and_kos(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Pemilik Kos',
            'email' => 'pemilik@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'account_type' => User::ROLE_PENYEDIA_KOS,
            'no_hp' => '081234567891',
            'alamat' => 'Jl. Betung Pemilik',
            'nama_kos' => 'Kos Pemilik Baru',
            'kota' => 'Betung',
        ]);

        $response->assertRedirect(route('penyedia.dashboard', absolute: false));
        $this->assertDatabaseHas('users', ['email' => 'pemilik@example.com', 'role' => User::ROLE_PENYEDIA_KOS]);
        $this->assertDatabaseHas('penyedia_kos', ['nama_lengkap' => 'Pemilik Kos']);
        $this->assertDatabaseHas('kos', ['nama_kos' => 'Kos Pemilik Baru']);
    }

    public function test_login_redirects_admin_and_penyewa_to_their_dashboards(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $penyewaUser = User::create([
            'name' => 'Penyewa',
            'email' => 'penyewa@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENYEWA,
        ]);

        $penyediaUser = User::create([
            'name' => 'Penyedia',
            'email' => 'penyedia@test.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENYEDIA_KOS,
        ]);

        PenyediaKos::create([
            'user_id' => $penyediaUser->id,
            'nama_lengkap' => 'Penyedia',
            'no_hp' => '082',
            'alamat' => 'Betung',
        ]);

        Penyewa::create([
            'user_id' => $penyewaUser->id,
            'nama_lengkap' => 'Penyewa',
            'no_hp' => '081',
            'alamat' => 'Betung',
            'jenis_kelamin' => 'Perempuan',
        ]);

        $this->post(route('login'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard', absolute: false));

        $this->post(route('logout'));

        $this->post(route('login'), ['email' => $penyewaUser->email, 'password' => 'password'])
            ->assertRedirect(route('penyewa.dashboard', absolute: false));

        $this->post(route('logout'));

        $this->post(route('login'), ['email' => $penyediaUser->email, 'password' => 'password'])
            ->assertRedirect(route('penyedia.dashboard', absolute: false));
    }

    public function test_admin_can_manage_master_data(): void
    {
        $admin = $this->adminUser();
        $kos = $this->kos();

        $this->actingAs($admin)->post(route('admin.fasilitas.store'), [
            'nama_fasilitas' => 'WiFi',
        ])->assertRedirect(route('admin.fasilitas.index', absolute: false));

        $fasilitas = Fasilitas::first();

        $this->actingAs($admin)->post(route('admin.kamar.store'), [
            'nama_kamar' => 'Kamar C1',
            'kos_id' => $kos->id,
            'tipe_kamar' => 'Premium',
            'harga_bulanan' => 1200000,
            'deskripsi' => 'Kamar premium untuk penghuni aktif.',
            'status' => Kamar::STATUS_TERSEDIA,
            'fasilitas' => [$fasilitas->id],
        ])->assertRedirect(route('admin.kamar.index', absolute: false));

        $this->assertDatabaseHas('kamars', ['nama_kamar' => 'Kamar C1']);
        $this->assertDatabaseHas('kamar_fasilitas', ['fasilitas_id' => $fasilitas->id]);
    }

    public function test_room_name_is_unique_per_kos_not_globally(): void
    {
        $admin = $this->adminUser();
        $firstKos = $this->kos();
        $secondKos = $this->kos();

        $payload = [
            'nama_kamar' => 'Kamar A1',
            'tipe_kamar' => 'Standar',
            'harga_bulanan' => 800000,
            'deskripsi' => 'Kamar standar nyaman.',
            'status' => Kamar::STATUS_TERSEDIA,
        ];

        $this->actingAs($admin)->post(route('admin.kamar.store'), [
            ...$payload,
            'kos_id' => $firstKos->id,
        ])->assertRedirect(route('admin.kamar.index', absolute: false));

        $this->actingAs($admin)->post(route('admin.kamar.store'), [
            ...$payload,
            'kos_id' => $secondKos->id,
        ])->assertRedirect(route('admin.kamar.index', absolute: false));

        $this->actingAs($admin)->post(route('admin.kamar.store'), [
            ...$payload,
            'kos_id' => $firstKos->id,
        ])->assertSessionHasErrors('nama_kamar');

        $this->assertDatabaseHas('kamars', ['nama_kamar' => 'Kamar A1', 'kos_id' => $firstKos->id]);
        $this->assertDatabaseHas('kamars', ['nama_kamar' => 'Kamar A1', 'kos_id' => $secondKos->id]);
        $this->assertDatabaseCount('kamars', 2);
    }

    public function test_penyedia_can_only_create_rooms_for_owned_kos(): void
    {
        $penyediaUser = User::create([
            'name' => 'Penyedia Mandiri',
            'email' => 'penyedia-mandiri@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENYEDIA_KOS,
        ]);

        $penyedia = PenyediaKos::create([
            'user_id' => $penyediaUser->id,
            'nama_lengkap' => 'Penyedia Mandiri',
            'no_hp' => '082111111111',
            'alamat' => 'Betung',
        ]);

        $ownedKos = Kos::create([
            'penyedia_kos_id' => $penyedia->id,
            'nama_kos' => 'Kos Milik Sendiri',
            'alamat' => 'Jl. Sendiri',
            'kota' => 'Betung',
            'deskripsi' => 'Kos yang dimiliki penyedia aktif.',
            'status' => Kos::STATUS_AKTIF,
        ]);

        $otherKos = $this->kos();
        $payload = [
            'nama_kamar' => 'Kamar Penyedia',
            'tipe_kamar' => 'Standar',
            'harga_bulanan' => 800000,
            'deskripsi' => 'Kamar standar nyaman.',
            'status' => Kamar::STATUS_TERSEDIA,
        ];

        $this->actingAs($penyediaUser)->post(route('penyedia.kamar.store'), [
            ...$payload,
            'kos_id' => $otherKos->id,
        ])->assertSessionHasErrors('kos_id');

        $this->actingAs($penyediaUser)->post(route('penyedia.kamar.store'), [
            ...$payload,
            'kos_id' => $ownedKos->id,
        ])->assertRedirect(route('penyedia.kamar.index', absolute: false));

        $this->assertDatabaseMissing('kamars', ['nama_kamar' => 'Kamar Penyedia', 'kos_id' => $otherKos->id]);
        $this->assertDatabaseHas('kamars', ['nama_kamar' => 'Kamar Penyedia', 'kos_id' => $ownedKos->id]);
    }

    public function test_penyedia_can_add_and_use_owned_facilities(): void
    {
        $penyediaUser = User::create([
            'name' => 'Penyedia Fasilitas',
            'email' => 'penyedia-fasilitas@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENYEDIA_KOS,
        ]);

        $penyedia = PenyediaKos::create([
            'user_id' => $penyediaUser->id,
            'nama_lengkap' => 'Penyedia Fasilitas',
            'no_hp' => '082111111114',
            'alamat' => 'Betung',
        ]);

        $ownedKos = Kos::create([
            'penyedia_kos_id' => $penyedia->id,
            'nama_kos' => 'Kos Fasilitas Milik',
            'alamat' => 'Jl. Fasilitas',
            'kota' => 'Betung',
            'deskripsi' => 'Kos untuk fasilitas penyedia.',
            'status' => Kos::STATUS_AKTIF,
        ]);

        $otherPenyedia = PenyediaKos::create([
            'user_id' => User::create([
                'name' => 'Penyedia Lain Fasilitas',
                'email' => 'penyedia-lain-fasilitas@example.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_PENYEDIA_KOS,
            ])->id,
            'nama_lengkap' => 'Penyedia Lain Fasilitas',
            'no_hp' => '082111111115',
            'alamat' => 'Betung',
        ]);

        $globalFacility = Fasilitas::create(['nama_fasilitas' => 'WiFi']);
        $otherFacility = Fasilitas::create([
            'penyedia_kos_id' => $otherPenyedia->id,
            'nama_fasilitas' => 'Ruang Jemur Privat',
        ]);

        $this->actingAs($penyediaUser)->get(route('penyedia.fasilitas.index'))
            ->assertOk()
            ->assertSee('WiFi')
            ->assertDontSee('Ruang Jemur Privat');

        $this->actingAs($penyediaUser)->post(route('penyedia.fasilitas.store'), [
            'nama_fasilitas' => 'Dapur Mini',
        ])->assertRedirect(route('penyedia.fasilitas.index', absolute: false));

        $ownedFacility = Fasilitas::where('nama_fasilitas', 'Dapur Mini')->firstOrFail();
        $this->assertSame($penyedia->id, $ownedFacility->penyedia_kos_id);

        $this->actingAs($penyediaUser)->post(route('penyedia.fasilitas.store'), [
            'nama_fasilitas' => 'WiFi',
        ])->assertSessionHasErrors('nama_fasilitas');

        $this->actingAs($penyediaUser)->post(route('penyedia.kamar.store'), [
            'nama_kamar' => 'Kamar Fasilitas A1',
            'kos_id' => $ownedKos->id,
            'tipe_kamar' => 'Standar',
            'harga_bulanan' => 800000,
            'deskripsi' => 'Kamar dengan fasilitas tambahan penyedia.',
            'status' => Kamar::STATUS_TERSEDIA,
            'fasilitas' => [$globalFacility->id, $ownedFacility->id],
        ])->assertRedirect(route('penyedia.kamar.index', absolute: false));

        $kamar = Kamar::where('nama_kamar', 'Kamar Fasilitas A1')->firstOrFail();
        $this->assertDatabaseHas('kamar_fasilitas', ['kamar_id' => $kamar->id, 'fasilitas_id' => $globalFacility->id]);
        $this->assertDatabaseHas('kamar_fasilitas', ['kamar_id' => $kamar->id, 'fasilitas_id' => $ownedFacility->id]);

        $this->actingAs($penyediaUser)->post(route('penyedia.kamar.store'), [
            'nama_kamar' => 'Kamar Fasilitas A2',
            'kos_id' => $ownedKos->id,
            'tipe_kamar' => 'Standar',
            'harga_bulanan' => 800000,
            'deskripsi' => 'Kamar yang mencoba fasilitas penyedia lain.',
            'status' => Kamar::STATUS_TERSEDIA,
            'fasilitas' => [$otherFacility->id],
        ])->assertSessionHasErrors('fasilitas.0');

        $this->actingAs($penyediaUser)->get(route('penyedia.fasilitas.edit', $otherFacility))
            ->assertRedirect(route('dashboard', absolute: false))
            ->assertSessionHas('error', 'Akses tidak diizinkan.');
    }

    public function test_penyedia_can_manage_owned_kos_catalog(): void
    {
        $penyediaUser = User::create([
            'name' => 'Pemilik Mandiri',
            'email' => 'pemilik-mandiri@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENYEDIA_KOS,
        ]);

        $penyedia = PenyediaKos::create([
            'user_id' => $penyediaUser->id,
            'nama_lengkap' => 'Pemilik Mandiri',
            'no_hp' => '082111111112',
            'alamat' => 'Betung',
        ]);

        $otherKos = $this->kos();

        $this->actingAs($penyediaUser)->get(route('penyedia.kos.index'))
            ->assertOk()
            ->assertDontSee($otherKos->nama_kos);

        $this->actingAs($penyediaUser)->post(route('penyedia.kos.store'), [
            'nama_kos' => 'Kos Mandiri Baru',
            'alamat' => 'Jl. Mandiri Betung',
            'kota' => 'Betung',
            'deskripsi' => 'Kos baru yang didaftarkan langsung oleh pemilik.',
            'status' => Kos::STATUS_AKTIF,
            'is_promoted' => '1',
        ])->assertRedirect(route('penyedia.kos.index', absolute: false));

        $ownedKos = Kos::where('nama_kos', 'Kos Mandiri Baru')->firstOrFail();
        $this->assertSame($penyedia->id, $ownedKos->penyedia_kos_id);

        $this->actingAs($penyediaUser)->put(route('penyedia.kos.update', $ownedKos), [
            'nama_kos' => 'Kos Mandiri Update',
            'alamat' => 'Jl. Mandiri Betung Update',
            'kota' => 'Betung',
            'deskripsi' => 'Kos milik pemilik yang sudah diperbarui.',
            'status' => Kos::STATUS_NONAKTIF,
            'is_promoted' => '0',
        ])->assertRedirect(route('penyedia.kos.index', absolute: false));

        $this->assertDatabaseHas('kos', [
            'id' => $ownedKos->id,
            'nama_kos' => 'Kos Mandiri Update',
            'status' => Kos::STATUS_NONAKTIF,
        ]);

        $this->actingAs($penyediaUser)->get(route('penyedia.kos.edit', $otherKos))
            ->assertRedirect(route('dashboard', absolute: false))
            ->assertSessionHas('error', 'Akses tidak diizinkan.');
        $this->actingAs($penyediaUser)->delete(route('penyedia.kos.destroy', $ownedKos))->assertRedirect(route('penyedia.kos.index', absolute: false));
        $this->assertDatabaseMissing('kos', ['id' => $ownedKos->id]);
    }

    public function test_penyedia_can_process_owned_bookings_and_incoming_payments(): void
    {
        $penyediaUser = User::create([
            'name' => 'Penyedia Transaksi',
            'email' => 'penyedia-transaksi@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENYEDIA_KOS,
        ]);

        $penyedia = PenyediaKos::create([
            'user_id' => $penyediaUser->id,
            'nama_lengkap' => 'Penyedia Transaksi',
            'no_hp' => '082111111113',
            'alamat' => 'Betung',
        ]);

        [$penyewaUser, $penyewa] = $this->penyewaUser();

        $ownedKos = Kos::create([
            'penyedia_kos_id' => $penyedia->id,
            'nama_kos' => 'Kos Transaksi Milik',
            'alamat' => 'Jl. Transaksi',
            'kota' => 'Betung',
            'deskripsi' => 'Kos untuk transaksi penyedia.',
            'status' => Kos::STATUS_AKTIF,
        ]);

        $ownedKamar = Kamar::create([
            'kos_id' => $ownedKos->id,
            'nama_kamar' => 'Kamar Transaksi A1',
            'tipe_kamar' => 'Standar',
            'harga_bulanan' => 800000,
            'deskripsi' => 'Kamar untuk transaksi penyedia.',
            'status' => Kamar::STATUS_TERSEDIA,
        ]);

        $pemesanan = Pemesanan::create([
            'penyewa_id' => $penyewa->id,
            'kamar_id' => $ownedKamar->id,
            'tanggal_pesan' => today(),
            'tanggal_masuk' => now()->addDay(),
            'status_pemesanan' => Pemesanan::STATUS_MENUNGGU,
        ]);

        $otherKos = $this->kos();
        $otherKamar = Kamar::create([
            'kos_id' => $otherKos->id,
            'nama_kamar' => 'Kamar Transaksi Lain',
            'tipe_kamar' => 'Standar',
            'harga_bulanan' => 700000,
            'deskripsi' => 'Kamar milik penyedia lain.',
            'status' => Kamar::STATUS_TERSEDIA,
        ]);
        $otherPemesanan = Pemesanan::create([
            'penyewa_id' => $penyewa->id,
            'kamar_id' => $otherKamar->id,
            'tanggal_pesan' => today(),
            'tanggal_masuk' => now()->addDay(),
            'status_pemesanan' => Pemesanan::STATUS_MENUNGGU,
        ]);

        $this->actingAs($penyediaUser)->get(route('penyedia.pemesanan.index'))
            ->assertOk()
            ->assertSee('Kamar Transaksi A1')
            ->assertDontSee('Kamar Transaksi Lain');

        $this->actingAs($penyediaUser)->patch(route('penyedia.pemesanan.approve', $otherPemesanan))
            ->assertRedirect(route('dashboard', absolute: false))
            ->assertSessionHas('error', 'Akses tidak diizinkan.');
        $this->actingAs($penyediaUser)->patch(route('penyedia.pemesanan.approve', $pemesanan), [
            'catatan_admin' => 'Silakan bayar DP.',
        ])->assertRedirect();

        $this->assertSame(Pemesanan::STATUS_DITERIMA, $pemesanan->fresh()->status_pemesanan);
        $this->assertDatabaseHas('pembayaran_awals', [
            'pemesanan_id' => $pemesanan->id,
            'status_pembayaran' => PembayaranAwal::STATUS_BELUM_BAYAR,
        ]);

        $dp = $pemesanan->pembayaranAwal()->firstOrFail();
        $dp->update([
            'jumlah_bayar' => 800000,
            'tanggal_bayar' => today(),
            'bukti_bayar' => 'pembayaran-awal/dp.pdf',
            'status_pembayaran' => PembayaranAwal::STATUS_MENUNGGU,
        ]);

        $this->actingAs($penyediaUser)->get(route('penyedia.keuangan.index'))
            ->assertOk()
            ->assertSee('Kos Transaksi Milik')
            ->assertSee('Rp 800.000');

        $this->actingAs($penyediaUser)->patch(route('penyedia.pembayaran-awal.approve', $dp))->assertRedirect();
        $this->assertSame(PembayaranAwal::STATUS_LUNAS, $dp->fresh()->status_pembayaran);
        $this->assertDatabaseHas('penghunis', [
            'penyewa_id' => $penyewa->id,
            'kamar_id' => $ownedKamar->id,
            'status_penghuni' => Penghuni::STATUS_AKTIF,
        ]);

        $tagihan = TagihanBulanan::firstOrFail();
        $pembayaranBulanan = PembayaranBulanan::create([
            'tagihan_bulanan_id' => $tagihan->id,
            'tanggal_bayar' => today(),
            'jumlah_bayar' => $tagihan->jumlah_tagihan,
            'bukti_bayar' => 'pembayaran-bulanan/bulanan.pdf',
            'status_pembayaran' => PembayaranBulanan::STATUS_MENUNGGU,
        ]);

        $this->actingAs($penyediaUser)->patch(route('penyedia.pembayaran-bulanan.approve', $pembayaranBulanan))->assertRedirect();
        $this->assertSame(PembayaranBulanan::STATUS_LUNAS, $pembayaranBulanan->fresh()->status_pembayaran);
        $this->assertSame(TagihanBulanan::STATUS_LUNAS, $tagihan->fresh()->status_tagihan);
    }

    public function test_booking_dp_monthly_payment_and_complaint_flow(): void
    {
        Storage::fake('public');

        $admin = $this->adminUser();
        [$penyewaUser, $penyewa] = $this->penyewaUser();
        $kos = $this->kos();
        $kamar = Kamar::create([
            'kos_id' => $kos->id,
            'nama_kamar' => 'Kamar A1',
            'tipe_kamar' => 'Standar',
            'harga_bulanan' => 800000,
            'deskripsi' => 'Kamar standar nyaman.',
            'status' => Kamar::STATUS_TERSEDIA,
        ]);

        $this->actingAs($penyewaUser)->post(route('penyewa.pemesanan.store', $kamar), [
            'tanggal_masuk' => now()->addDay()->format('Y-m-d'),
            'catatan_penyewa' => 'Siap masuk.',
        ])->assertRedirect(route('penyewa.pemesanan.index', absolute: false));

        $pemesanan = Pemesanan::first();
        $this->assertSame(Pemesanan::STATUS_MENUNGGU, $pemesanan->status_pemesanan);

        $this->actingAs($admin)->patch(route('admin.pemesanan.approve', $pemesanan), [
            'catatan_admin' => 'Diterima.',
        ])->assertRedirect();

        $this->assertDatabaseHas('kamars', ['id' => $kamar->id, 'status' => Kamar::STATUS_DIPESAN]);
        $this->assertDatabaseHas('pembayaran_awals', ['pemesanan_id' => $pemesanan->id, 'status_pembayaran' => PembayaranAwal::STATUS_BELUM_BAYAR]);

        $this->actingAs($penyewaUser)->post(route('penyewa.pembayaran-awal.store', $pemesanan), [
            'jumlah_bayar' => 800000,
            'tanggal_bayar' => now()->format('Y-m-d'),
            'bukti_bayar' => UploadedFile::fake()->create('dp.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('penyewa.pembayaran-awal.index', absolute: false));

        $dp = PembayaranAwal::first();
        $this->assertSame(PembayaranAwal::STATUS_MENUNGGU, $dp->status_pembayaran);

        $this->actingAs($admin)->patch(route('admin.pembayaran-awal.approve', $dp))->assertRedirect();

        $this->assertDatabaseHas('pemesanans', ['id' => $pemesanan->id, 'status_pemesanan' => Pemesanan::STATUS_SELESAI]);
        $this->assertDatabaseHas('kamars', ['id' => $kamar->id, 'status' => Kamar::STATUS_TERISI]);
        $this->assertDatabaseHas('penghunis', ['penyewa_id' => $penyewa->id, 'status_penghuni' => Penghuni::STATUS_AKTIF]);
        $this->assertDatabaseCount('tagihan_bulanans', 1);

        $tagihan = TagihanBulanan::first();

        $this->actingAs($penyewaUser)->post(route('penyewa.tagihan.store-payment', $tagihan), [
            'tanggal_bayar' => now()->format('Y-m-d'),
            'jumlah_bayar' => $tagihan->jumlah_tagihan,
            'bukti_bayar' => UploadedFile::fake()->create('bulanan.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('penyewa.tagihan.index', absolute: false));

        $pembayaranBulanan = PembayaranBulanan::first();
        $this->assertSame(TagihanBulanan::STATUS_MENUNGGU, $tagihan->fresh()->status_tagihan);

        $this->actingAs($admin)->patch(route('admin.pembayaran-bulanan.approve', $pembayaranBulanan))->assertRedirect();
        $this->assertSame(TagihanBulanan::STATUS_LUNAS, $tagihan->fresh()->status_tagihan);

        $this->actingAs($penyewaUser)->post(route('penyewa.keluhan.store'), [
            'kategori' => 'Kebersihan',
            'judul' => 'Kamar perlu dibersihkan',
            'deskripsi' => 'Mohon bantuan kebersihan.',
        ])->assertRedirect(route('penyewa.keluhan.index', absolute: false));

        $keluhan = Keluhan::first();
        $this->actingAs($admin)->patch(route('admin.keluhan.update-status', $keluhan), [
            'status_keluhan' => Keluhan::STATUS_SELESAI,
            'catatan_admin' => 'Sudah ditangani.',
        ])->assertRedirect();

        $this->assertSame(Keluhan::STATUS_SELESAI, $keluhan->fresh()->status_keluhan);
    }

    public function test_public_maps_and_favorites_work(): void
    {
        [$penyewaUser] = $this->penyewaUser();
        $kos = $this->kos();
        $kamar = Kamar::create([
            'kos_id' => $kos->id,
            'nama_kamar' => 'Kamar Favorit',
            'tipe_kamar' => 'Standar',
            'harga_bulanan' => 800000,
            'deskripsi' => 'Kamar favorit nyaman.',
            'status' => Kamar::STATUS_TERSEDIA,
        ]);

        $this->get(route('public.maps'))->assertOk()->assertSee('Cari kost lewat peta');
        $this->get(route('public.kamar.index', ['promo' => 1]))->assertOk();

        $this->actingAs($penyewaUser)->post(route('penyewa.favorit.store', $kamar))->assertRedirect();
        $this->actingAs($penyewaUser)->get(route('penyewa.favorit.index'))->assertOk()->assertSee('Kamar Favorit');
    }

    public function test_inactive_kos_rooms_are_hidden_and_not_bookable(): void
    {
        [$penyewaUser] = $this->penyewaUser();
        $kos = $this->kos();
        $kos->update(['status' => Kos::STATUS_NONAKTIF]);

        $kamar = Kamar::create([
            'kos_id' => $kos->id,
            'nama_kamar' => 'Kamar Nonaktif',
            'tipe_kamar' => 'Standar',
            'harga_bulanan' => 800000,
            'deskripsi' => 'Kamar dari kos nonaktif.',
            'status' => Kamar::STATUS_TERSEDIA,
        ]);

        $this->get(route('public.kamar.show', $kamar))->assertNotFound();
        $this->actingAs($penyewaUser)->get(route('penyewa.kamar.show', $kamar))->assertNotFound();
        $this->actingAs($penyewaUser)->post(route('penyewa.favorit.store', $kamar))->assertNotFound();
        $this->actingAs($penyewaUser)->post(route('penyewa.pemesanan.store', $kamar), [
            'tanggal_masuk' => now()->addDay()->format('Y-m-d'),
        ])->assertNotFound();

        $this->assertDatabaseMissing('favorit_kamars', ['kamar_id' => $kamar->id]);
        $this->assertDatabaseMissing('pemesanans', ['kamar_id' => $kamar->id]);
    }

    public function test_room_cannot_receive_multiple_active_bookings(): void
    {
        [$firstUser] = $this->penyewaUser();
        [$secondUser] = $this->penyewaUser();
        $kos = $this->kos();

        $kamar = Kamar::create([
            'kos_id' => $kos->id,
            'nama_kamar' => 'Kamar Booking Ganda',
            'tipe_kamar' => 'Standar',
            'harga_bulanan' => 800000,
            'deskripsi' => 'Kamar untuk cek pemesanan ganda.',
            'status' => Kamar::STATUS_TERSEDIA,
        ]);

        $payload = [
            'tanggal_masuk' => now()->addDay()->format('Y-m-d'),
            'catatan_penyewa' => 'Siap masuk.',
        ];

        $this->actingAs($firstUser)->post(route('penyewa.pemesanan.store', $kamar), $payload)
            ->assertRedirect(route('penyewa.pemesanan.index', absolute: false));

        $this->actingAs($secondUser)
            ->from(route('penyewa.pemesanan.create', $kamar, absolute: false))
            ->post(route('penyewa.pemesanan.store', $kamar), $payload)
            ->assertRedirect(route('penyewa.pemesanan.create', $kamar, absolute: false))
            ->assertSessionHas('error', 'Kamar sedang dalam proses pemesanan.');

        $this->assertDatabaseCount('pemesanans', 1);
    }

    public function test_kamar_photo_must_be_an_image(): void
    {
        Storage::fake('public');

        $admin = $this->adminUser();
        $kos = $this->kos();

        $this->actingAs($admin)->post(route('admin.kamar.store'), [
            'nama_kamar' => 'Kamar Foto PDF',
            'kos_id' => $kos->id,
            'tipe_kamar' => 'Premium',
            'harga_bulanan' => 1200000,
            'deskripsi' => 'Kamar dengan upload foto yang harus berupa gambar.',
            'status' => Kamar::STATUS_TERSEDIA,
            'foto' => UploadedFile::fake()->create('kamar.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('foto');

        $this->assertDatabaseMissing('kamars', ['nama_kamar' => 'Kamar Foto PDF']);
    }

    public function test_rejected_initial_payment_reupload_replaces_old_proof_and_blocks_waiting_resubmit(): void
    {
        Storage::fake('public');

        [$penyewaUser] = $this->penyewaUser();
        $kos = $this->kos();
        $kamar = Kamar::create([
            'kos_id' => $kos->id,
            'nama_kamar' => 'Kamar Upload Ulang',
            'tipe_kamar' => 'Standar',
            'harga_bulanan' => 800000,
            'deskripsi' => 'Kamar untuk cek upload ulang DP.',
            'status' => Kamar::STATUS_DIPESAN,
        ]);

        $pemesanan = Pemesanan::create([
            'penyewa_id' => $penyewaUser->penyewa->id,
            'kamar_id' => $kamar->id,
            'tanggal_pesan' => today(),
            'tanggal_masuk' => now()->addDay(),
            'status_pemesanan' => Pemesanan::STATUS_DITERIMA,
        ]);

        Storage::disk('public')->put('pembayaran-awal/old.pdf', 'old-proof');
        $payment = PembayaranAwal::create([
            'pemesanan_id' => $pemesanan->id,
            'jumlah_bayar' => 800000,
            'tanggal_bayar' => today(),
            'bukti_bayar' => 'pembayaran-awal/old.pdf',
            'status_pembayaran' => PembayaranAwal::STATUS_DITOLAK,
            'catatan_admin' => 'Bukti kurang jelas.',
        ]);

        $this->actingAs($penyewaUser)->post(route('penyewa.pembayaran-awal.store', $pemesanan), [
            'jumlah_bayar' => 800000,
            'tanggal_bayar' => now()->format('Y-m-d'),
            'bukti_bayar' => UploadedFile::fake()->create('dp-baru.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('penyewa.pembayaran-awal.index', absolute: false));

        $payment->refresh();
        $this->assertSame(PembayaranAwal::STATUS_MENUNGGU, $payment->status_pembayaran);
        Storage::disk('public')->assertMissing('pembayaran-awal/old.pdf');
        Storage::disk('public')->assertExists($payment->bukti_bayar);

        $this->actingAs($penyewaUser)
            ->from(route('penyewa.pembayaran-awal.create', $pemesanan, absolute: false))
            ->post(route('penyewa.pembayaran-awal.store', $pemesanan), [
                'jumlah_bayar' => 800000,
                'tanggal_bayar' => now()->format('Y-m-d'),
                'bukti_bayar' => UploadedFile::fake()->create('dp-lagi.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('penyewa.pembayaran-awal.create', $pemesanan, absolute: false))
            ->assertSessionHas('error', 'Pembayaran awal sedang diproses atau sudah lunas.');
    }

    public function test_admin_can_preview_rental_report_and_filter_complaints(): void
    {
        $admin = $this->adminUser();
        [, $penyewa] = $this->penyewaUser();
        $kos = $this->kos();

        $kamar = Kamar::create([
            'kos_id' => $kos->id,
            'nama_kamar' => 'Kamar Report A1',
            'tipe_kamar' => 'Deluxe',
            'harga_bulanan' => 950000,
            'deskripsi' => 'Kamar untuk preview report.',
            'status' => Kamar::STATUS_TERISI,
        ]);

        $penghuni = Penghuni::create([
            'penyewa_id' => $penyewa->id,
            'kamar_id' => $kamar->id,
            'tanggal_masuk' => today(),
            'harga_bulanan' => $kamar->harga_bulanan,
            'tanggal_jatuh_tempo' => today()->addDays(10),
            'status_penghuni' => Penghuni::STATUS_AKTIF,
        ]);

        Keluhan::create([
            'penghuni_id' => $penghuni->id,
            'kategori' => 'Listrik bermasalah',
            'judul' => 'Lampu kamar berkedip',
            'deskripsi' => 'Lampu kamar perlu diperiksa.',
            'status_keluhan' => Keluhan::STATUS_DIKIRIM,
        ]);

        $this->actingAs($admin)->get(route('admin.laporan.index', [
            'type' => 'penyewaan',
            'status' => Penghuni::STATUS_AKTIF,
        ]))->assertOk()
            ->assertSee('Report Penyewaan')
            ->assertSee('Kamar Report A1')
            ->assertSee('Estimasi Sewa Aktif');

        $this->actingAs($admin)->get(route('admin.laporan.pdf', 'penyewa'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($admin)->get(route('admin.keluhan.index', [
            'q' => 'Lampu',
            'kategori' => 'Listrik bermasalah',
        ]))->assertOk()
            ->assertSee('Lampu kamar berkedip')
            ->assertSee('Listrik bermasalah');
    }

    public function test_asset_kost_seeder_creates_multi_provider_catalog(): void
    {
        $this->seed(KostAssetSeeder::class);

        $this->assertDatabaseHas('kos', [
            'nama_kos' => 'Kos Putri Asri',
            'foto' => 'assets/kos/asri-kost.jpeg',
            'status' => Kos::STATUS_AKTIF,
        ]);

        $this->assertDatabaseHas('penyedia_kos', ['nama_lengkap' => 'Ibu Fitri Permata']);
        $this->assertDatabaseHas('kamars', ['nama_kamar' => 'Putri Permata F3', 'status' => Kamar::STATUS_TERSEDIA]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Pilihan kost dari beberapa penyedia')
            ->assertSee('Kos Putri Asri')
            ->assertSee('Kos Putri Permata');
    }

    public function test_database_seeder_can_be_repeated_without_duplicate_demo_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $counts = [
            'users' => User::count(),
            'penyedia_kos' => PenyediaKos::count(),
            'penyewas' => Penyewa::count(),
            'kos' => Kos::count(),
            'kamars' => Kamar::count(),
            'fasilitas' => Fasilitas::count(),
        ];

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($counts['users'], User::count());
        $this->assertSame($counts['penyedia_kos'], PenyediaKos::count());
        $this->assertSame($counts['penyewas'], Penyewa::count());
        $this->assertSame($counts['kos'], Kos::count());
        $this->assertSame($counts['kamars'], Kamar::count());
        $this->assertSame($counts['fasilitas'], Fasilitas::count());
        $this->assertDatabaseHas('users', ['email' => 'admin@kos.com', 'role' => User::ROLE_ADMIN]);
        $this->assertDatabaseHas('users', ['email' => 'penyedia@kos.com', 'role' => User::ROLE_PENYEDIA_KOS]);

        $activeKosFotos = Kos::where('status', Kos::STATUS_AKTIF)->pluck('foto')->filter()->values();
        $activeKamarFotos = Kamar::whereHas('kos', fn ($query) => $query->where('status', Kos::STATUS_AKTIF))
            ->pluck('foto')
            ->filter()
            ->values();

        $this->assertSame(Kos::where('status', Kos::STATUS_AKTIF)->count(), $activeKosFotos->count());
        $this->assertSame($activeKosFotos->count(), $activeKosFotos->unique()->count());
        $this->assertSame(Kamar::whereHas('kos', fn ($query) => $query->where('status', Kos::STATUS_AKTIF))->count(), $activeKamarFotos->count());
        $this->assertSame($activeKamarFotos->count(), $activeKamarFotos->unique()->count());
        $this->assertEmpty($activeKosFotos->intersect($activeKamarFotos)->all());
    }

    private function adminUser(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);
    }

    private function penyewaUser(): array
    {
        $user = User::create([
            'name' => 'Penyewa',
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENYEWA,
        ]);

        $penyewa = Penyewa::create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Penyewa',
            'no_hp' => '081234567890',
            'alamat' => 'Betung',
            'jenis_kelamin' => 'Perempuan',
        ]);

        return [$user, $penyewa];
    }

    private function kos(): Kos
    {
        $penyediaUser = User::create([
            'name' => 'Penyedia '.fake()->unique()->word(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_PENYEDIA_KOS,
        ]);

        $penyedia = PenyediaKos::create([
            'user_id' => $penyediaUser->id,
            'nama_lengkap' => $penyediaUser->name,
            'no_hp' => '081234567899',
            'alamat' => 'Betung',
        ]);

        return Kos::create([
            'penyedia_kos_id' => $penyedia->id,
            'nama_kos' => 'Kos Test '.fake()->unique()->word(),
            'alamat' => 'Jl. Betung Test',
            'kota' => 'Betung',
            'deskripsi' => 'Kos test.',
            'latitude' => -2.8836,
            'longitude' => 104.2169,
            'status' => Kos::STATUS_AKTIF,
            'is_promoted' => true,
        ]);
    }
}
