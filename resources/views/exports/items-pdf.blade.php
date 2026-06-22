<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Item Perpustakaan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { text-align: center; font-size: 18px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f3f3; font-weight: bold; }
        tr:nth-child(even) { background-color: #fafafa; }
        .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <h1>Daftar Item Koleksi Perpustakaan</h1>
    <p>Tanggal cetak: {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Tipe</th>
                <th>Kategori</th>
                <th>Tahun</th>
                <th>Stok</th>
                <th>Tersedia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->code }}</td>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->author }}</td>
                    <td>{{ ucfirst($item->type->value) }}</td>
                    <td>{{ $item->category?->name ?? '-' }}</td>
                    <td>{{ $item->year }}</td>
                    <td>{{ $item->total_stock }}</td>
                    <td>{{ $item->available_stock }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistem Manajemen Perpustakaan — Dicetak otomatis
    </div>
</body>
</html>
