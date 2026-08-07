# Black Box Testing Sistem Informasi Manajemen Pengelolaan Kos

| No | Fitur yang diuji | Skenario pengujian | Input | Hasil yang diharapkan | Hasil pengujian | Status |
|---:|---|---|---|---|---|---|
| 1 | Registrasi penyewa | User mengisi form registrasi lengkap | Nama, email unik, password, no HP, alamat, jenis kelamin | Akun penyewa dibuat dan masuk dashboard penyewa | Sesuai | Berhasil |
| 2 | Login admin | Admin login dengan kredensial valid | admin@kos.com / password | Admin diarahkan ke dashboard admin | Sesuai | Berhasil |
| 3 | Login penyewa | Penyewa login dengan kredensial valid | Email penyewa / password | Penyewa diarahkan ke dashboard penyewa | Sesuai | Berhasil |
| 4 | Tambah kamar | Admin menambah data kamar | Nama, tipe, harga, deskripsi, status, fasilitas | Data kamar tersimpan | Sesuai | Berhasil |
| 5 | Edit kamar | Admin mengubah data kamar | Perubahan harga/status/fasilitas | Data kamar diperbarui | Sesuai | Berhasil |
| 6 | Hapus kamar | Admin menghapus kamar tanpa transaksi | Tombol hapus | Kamar terhapus, atau ditolak jika punya riwayat transaksi | Sesuai | Berhasil |
| 7 | Tambah fasilitas | Admin menambah fasilitas baru | Nama fasilitas | Fasilitas tersimpan | Sesuai | Berhasil |
| 8 | Pemesanan kamar | Penyewa memesan kamar tersedia | Tanggal masuk, catatan | Status pemesanan menjadi menunggu konfirmasi | Sesuai | Berhasil |
| 9 | Konfirmasi pemesanan | Admin menerima pemesanan | Tombol terima | Status pemesanan diterima dan kamar dipesan | Sesuai | Berhasil |
| 10 | Upload pembayaran awal | Penyewa upload bukti DP | Jumlah, tanggal, file valid | Status pembayaran menunggu konfirmasi | Sesuai | Berhasil |
| 11 | Validasi pembayaran awal | Admin menyetujui DP | Tombol setujui | DP lunas, pemesanan selesai, kamar terisi | Sesuai | Berhasil |
| 12 | Perubahan penghuni aktif | Sistem membuat penghuni setelah DP lunas | Validasi DP lunas | Data penghuni aktif dibuat | Sesuai | Berhasil |
| 13 | Generate tagihan bulanan | Admin generate tagihan | Tombol generate | Tagihan baru dibuat untuk penghuni aktif | Sesuai | Berhasil |
| 14 | Upload pembayaran bulanan | Penghuni upload bukti pembayaran | Jumlah, tanggal, file valid | Tagihan menunggu konfirmasi | Sesuai | Berhasil |
| 15 | Validasi pembayaran bulanan | Admin menyetujui pembayaran | Tombol setujui | Pembayaran dan tagihan menjadi lunas | Sesuai | Berhasil |
| 16 | Keluhan penghuni | Penghuni membuat keluhan | Kategori, judul, deskripsi, foto opsional | Keluhan tersimpan dengan status dikirim | Sesuai | Berhasil |
| 17 | Ubah status keluhan | Admin memproses keluhan | Status dan catatan admin | Status keluhan berubah | Sesuai | Berhasil |
| 18 | Cetak laporan PDF | Admin mencetak laporan | Jenis laporan dan filter | File PDF terunduh | Sesuai | Berhasil |
| 19 | Logout | User menekan logout | Tombol logout | Session berakhir dan kembali ke halaman publik | Sesuai | Berhasil |
