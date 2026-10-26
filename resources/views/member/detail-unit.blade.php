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

    $code = request('code') ?? request('kode');
    $currentMonster = isset($monster) ? $monster : null;
    if (!$currentMonster && $code) {
        $currentMonster = \App\Models\Monster::with('categories')->where('monster_code', $code)->first();
    }
    if (!$currentMonster) {
        $currentMonster = \App\Models\Monster::with('categories')->first();
    }

    $unit = [
        'id'          => $currentMonster->id ?? 1,
        'code'        => $currentMonster->monster_code ?? 'MON-001',
        'name'        => $currentMonster->name ?? 'Entitas Monster',
        'category'    => $currentMonster?->categories?->pluck('name')->implode(', ') ?: ($currentMonster->origin_type ?? 'Kelas Khusus'),
        'condition'   => $currentMonster->condition ?? 'Hanggar Utama',
        'status'      => ($currentMonster?->status === 'available') ? 'Tersedia' : ($currentMonster?->status === 'maintenance' ? 'Perawatan' : 'Dipinjam'),
        'type'        => $currentMonster->origin_type ?? 'Unit Monster Terdaftar',
        'threat'      => $currentMonster->threat_level ?? 'Normal',
        'description' => $currentMonster->description ?: 'Unit monster terdaftar untuk kebutuhan operasional dan simulasi anggota sesuai ketentuan pengelolaan entitas. Dicatat sebagai satu unit inventaris.',
        'image'       => $currentMonster?->imageUrl() ?? asset('images/monsters/default.svg'),
    ];

    $activeCount = isset($activeLoansCount) ? $activeLoansCount : ($user ? \App\Models\Loan::where('user_id', $user->id)->whereIn('status', ['active', 'borrowed', 'approved', 'pending'])->count() : 0);
    $isAvailable = ($unit['status'] === 'Tersedia');
    $canSchedule = $currentMonster && $currentMonster->canSchedule();
    $canBorrow = ($activeCount < 2) && $canSchedule;
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
                @if ($activeCount > 0)
                    <span class="badge" style="margin-left:auto; background: #059669; color:#fff; font-size:11px; font-weight:600; padding:2px 7px; border-radius:10px;">{{ $activeCount }}</span>
                @endif
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
            @if (session('error'))
                <div style="background: #fef2f2; border: 1px solid #f87171; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="schedule-errors" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

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
                            <span class="status-pill {{ $isAvailable ? '' : 'status-borrowed' }}" style="{{ $isAvailable ? '' : 'background: #fee2e2; color: #991b1b;' }}">
                                <span></span>{{ $unit['status'] }}
                            </span>
                        </div>

                        <div class="unit-visual">
                            <div class="unit-art-frame">
                                <img src="{{ $unit['image'] }}" alt="Ilustrasi {{ $unit['name'] }}">
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
                            <div>
                                <span>Tingkat Ancaman</span>
                                <strong>{{ $unit['threat'] }}</strong>
                            </div>
                            <div>
                                <span>Kondisi Fisik</span>
                                <strong>{{ $unit['condition'] }}</strong>
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
                                <p>Transaksi ini hanya mencatat entitas {{ $unit['name'] }}.</p>
                            </div>
                        </div>

                        <div class="rule-item">
                            <i data-lucide="shield-alert"></i>
                            <div>
                                <strong>Batas Kuota Peminjaman (FR-008)</strong>
                                <p>Setiap anggota dibatasi maksimal 2 peminjaman aktif, termasuk reservasi mendatang. Durasi setiap peminjaman maksimal 5 hari (120 jam).</p>
                            </div>
                        </div>

                        <div class="rule-item">
                            <i data-lucide="handshake"></i>
                            <div>
                                <strong>Pengembalian dicatat oleh Admin</strong>
                                <p>Serahkan unit secara fisik ke petugas Admin di ruang inventaris saat masa peminjaman selesai.</p>
                            </div>
                        </div>
                    </article>
                </section>

                <aside class="panel review-panel">
                    <div class="review-heading">
                        <h2>Konfirmasi peminjaman</h2>
                        <p>Pastikan rincian berikut sudah sesuai.</p>
                    </div>

                    @if ($activeCount >= 2)
                        <div class="review-notice" style="background: #fef2f2; border: 1px solid #fca5a5;">
                            <i data-lucide="alert-octagon" style="color: #dc2626;"></i>
                            <div>
                                <strong style="color: #991b1b;">Kapasitas Penuh · 2 dari 2 unit aktif</strong>
                                <p style="color: #7f1d1d;">Anda telah mencapai batas kuota maksimal 2 peminjaman aktif. Kembalikan salah satu unit untuk dapat meminjam kembali.</p>
                            </div>
                        </div>
                    @elseif (!$canSchedule)
                        <div class="review-notice" style="background: #fffbeb; border: 1px solid #fcd34d;">
                            <i data-lucide="alert-triangle" style="color: #d97706;"></i>
                            <div>
                                <strong style="color: #92400e;">Unit Tidak Tersedia</strong>
                                <p style="color: #78350f;">Unit sedang dalam perawatan atau tidak dapat dijadwalkan. Silakan pilih unit lain.</p>
                            </div>
                        </div>
                    @else
                        <div class="review-notice">
                            <i data-lucide="circle-check"></i>
                            <div>
                                <strong>{{ $activeCount }} dari 2 unit aktif</strong>
                                <p>Tersisa {{ 2 - $activeCount }} slot. Setelah transaksi ini, kapasitas Anda menjadi {{ $activeCount + 1 }} dari 2 unit.</p>
                            </div>
                        </div>
                    @endif

                    <div class="review-field">
                        <span>Peminjam</span>
                        <strong>{{ $user->name }}</strong>
                    </div>

                    <div class="review-divider"></div>

                    <form id="loanReviewForm" action="{{ route('loan.store') }}" method="POST"
                        data-availability-url="{{ route('loan.availability') }}" data-server-now="{{ now()->getTimestampMs() }}">
                    <div class="schedule-fields">
                        <div class="review-field">
                            <label for="starts_at">Tanggal dan jam mulai (WIB)</label>
                            <input type="datetime-local" id="starts_at" name="starts_at" form="loanReviewForm"
                                value="{{ old('starts_at') }}" min="{{ now()->ceilMinute()->format('Y-m-d\\TH:i') }}"
                                step="60" required aria-describedby="scheduleHelp availabilityMessage" @disabled(!$canBorrow)>
                        </div>
                        <div class="review-field">
                            <label for="ends_at">Tanggal dan jam selesai (WIB)</label>
                            <input type="datetime-local" id="ends_at" name="ends_at" form="loanReviewForm"
                                value="{{ old('ends_at') }}" step="60" required
                                aria-describedby="scheduleHelp availabilityMessage" @disabled(!$canBorrow)>
                        </div>
                    </div>
                    <p id="scheduleHelp" class="schedule-help">Semua waktu menggunakan WIB (Asia/Jakarta). Durasi maksimal 5 hari (120 jam). Jadwal yang bersebelahan diperbolehkan.</p>
                    <p id="availabilityMessage" class="schedule-help" role="status" aria-live="polite">Pilih jadwal untuk memeriksa ketersediaan unit.</p>
                    <div class="review-field schedule-summary">
                        <span>Ringkasan jadwal</span>
                        <strong id="scheduleSummary">Tanggal dan jam belum dipilih.</strong>
                    </div>

                    <div class="review-divider"></div>

                        @csrf
                        <input type="hidden" name="monster_id" value="{{ $unit['id'] }}">

                        @if ($canBorrow)
                            <label class="consent">
                                <input type="checkbox" id="consentCheck" name="consent" required @checked(old('consent'))>
                                <span>Saya memahami batas waktu dan ketentuan pengembalian melalui Admin.</span>
                            </label>

                            <button class="confirm-button" type="submit" id="btnConfirmLoan">
                                Konfirmasi &amp; pinjam unit
                                <i data-lucide="arrow-right"></i>
                            </button>
                        @else
                            <button class="confirm-button" type="button" disabled style="opacity: 0.6; cursor: not-allowed; background: #6b7280;">
                                @if ($activeCount >= 2)
                                    Kuota Penuh (Maks 2 Unit)
                                @else
                                    Unit Tidak Tersedia
                                @endif
                                <i data-lucide="ban"></i>
                            </button>
                        @endif
                    </form>

                    <p class="review-footnote">
                        Dengan konfirmasi, peminjaman langsung tercatat di sistem secara resmi.
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

<script src="{{ asset('js/loan-schedule.js') }}" defer></script>
<script>
    if (window.lucide) lucide.createIcons();
</script>
</body>
</html>