@extends('layouts.admin')

@section('title', 'Kelola Inventaris Monster')

@section('breadcrumbs')
    <span class="current">Inventaris Monster</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Kelola Inventaris Monster</h1>
        <p class="page-subtitle">Manajemen data unit monster, tingkat ancaman, status ketersediaan, dan kategori (FR-005, BR-005, BR-006, BR-007, BR-009).</p>
    </div>
    <div>
        <a href="{{ route('admin.monsters.create') }}" class="btn btn-primary">
            <i data-lucide="plus"></i> Tambah Monster Baru
        </a>
    </div>
</div>

<div class="card-panel">
    <div class="card-panel-header">
        <form method="GET" action="{{ route('admin.monsters.index') }}" class="filter-bar">
            {{-- Search Bar Nama (BR-008) & Kode (BR-007) --}}
            <div class="search-input-group">
                <i data-lucide="search"></i>
                <input type="text" name="search" class="search-input"
                       placeholder="Cari nama atau kode unit..."
                       value="{{ request('search') }}">
            </div>

            {{-- Filter Status --}}
            <select name="status" class="select-filter" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Tersedia (Available)</option>
                <option value="borrowed" {{ request('status') === 'borrowed' ? 'selected' : '' }}>Dipinjam (Borrowed)</option>
                <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Karantina / Perawatan</option>
            </select>

            {{-- Filter Kategori --}}
            <select name="category_id" class="select-filter" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>

            {{-- Filter Threat Level --}}
            <select name="threat_level" class="select-filter" onchange="this.form.submit()">
                <option value="">Semua Tingkat Ancaman</option>
                <option value="Low" {{ request('threat_level') === 'Low' ? 'selected' : '' }}>Low</option>
                <option value="Medium" {{ request('threat_level') === 'Medium' ? 'selected' : '' }}>Medium</option>
                <option value="High" {{ request('threat_level') === 'High' ? 'selected' : '' }}>High</option>
                <option value="Extreme" {{ request('threat_level') === 'Extreme' ? 'selected' : '' }}>Extreme</option>
            </select>

            <button type="submit" class="btn btn-secondary btn-sm">
                <i data-lucide="filter"></i> Filter
            </button>

            @if(request()->anyFilled(['search', 'status', 'category_id', 'threat_level']))
                <a href="{{ route('admin.monsters.index') }}" class="btn btn-secondary btn-sm" title="Reset semua filter">
                    <i data-lucide="x"></i> Reset
                </a>
            @endif
        </form>

        <div style="font-size: 13px; color: var(--muted);">
            Menampilkan: <strong>{{ $monsters->total() }}</strong> unit monster
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 110px;">Kode Unit</th>
                    <th>Nama Monster</th>
                    <th>Kategori (Multi)</th>
                    <th>Tingkat Ancaman</th>
                    <th>Kondisi / Tipe</th>
                    <th>Status Unit</th>
                    <th style="text-align: right; width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monsters as $monster)
                    @php
                        $threatClass = match(strtolower($monster->threat_level)) {
                            'low' => 'badge-threat-low',
                            'medium' => 'badge-threat-medium',
                            'high' => 'badge-threat-high',
                            'extreme' => 'badge-threat-extreme',
                            default => 'badge-threat-unknown',
                        };

                        $statusBadge = match($monster->status) {
                            'available' => ['class' => 'badge-status-available', 'label' => 'Tersedia', 'icon' => 'check-circle-2'],
                            'borrowed' => ['class' => 'badge-status-borrowed', 'label' => 'Dipinjam', 'icon' => 'clock'],
                            'maintenance' => ['class' => 'badge-status-maintenance', 'label' => 'Perawatan', 'icon' => 'wrench'],
                            default => ['class' => 'badge-status-maintenance', 'label' => ucfirst($monster->status), 'icon' => 'help-circle'],
                        };
                    @endphp
                    <tr>
                        <td>
                            <span class="mono-code">{{ $monster->monster_code }}</span>
                        </td>
                        <td>
                            <strong style="color: var(--text); font-size: 14px;">{{ $monster->name }}</strong>
                            @if($monster->origin_type)
                                <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">
                                    Tipe Asal: {{ $monster->origin_type }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; flex-wrap: wrap; gap: 4px; max-width: 250px;">
                                @forelse($monster->categories as $category)
                                    <span class="category-pill">{{ $category->name }}</span>
                                @empty
                                    <span style="color: var(--muted); font-size: 11px;">- Belum ada kategori -</span>
                                @endforelse
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $threatClass }}">
                                <i data-lucide="shield-alert" style="width: 12px; height: 12px;"></i>
                                {{ $monster->threat_level }}
                            </span>
                        </td>
                        <td>
                            <div style="font-size: 13px; color: var(--text);">{{ $monster->condition ?? 'Contained' }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $statusBadge['class'] }}">
                                <i data-lucide="{{ $statusBadge['icon'] }}" style="width: 12px; height: 12px;"></i>
                                {{ $statusBadge['label'] }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons" style="justify-content: flex-end;">
                                <a href="{{ route('admin.monsters.edit', $monster) }}" class="btn btn-secondary btn-sm" title="Ubah Monster">
                                    <i data-lucide="pencil"></i> Edit
                                </a>

                                <form method="POST" action="{{ route('admin.monsters.destroy', $monster) }}"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus monster \'{{ $monster->name }}\' [{{ $monster->monster_code }}]?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-soft btn-sm"
                                            title="{{ $monster->status === 'borrowed' ? 'Tidak dapat menghapus unit yang sedang dipinjam' : 'Hapus Monster' }}"
                                            {{ $monster->status === 'borrowed' ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }}>
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i data-lucide="boxes"></i>
                                </div>
                                <h3 class="empty-state-title">Tidak ada monster ditemukan</h3>
                                <p class="empty-state-desc">
                                    @if(request()->anyFilled(['search', 'status', 'category_id', 'threat_level']))
                                        Tidak ada monster yang cocok dengan filter yang Anda gunakan. Coba ubah atau reset filter.
                                    @else
                                        Inventaris Anda masih kosong. Daftarkan unit monster pertama Anda sekarang.
                                    @endif
                                </p>
                                <a href="{{ route('admin.monsters.create') }}" class="btn btn-primary btn-sm">
                                    <i data-lucide="plus"></i> Tambah Monster Sekarang
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($monsters->hasPages())
        <div class="pagination-wrapper">
            {{ $monsters->links() }}
        </div>
    @endif
</div>
@endsection
