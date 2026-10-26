<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Monster - ArsenalTrack</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<div class="app-layout">

    <aside class="sidebar">
        <a href="{{ route('dashboard') }}" class="brand">
            <span class="brand-icon"><i data-lucide="layers"></i></span>
            <span>ArsenalTrack</span>
        </a>

        <div class="nav-label">RUANG ANGGOTA</div>

        <nav class="nav-menu">
            <a href="{{ route('dashboard') }}" class="nav-link">
                <i data-lucide="layout-dashboard"></i> <span>Dashboard</span>
            </a>
            <a href="{{ route('catalog') }}" class="nav-link active">
                <i data-lucide="boxes"></i> <span>Katalog Monster</span>
            </a>
            <a href="{{ route('loan.active') }}" class="nav-link">
                <i data-lucide="clipboard-list"></i> <span>Peminjaman aktif</span>
                @php
                    $sidebarActiveCount = $activeLoansCount ?? (auth()->check() ? \App\Models\Loan::where('user_id', auth()->id())->whereIn('status', ['active', 'borrowed', 'approved', 'pending'])->count() : 0);
                @endphp
                @if ($sidebarActiveCount > 0)
                    <span class="badge" style="margin-left:auto; background: #059669; color:#fff; font-size:11px; font-weight:600; padding:2px 7px; border-radius:10px;">{{ $sidebarActiveCount }}</span>
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
            <div class="institution-title"><i data-lucide="landmark"></i> Pusat Pelatihan Arunika</div>
            <p>Sistem inventaris dan peminjaman monster untuk anggota terdaftar.</p>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout-button" type="submit">
                <i data-lucide="log-out"></i> Keluar
            </button>
        </form>
    </aside>

    <main class="main-area">
        <header class="topbar">
            <div class="breadcrumb">
                <span>Anggota</span>
                <span>/</span>
                <strong>Katalog Monster</strong>
            </div>

            <div class="account">
                <span class="account-caption">Portal inventaris anggota</span>
                <span class="account-divider"></span>
                <div class="avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="account-info">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>Anggota</small>
                </div>
            </div>
        </header>

        <div class="page-content">
            <section class="page-heading">
                <div>
                    <h1>Katalog Monster</h1>
                    <p>Jelajahi unit monster dan periksa ketersediaannya sebelum mengajukan peminjaman.</p>
                </div>
            </section>

            <section class="notice">
                <span class="notice-icon">✓</span>
                <div>
                    <strong>Informasi ketersediaan unit</strong>
                    <p>Pilih unit dan tentukan jadwal peminjaman. Unit yang sudah dipesan dapat dipinjam pada jadwal lain yang tidak bertabrakan; status Dipinjam mencakup reservasi mendatang.</p>
                </div>
            </section>

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

            @php
                $monstersList = isset($monsters) ? $monsters : \App\Models\Monster::with('categories')->get();
            @endphp

            <section class="inventory-panel">
                <div class="filter-bar">
                    <label class="search-box">
                        <span>⌕</span>
                        <input type="search" id="searchMonster"
                               placeholder="Cari nama atau kode unit...">
                    </label>

                    <select id="statusFilter" aria-label="Filter ketersediaan">
                        <option value="">Semua status</option>
                        <option value="Tersedia">Tersedia</option>
                        <option value="Dipinjam">Dipinjam</option>
                        <option value="Perawatan">Perawatan</option>
                    </select>

                    <span class="unit-count" id="unitCount">
                        {{ count($monstersList) }} unit ditemukan
                    </span>
                </div>

                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>KODE UNIT</th>
                                <th>NAMA UNIT</th>
                                <th>KATEGORI</th>
                                <th>STATUS</th>
                                <th>KONDISI / LOKASI</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="monsterTable">
                            @foreach ($monstersList as $m)
                                @php
                                    $isAvail = ($m->status === 'available');
                                    $statusLabel = $isAvail ? 'Tersedia' : ($m->status === 'maintenance' ? 'Perawatan' : 'Dipinjam');
                                    $categoryLabel = $m->categories->isNotEmpty() 
                                        ? $m->categories->pluck('name')->implode(', ') 
                                        : ($m->origin_type ?? 'Anomali');
                                @endphp
                                <tr data-name="{{ strtolower($m->name) }}"
                                    data-code="{{ strtolower($m->monster_code) }}"
                                    data-status="{{ $statusLabel }}">
                                    <td class="unit-code">{{ $m->monster_code }}</td>
                                    <td>
                                        <div class="monster-name">
                                            <img class="monster-icon" src="{{ $m->imageUrl() }}" alt="Ilustrasi {{ $m->name }}" loading="lazy">
                                            <span>{{ $m->name }}</span>
                                        </div>
                                    </td>
                                    <td class="muted-cell">{{ $categoryLabel }}</td>
                                    <td>
                                        <span class="status-pill {{ $isAvail ? 'available' : 'borrowed' }}">
                                            <span class="status-dot"></span>
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="muted-cell">{{ $m->condition ?? 'Hanggar Utama' }}</td>
                                    <td>
                                        @if (in_array($m->status, ['available', 'borrowed'], true))
                                            <a href="{{ route('unit.detail', ['code' => $m->monster_code]) }}"
                                                class="detail-button">
                                                Lihat unit <span>→</span>
                                            </a>
                                        @else
                                            <span class="unavailable-link">Tidak tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div id="emptyState" class="empty-state" hidden>
                        <span class="empty-icon">⌕</span>
                        <strong>Monster tidak ditemukan</strong>
                        <p>Coba ubah kata pencarian atau filter status.</p>
                        <button type="button" id="resetFilters" class="reset-button">
                            Reset pencarian
                        </button>
                    </div>
                </div>

                <footer class="table-footer">
                    <span>Data katalog contoh untuk pengembangan</span>
                    <span>ArsenalTrack · Inventaris Monster</span>
                </footer>
            </section>

            <footer class="page-footer">
                <span>ArsenalTrack · Portal Anggota</span>
                <span>Gunakan unit sesuai ketentuan peminjaman.</span>
            </footer>
        </div>
    </main>
</div>

<script>
    const searchInput = document.getElementById('searchMonster');
    const statusFilter = document.getElementById('statusFilter');
    const rows = [...document.querySelectorAll('#monsterTable tr')];
    const unitCount = document.getElementById('unitCount');
    const emptyState = document.getElementById('emptyState');
    const table = document.querySelector('table');

    function filterMonsters() {
        const query = searchInput.value.trim().toLowerCase();
        const status = statusFilter.value;
        let visible = 0;

        rows.forEach(row => {
            const matchesText =
                row.dataset.name.includes(query) ||
                row.dataset.code.includes(query);
            const matchesStatus = !status || row.dataset.status === status;
            const show = matchesText && matchesStatus;

            row.hidden = !show;
            if (show) visible++;
        });

        unitCount.textContent = `${visible} unit ditemukan`;
        emptyState.hidden = visible !== 0;
        table.hidden = visible === 0;
    }

    searchInput.addEventListener('input', filterMonsters);
    statusFilter.addEventListener('change', filterMonsters);

    document.getElementById('resetFilters').addEventListener('click', () => {
        searchInput.value = '';
        statusFilter.value = '';
        filterMonsters();
    });

    if (window.lucide) {
        lucide.createIcons();
    }
</script>
</body>
</html>
