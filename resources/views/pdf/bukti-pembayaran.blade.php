<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bukti Pembayaran</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        td { padding: 8px; border: 1px solid #d1d5db; }
        .label { width: 35%; font-weight: bold; background: #f3f4f6; }
        .sign { margin-top: 45px; width: 220px; float: right; text-align: center; }
    </style>
</head>
<body>
    <h1>Bukti Pembayaran Lunas</h1>
    <p>Sistem Informasi Manajemen Pengelolaan {{ config('app.name') }}</p>
    <table>
        <tr><td class="label">Nama Penghuni</td><td>{{ $pembayaranBulanan->tagihanBulanan->penghuni->penyewa->nama_lengkap }}</td></tr>
        <tr><td class="label">Nomor Kamar</td><td>{{ $pembayaranBulanan->tagihanBulanan->penghuni->kamar->nama_kamar }}</td></tr>
        <tr><td class="label">Bulan/Tahun Tagihan</td><td>{{ $pembayaranBulanan->tagihanBulanan->periode }}</td></tr>
        <tr><td class="label">Jumlah Bayar</td><td>{{ $pembayaranBulanan->jumlah_format }}</td></tr>
        <tr><td class="label">Tanggal Bayar</td><td>{{ $pembayaranBulanan->tanggal_bayar->format('d/m/Y') }}</td></tr>
        <tr><td class="label">Status</td><td>Lunas</td></tr>
        <tr><td class="label">Tanggal Cetak</td><td>{{ now()->format('d/m/Y H:i') }}</td></tr>
    </table>
    <div class="sign">
        <p>Betung, {{ now()->format('d/m/Y') }}</p>
        <p>Admin/Pemilik Kos</p>
        <br><br><br>
        <p>________________________</p>
    </div>
</body>
</html>
