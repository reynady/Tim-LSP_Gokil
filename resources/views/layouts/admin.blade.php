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
            <span class="brand-badge">ADMIN</span>
        </a>

        <div class="nav-section">
            <div class="nav-label">Menu Utama</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard"></i> <span>Dashboard</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Master Data</div>
            <a href="{{ route('admin.monsters.index') }}" class="nav-link {{ request()->routeIs('admin.monsters.*') ? 'active' : '' }}">
                <i data-lucide="boxes"></i> <span>Kelola Monster</span>
            </a>
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i data-lucide="tags"></i> <span>Kelola Kategori</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Navigasi Luar</div>
            <a href="{{ route('dashboard') }}" class="nav-link" target="_blank" title="Buka Portal Anggota di tab baru">
                <i data-lucide="external-link"></i> <span>Portal Anggota</span>
            </a>
        </div>

        <div class="sidebar-spacer"></div>

        <div class="admin-card">
            <div class="admin-card-title">
                <i data-lucide="shield-check"></i> Mode Administrator
            </div>
            <span>Hak akses penuh terhadap Master Data inventaris & kategori monster.</span>
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
                <a href="{{ route('admin.dashboard') }}"><i data-lucide="home" style="width: 14px; height: 14px;"></i></a>
                <span class="separator">/</span>
                @yield('breadcrumbs')
            </nav>

            <div class="topbar-right">
                @php
                    $admin = auth()->user();
                    $initial = $admin ? strtoupper(substr($admin->name ?? 'AD', 0, 2)) : 'AD';
                @endphp
                <div class="user-chip">
                    <div class="user-avatar">{{ $initial }}</div>
                    <div class="user-meta">
                        <span class="user-name">{{ $admin->name ?? 'Administrator' }}</span>
                        <span class="user-role">Administrator</span>
                    </div>
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
