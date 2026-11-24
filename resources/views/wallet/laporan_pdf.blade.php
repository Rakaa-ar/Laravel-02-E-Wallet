<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekening Koran - {{ $user->account_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #222; }
        .header { border-bottom: 2px solid #222; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 18px; }
        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { padding: 3px 0; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .summary td { border: 1px solid #ccc; padding: 8px; background: #f8f9fa; }
        table.mutasi { width: 100%; border-collapse: collapse; }
        table.mutasi th, table.mutasi td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        table.mutasi th { background: #212529; color: #fff; }
        .text-right { text-align: right !important; }
        .masuk { color: #198754; font-weight: bold; }
        .keluar { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN MUTASI REKENING E-WALLET</h2>
        <small>Dicetak pada: {{ now()->format('d/m/Y H:i') }} WIB</small>
    </div>

    <table class="info-table">
        <tr>
            <td width="20%"><strong>Nama Pemilik</strong></td>
            <td width="30%">: {{ $user->name }}</td>
            <td width="20%"><strong>Saldo Akhir</strong></td>
            <td width="30%">: <strong>Rp {{ number_format($dompet->saldo, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td><strong>ID Akun</strong></td>
            <td>: {{ $user->account_number }}</td>
            <td><strong>Periode</strong></td>
            <td>: {{ request('dari_tanggal') ?? 'Awal' }} s/d {{ request('sampai_tanggal') ?? 'Sekarang' }}</td>
        </tr>
    </table>

    <table class="summary">
        <tr>
            <td><strong>Total Dana Masuk:</strong> <span class="masuk">+ Rp {{ number_format($totalMasuk, 0, ',', '.') }}</span></td>
            <td><strong>Total Dana Keluar:</strong> <span class="keluar">- Rp {{ number_format($totalKeluar, 0, ',', '.') }}</span></td>
        </tr>
    </table>

    <table class="mutasi">
        <thead>
            <tr>
                <th width="18%">Tanggal</th>
                <th width="22%">Kode Transaksi</th>
                <th width="40%">Keterangan</th>
                <th width="20%" class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $item)
                <tr>
                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $item->kode_transaksi }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td class="text-right {{ $item->jenis }}">
                        {{ $item->jenis == 'masuk' ? '+' : '-' }} Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center;">Tidak ada data transaksi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>