@extends('layouts.admin')

@section('title', 'Proses Pengembalian - ' . $loan->loan_code)

@section('breadcrumbs')
    <a href="{{ route('admin.loans.index') }}">Peminjaman aktif</a>
    <span class="separator">/</span>
    <span class="current">Proses pengembalian</span>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/return_process.css') }}">
@endpush

@section('content')
<div class="return-process-wrapper">

    {{-- ── 1. Header Halaman ── --}}
    <div class="rp-header">
        <div class="rp-header-left">
            <h1 class="rp-title">Proses pengembalian</h1>
            <p class="rp-subtitle">Verifikasi serah terima fisik sebelum menyelesaikan transaksi.</p>
        </div>
        <div>
            <a href="{{ route('admin.loans.index') }}" class="rp-btn-back">
                <i data-lucide="arrow-left"></i> Kembali ke peminjaman
            </a>
        </div>
    </div>

    {{-- ── 2. Pesan Kontekstual (Warning Alert) ── --}}
    <div class="rp-alert-warning">
        <i data-lucide="info"></i>
        <div class="rp-alert-body">
            <div class="rp-alert-title">Transaksi masih aktif · belum dikembalikan</div>
            <p class="rp-alert-text">
                Anda sedang meninjau pengembalian <strong>{{ $loan->user->name ?? 'Anggota' }}</strong>. Tidak ada data yang berubah sebelum tombol konfirmasi dipilih.
            </p>
        </div>
    </div>

    {{-- ── 3. Formulir & Pratinjau Grid ── --}}
    <form method="POST" action="{{ route('admin.loans.process-return', $loan->id) }}" id="returnForm">
        @csrf

        <div class="rp-grid">

            {{-- Kolom Kiri: Panel Informasi & Form Serah Terima --}}
            <div class="rp-card">
                <h2 class="rp-card-header">Rincian peminjaman</h2>

                {{-- Baris Kode & Status Transaksi --}}
                <div class="rp-tx-bar">
                    <span class="rp-tx-code">{{ $loan->loan_code }}</span>
                    @if($isOverdue)
                        <span class="rp-status-tag overdue">
                            <span class="dot"></span> Terlambat
                        </span>
                    @else
                        <span class="rp-status-tag ontime">
                            <span class="dot"></span> Tepat Waktu
                        </span>
                    @endif
                </div>

                <hr class="rp-divider">

                {{-- Data Peminjam & Unit --}}
                <div class="rp-row-2col">
                    <div class="rp-info-block">
                        <span class="rp-info-label">Anggota</span>
                        <span class="rp-info-val">{{ $loan->user->name ?? '-' }}</span>
                        <span class="rp-info-sub">{{ $loan->user->email ?? '-' }}</span>
                    </div>
                    <div class="rp-info-block">
                        <span class="rp-info-label">Unit</span>
                        <span class="rp-info-val">{{ $monster->name ?? 'Entitas Monster' }}</span>
                        <span class="rp-info-sub mono">{{ $monster->monster_code ?? '-' }}</span>
                    </div>
                </div>

                {{-- Tanggal Asli Peminjaman --}}
                <div class="rp-row-2col">
                    <div class="rp-info-block">
                        <span class="rp-info-label">Jadwal mulai</span>
                        <span class="rp-info-val">
                            {{ $loan->scheduled_start ? $loan->scheduled_start->locale('id')->translatedFormat('d F Y H:i') . ' WIB' : '-' }}
                        </span>
                    </div>
                    <div class="rp-info-block">
                        <span class="rp-info-label">Jadwal selesai</span>
                        <span class="rp-info-val">
                            {{ $loan->scheduled_end ? $loan->scheduled_end->locale('id')->translatedFormat('d F Y H:i') . ' WIB' : '-' }}
                        </span>
                    </div>
                </div>

                {{-- Tanggal Pengembalian Otomatis Sistem --}}
                <div class="rp-field-group">
                    <label class="rp-field-label">Tanggal pengembalian · otomatis dari sistem</label>
                    <div class="rp-input-locked">
                        <span>{{ now()->translatedFormat('d F Y') }}</span>
                        <i data-lucide="lock"></i>
                    </div>
                    <span class="rp-field-helper">Tanggal hari ini digunakan saat konfirmasi. Bukan tanggal yang dipilih manual.</span>
                </div>

                {{-- Perhitungan Keterlambatan --}}
                <div class="rp-calc-box">
                    <div class="rp-calc-grid">
                        <div class="rp-calc-item">
                            <span class="calc-lbl">Keterlambatan</span>
                            <span class="calc-val">
                                {{ $overdueDays > 0 ? $overdueDays . ' hari' : '0 hari (Tepat Waktu)' }}
                            </span>
                        </div>
                        <div class="rp-calc-item">
                            <span class="calc-lbl">Nominal denda</span>
                            <span class="calc-val">Belum ditetapkan</span>
                        </div>
                    </div>
                    <div class="rp-calc-note">
                        @if($overdueDays > 0)
                            Jadwal selesai: {{ $loan->scheduled_end ? $loan->scheduled_end->locale('id')->translatedFormat('d F Y H:i') . ' WIB' : '-' }}. Keterlambatan {{ $overdueDays }} hari dihitung berdasarkan tanggal jatuh tempo. Pengembalian pada tanggal jatuh tempo dihitung 0 hari.
                        @else
                            Unit dikembalikan sebelum atau tepat pada batas tanggal jatuh tempo (0 hari terlambat).
                        @endif
                    </div>
                </div>

                <hr class="rp-divider">

                {{-- Konfirmasi Serah Terima Fisik --}}
                <label class="rp-confirm-box" for="confirm_physical">
                    <input type="checkbox" name="confirm_physical" id="confirm_physical" value="1" required {{ old('confirm_physical') ? 'checked' : '' }}>
                    <div class="rp-confirm-text">
                        <span class="rp-confirm-title">Saya telah menerima unit ini secara fisik.</span>
                        <span class="rp-confirm-subtitle">
                            Kode {{ $monster->monster_code ?? '-' }} telah dicocokkan dengan unit yang diserahkan {{ $loan->user->name ?? 'anggota' }}.
                        </span>
                    </div>
                </label>

                {{-- Tombol Aksi --}}
                <div class="rp-actions">
                    <a href="{{ route('admin.loans.index') }}" class="rp-btn-cancel">Batal</a>
                    <button type="submit" class="rp-btn-submit">
                        <i data-lucide="check"></i> Konfirmasi pengembalian
                    </button>
                </div>
            </div>

            {{-- Kolom Kanan: Dampak Konfirmasi & Info --}}
            <div class="rp-side-col">
                <div class="rp-side-card">
                    <h2 class="rp-side-card-title">Setelah dikonfirmasi</h2>
                    <span class="rp-side-badge">PRATINJAU HASIL · BELUM DISIMPAN</span>

                    <div class="rp-side-item">
                        <span class="item-lbl">Transaksi</span>
                        <span class="item-val">Aktif &rarr; Selesai</span>
                    </div>

                    <hr class="rp-divider">

                    <div class="rp-side-item">
                        <span class="item-lbl">Ketersediaan unit</span>
                        <span class="item-val">Dipinjam &rarr; Tersedia</span>
                    </div>

                    <hr class="rp-divider">

                    <div class="rp-side-item">
                        <span class="item-lbl">Hari terlambat tersimpan</span>
                        <span class="item-val">{{ $overdueDays }} hari</span>
                    </div>

                    <p class="rp-side-explain">
                        Tanggal kembali dicatat, unit dilepas untuk dipinjam lagi, dan transaksi dipindahkan ke riwayat. Seluruh relasi tetap tersimpan.
                    </p>
                </div>

                <div class="rp-side-notice">
                    <i data-lucide="check-circle-2"></i>
                    <div class="rp-notice-body">
                        <div class="rp-notice-title">Satu kali pencatatan</div>
                        <p class="rp-notice-text">
                            Pengembalian hanya berlaku pada transaksi aktif. Setelah selesai, tindakan ini tidak ditampilkan lagi pada transaksi tersebut.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection
