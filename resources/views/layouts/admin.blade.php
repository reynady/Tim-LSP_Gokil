<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - ArsenalTrack</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    @stack('styles')
</head>
<body>
<div class="app-layout">

    {{-- Sidebar Admin --}}
    <aside class="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="brand">
            <span class="brand-icon"><i data-lucide="layers"></i></span>
            <span>ArsenalTrack</span>
        </a>

        <div class="nav-section">
            <div class="nav-label">RUANG ADMIN</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.members.index') }}" class="nav-link {{ request()->routeIs('admin.members.*') ? 'active' : '' }}">
                <i data-lucide="users"></i> <span>Anggota</span>
            </a>
            <a href="{{ route('admin.monsters.index') }}" class="nav-link {{ request()->routeIs('admin.monsters.*') ? 'active' : '' }}">
                <i data-lucide="boxes"></i> <span>Unit</span>
            </a>
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i data-lucide="tags"></i> <span>Kategori</span>
            </a>
            <a href="{{ route('admin.loans.index') }}" class="nav-link {{ request()->routeIs('admin.loans.*') ? 'active' : '' }}">
                <i data-lucide="archive"></i> <span>Peminjaman aktif</span>
            </a>
            <a href="{{ route('admin.fines.index') }}" class="nav-link {{ request()->routeIs('admin.fines.*') ? 'active' : '' }}">
                <i data-lucide="calendar-clock"></i> <span>Keterlambatan & denda</span>
            </a>
            <a href="{{ route('admin.loans.history') }}" class="nav-link {{ request()->routeIs('admin.loans.history*') ? 'active' : '' }}">
                <i data-lucide="history"></i> <span>Riwayat</span>
            </a>
            <a href="{{ route('profile') }}" class="nav-link {{ request()->routeIs('profile*') ? 'active' : '' }}">
                <i data-lucide="user"></i> <span>Profil saya</span>
            </a>
        </div>

        <div class="sidebar-spacer"></div>

        <div class="institution-card">
            <div class="institution-card-header">
                <i data-lucide="shield-check" style="width: 16px; height: 16px; color: #4ADE80;"></i>
                <span>Pusat Pelatihan Arunika</span>
            </div>
            <span class="institution-card-desc">Lingkungan simulasi akademik. Seluruh data bersifat fiktif.</span>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-button" type="submit">
                <i data-lucide="log-out"></i> Keluar
            </button>
        </form>
    </aside>

    {{-- Main Area --}}
    <main class="main-area">
        {{-- Topbar Header --}}
        <header class="topbar">
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <span>Ruang Admin</span>
                <span class="separator">/</span>
                @yield('breadcrumbs')
            </nav>

            <div class="topbar-right">
                <div class="topbar-date">
                    {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                </div>
                <div class="topbar-divider"></div>

                @php
                    $admin = auth()->user();
                    $initial = $admin ? strtoupper(substr($admin->name ?? 'AL', 0, 2)) : 'AL';
                @endphp
                <div class="user-profile-menu">
                    <div class="user-avatar-circle">{{ $initial }}</div>
                    <div class="user-meta-block">
                        <span class="user-meta-name">{{ $admin->name ?? 'Aditya Laksono' }}</span>
                        <span class="user-meta-role">Admin</span>
                    </div>
                    <i data-lucide="chevron-down" style="width: 14px; height: 14px; color: #6D7972;"></i>
                </div>
            </div>
        </header>

        {{-- Content Area --}}
        <div class="content-container">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success">
                    <i data-lucide="check-circle-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i data-lucide="alert-triangle"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i data-lucide="alert-circle"></i>
                    <div>
                        <strong>Terdapat beberapa kesalahan input:</strong>
                        <ul style="margin: 6px 0 0; padding-left: 18px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        lucide.createIcons();
    });
</script>
@stack('scripts')
</body>
</html>
