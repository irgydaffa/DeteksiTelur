<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Deteksi Telur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .info {
            margin-bottom: 15px;
        }
        .info-item {
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th,
        td {
            border: 1px solid #ddd;
            padding: 5px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .summary {
            margin-top: 15px;
        }

        .summary-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Laporan Deteksi Telur</div>
        <div class="subtitle">Sistem Deteksi Kualitas Telur Otomatis</div>
    </div>
    <div class="info">
        <div class="info-item">
            <strong>Periode:</strong> {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
        </div>
        <div class="info-item">
            <strong>Dicetak pada:</strong> {{ now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
        </div>
        @if($kategori != 'semua')
        <div class="info-item">
            <strong>Filter Kategori:</strong> {{ $kategori }}
        </div>
        @endif
    </div>
    <div class="summary">
        <div class="summary-title">Ringkasan Statistik</div>
        <table>
            <tr>
                <th>Total Deteksi</th>
                <th>Telur MUTU 1</th>
                <th>Telur MUTU 2</th>
                <th>Telur MUTU 3</th>
                <th>Total Telur</th>
            </tr>
            <tr>
                <td>{{ $detections->count() }}</td>
                <td>
                    @if($kategori == 'semua' || $kategori == 'MUTU 1')
                        {{ $totalMutu1 }}
                    @else
                        0
                    @endif
                </td>
                <td>
                    @if($kategori == 'semua' || $kategori == 'MUTU 2')
                        {{ $totalMutu2 }}
                    @else
                        0
                    @endif
                </td>
                <td>
                    @if($kategori == 'semua' || $kategori == 'MUTU 3')
                        {{ $totalMutu3 }}
                    @else
                        0
                    @endif
                </td>
                <td>
                    @if($kategori == 'semua')
                        {{ $totalMutu1 + $totalMutu2 + $totalMutu3 }}
                    @elseif($kategori == 'MUTU 1')
                        {{ $totalMutu1 }}
                    @elseif($kategori == 'MUTU 2')
                        {{ $totalMutu2 }}
                    @elseif($kategori == 'MUTU 3')
                        {{ $totalMutu3 }}
                    @endif
                </td>
            </tr>
        </table>
    </div>
    <div class="detail">
        <div class="summary-title">Detail Deteksi</div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Nama File</th>
                    <th>Pengguna</th>
                    <th>MUTU 1</th>
                    <th>MUTU 2</th>
                    <th>MUTU 3</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detections as $detection)
                    <tr>
                        <td>{{ $detection->id }}</td>
                        <td>{{ $detection->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                        <td>{{ $detection->nama_file }}</td>
                        <td>{{ $detection->user->nama ?? '-' }}</td>
                        <td>
                            @if($kategori == 'semua' || $kategori == 'MUTU 1')
                                {{ $detection->jumlah_mutu1 }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if($kategori == 'semua' || $kategori == 'MUTU 2')
                                {{ $detection->jumlah_mutu2 }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if($kategori == 'semua' || $kategori == 'MUTU 3')
                                {{ $detection->jumlah_mutu3 }}
                            @else
                                0
                            @endif
                        </td>
                        <td>
                            @if($kategori == 'semua')
                                {{ $detection->jumlah_mutu1 + $detection->jumlah_mutu2 + $detection->jumlah_mutu3 }}
                            @elseif($kategori == 'MUTU 1')
                                {{ $detection->jumlah_mutu1 }}
                            @elseif($kategori == 'MUTU 2')
                                {{ $detection->jumlah_mutu2 }}
                            @elseif($kategori == 'MUTU 3')
                                {{ $detection->jumlah_mutu3 }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center;">Tidak ada data deteksi tersedia</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right;"><strong>Total:</strong></td>
                    <td><strong>
                        @if($kategori == 'semua' || $kategori == 'MUTU 1')
                            {{ $totalMutu1 }}
                        @else
                            0
                        @endif
                    </strong></td>
                    <td><strong>
                        @if($kategori == 'semua' || $kategori == 'MUTU 2')
                            {{ $totalMutu2 }}
                        @else
                            0
                        @endif
                    </strong></td>
                    <td><strong>
                        @if($kategori == 'semua' || $kategori == 'MUTU 3')
                            {{ $totalMutu3 }}
                        @else
                            0
                        @endif
                    </strong></td>
                    <td><strong>
                        @if($kategori == 'semua')
                            {{ $totalMutu1 + $totalMutu2 + $totalMutu3 }}
                        @elseif($kategori == 'MUTU 1')
                            {{ $totalMutu1 }}
                        @elseif($kategori == 'MUTU 2')
                            {{ $totalMutu2 }}
                        @elseif($kategori == 'MUTU 3')
                            {{ $totalMutu3 }}
                        @endif
                    </strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="footer">
        <p>Ditandatangani oleh: _______________________</p>
    </div>
</body>
</html>