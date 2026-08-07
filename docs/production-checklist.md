# Production Checklist

Checklist ini dipakai saat aplikasi dipindahkan dari Laragon/local ke hosting/VPS produksi.

## Environment

- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Set `APP_URL` sesuai domain produksi, misalnya `https://kos2putribetung.com`.
- Gunakan database MySQL produksi terpisah dari database lokal.
- Jangan commit atau membagikan file `.env` produksi.

## Server

- Arahkan document root web server ke folder `public`.
- Aktifkan HTTPS/SSL.
- Pastikan PHP minimal 8.3 dan ekstensi umum Laravel aktif.
- Jalankan `composer install --no-dev --optimize-autoloader`.
- Jalankan `npm run build` sebelum deploy atau upload folder `public/build`.

## Laravel

- Jalankan `php artisan key:generate` hanya jika `APP_KEY` belum ada.
- Jalankan `php artisan migrate --force`.
- Jalankan `php artisan storage:link`.
- Jalankan `php artisan config:cache`, `php artisan route:cache`, dan `php artisan view:cache`.

## Data dan Keamanan

- Ganti password admin default setelah deploy.
- Batasi permission folder agar hanya `storage` dan `bootstrap/cache` yang writable.
- Buat jadwal backup database dan folder `storage/app/public`.
- Validasi upload file tetap memakai batas maksimal 2 MB sesuai aplikasi.
- Gunakan akun database dengan hak akses secukupnya, bukan root.

## Operasional

- Cek alur utama setelah deploy: login admin, login penyewa, pemesanan, upload bukti, validasi pembayaran, generate laporan PDF.
- Monitor log di `storage/logs/laravel.log`.
- Aktifkan maintenance mode saat migrasi besar dengan `php artisan down`, lalu `php artisan up` setelah selesai.
