@extends('layouts.admin')

@section('title', 'Dashboard Administrator')

@section('breadcrumbs')
    <span class="current">Dashboard</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard Administrator</h1>
        <p class="page-subtitle">Ringkasan inventaris monster, status ketersediaan unit, dan kontrol master data ArsenalTrack.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('admin.categories.create') }}" class="btn btn-secondary">
            <i data-lucide="tag"></i> Tambah Kategori
        </a>
        <a href="{{ route('admin.monsters.create') }}" class="btn btn-primary">
            <i data-lucide="plus"></i> Tambah Monster
        </a>
    </div>
</div>

{{-- Stat Cards --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Total Monster</span>
            <span class="stat-value">{{ $totalMonsters }}</span>
            <span class="stat-desc">Unit terdaftar di sistem</span>
        </div>
        <div class="stat-icon">
            <i data-lucide="boxes"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Monster Tersedia</span>
            <span class="stat-value" style="color: var(--status-avail-txt);">{{ $availableMonsters }}</span>
            <span class="stat-desc">Siap untuk dipinjam anggota</span>
        </div>
        <div class="stat-icon" style="background: var(--status-avail-bg); color: var(--status-avail-txt);">
            <i data-lucide="check-circle-2"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Monster Dipinjam</span>
            <span class="stat-value" style="color: var(--status-borrow-txt);">{{ $borrowedMonsters }}</span>
            <span class="stat-desc">Sedang aktif di luar hanggar</span>
        </div>
        <div class="stat-icon" style="background: var(--status-borrow-bg); color: var(--status-borrow-txt);">
            <i data-lucide="clock"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <span class="stat-label">Total Kategori</span>
            <span class="stat-value">{{ $totalCategories }}</span>
            <span class="stat-desc">Klasifikasi elemen & tipe</span>
        </div>
        <div class="stat-icon" style="background: #F0FDF4; color: var(--green);">
            <i data-lucide="tags"></i>
        </div>
    </div>
</div>

{{-- Panel Monster Terbaru --}}
<div class="card-panel">
    <div class="card-panel-header">
        <h2 class="card-panel-title">
            <i data-lucide="sparkles" style="color: var(--green); width: 18px; height: 18px;"></i>
            Unit Monster Terbaru di Inventaris
        </h2>
        <a href="{{ route('admin.monsters.index') }}" class="btn btn-secondary btn-sm">
            Lihat Seluruh Monster <i data-lucide="arrow-right"></i>
        </a>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 110px;">Kode Unit</th>
                    <th>Nama Monster</th>
                    <th>Kategori</th>
                    <th>Tingkat Ancaman</th>
                    <th>Status Unit</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentMonsters as $monster)
                    @php
                        $threatClass = match(strtolower($monster->threat_level)) {
                            'low' => 'badge-threat-low',
                            'medium' => 'badge-threat-medium',
                            'high' => 'badge-threat-high',
                            'extreme' => 'badge-threat-extreme',
                            default => 'badge-threat-unknown',
                        };

                        $statusBadge = match($monster->status) {
                            'available' => ['class' => 'badge-status-available', 'label' => 'Tersedia'],
                            'borrowed' => ['class' => 'badge-status-borrowed', 'label' => 'Dipinjam'],
                            'maintenance' => ['class' => 'badge-status-maintenance', 'label' => 'Perawatan'],
                            default => ['class' => 'badge-status-maintenance', 'label' => ucfirst($monster->status)],
                        };
                    @endphp
                    <tr>
                        <td>
                            <span class="mono-code">{{ $monster->monster_code }}</span>
                        </td>
                        <td>
                            <strong style="color: var(--text);">{{ $monster->name }}</strong>
                            <div style="font-size: 11px; color: var(--muted);">{{ $monster->origin_type }}</div>
                        </td>
                        <td>
                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                @forelse($monster->categories as $category)
                                    <span class="category-pill">{{ $category->name }}</span>
                                @empty
                                    <span style="color: var(--muted); font-size: 11px;">-</span>
                                @endforelse
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $threatClass }}">
                                {{ $monster->threat_level }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $statusBadge['class'] }}">
                                {{ $statusBadge['label'] }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons" style="justify-content: flex-end;">
                                <a href="{{ route('admin.monsters.edit', $monster) }}" class="btn btn-secondary btn-sm">
                                    <i data-lucide="pencil"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i data-lucide="boxes"></i>
                                </div>
                                <h3 class="empty-state-title">Belum ada monster terdaftar</h3>
                                <p class="empty-state-desc">Tambahkan unit monster pertama ke sistem untuk memulai manajemen inventaris.</p>
                                <a href="{{ route('admin.monsters.create') }}" class="btn btn-primary btn-sm">
                                    <i data-lucide="plus"></i> Tambah Monster
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
