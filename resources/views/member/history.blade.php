<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - ArsenalTrack</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/history.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
@php
    $user = auth()->user();
    $userName = $user->name ?? 'Raka Pratama';
    $words = explode(' ', trim($userName));
    $initial = count($words) >= 2
        ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
        : strtoupper(substr($userName, 0, 2));

    /* Hitung peminjaman aktif untuk badge navigasi */
    $activeCount = $user ? \App\Models\Loan::where('user_id', $user->id)
        ->whereIn('status', ['active', 'borrowed', 'approved', 'pending'])
        ->count() : 0;

    /* Data arsip riwayat peminjaman dari database */
    $dbHistoryLoans = $user ? \App\Models\Loan::with(['details.monster', 'details.fine'])
        ->where('user_id', $user->id)
        ->whereIn('status', ['returned', 'completed'])
        ->latest('loan_date')
        ->get() : collect();

    $historyLoans = $dbHistoryLoans->map(function($loan) {
        $detail = $loan->details->first();
        $monster = $detail?->monster;
        $returnDate = $detail?->returned_at ? \Carbon\Carbon::parse($detail->returned_at) : ($loan->updated_at ? \Carbon\Carbon::parse($loan->updated_at) : null);
        $dueDate = $loan->due_date;
        $isLate = false;
        $note = 'Tepat waktu';
        $subNote = null;

        if ($returnDate && $dueDate && $returnDate->gt($dueDate)) {
            $isLate = true;
            $diff = $returnDate->diffInDays($dueDate);
            $note = 'Terlambat ' . $diff . ' hari';
            if ($detail && $detail->fine) {
                $subNote = 'Denda Rp ' . number_format($detail->fine->amount ?? 0, 0, ',', '.');
            } else {
                $subNote = 'Denda tercatat';
            }
        }

        return [
            'tx_code'     => $loan->loan_code,
            'unit_name'   => $monster->name ?? 'Entitas Monster',
            'unit_code'   => $monster->monster_code ?? '-',
            'loan_date'   => $loan->loan_date ? $loan->loan_date->translatedFormat('d F Y') : '-',
            'due_date'    => $dueDate ? $dueDate->translatedFormat('d F Y') : '-',
            'return_date' => $returnDate ? $returnDate->translatedFormat('d F Y') : '-',
            'status'      => 'Selesai',
            'is_late'     => $isLate,
            'note'        => $note,
            'sub_note'    => $subNote,
        ];
    })->values()->all();

    $totalTransactions = count($historyLoans);
    $onTimeCount = count(array_filter($historyLoans, fn($l) => !$l['is_late']));
    $onTimePercent = $totalTransactions > 0 ? round(($onTimeCount / $totalTransactions) * 100) : 100;
    $lastCompletedDate = $totalTransactions > 0 ? $historyLoans[0]['return_date'] : '-';
    $lastTxCode = $totalTransactions > 0 ? $historyLoans[0]['tx_code'] : 'Belum ada arsip';
@endphp

<div class="app-shell">

    {{-- ── Navigasi anggota (Sidebar) ── --}}
    <aside class="sidebar">
        <a href="{{ route('dashboard') }}" class="brand">
            <span class="brand-icon"><i data-lucide="layers"></i></span>
            <span>ArsenalTrack</span>
        </a>

        <nav class="nav-section">
            <p class="nav-label">RUANG ANGGOTA</p>

            <a href="{{ route('dashboard') }}" class="nav-link">
                <i data-lucide="layout-dashboard"></i><span>Dashboard</span>
            </a>
            <a href="{{ route('catalog') }}" class="nav-link">
                <i data-lucide="boxes"></i><span>Katalog Monster</span>
            </a>
            <a href="{{ route('loan.active') }}" class="nav-link">
                <i data-lucide="clipboard-list"></i>
                <span>Peminjaman aktif</span>
                <span class="badge">{{ $activeCount }}</span>
            </a>
            <a href="{{ route('loan.history') }}" class="nav-link active">
                <i data-lucide="history"></i><span>Riwayat</span>
            </a>
            <a href="{{ route('profile') }}" class="nav-link">
                <i data-lucide="user-round"></i><span>Profil saya</span>
            </a>
        </nav>

        <div class="sidebar-spacer"></div>

        <div class="institution-card">
            <i data-lucide="landmark"></i>
            <strong>Pusat Pelatihan Arunika</strong>
            <p>Lingkungan simulasi akademik. Seluruh data bersifat fiktif.</p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-btn" type="submit">
                <i data-lucide="log-out"></i> <span>Keluar</span>
            </button>
        </form>
    </aside>

    {{-- ── Area kerja (Workspace) ── --}}
    <main class="workspace">

        {{-- Bilah akun (Topbar) --}}
        <header class="topbar">
            <div class="breadcrumb">
                <span>Ruang anggota</span>
                <span>/</span>
                <strong>Riwayat</strong>
            </div>

            <div class="account">
                <span class="account-label">{{ now()->translatedFormat('l, d F Y') }}</span>
                <span class="account-divider"></span>
                <div class="avatar">{{ $initial }}</div>
                <div class="account-info">
                    <strong>{{ $userName }}</strong>
                    <small>Anggota</small>
                </div>
                <i data-lucide="chevron-down" class="account-chevron"></i>
            </div>
        </header>

        {{-- Konten halaman --}}
        <div class="page-content">

            {{-- Judul halaman --}}
            <section class="page-heading" style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h1>Riwayat peminjaman</h1>
                    <p>Arsip seluruh transaksi peminjaman yang telah selesai dan dicatat oleh Admin.</p>
                </div>
                <a href="{{ route('loan.history.print') }}" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; background: #187653; color: #FFFFFF; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px;">
                    <i data-lucide="printer" style="width: 16px; height: 16px;"></i>
                    <span>Cetak Riwayat</span>
                </a>
            </section>

            {{-- Ringkasan riwayat (3 Cards) --}}
            <div class="summary-grid">
                {{-- Card 1 --}}
                <div class="summary-card">
                    <div class="summary-card-header">
                        <span class="summary-label">Total transaksi</span>
                        <i data-lucide="history" class="summary-icon"></i>
                    </div>
                    <span class="summary-val">{{ $totalTransactions }}</span>
                    <span class="summary-sub">Selesai dikembalikan</span>
                </div>

                {{-- Card 2 --}}
                <div class="summary-card">
                    <div class="summary-card-header">
                        <span class="summary-label">Tingkat ketepatan</span>
                        <i data-lucide="circle-check" class="summary-icon"></i>
                    </div>
                    <span class="summary-val">{{ $onTimePercent }}%</span>
                    <span class="summary-sub">{{ $onTimeCount }} dari {{ $totalTransactions }} pengembalian tepat waktu</span>
                </div>

                {{-- Card 3 --}}
                <div class="summary-card">
                    <div class="summary-card-header">
                        <span class="summary-label">Terakhir selesai</span>
                        <i data-lucide="calendar-clock" class="summary-icon"></i>
                    </div>
                    <span class="summary-val">{{ $lastCompletedDate }}</span>
                    <span class="summary-sub">{{ $lastTxCode }}</span>
                </div>
            </div>

            {{-- Arsip transaksi (Tabel) --}}
            <div class="archive-card">
                <div class="archive-filter-row">
                    <h2>Arsip transaksi</h2>
                    <div class="sort-label">
                        <span>Terbaru dulu</span>
                        <i data-lucide="arrow-down-wide-narrow"></i>
                    </div>
                </div>

                {{-- Kepala riwayat --}}
                <div class="table-head">
                    <span class="table-col-header">MONSTER &amp; KODE TRANSAKSI</span>
                    <span class="table-col-header">TANGGAL PINJAM</span>
                    <span class="table-col-header">BATAS KEMBALI</span>
                    <span class="table-col-header">TANGGAL KEMBALI</span>
                    <span class="table-col-header">STATUS</span>
                    <span class="table-col-header">CATATAN / DENDA</span>
                </div>

                {{-- Baris riwayat transaksi / Empty state --}}
                @if ($totalTransactions > 0)
                    @foreach ($historyLoans as $loan)
                        <div class="table-row">
                            <div class="col-identity">
                                <span class="tx-code">{{ $loan['tx_code'] }}</span>
                                <span class="unit-name">{{ $loan['unit_name'] }}</span>
                                <span class="unit-code">{{ $loan['unit_code'] }}</span>
                            </div>
                            <div class="col-date">{{ $loan['loan_date'] }}</div>
                            <div class="col-date">{{ $loan['due_date'] }}</div>
                            <div class="col-date">{{ $loan['return_date'] }}</div>
                            <div class="col-status">
                                <span class="status-badge-done">
                                    <span class="dot"></span>
                                    {{ $loan['status'] }}
                                </span>
                            </div>
                            <div class="col-delay">
                                @if ($loan['is_late'])
                                    <span class="delay-late">{{ $loan['note'] }}</span>
                                    @if ($loan['sub_note'])
                                        <span class="delay-sub">{{ $loan['sub_note'] }}</span>
                                    @endif
                                @else
                                    <span class="delay-ontime">{{ $loan['note'] }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    {{-- Jumlah arsip (Footer tabel) --}}
                    <div class="archive-footer">
                        <span>Menampilkan {{ $totalTransactions }} dari {{ $totalTransactions }} arsip</span>
                        <span>Semua pengembalian diverifikasi oleh Admin</span>
                    </div>
                @else
                    <div style="padding: 52px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: #EFF4F2; display: grid; place-items: center; color: #52796F;">
                            <i data-lucide="history" style="width: 24px; height: 24px;"></i>
                        </div>
                        <strong style="font-size: 15px; color: #1E3A34;">Belum Ada Riwayat Peminjaman</strong>
                        <p style="margin: 0; font-size: 13px; color: #52796F; max-width: 440px; line-height: 1.5;">
                            Seluruh peminjaman entitas monster yang telah dikembalikan dan diverifikasi oleh Admin akan tercatat permanen dalam arsip ini.
                        </p>
                        <a href="{{ route('catalog') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #2A5448; font-weight: 600; margin-top: 6px; text-decoration: none;">
                            Jelajahi Katalog Monster <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                        </a>
                    </div>
                @endif
            </div>

            {{-- Informasi (Ketentuan arsip transaksi) --}}
            <div class="warn-notice">
                <i data-lucide="info"></i>
                <div class="warn-body">
                    <strong>Ketentuan arsip transaksi</strong>
                    <p>Riwayat ini bersifat permanen dan menjadi catatan kedisiplinan anggota dalam peminjaman fasilitas Pusat Pelatihan Arunika.</p>
                </div>
            </div>

        </div>

        {{-- Catatan halaman (Footer) --}}
        <footer class="page-footer">
            <span>© {{ date('Y') }} ArsenalTrack · Pusat Pelatihan Arunika</span>
            <span>Simulasi akademik · Data contoh</span>
        </footer>

    </main>
</div>

<script>
    if (window.lucide) lucide.createIcons();
</script>
</body>
</html>
