<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Riwayat Peminjaman - ArsenalTrack</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/print-history.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="print-preview-body">

    {{-- ── Kontrol Pratinjau Cetak (Top Control Bar) ── --}}
    <header class="print-control-bar">
        <div class="print-guidance">
            <span class="print-context-title">Pratinjau cetak</span>
            <span class="print-context-hint">Gunakan ukuran kertas A4, orientasi lanskap, dan aktifkan grafis latar belakang pada pengaturan cetak peramban.</span>
        </div>
        <div class="print-actions">
            <a href="{{ $backUrl }}" class="btn-print-back">Kembali</a>
            <button type="button" onclick="window.print()" class="btn-print-action">
                <i data-lucide="printer"></i>
                <span>Cetak dokumen</span>
            </button>
        </div>
    </header>

    {{-- ── Dokumen Riwayat Institusi ── --}}
    <main class="print-doc-container">

        {{-- Kop Dokumen --}}
        <section class="print-kop">
            <div class="print-institution">
                <div class="print-institution-logo">
                    <i data-lucide="layers"></i>
                </div>
                <div class="print-institution-identity">
                    <span class="print-institution-name">ArsenalTrack</span>
                    <span class="print-institution-system">Sistem Inventarisasi &amp; Peminjaman Taktis</span>
                </div>
            </div>
            <div class="print-doc-reference">
                {{ $referenceCode }}
            </div>
        </section>

        {{-- Garis Kop --}}
        <div class="print-kop-line"></div>

        {{-- Judul dan Pencetak --}}
        <section class="print-header-grid">
            <div class="print-report-title-block">
                <h1 class="print-report-title">Laporan Riwayat Peminjaman Unit</h1>
                <p class="print-report-desc">Rekapitulasi sirkulasi peminjaman dan pengembalian unit latihan operasional.</p>
                <p class="print-report-status">Status data: Seluruh catatan transaksi terverifikasi dan tersimpan dalam pangkalan data.</p>
            </div>
            <div class="print-metadata-block">
                <span class="print-meta-label">Dicetak oleh</span>
                <span class="print-meta-user">{{ $printedBy }} ({{ $printedRole }})</span>
                <span class="print-meta-time">Waktu cetak: {{ $printedAt->locale('id')->translatedFormat('d F Y, H:i') }} WIB</span>
                <span class="print-meta-scope">Cakupan: {{ $scopeTitle }}</span>
            </div>
        </section>

        {{-- Tabel Data Riwayat --}}
        <section class="print-table-wrapper">
            <table class="print-data-table">
                <thead>
                    <tr>
                        <th style="width: 24%;">KODE TRANSAKSI / UNIT</th>
                        <th style="width: 14%;">PEMINJAM</th>
                        <th style="width: 12%;">JADWAL MULAI</th>
                        <th style="width: 12%;">JADWAL SELESAI</th>
                        <th style="width: 12%;">TGL KEMBALI</th>
                        <th style="width: 10%;">HARI TERLAMBAT</th>
                        <th style="width: 16%;">STATUS / CATATAN</th>
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
                            <td>
                                <div class="cell-print-tx-unit">
                                    <span class="cell-print-tx-code">{{ $loan->loan_code }}</span>
                                    <span class="cell-print-unit-name">{{ $monster->name ?? 'Unit Perlengkapan' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="cell-print-text">{{ $loan->user->name ?? 'Anggota' }}</span>
                            </td>
                            <td>
                                <span class="cell-print-text">
                                    {{ $loan->scheduled_start ? $loan->scheduled_start->locale('id')->translatedFormat('d M Y H:i') . ' WIB' : '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="cell-print-text">
                                    {{ $loan->scheduled_end ? $loan->scheduled_end->locale('id')->translatedFormat('d M Y H:i') . ' WIB' : '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="cell-print-text">
                                    {{ $returnDate ? $returnDate->locale('id')->translatedFormat('d M Y') : 'Belum kembali' }}
                                </span>
                            </td>
                            <td>
                                <span class="cell-print-text">{{ $loan->calculated_overdue_days ?? 0 }} hari</span>
                            </td>
                            <td>
                                <span class="cell-print-text">
                                    @if($loan->status === 'returned')
                                        @if(($loan->calculated_overdue_days ?? 0) > 0)
                                            Selesai terlambat ({{ $loan->calculated_overdue_days }}h)
                                        @else
                                            Selesai tepat waktu
                                        @endif
                                    @elseif(in_array($loan->status, ['active', 'borrowed', 'pending']))
                                        @if(($loan->calculated_overdue_days ?? 0) > 0)
                                            Aktif terlambat ({{ $loan->calculated_overdue_days }}h)
                                        @else
                                            Sedang dipinjam
                                        @endif
                                    @else
                                        {{ ucfirst($loan->status) }}
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 28px; color: #6D7972;">
                                Tidak ada data riwayat peminjaman untuk dicetak.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Ringkasan Tabel --}}
            <div class="print-table-summary">
                Menampilkan {{ $loans->count() }} rekaman sirkulasi peminjaman resmi.
            </div>
        </section>

        {{-- Total Laporan (4 Stat Cards) --}}
        <section class="print-totals-row">
            <div class="print-total-card">
                <span class="print-total-label">Total transaksi</span>
                <span class="print-total-value">{{ $totalLoans }} transaksi</span>
            </div>
            <div class="print-total-card">
                <span class="print-total-label">Selesai tepat waktu</span>
                <span class="print-total-value">{{ $onTimeCount }} transaksi</span>
            </div>
            <div class="print-total-card">
                <span class="print-total-label">Selesai terlambat</span>
                <span class="print-total-value">{{ $lateCount }} transaksi</span>
            </div>
            <div class="print-total-card">
                <span class="print-total-label">Sedang dipinjam (aktif)</span>
                <span class="print-total-value">{{ $activeCount }} unit</span>
            </div>
        </section>

        {{-- Pemisah --}}
        <div class="print-section-divider"></div>

        {{-- Catatan Dokumen --}}
        <footer class="print-doc-notes">
            <div class="print-notes-left">
                <span class="print-notes-text">Dokumen ini diterbitkan secara otomatis oleh sistem ArsenalTrack sebagai bukti rekapitulasi sirkulasi resmi.</span>
                <span class="print-notes-text">Arsip digital sah tanpa memerlukan tanda tangan basah untuk keperluan administrasi internal.</span>
            </div>
            <div class="print-page-num">
                Halaman 1 dari 1
            </div>
        </footer>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();
        });
    </script>
</body>
</html>
