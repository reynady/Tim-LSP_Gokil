<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Unit - ArsenalTrack</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/detail-unit.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
@php
    $user = auth()->user();

    $unit = [
        'code'     => request('kode', 'MON-001'),
        'name'     => request('nama', 'Kaiju Alpha'),
        'category' => request('kategori', 'Kelas I'),
        'location' => request('lokasi', 'Hanggar A-01'),
        'status'   => 'Tersedia',
        'type'     => request('tipe', 'Unit Monster Terdaftar'),
        'description' => 'Unit monster terdaftar untuk kebutuhan operasional dan simulasi anggota sesuai ketentuan pengelolaan entitas. Dicatat sebagai satu unit inventaris.',
    ];

    $loanDate   = now();
    $returnDate = now()->addDays(5);
@endphp

<div class="app-shell">
    <aside class="sidebar">
        <a href="{{ route('dashboard') }}" class="brand">
            <span class="brand-icon"><i data-lucide="layers"></i></span>
            <span>ArsenalTrack</span>
        </a>

        <nav class="nav-section">
            <p class="nav-label">RUANG ANGGOTA</p>
            <a href="{{ route('dashboard') }}" class="nav-link">
                <i data-lucide="layout-dashboard"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('catalog') }}" class="nav-link active">
                <i data-lucide="boxes"></i> <span>Katalog Monster</span>
            </a>
            <a href="{{ route('loan.active') }}" class="nav-link">
                <i data-lucide="clipboard-list"></i> <span>Peminjaman aktif</span>
            </a>
            <a href="{{ route('loan.history') }}" class="nav-link">
                <i data-lucide="history"></i> <span>Riwayat</span>
            </a>
            <a href="{{ route('profile') }}" class="nav-link">
                <i data-lucide="user-round"></i> <span>Profil saya</span>
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

    <main class="workspace">
        <header class="topbar">
            <div class="breadcrumb">
                <a href="{{ route('catalog') }}">Katalog Monster</a>
                <span>/</span>
                <strong>Detail Unit</strong>
            </div>

            <div class="account">
                <span class="account-label">Portal inventaris anggota</span>
                <span class="account-divider"></span>
                <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <div class="account-info">
                    <strong>{{ $user->name }}</strong>
                    <small>Anggota</small>
                </div>
            </div>
        </header>

        <div class="page-content">
            <div class="page-heading">
                <div>
                    <h1>Detail Unit Monster</h1>
                    <p>Tinjau informasi unit dan ketentuan sebelum mengajukan peminjaman.</p>
                </div>
                <a href="{{ route('catalog') }}" class="back-button">
                    <i data-lucide="arrow-left"></i> Kembali ke katalog
                </a>
            </div>

            <div class="detail-layout">
                <section class="unit-column">
                    <article class="panel unit-panel">
                        <div class="unit-status-row">
                            <span class="eyebrow">INVENTARIS / {{ strtoupper($unit['category']) }}</span>
                            <span class="status-pill"><span></span>Tersedia</span>
                        </div>

                        <div class="unit-visual">
                            <div class="unit-symbol">
                                <i data-lucide="boxes"></i>
                            </div>
                            <div class="unit-identity">
                                <span>{{ strtoupper($unit['type']) }}</span>
                                <strong>{{ $unit['code'] }}</strong>
                                <div class="archive-mark">
                                    @for ($i = 0; $i < 18; $i++)
                                        <span></span>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <div class="unit-description">
                            <h2>{{ $unit['name'] }}</h2>
                            <p>{{ $unit['description'] }}</p>
                        </div>

                        <div class="unit-metadata">
                            <div>
                                <span>Kode unik</span>
                                <strong>{{ $unit['code'] }}</strong>
                            </div>
                            <div>
                                <span>Kategori</span>
                                <strong>{{ $unit['category'] }}</strong>
                            </div>
                        </div>

                        <p class="unit-footnote">
                            Informasi ini bersifat administratif, bukan panduan pengoperasian peralatan.
                        </p>
                    </article>

                    <article class="panel rules-panel">
                        <h2>Sebelum Anda meminjam</h2>

                        <div class="rule-item">
                            <i data-lucide="package-check"></i>
                            <div>
                                <strong>Satu unit per transaksi</strong>
                                <p>Transaksi ini hanya mencatat {{ $unit['name'] }}.</p>
                            </div>
                        </div>

                        <div class="rule-item">
                            <i data-lucide="handshake"></i>
                            <div>
                                <strong>Pengembalian dicatat oleh Admin</strong>
                                <p>Serahkan unit secara langsung dan hubungi Admin melalui kanal internal di luar aplikasi.</p>
                            </div>
                        </div>
                    </article>
                </section>

                <aside class="panel review-panel">
                    <div class="review-heading">
                        <h2>Konfirmasi peminjaman</h2>
                        <p>Pastikan rincian berikut sudah sesuai.</p>
                    </div>

                    <div class="review-notice">
                        <i data-lucide="circle-check"></i>
                        <div>
                            <strong>1 dari 2 unit aktif</strong>
                            <p>Tersisa 1 slot. Setelah transaksi ini, kapasitas Anda menjadi 2 dari 2 unit.</p>
                        </div>
                    </div>

                    <div class="review-field">
                        <span>Peminjam</span>
                        <strong>{{ $user->name }}</strong>
                    </div>

                    <div class="review-divider"></div>

                    <div class="date-grid">
                        <div class="review-field">
                            <span>Tanggal pinjam</span>
                            <strong>{{ $loanDate->translatedFormat('d F Y') }}</strong>
                        </div>
                        <div class="review-field">
                            <span>Batas kembali</span>
                            <strong>{{ $returnDate->translatedFormat('d F Y') }}</strong>
                        </div>
                    </div>

                    <div class="locked-note">
                        <i data-lucide="lock-keyhole"></i>
                        5 hari kalender &middot; Tanggal otomatis dan tidak dapat diubah.
                    </div>

                    <div class="review-divider"></div>

                    <form id="loanReviewForm" action="{{ route('loan.success') }}" method="GET">
                        <label class="consent">
                            <input type="checkbox" id="consentCheck" required>
                            <span>Saya memahami batas waktu dan ketentuan pengembalian melalui Admin.</span>
                        </label>

                        <button class="confirm-button" type="submit">
                            Konfirmasi &amp; pinjam unit
                            <i data-lucide="arrow-right"></i>
                        </button>
                    </form>

                    <p class="review-footnote">
                        Dengan konfirmasi, peminjaman langsung tercatat. Tidak ada tahap persetujuan atau reservasi.
                    </p>
                </aside>
            </div>
        </div>

        <footer class="page-footer">
            <span>ArsenalTrack · Portal Anggota</span>
            <span>Inventaris Monster</span>
        </footer>
    </main>
</div>

<script>
    if (window.lucide) lucide.createIcons();

    document.getElementById('loanReviewForm').addEventListener('submit', function (event) {
        if (!document.getElementById('consentCheck').checked) {
            event.preventDefault();
            return;
        }

        event.preventDefault();
        window.location.href = "{{ route('loan.success') }}";
    });
</script>
</body>
</html>