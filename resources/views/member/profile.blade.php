<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - ArsenalTrack</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
@php
    $user = auth()->user();
    $userName = $user->name ?? 'Raka Pratama';
    $userEmail = $user->email ?? 'raka@arunika.ac.id';
    $words = explode(' ', trim($userName));
    $initial = count($words) >= 2
        ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
        : strtoupper(substr($userName, 0, 2));

    $activeCount = $user ? \App\Models\Loan::where('user_id', $user->id)
        ->whereIn('status', ['active', 'borrowed', 'approved', 'pending'])
        ->count() : 0;
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
            <a href="{{ route('loan.history') }}" class="nav-link">
                <i data-lucide="history"></i><span>Riwayat</span>
            </a>
            <a href="{{ route('profile') }}" class="nav-link active">
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
                <strong>Profil saya</strong>
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
                <h1>Profil saya</h1>
                <p>Informasi identitas akun anggota dan pengaturan data diri Anda.</p>
            </section>

            {{-- Informasi (Perubahan tersimpan) --}}
            <div class="success-notice">
                <i data-lucide="circle-check"></i>
                <div class="success-body">
                    <strong>Perubahan tersimpan</strong>
                    <p>Data profil Anda berhasil diperbarui dan telah disinkronkan ke seluruh sistem.</p>
                </div>
            </div>

            {{-- Profil dan informasi akun --}}
            <div class="profile-grid">

                {{-- Formulir profil (Kiri) --}}
                <div class="profile-form-card">

                    {{-- Identitas anggota --}}
                    <div class="member-identity">
                        <div class="avatar-large">{{ $initial }}</div>
                        <div class="member-meta">
                            <span class="member-name">{{ $userName }}</span>
                            <span class="member-email">{{ $userEmail }}</span>
                        </div>
                        <div class="badge-active">
                            <span class="dot"></span>
                            <span>Aktif</span>
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    {{-- Informasi dasar heading --}}
                    <div class="form-section-header">
                        <h2>Informasi dasar</h2>
                        <p>Perbarui informasi nama lengkap Anda jika diperlukan.</p>
                    </div>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('profile.update') }}" style="display: flex; flex-direction: column; gap: 20px;">
                        @csrf

                        {{-- Kolom Nama --}}
                        <div class="form-group">
                            <label class="form-label" for="userNameInput">Nama lengkap</label>
                            <div class="form-input-box">
                                <input type="text" id="userNameInput" name="name" value="{{ $userName }}" required>
                            </div>
                            <span class="form-help">Nama ini akan ditampilkan pada seluruh bukti peminjaman dan arsip transaksi.</span>
                        </div>

                        {{-- Kolom Email --}}
                        <div class="form-group">
                            <label class="form-label" for="userEmailInput">Email</label>
                            <div class="form-input-box">
                                <input type="email" id="userEmailInput" value="{{ $userEmail }}" readonly>
                                <div class="email-verified">
                                    <i data-lucide="circle-check"></i>
                                    <span>Terverifikasi</span>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom Peran akun --}}
                        <div class="form-group">
                            <label class="form-label">Peran akun</label>
                            <div class="form-input-box readonly">
                                <input type="text" value="Anggota" readonly>
                                <i data-lucide="lock-keyhole"></i>
                            </div>
                            <span class="form-help">Peran dan hak akses dikelola sepenuhnya oleh Admin Pusat Pelatihan Arunika.</span>
                        </div>

                        <div class="form-divider"></div>

                        {{-- Aksi profil --}}
                        <div class="form-actions">
                            <span class="sync-status">Terakhir disinkronkan hari ini</span>
                            <div class="action-buttons">
                                <a href="{{ route('profile') }}" class="btn-secondary">Batal</a>
                                <button type="submit" class="btn-primary">Simpan profil</button>
                            </div>
                        </div>

                    </form>

                </div>

                {{-- Panduan akun (Kanan) --}}
                <aside class="profile-guide-col">

                    {{-- Tentang akun Anda --}}
                    <div class="about-card">
                        <h2 class="about-title">Tentang akun Anda</h2>

                        <div class="guide-item">
                            <i data-lucide="user-round" class="guide-icon"></i>
                            <div class="guide-text">
                                <strong>Akses peminjaman</strong>
                                <p>Maksimal peminjaman 2 unit aktif dalam satu waktu.</p>
                            </div>
                        </div>

                        <div class="guide-item">
                            <i data-lucide="mail" class="guide-icon"></i>
                            <div class="guide-text">
                                <strong>Notifikasi email</strong>
                                <p>Pemberitahuan batas waktu pengembalian dikirim ke email terdaftar.</p>
                            </div>
                        </div>

                        <div class="guide-item">
                            <i data-lucide="lock-keyhole" class="guide-icon"></i>
                            <div class="guide-text">
                                <strong>Keamanan akun</strong>
                                <p>Hubungi Admin jika ingin mengganti kata sandi atau memperbarui data institusi.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Bantuan anggota --}}
                    <div class="support-card">
                        <i data-lucide="messages-square"></i>
                        <strong>Butuh bantuan akun?</strong>
                        <p>Silakan hubungi tim Admin di ruang operasional inventaris jika menemui kendala terkait akun atau unit yang dipinjam.</p>
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
