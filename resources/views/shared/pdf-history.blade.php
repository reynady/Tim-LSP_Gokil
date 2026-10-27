<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat Peminjaman</title>
    <style>
        @page {
            margin: 28px 32px;
        }

        body {
            color: #1c3029;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        .header {
            border-bottom: 2px solid #187653;
            margin-bottom: 18px;
            padding-bottom: 12px;
        }

        .brand {
            color: #187653;
            font-size: 18px;
            font-weight: bold;
        }

        .subtitle,
        .muted {
            color: #6d7972;
        }

        .reference {
            color: #6d7972;
            float: right;
            font-size: 9px;
        }

        .metadata {
            margin: 10px 0 16px;
            width: 100%;
        }

        .metadata td {
            padding: 2px 0;
            vertical-align: top;
        }

        h1 {
            font-size: 15px;
            margin: 0 0 5px;
        }

        table.report {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        .report th,
        .report td {
            border: 1px solid #cbd5cf;
            padding: 6px 5px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .report th {
            background: #eaf2ed;
            color: #1c3029;
            font-size: 8px;
            text-align: left;
        }

        .report tr {
            page-break-inside: avoid;
        }

        .summary {
            border-collapse: collapse;
            margin-top: 16px;
            width: 100%;
        }

        .summary td {
            background: #f3f7f4;
            border: 1px solid #cbd5cf;
            padding: 8px;
            text-align: center;
            width: 25%;
        }

        .summary strong {
            display: block;
            font-size: 12px;
            margin-top: 4px;
        }

        .footer {
            border-top: 1px solid #cbd5cf;
            color: #6d7972;
            margin-top: 18px;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <span class="reference">{{ $referenceCode }}</span>
        <div class="brand">ArsenalTrack</div>
        <div class="subtitle">Sistem Inventarisasi &amp; Peminjaman Taktis</div>
    </div>

    <h1>Laporan Riwayat Peminjaman Unit</h1>
    <div class="muted">Rekapitulasi sirkulasi peminjaman dan pengembalian unit.</div>

    <table class="metadata">
        <tr>
            <td><strong>Cakupan:</strong> {{ $scopeTitle }}</td>
            <td><strong>Dicetak oleh:</strong> {{ $printedBy }} ({{ $printedRole }})</td>
            <td><strong>Waktu cetak:</strong> {{ $printedAt->locale('id')->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th style="width: 20%;">KODE TRANSAKSI / UNIT</th>
                <th style="width: 13%;">PEMINJAM</th>
                <th style="width: 13%;">JADWAL MULAI</th>
                <th style="width: 13%;">JADWAL SELESAI</th>
                <th style="width: 12%;">TGL KEMBALI</th>
                <th style="width: 10%;">TERLAMBAT</th>
                <th style="width: 19%;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loans as $loan)
                @php
                    $detail = $loan->details->first();
                    $monster = $detail?->monster;
                    $returnDate = $detail?->returned_at
                        ? \Carbon\Carbon::parse($detail->returned_at)
                        : ($loan->status === 'returned' && $loan->updated_at ? \Carbon\Carbon::parse($loan->updated_at) : null);
                @endphp
                <tr>
                    <td>{{ $loan->loan_code }}<br>{{ $monster->name ?? 'Unit Perlengkapan' }}</td>
                    <td>{{ $loan->user->name ?? 'Anggota' }}</td>
                    <td>{{ $loan->scheduled_start->locale('id')->translatedFormat('d M Y H:i') }} WIB</td>
                    <td>{{ $loan->scheduled_end->locale('id')->translatedFormat('d M Y H:i') }} WIB</td>
                    <td>{{ $returnDate ? $returnDate->locale('id')->translatedFormat('d M Y') : 'Belum kembali' }}</td>
                    <td>{{ $loan->calculated_overdue_days ?? 0 }} hari</td>
                    <td>
                        @if($loan->status === 'returned')
                            {{ ($loan->calculated_overdue_days ?? 0) > 0 ? 'Selesai terlambat' : 'Selesai tepat waktu' }}
                        @elseif(in_array($loan->status, ['active', 'borrowed', 'pending']))
                            {{ ($loan->calculated_overdue_days ?? 0) > 0 ? 'Aktif terlambat' : 'Sedang dipinjam' }}
                        @else
                            {{ ucfirst($loan->status) }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data riwayat peminjaman.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td>Total transaksi<strong>{{ $totalLoans }}</strong></td>
            <td>Selesai tepat waktu<strong>{{ $onTimeCount }}</strong></td>
            <td>Selesai terlambat<strong>{{ $lateCount }}</strong></td>
            <td>Sedang dipinjam<strong>{{ $activeCount }}</strong></td>
        </tr>
    </table>

    <div class="footer">Dokumen ini diterbitkan otomatis oleh sistem ArsenalTrack sebagai rekapitulasi sirkulasi resmi.</div>
</body>
</html>
