<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Berhasil - ArsenalTrack</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/loan-success.css') }}">
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
    $dbActiveLoans = $user ? \App\Models\Loan::with('details.monster')
        ->where('user_id', $user->id)
        ->whereIn('status', ['active', 'borrowed', 'approved', 'pending'])
        ->get() : collect();

    $activeCount = $dbActiveLoans->count();

    /* Data transaksi peminjaman */
    $currentTxLoan = (isset($latestLoan) && $latestLoan) ? $latestLoan : (
        request('code') ? \App\Models\Loan::with('details.monster')->where('loan_code', request('code'))->first() : $dbActiveLoans->last()
    );
    $txMonster = $currentTxLoan?->details->first()?->monster;

    $tx = [
        'number'    => $currentTxLoan->loan_code ?? ('AT-PJM-' . now()->format('ymd') . '-001'),
        'unit_name' => $txMonster->name ?? 'Entitas Monster',
        'unit_code' => $txMonster->monster_code ?? 'MNST-001',
        'unit_type' => $txMonster->origin_type ?? 'Kaiju/SCP Anomaly',
        'borrower'  => $userName,
        'qty'       => '1 unit',
        'loan_date' => $currentTxLoan?->loan_date ? $currentTxLoan->loan_date->translatedFormat('d F Y') : now()->translatedFormat('d F Y'),
        'due_date'  => $currentTxLoan?->due_date ? $currentTxLoan->due_date->translatedFormat('d F Y') : now()->addDays(5)->translatedFormat('d F Y'),
    ];

    /* Peminjaman aktif saat ini */
    $activeLoans = $dbActiveLoans->map(function($loan) use ($currentTxLoan) {
        $monster = $loan->details->first()?->monster;
        return [
            'name'    => $monster->name ?? 'Entitas Monster',
            'code'    => $monster->monster_code ?? $loan->loan_code,
            'due'     => $loan->due_date ? $loan->due_date->translatedFormat('d F Y') : now()->addDays(5)->translatedFormat('d F Y'),
            'is_new'  => ($currentTxLoan && $currentTxLoan->id === $loan->id),
        ];
    })->values()->all();
@endphp

<div class="app-shell">

    {{-- ── Sidebar ── --}}
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
                <span class="badge">{{ $activeCount > 0 ? $activeCount : 1 }}</span>
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

    {{-- ── Workspace ── --}}
    <main class="workspace">

        {{-- Topbar --}}
        <header class="topbar">
            <div class="breadcrumb">
                <span>Ruang anggota</span>
                <span>/</span>
                <span>Katalog Monster</span>
                <span>/</span>
                <strong>Peminjaman berhasil</strong>
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

        {{-- Page content --}}
        <div class="page-content">

            {{-- Page heading --}}
            <section class="page-heading">
                <h1>Peminjaman berhasil</h1>
                <p>Transaksi Anda sudah tercatat dan unit kini berstatus Dipinjam.</p>
            </section>

            {{-- Success banner --}}
            <div class="success-banner">
                <div class="success-icon">
                    <i data-lucide="check"></i>
                </div>

                <div class="success-text">
                    <h2>Sudah tercatat. Siap Anda pantau.</h2>
                    <p>{{ $tx['unit_name'] }} ditambahkan ke peminjaman aktif Anda pada {{ now()->translatedFormat('d F Y') }}.</p>
                </div>

                <div class="active-badge">
                    <span></span> Aktif
                </div>
            </div>

            {{-- Body grid --}}
            <div class="body-grid">

                {{-- Receipt panel (left) --}}
                <article class="receipt-panel">

                    {{-- Transaction number --}}
                    <div class="receipt-header">
                        <div class="tx-identity">
                            <span class="tx-label">TRANSAKSI BARU</span>
                            <span class="tx-number">{{ $tx['number'] }}</span>
                        </div>
                        <i data-lucide="clipboard-check" class="receipt-icon"></i>
                    </div>

                    <div class="receipt-divider"></div>

                    {{-- Unit --}}
                    <div class="unit-row">
                        <div class="unit-thumb">
                            <i data-lucide="box"></i>
                        </div>
                        <div class="unit-meta">
                            <strong>{{ $tx['unit_name'] }}</strong>
                            <small>{{ $tx['unit_code'] }} · {{ $tx['unit_type'] }}</small>
                        </div>
                    </div>

                    {{-- Borrower + qty --}}
                    <div class="detail-grid">
                        <div class="detail-field">
                            <span>Peminjam</span>
                            <strong>{{ $tx['borrower'] }}</strong>
                        </div>
                        <div class="detail-field">
                            <span>Jumlah unit</span>
                            <strong>{{ $tx['qty'] }}</strong>
                        </div>
                    </div>

                    <div class="receipt-divider"></div>

                    {{-- Dates --}}
                    <div class="detail-grid">
                        <div class="detail-field">
                            <span>Tanggal pinjam</span>
                            <strong>{{ $tx['loan_date'] }}</strong>
                        </div>
                        <div class="detail-field">
                            <span>Batas pengembalian</span>
                            <strong>{{ $tx['due_date'] }}</strong>
                        </div>
                    </div>

                    <p class="duration-note">
                        Durasi 5 hari kalender. Tanggal ditetapkan otomatis oleh sistem.
                    </p>

                    {{-- Actions --}}
                    <div class="action-row">
                        <a href="{{ route('loan.active') }}" class="btn-primary">
                            Lihat peminjaman saya
                            <i data-lucide="arrow-right"></i>
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn-link">
                            Kembali ke dashboard
                            <i data-lucide="arrow-up-right"></i>
                        </a>
                    </div>

                </article>

                {{-- Right column --}}
                <aside class="right-col">

                    {{-- Capacity warning --}}
                    @if (count($activeLoans) >= 2)
                        <div class="warn-notice">
                            <i data-lucide="info"></i>
                            <div class="warn-body">
                                <strong>Kapasitas penuh · 2 dari 2 unit</strong>
                                <p>Anda tidak dapat membuat peminjaman lain sampai salah satu unit dikembalikan dan dicatat oleh Admin.</p>
                            </div>
                        </div>
                    @else
                        <div class="warn-notice" style="background: var(--green-light); border-color: var(--border);">
                            <i data-lucide="circle-check" style="color: var(--green);"></i>
                            <div class="warn-body">
                                <strong style="color: var(--green);">Kapasitas tersedia · {{ count($activeLoans) }} dari 2 unit</strong>
                                <p>Anda masih memiliki kuota 1 entitas monster lagi jika dibutuhkan.</p>
                            </div>
                        </div>
                    @endif

                    {{-- Active loans --}}
                    <div class="active-panel">
                        <h2 class="panel-title">Peminjaman Anda sekarang</h2>

                        @foreach ($activeLoans as $index => $loan)
                            @if ($index > 0)
                                <div class="panel-divider"></div>
                            @endif

                            <div class="loan-entry">
                                <div class="loan-name-row">
                                    <strong>{{ $loan['name'] }}</strong>
                                    @if ($loan['is_new'])
                                        <span class="new-badge"><span></span>Baru</span>
                                    @endif
                                </div>
                                <span class="loan-code">{{ $loan['code'] }}</span>
                                <span class="loan-due">Batas kembali: {{ $loan['due'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="return-rule">
                        <i data-lucide="handshake"></i>
                        <div class="return-text">
                            <strong>Saat mengembalikan</strong>
                            <p>Serahkan unit secara langsung dan hubungi Admin di luar aplikasi. Status berubah setelah Admin mencatat pengembalian.</p>
                        </div>
                    </div>

                </aside>
            </div>

        </div>

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
