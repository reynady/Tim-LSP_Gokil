<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Anggota - ArsenalTrack</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
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

    /* Data peminjaman dari database (0 saat belum ada transaksi) */
    $dbLoans = $user ? \App\Models\Loan::with('details.monster')->where('user_id', $user->id)->get() : collect();
    $activeLoansDb = $dbLoans->whereIn('status', ['active', 'borrowed', 'approved', 'pending']);
    $completedLoansDb = $dbLoans->whereIn('status', ['returned', 'completed']);

    $activeCount = $activeLoansDb->count();
    $completedCount = $completedLoansDb->count();
    $totalCount = $dbLoans->count();

    $activeMonsters = $activeLoansDb->map(function($loan) {
        $monster = $loan->details->first()?->monster;
        return [
            'name'      => $monster->name ?? 'Entitas Monster',
            'code'      => $monster->monster_code ?? $loan->loan_code,
            'category'  => $monster->origin_type ?? 'Kaiju/SCP Anomaly',
            'due_date'  => $loan->due_date ? $loan->due_date->translatedFormat('d F Y') : '-',
            'remaining' => $loan->due_date ? (now()->diffInDays($loan->due_date, false) >= 0 ? now()->diffInDays($loan->due_date) . ' hari lagi' : 'Terlambat') : '-',
            'status'    => ucfirst($loan->status ?? 'Dipinjam'),
        ];
    })->values()->all();
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

            <a href="{{ route('dashboard') }}" class="nav-link active">
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
                <strong>Dashboard</strong>
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
            <section class="page-heading">
                <div class="heading-text">
                    <h1>Dashboard Anggota</h1>
                    <p>Pantau aktivitas peminjaman monster (Kaiju &amp; SCP Anomaly) dan akses operasional Anda.</p>
                </div>
                <a href="{{ route('catalog') }}" class="btn-header">
                    Jelajahi Katalog Monster
                    <i data-lucide="arrow-right"></i>
                </a>
            </section>

            {{-- Ringkasan (3 Stat Cards) --}}
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-card-header">
                        <span class="summary-label">Total peminjaman</span>
                        <i data-lucide="history" class="summary-icon"></i>
                    </div>
                    <span class="summary-val">{{ $totalCount }} entitas</span>
                    <span class="summary-sub">Seluruh transaksi monster</span>
                </div>

                <div class="summary-card">
                    <div class="summary-card-header">
                        <span class="summary-label">Sedang dipinjam</span>
                        <i data-lucide="clipboard-list" class="summary-icon"></i>
                    </div>
                    <span class="summary-val">{{ $activeCount }} monster</span>
                    <span class="summary-sub">Kapasitas 2 dari 2 entitas penuh</span>
                </div>

                <div class="summary-card">
                    <div class="summary-card-header">
                        <span class="summary-label">Selesai dikembalikan</span>
                        <i data-lucide="circle-check" class="summary-icon"></i>
                    </div>
                    <span class="summary-val">{{ $completedCount }} arsip</span>
                    <span class="summary-sub">100% verifikasi Admin</span>
                </div>
            </div>

            {{-- Informasi Kapasitas --}}
            @if ($activeCount >= 2)
                <div class="warn-notice">
                    <i data-lucide="info"></i>
                    <div class="warn-body">
                        <strong>Kapasitas penuh · {{ $activeCount }} dari 2 monster aktif dipinjam</strong>
                        <p>Anda telah mencapai kuota maksimal. Kembalikan salah satu entitas monster melalui Admin sebelum mengajukan peminjaman baru.</p>
                    </div>
                </div>
            @else
                <div class="warn-notice" style="background: var(--green-light); border: 1px solid var(--border);">
                    <i data-lucide="circle-check" style="color: var(--green);"></i>
                    <div class="warn-body">
                        <strong style="color: var(--green);">Kapasitas tersedia · {{ $activeCount }} dari 2 monster dipinjam</strong>
                        <p>Anda memiliki slot peminjaman aktif. Silakan pilih entitas monster melalui Katalog Monster.</p>
                    </div>
                </div>
            @endif

            {{-- Dashboard Grid (2 Columns) --}}
            <div class="dashboard-grid">

                {{-- Kolom Kiri: Peminjaman Aktif & Akses Cepat --}}
                <div class="main-column">

                    {{-- Card: Peminjaman Monster Aktif --}}
                    <div class="panel">
                        <div class="panel-header">
                            <h2>Peminjaman Monster Aktif</h2>
                            <a href="{{ route('loan.active') }}" class="panel-link">
                                Buka peminjaman aktif <i data-lucide="arrow-right"></i>
                            </a>
                        </div>

                        @if (count($activeMonsters) > 0)
                            @foreach ($activeMonsters as $monster)
                                <div class="loan-item-row">
                                    <div class="loan-info">
                                        <div class="monster-thumb">
                                            <i data-lucide="box"></i>
                                        </div>
                                        <div class="monster-details">
                                            <span class="monster-name">{{ $monster['name'] }}</span>
                                            <span class="monster-meta">{{ $monster['code'] }} · {{ $monster['category'] }}</span>
                                        </div>
                                    </div>

                                    <div class="loan-dates">
                                        <span class="loan-due">{{ $monster['due_date'] }}</span>
                                        <span class="loan-remaining">{{ $monster['remaining'] }}</span>
                                    </div>

                                    <div class="status-badge">
                                        <span class="dot"></span>
                                        {{ $monster['status'] }}
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div style="padding: 44px 22px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 10px;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--soft); display: grid; place-items: center; color: var(--muted);">
                                    <i data-lucide="clipboard-list" style="width: 22px; height: 22px;"></i>
                                </div>
                                <strong style="font-size: 14px; color: var(--text);">Belum ada monster yang sedang dipinjam</strong>
                                <p style="margin: 0; font-size: 12px; color: var(--muted); max-width: 400px; line-height: 1.5;">
                                    Pengajuan peminjaman monster yang disetujui akan muncul di sini. Silakan jelajahi katalog untuk memilih entitas.
                                </p>
                                <a href="{{ route('catalog') }}" class="panel-link" style="margin-top: 4px;">
                                    Jelajahi Katalog Monster <i data-lucide="arrow-right"></i>
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Card: Akses Cepat Layanan --}}
                    <div class="panel">
                        <div class="panel-header">
                            <h2>Akses Cepat Layanan</h2>
                        </div>

                        <div class="quick-grid">
                            <a href="{{ route('catalog') }}" class="quick-card">
                                <div class="quick-icon"><i data-lucide="boxes"></i></div>
                                <strong>Katalog Monster</strong>
                                <p>Jelajahi entitas Kaiju &amp; SCP anomali yang tersedia.</p>
                            </a>

                            <a href="{{ route('loan.history') }}" class="quick-card">
                                <div class="quick-icon"><i data-lucide="history"></i></div>
                                <strong>Riwayat Peminjaman</strong>
                                <p>Lihat arsip pengembalian dan catatan ketertiban Anda.</p>
                            </a>

                            <a href="{{ route('profile') }}" class="quick-card">
                                <div class="quick-icon"><i data-lucide="user-round"></i></div>
                                <strong>Profil Saya</strong>
                                <p>Periksa identitas akun dan informasi keanggotaan.</p>
                            </a>
                        </div>
                    </div>

                </div>

                {{-- Kolom Kanan: Panduan Protokol & Bantuan --}}
                <aside class="side-column">

                    {{-- Card: Protokol Penanganan Monster --}}
                    <div class="guide-card">
                        <h2>Protokol Penanganan Monster</h2>

                        <div class="rule-list">
                            <div class="rule-item">
                                <i data-lucide="shield-alert"></i>
                                <div class="rule-text">
                                    <strong>Kelas Ancaman &amp; Kategori</strong>
                                    <p>Pahami klasifikasi monster (Kaiju, Humanoid, Anomaly) sebelum mengajukan peminjaman.</p>
                                </div>
                            </div>

                            <div class="rule-item">
                                <i data-lucide="calendar-days"></i>
                                <div class="rule-text">
                                    <strong>Batas Waktu Containment</strong>
                                    <p>Durasi peminjaman 5 hari kalender dan tanggal pengembalian ditetapkan otomatis oleh sistem.</p>
                                </div>
                            </div>

                            <div class="rule-item">
                                <i data-lucide="clipboard-check"></i>
                                <div class="rule-text">
                                    <strong>Verifikasi Fisik oleh Admin</strong>
                                    <p>Serahkan entitas monster langsung ke ruang inventaris untuk diperiksa oleh petugas Admin.</p>
                                </div>
                            </div>

                            <div class="rule-item">
                                <i data-lucide="handshake"></i>
                                <div class="rule-text">
                                    <strong>Ketertiban &amp; Sanksi</strong>
                                    <p>Keterlambatan pengembalian dicatat permanen dalam arsip kedisiplinan anggota.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Bantuan Operasional --}}
                    <div class="support-box">
                        <i data-lucide="messages-square"></i>
                        <strong>Ruang Pengendalian &amp; Inventaris</strong>
                        <p>Silakan hubungi tim Admin jika membutuhkan panduan khusus penanganan monster atau klarifikasi operasional.</p>
                    </div>

                </aside>

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
