<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 18px; margin: 0; }
        .meta { margin-top: 6px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .total { margin-top: 14px; font-weight: bold; }
        .summary { width: 100%; margin-top: 14px; border-collapse: collapse; }
        .summary td { width: 25%; background: #f9fafb; }
        .summary .label { color: #4b5563; font-size: 9px; text-transform: uppercase; }
        .summary .value { margin-top: 3px; font-size: 13px; font-weight: bold; }
        .sign { margin-top: 45px; width: 220px; float: right; text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="meta">{{ config('app.name') }} | Tanggal cetak: {{ now()->format('d/m/Y H:i') }}</p>
    <p class="meta">Filter: {{ collect($filters)->filter()->map(fn ($v, $k) => "$k=$v")->join(', ') ?: 'Semua data' }}</p>

    @if (! empty($summary))
        <table class="summary">
            <tr>
                @foreach ($summary as $item)
                    <td>
                        <div class="label">{{ $item['label'] }}</div>
                        <div class="value">{{ $item['value'] }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    @if ($type === 'penyewa')
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Penyewa</th>
                    <th>Email</th>
                    <th>No HP</th>
                    <th>Jenis Kelamin</th>
                    <th>Alamat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row->nama_lengkap }}</td>
                        <td>{{ $row->user->email }}</td>
                        <td>{{ $row->no_hp }}</td>
                        <td>{{ $row->jenis_kelamin }}</td>
                        <td>{{ $row->alamat }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Tidak ada data penyewa.</td></tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Data Utama</th>
                    <th>Keterangan</th>
                    <th>Status/Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if ($type === 'penyewaan')
                                {{ $row->penyewa->nama_lengkap }}<br>{{ $row->kamar->nama_kamar }}
                            @elseif ($type === 'kamar')
                                {{ $row->nama_kamar }}<br>{{ $row->tipe_kamar }}
                            @elseif ($type === 'penghuni')
                                {{ $row->penyewa->nama_lengkap }}<br>{{ $row->kamar->nama_kamar }}
                            @elseif ($type === 'pemesanan')
                                {{ $row->penyewa->nama_lengkap }}<br>{{ $row->kamar->nama_kamar }}
                            @elseif ($type === 'pembayaran-awal')
                                {{ $row->pemesanan->penyewa->nama_lengkap }}<br>{{ $row->pemesanan->kamar->nama_kamar }}
                            @elseif (in_array($type, ['tagihan-bulanan','terlambat'], true))
                                {{ $row->penghuni->penyewa->nama_lengkap }}<br>{{ $row->penghuni->kamar->nama_kamar }}
                            @else
                                {{ $row->tagihanBulanan->penghuni->penyewa->nama_lengkap }}<br>{{ $row->tagihanBulanan->penghuni->kamar->nama_kamar }}
                            @endif
                        </td>
                        <td>
                            @if ($type === 'penyewaan')
                                Masuk {{ $row->tanggal_masuk->format('d/m/Y') }}<br>Jatuh tempo {{ $row->tanggal_jatuh_tempo->format('d/m/Y') }}
                            @elseif ($type === 'kamar')
                                {{ $row->fasilitas->pluck('nama_fasilitas')->join(', ') }}
                            @elseif ($type === 'penghuni')
                                Masuk {{ $row->tanggal_masuk->format('d/m/Y') }}
                            @elseif ($type === 'pemesanan')
                                Masuk {{ $row->tanggal_masuk->format('d/m/Y') }}
                            @elseif ($type === 'pembayaran-awal')
                                Bayar {{ optional($row->tanggal_bayar)->format('d/m/Y') }}
                            @elseif (in_array($type, ['tagihan-bulanan','terlambat'], true))
                                Periode {{ $row->periode }}<br>Jatuh tempo {{ $row->tanggal_jatuh_tempo->format('d/m/Y') }}
                            @else
                                Periode {{ $row->tagihanBulanan->periode }}<br>Bayar {{ $row->tanggal_bayar->format('d/m/Y') }}
                            @endif
                        </td>
                        <td>
                            @if ($type === 'penyewaan')
                                {{ ucfirst($row->status_penghuni) }}<br>{{ $row->harga_format }}
                            @elseif ($type === 'kamar')
                                {{ ucfirst($row->status) }}<br>{{ $row->harga_format }}
                            @elseif ($type === 'penghuni')
                                {{ ucfirst($row->status_penghuni) }}<br>{{ $row->harga_format }}
                            @elseif ($type === 'pemesanan')
                                {{ ucfirst(str_replace('_', ' ', $row->status_pemesanan)) }}
                            @elseif ($type === 'pembayaran-awal')
                                {{ ucfirst(str_replace('_', ' ', $row->status_pembayaran)) }}<br>{{ $row->jumlah_format }}
                            @elseif (in_array($type, ['tagihan-bulanan','terlambat'], true))
                                {{ ucfirst(str_replace('_', ' ', $row->status_tagihan)) }}<br>{{ $row->jumlah_format }}
                            @elseif (in_array($type, ['pembayaran-bulanan','pendapatan'], true))
                                {{ ucfirst(str_replace('_', ' ', $row->status_pembayaran)) }}<br>{{ $row->jumlah_format }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <p class="total">Total Data: {{ $rows->count() }}</p>
    @if ($totalPendapatan > 0)
        <p class="total">Total Pendapatan: Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
    @endif

    <div class="sign">
        <p>Betung, {{ now()->format('d/m/Y') }}</p>
        <p>Admin/Pemilik Kos</p>
        <br><br><br>
        <p>________________________</p>
    </div>
</body>
</html>
