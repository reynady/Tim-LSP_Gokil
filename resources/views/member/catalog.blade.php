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
                    <p>Pastikan status unit tersedia sebelum melanjutkan proses peminjaman. Unit yang sedang dipinjam tidak dapat dipilih.</p>
                </div>
            </section>

            @php
                /*
                 * Data sementara untuk tampilan.
                 * Nantinya dapat diganti dengan data dari tabel monsters.
                 */
                $monsters = [
                    ['code' => 'MON-001', 'name' => 'Kaiju Alpha', 'category' => 'Kelas I', 'location' => 'Hanggar A-01', 'status' => 'Tersedia'],
                    ['code' => 'MON-002', 'name' => 'Kaiju Beta', 'category' => 'Kelas II', 'location' => 'Hanggar A-02', 'status' => 'Tersedia'],
                    ['code' => 'MON-003', 'name' => 'Guardian Unit', 'category' => 'Pertahanan', 'location' => 'Hanggar B-01', 'status' => 'Tersedia'],
                    ['code' => 'MON-004', 'name' => 'SCP Sentinel', 'category' => 'Pengawasan', 'location' => 'Sektor C-04', 'status' => 'Dipinjam'],
                    ['code' => 'MON-005', 'name' => 'Titan Striker', 'category' => 'Kelas III', 'location' => 'Hanggar B-03', 'status' => 'Dipinjam'],
                    ['code' => 'MON-006', 'name' => 'Mecha Ranger', 'category' => 'Pendukung', 'location' => 'Hanggar C-02', 'status' => 'Tersedia'],
                    ['code' => 'MON-007', 'name' => 'Containment Unit', 'category' => 'Pengamanan', 'location' => 'Sektor D-01', 'status' => 'Tersedia'],
                    ['code' => 'MON-008', 'name' => 'Kaiju Omega', 'category' => 'Kelas Khusus', 'location' => 'Hanggar X-01', 'status' => 'Dipinjam'],
                ];
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
                    </select>

                    <span class="unit-count" id="unitCount">
                        {{ count($monsters) }} unit ditemukan
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
                                <th>LOKASI</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="monsterTable">
                            @foreach ($monsters as $monster)
                                <tr data-name="{{ strtolower($monster['name']) }}"
                                    data-code="{{ strtolower($monster['code']) }}"
                                    data-status="{{ $monster['status'] }}">
                                    <td class="unit-code">{{ $monster['code'] }}</td>
                                    <td>
                                        <div class="monster-name">
                                            <span class="monster-icon">◇</span>
                                            <span>{{ $monster['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="muted-cell">{{ $monster['category'] }}</td>
                                    <td>
                                        <span class="status-pill {{ $monster['status'] === 'Tersedia' ? 'available' : 'borrowed' }}">
                                            <span class="status-dot"></span>
                                            {{ $monster['status'] }}
                                        </span>
                                    </td>
                                    <td class="muted-cell">{{ $monster['location'] }}</td>
                                    <td>
                                        @if ($monster['status'] === 'Tersedia')
                                            <a href="{{ route('unit.detail', ['code' => $monster['code']]) }}"
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
