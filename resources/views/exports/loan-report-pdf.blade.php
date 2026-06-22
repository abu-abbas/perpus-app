<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Peminjaman</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h1 { text-align: center; font-size: 16px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 5px 7px; text-align: left; }
        th { background-color: #e8e8e8; font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #666; }
        .summary { margin-bottom: 15px; }
        .summary p { margin: 3px 0; }
    </style>
</head>
<body>
    <h1>Laporan Peminjaman Perpustakaan</h1>
    <p>Tanggal cetak: {{ now()->format('d/m/Y H:i') }}</p>

    <div class="summary">
        <p><strong>Total Peminjaman:</strong> {{ count($loans) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Anggota</th>
                <th>Item</th>
                <th>Tgl Pinjam</th>
                <th>Tgl Jatuh Tempo</th>
                <th>Tgl Kembali</th>
                <th>Status</th>
                <th>Denda</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans as $index => $loan)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $loan->member?->full_name }}</td>
                    <td>{{ $loan->item?->title }}</td>
                    <td>{{ $loan->loan_date?->format('d/m/Y') }}</td>
                    <td>{{ $loan->due_date?->format('d/m/Y') }}</td>
                    <td>{{ $loan->return_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ ucfirst($loan->status->value) }}</td>
                    <td>{{ $loan->fine ? 'Rp ' . number_format($loan->fine->amount, 0, ',', '.') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistem Manajemen Perpustakaan — Laporan Peminjaman
    </div>
</body>
</html>
