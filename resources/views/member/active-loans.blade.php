<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Aktif - ArsenalTrack</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/active-loans.css') }}">
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

    /* Data peminjaman aktif dari database */
    $dbActiveLoans = $user ? \App\Models\Loan::with('details.monster')
        ->where('user_id', $user->id)
        ->whereIn('status', ['active', 'borrowed', 'approved', 'pending'])
        ->get() : collect();

    $activeLoans = $dbActiveLoans->map(function($loan) {
        $monster = $loan->details->first()?->monster;
        $due = $loan->due_date ? $loan->due_date->startOfDay() : now()->startOfDay();
        $today = now()->startOfDay();
        $isOverdue = $today->gt($due);
        $overdueDays = $isOverdue ? $due->diffInDays($today) : 0;
        return [
            'id'          => $loan->id,
            'tx_code'     => $loan->loan_code,
            'unit_name'   => $monster->name ?? 'Entitas Monster',
            'unit_code'   => $monster->monster_code ?? '-',
            'borrowed'    => $loan->loan_date ? $loan->loan_date->translatedFormat('d F Y') : '-',
            'due'         => $loan->due_date ? $loan->due_date->translatedFormat('d F Y') : '-',
            'remaining'   => $isOverdue
                ? 'Terlambat ' . $overdueDays . ' hari'
                : ($loan->due_date ? (now()->diffInDays($loan->due_date, false) >= 0 ? now()->diffInDays($loan->due_date) . ' hari lagi' : 'Terlambat') : '-'),
            'is_overdue'  => $isOverdue,
            'overdue_days'=> $overdueDays,
            'status'      => ucfirst($loan->status ?? 'Dipinjam'),
        ];
    })->values()->all();

    $totalBorrowed = count($activeLoans);
    $hasOverdue    = collect($activeLoans)->contains('is_overdue', true);
    $nearestDue    = $totalBorrowed > 0 ? $activeLoans[0]['due'] : '-';
    $nearestUnit   = $totalBorrowed > 0 ? $activeLoans[0]['unit_name'] : 'Tidak ada pinjaman';
    $nearestRemaining = $totalBorrowed > 0 ? ' · ' . $activeLoans[0]['remaining'] : '';
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
            <a href="{{ route('loan.active') }}" class="nav-link active">
                <i data-lucide="clipboard-list"></i>
                <span>Peminjaman aktif</span>
                <span class="badge">{{ $totalBorrowed }}</span>
            </a>
            <a href="{{ route('loan.history') }}" class="nav-link">
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
                <strong>Peminjaman aktif</strong>
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

            @if (session('error'))
                <div style="background: #fef2f2; border: 1px solid #f87171; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if (session('success'))
                <div style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Judul halaman --}}
            <section class="page-heading">
                <h1>Peminjaman aktif</h1>
                <p>Pantau entitas monster yang sedang Anda pinjam, batas waktu pengembalian, dan ketentuan pemakaian.</p>
            </section>

            {{-- Ringkasan peminjaman (3 Cards) --}}
            <div class="summary-grid">
                {{-- Card 1 --}}
                <div class="summary-card">
                    <div class="summary-card-header">
                        <span class="summary-label">Monster dipinjam</span>
                        <i data-lucide="clipboard-list" class="summary-icon"></i>
                    </div>
                    <span class="summary-val">{{ $totalBorrowed }} monster</span>
                    <span class="summary-sub">Kapasitas maksimal 2 entitas</span>
                </div>

                {{-- Card 2 --}}
                <div class="summary-card">
                    <div class="summary-card-header">
                        <span class="summary-label">Batas kembali terdekat</span>
                        <i data-lucide="calendar-days" class="summary-icon"></i>
                    </div>
                    <span class="summary-val">{{ $nearestDue }}</span>
                    <span class="summary-sub">{{ $nearestUnit }}{{ $nearestRemaining }}</span>
                </div>

                {{-- Card 3 --}}
                <div class="summary-card" style="{{ $hasOverdue ? 'border-color: #F5E5C9; background: #FBF4E6;' : '' }}">
                    <div class="summary-card-header">
                        <span class="summary-label">Status pengembalian</span>
                        <i data-lucide="{{ $hasOverdue ? 'alert-triangle' : 'circle-check' }}" class="summary-icon" style="color: {{ $hasOverdue ? '#94651E' : '' }};"></i>
                    </div>
                    @if($hasOverdue)
                        <span class="summary-val" style="color: #94651E;">Ada keterlambatan</span>
                        <span class="summary-sub" style="color: #94651E;">Segera temui Admin untuk pengembalian</span>
                    @else
                        <span class="summary-val">{{ $totalBorrowed > 0 ? 'Tepat waktu' : 'Tertib' }}</span>
                        <span class="summary-sub">{{ $totalBorrowed > 0 ? 'Tidak ada unit yang melewati batas' : 'Tidak ada tanggungan peminjaman' }}</span>
                    @endif
                </div>
            </div>

            {{-- Informasi Kapasitas --}}
            @if ($totalBorrowed >= 2)
                <div class="warn-notice">
                    <i data-lucide="info"></i>
                    <div class="warn-body">
                        <strong>Kapasitas penuh · {{ $totalBorrowed }} dari 2 monster dipinjam</strong>
                        <p>Anda telah mencapai batas maksimal peminjaman (VAL-L-03). Kembalikan salah satu entitas monster untuk dapat meminjam kembali.</p>
                    </div>
                </div>
            @else
                <div class="warn-notice" style="background: var(--green-light); border: 1px solid var(--border);">
                    <i data-lucide="circle-check" style="color: var(--green);"></i>
                    <div class="warn-body">
                        <strong style="color: var(--green);">Kapasitas tersedia · {{ $totalBorrowed }} dari 2 monster dipinjam</strong>
                        <p>Anda memiliki slot peminjaman aktif. Silakan pilih entitas monster melalui Katalog Monster.</p>
                    </div>
                </div>
            @endif

            {{-- Tabel pinjaman aktif --}}
            <div class="table-card">
                <div class="table-card-header">
                    <h2>Daftar monster aktif</h2>
                    <span class="count-label">{{ $totalBorrowed }} transaksi aktif</span>
                </div>

                {{-- Kepala tabel --}}
                <div class="table-head">
                    <span class="table-col-header">KODE TRANSAKSI</span>
                    <span class="table-col-header">MONSTER &amp; KODE</span>
                    <span class="table-col-header">TANGGAL PINJAM</span>
                    <span class="table-col-header">BATAS PENGEMBALIAN</span>
                    <span class="table-col-header">STATUS</span>
                    <span class="table-col-header" style="text-align: right;">INFO</span>
                </div>

                {{-- Baris transaksi / Empty State --}}
                @if (count($activeLoans) > 0)
                    @foreach ($activeLoans as $loan)
                        <div class="table-row">
                            <div class="col-tx-code">{{ $loan['tx_code'] }}</div>
                            <div class="col-unit">
                                <span class="unit-name">{{ $loan['unit_name'] }}</span>
                                <span class="unit-code">{{ $loan['unit_code'] }}</span>
                            </div>
                            <div class="col-date">{{ $loan['borrowed'] }}</div>
                            <div class="col-due">
                                <span class="due-date" style="{{ $loan['is_overdue'] ? 'color: #94651E; font-weight: 600;' : '' }}">{{ $loan['due'] }}</span>
                                <span class="due-remaining" style="{{ $loan['is_overdue'] ? 'color: #94651E;' : '' }}">{{ $loan['remaining'] }}</span>
                            </div>
                            <div class="col-status">
                                @if($loan['is_overdue'])
                                    <span class="status-badge" style="background: #FBF4E6; color: #94651E; border-color: #F5E5C9;">
                                        <span class="dot" style="background: #94651E;"></span>
                                        Terlambat
                                    </span>
                                @else
                                    <span class="status-badge">
                                        <span class="dot"></span>
                                        {{ $loan['status'] }}
                                    </span>
                                @endif
                            </div>
                            <div class="col-action" style="text-align: right;">
                                @if($loan['is_overdue'])
                                    <span style="background: #FBF4E6; border: 1px solid #F5E5C9; color: #94651E; border-radius: 6px; padding: 5px 10px; font-size: 12px; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                                        <i data-lucide="alert-triangle" style="width: 13px; height: 13px;"></i> Hubungi Admin
                                    </span>
                                @else
                                    <span style="background: var(--soft); border: 1px solid var(--border); color: var(--muted); border-radius: 6px; padding: 5px 10px; font-size: 12px; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                                        <i data-lucide="shield-check" style="width: 13px; height: 13px;"></i> Lewat Admin
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="padding: 48px 22px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--soft); display: grid; place-items: center; color: var(--muted);">
                            <i data-lucide="boxes" style="width: 22px; height: 22px;"></i>
                        </div>
                        <strong style="font-size: 14px; color: var(--text);">Belum ada peminjaman monster aktif</strong>
                        <p style="margin: 0; font-size: 12px; color: var(--muted); max-width: 420px; line-height: 1.5;">
                            Anda belum meminjam entitas monster saat ini. Seluruh kuota (2 slot) tersedia untuk digunakan.
                        </p>
                        <a href="{{ route('catalog') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: var(--green); font-weight: 600; margin-top: 4px;">
                            Jelajahi Katalog Monster <i data-lucide="arrow-right"></i>
                        </a>
                    </div>
                @endif

                {{-- Catatan tabel --}}
                <div class="table-card-footer">
                    Pengembalian hanya dapat dicatat dan disetujui langsung oleh Admin di luar sistem.
                </div>
            </div>

            {{-- Bagaimana cara mengembalikan unit? --}}
            <div class="guide-card">
                <h2 class="guide-title">Bagaimana cara mengembalikan unit?</h2>

                <div class="guide-grid">
                    <div class="guide-item">
                        <i data-lucide="package" class="guide-icon"></i>
                        <div class="guide-text">
                            <strong>1. Serahkan unit fisik</strong>
                            <p>Bawa dan serahkan unit langsung ke petugas Admin di ruang inventaris.</p>
                        </div>
                    </div>

                    <div class="guide-item">
                        <i data-lucide="messages-square" class="guide-icon"></i>
                        <div class="guide-text">
                            <strong>2. Verifikasi kondisi</strong>
                            <p>Admin akan memeriksa kelengkapan dan kondisi fisik unit sebelum konfirmasi.</p>
                        </div>
                    </div>

                    <div class="guide-item">
                        <i data-lucide="clipboard-check" class="guide-icon"></i>
                        <div class="guide-text">
                            <strong>3. Pencatatan sistem</strong>
                            <p>Admin mencatat pengembalian di sistem. Status unit otomatis berubah menjadi Selesai.</p>
                        </div>
                    </div>
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
