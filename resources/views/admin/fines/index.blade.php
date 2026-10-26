@extends('layouts.admin')

@section('title', 'Keterlambatan & denda')

@section('breadcrumbs')
    <span class="current">Keterlambatan & denda</span>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/overdue-fines.css') }}">
@endpush

@section('content')
<div class="overdue-page">

    {{-- Judul Halaman --}}
    <div class="overdue-header">
        <h1 class="overdue-title">Keterlambatan & denda</h1>
        <p class="overdue-subtitle">Denda berlaku setelah masa tenggang 5 hari keterlambatan.</p>
    </div>

    {{-- Pesan Kontekstual --}}
    <div class="overdue-alert">
        <i data-lucide="info" class="overdue-alert-icon"></i>
        <div class="overdue-alert-body">
        <span class="overdue-alert-title">Tarif denda keterlambatan</span>
            <span class="overdue-alert-desc">
            5 hari keterlambatan pertama bebas denda. Mulai hari ke-6, denda sebesar {{ number_format(\App\Models\Fine::DAILY_RATE_ETHER, 0, ',', '.') }} Ether per hari. Total denda dihitung saat pengembalian dikonfirmasi.
            </span>
        </div>
    </div>

    {{-- Ringkasan Keterlambatan --}}
    <div class="overdue-summary-grid">
        {{-- Card 1: Aktif terlambat --}}
        <div class="overdue-summary-card">
            <div class="overdue-summary-top">
                <span class="overdue-summary-label">Aktif terlambat</span>
                <span class="overdue-summary-icon">
                    <i data-lucide="calendar-clock"></i>
                </span>
            </div>
            <div class="overdue-summary-value">{{ $activeOverdueCount }} transaksi</div>
            <div class="overdue-summary-desc">
                {{ $activeOverdueMembersCount }} anggota · {{ $activeOverdueUnitsCount }} unit belum kembali
            </div>
        </div>

        {{-- Card 2: Keterlambatan berjalan --}}
        <div class="overdue-summary-card">
            <div class="overdue-summary-top">
                <span class="overdue-summary-label">Keterlambatan berjalan</span>
                <span class="overdue-summary-icon">
                    <i data-lucide="clock"></i>
                </span>
            </div>
            <div class="overdue-summary-value">{{ $totalActiveOverdueDays }} hari</div>
            <div class="overdue-summary-desc">{{ $activeBreakdownText }}</div>
        </div>

        {{-- Card 3: Sudah kembali terlambat --}}
        <div class="overdue-summary-card">
            <div class="overdue-summary-top">
                <span class="overdue-summary-label">Sudah kembali terlambat</span>
                <span class="overdue-summary-icon">
                    <i data-lucide="rotate-ccw"></i>
                </span>
            </div>
            <div class="overdue-summary-value">{{ $returnedOverdueCount }} transaksi</div>
            <div class="overdue-summary-desc">{{ $totalReturnedOverdueDays }} hari tercatat dalam riwayat</div>
        </div>
    </div>

    {{-- Section 1: Pinjaman Aktif yang Terlambat --}}
    <div class="overdue-section">
        <h2 class="overdue-section-title">Pinjaman aktif yang terlambat</h2>

        <div class="overdue-table-card">
            {{-- Filter & Pencarian --}}
            <div class="overdue-filter-bar">
                <form method="GET" action="{{ route('admin.fines.index') }}" style="margin: 0;">
                    <div class="overdue-search-wrapper">
                        <i data-lucide="search"></i>
                        <input type="text"
                               name="search"
                               class="overdue-search-input"
                               placeholder="Cari anggota atau transaksi..."
                               value="{{ request('search') }}"
                               onkeydown="if(event.key === 'Enter'){ this.form.submit(); }">
                    </div>
                </form>
                <div class="overdue-filter-date">
                    Per {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
                </div>
            </div>

            {{-- Tabel Pinjaman Aktif Terlambat --}}
            <div style="overflow-x: auto;">
                <table class="overdue-data-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">TRANSAKSI / ANGGOTA</th>
                            <th style="width: 25%;">UNIT</th>
                            <th style="width: 15%;">BATAS KEMBALI</th>
                            <th style="width: 12%;">TERLAMBAT</th>
                            <th style="width: 13%;">NOMINAL DENDA</th>
                            <th style="width: 10%;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeOverdueLoans as $loan)
                            @php
                                $monster = $loan->details->first()?->monster;
                            @endphp
                            <tr>
                                <td>
                                    <div class="cell-tx-member">
                                        <span class="cell-tx-code">{{ $loan->loan_code }}</span>
                                        <span class="cell-member-name">{{ $loan->user->name ?? 'Anggota' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-unit">
                                        <span class="cell-unit-code">{{ $monster->monster_code ?? '-' }}</span>
                                        <span class="cell-unit-name">{{ $monster->name ?? 'Unit Perlengkapan' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="cell-date-text">
                                        {{ $loan->due_date ? $loan->due_date->locale('id')->translatedFormat('d M Y') : '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-overdue-warning">
                                        <span class="badge-overdue-dot"></span>
                                        <span>{{ $loan->calculated_overdue_days }} hari</span>
                                    </span>
                                </td>
                                <td>
                                    <span class="cell-fine-pending">
                                        {{ number_format($loan->calculated_fine_amount, 0, ',', '.') }} Ether
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.loans.return', $loan->id) }}" class="overdue-action-link">
                                        <span>Proses pengembalian</span>
                                        <span>&rarr;</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 32px; color: #6D7972;">
                                    Tidak ada pinjaman aktif yang berstatus terlambat saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Ringkasan Tabel --}}
            <div class="overdue-table-summary">
                @if($activeOverdueCount > 0)
                    Hanya transaksi aktif memiliki tindakan pengembalian. {{ $activeOverdueCount == 2 ? 'Keduanya berstatus Terlambat.' : $activeOverdueCount . ' transaksi berstatus Terlambat.' }}
                @else
                    Hanya transaksi aktif memiliki tindakan pengembalian. Tidak ada transaksi yang berstatus Terlambat.
                @endif
            </div>
        </div>
    </div>

    {{-- Section 2: Sudah Dikembalikan · Keterlambatan Tersimpan --}}
    <div class="overdue-section">
        <h2 class="overdue-section-title">Sudah dikembalikan · keterlambatan tersimpan</h2>

        <div class="overdue-table-card">
            <div style="overflow-x: auto;">
                <table class="overdue-data-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">TRANSAKSI / ANGGOTA</th>
                            <th style="width: 25%;">UNIT</th>
                            <th style="width: 14%;">BATAS KEMBALI</th>
                            <th style="width: 14%;">DIKEMBALIKAN</th>
                            <th style="width: 10%;">TERLAMBAT</th>
                            <th style="width: 12%;">NOMINAL / STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returnedOverdueLoans as $loan)
                            @php
                                $detail = $loan->details->first();
                                $monster = $detail?->monster;
                            @endphp
                            <tr>
                                <td>
                                    <div class="cell-tx-member">
                                        <span class="cell-tx-code">{{ $loan->loan_code }}</span>
                                        <span class="cell-member-name-plain">{{ $loan->user->name ?? 'Anggota' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-unit">
                                        <span class="cell-unit-code">{{ $monster->monster_code ?? '-' }}</span>
                                        <span class="cell-unit-name">{{ $monster->name ?? 'Unit Perlengkapan' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="cell-date-text">
                                        {{ $loan->due_date ? $loan->due_date->locale('id')->translatedFormat('d M Y') : '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="cell-date-text">
                                        {{ $detail?->returned_at ? $detail->returned_at->locale('id')->translatedFormat('d M Y') : '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="cell-date-text">{{ $loan->calculated_overdue_days }} hari</span>
                                </td>
                                <td>
                                    <div class="cell-fine-returned">
                                        <span class="cell-fine-returned-text">
                                            {{ number_format($loan->calculated_fine_amount, 0, ',', '.') }} Ether
                                        </span>
                                        <span class="badge-completed-status">
                                            <span class="badge-completed-dot"></span>
                                            <span>Selesai · {{ $loan->calculated_fine_amount > 0 ? (($detail?->fine?->status === 'paid') ? 'Lunas' : 'Belum dibayar') : 'Tidak dikenakan' }}</span>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 32px; color: #6D7972;">
                                    Belum ada catatan riwayat keterlambatan dari transaksi yang telah dikembalikan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Ringkasan Tabel Riwayat --}}
            <div class="overdue-table-summary">
                {{ $returnedOverdueCount }} transaksi selesai terlambat · Tidak dapat diproses kembali
            </div>
        </div>
    </div>

    {{-- Catatan Halaman (Footer) --}}
    <footer class="overdue-page-footer">
        <div class="overdue-footer-left">
            &copy; 2026 ArsenalTrack - Pusat Pelatihan Arunika
        </div>
        <div class="overdue-footer-right">
            Simulasi akademik • Data contoh
        </div>
    </footer>

</div>
@endsection
