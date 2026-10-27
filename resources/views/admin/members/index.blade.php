@extends('layouts.admin')

@section('title', 'Daftar Anggota')

@section('breadcrumbs')
    <span class="current">Daftar Anggota</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Daftar Anggota</h1>
        <p class="page-subtitle">Kelola pendaftaran anggota, validasi akun, status keaktifan, dan kontrol keanggotaan (FR-004, BR-003, BR-004).</p>
    </div>
    <div>
        <a href="{{ route('admin.members.create') }}" class="btn btn-primary">
            <i data-lucide="user-plus"></i> Tambah Anggota Baru
        </a>
    </div>
</div>

<div class="card-panel">
    <div class="card-panel-header">
        <form method="GET" action="{{ route('admin.members.index') }}" class="filter-bar">
            {{-- Search Bar Nama / Email (BR-003, BR-004) --}}
            <div class="search-input-group">
                <i data-lucide="search"></i>
                <input type="text" name="search" class="search-input"
                       placeholder="Cari nama atau email anggota..."
                       value="{{ request('search') }}">
            </div>

            {{-- Filter Status Akun --}}
            <select name="status" class="select-filter" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Validasi</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>

            <button type="submit" class="btn btn-secondary btn-sm">
                <i data-lucide="filter"></i> Filter
            </button>

            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('admin.members.index') }}" class="btn btn-secondary btn-sm" title="Reset semua filter">
                    <i data-lucide="x"></i> Reset
                </a>
            @endif
        </form>

        <div style="font-size: 13px; color: var(--muted);">
            Menampilkan: <strong>{{ $members->total() }}</strong> anggota
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Anggota</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Peminjaman Aktif</th>
                    <th>Status Akun</th>
                    <th style="text-align: right; width: 190px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                    @php
                        $statusBadge = match ($member->approval_status) {
                            'pending' => ['class' => 'badge-status-borrowed', 'label' => 'Menunggu Validasi', 'icon' => 'clock-3'],
                            'rejected' => ['class' => 'badge-status-maintenance', 'label' => 'Pendaftaran Ditolak', 'icon' => 'x-circle'],
                            default => $member->is_active
                                ? ['class' => 'badge-status-available', 'label' => 'Aktif', 'icon' => 'check-circle-2']
                                : ['class' => 'badge-status-maintenance', 'label' => 'Nonaktif', 'icon' => 'x-circle'],
                        };
                    @endphp
                    <tr>
                        <td>
                            <span style="color: var(--muted); font-size: 13px;">{{ $loop->iteration + ($members->currentPage() - 1) * $members->perPage() }}</span>
                        </td>
                        <td>
                            <strong style="color: var(--text); font-size: 14px;">{{ $member->name }}</strong>
                            @if($member->profile && $member->profile->address)
                                <div style="font-size: 11px; color: var(--muted); margin-top: 2px;">
                                    {{ Str::limit($member->profile->address, 40) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="mono-code">{{ $member->email }}</span>
                        </td>
                        <td>
                            @if($member->profile && $member->profile->phone)
                                <span style="font-size: 13px;">{{ $member->profile->phone }}</span>
                            @else
                                <span style="color: var(--muted); font-size: 12px;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($member->active_loans_count > 0)
                                <span class="badge badge-status-borrowed">
                                    <i data-lucide="clipboard-list" style="width: 12px; height: 12px;"></i>
                                    {{ $member->active_loans_count }} aktif
                                </span>
                            @else
                                <span style="color: var(--muted); font-size: 12px;">0 aktif</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $statusBadge['class'] }}">
                                <i data-lucide="{{ $statusBadge['icon'] }}" style="width: 12px; height: 12px;"></i>
                                {{ $statusBadge['label'] }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons" style="justify-content: flex-end;">
                                @if(in_array($member->approval_status, ['pending', 'rejected'], true))
                                    <form method="POST" action="{{ route('admin.members.review-registration', $member) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="decision" value="approve">
                                        <button type="submit" class="btn btn-secondary btn-sm" title="Setujui pendaftaran dan aktifkan akun">
                                            <i data-lucide="user-check"></i> Setujui
                                        </button>
                                    </form>
                                @endif
                                @if($member->approval_status === 'pending')
                                    <form method="POST" action="{{ route('admin.members.review-registration', $member) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="decision" value="reject">
                                        <button type="submit" class="btn btn-danger-soft btn-sm" title="Tolak pendaftaran">
                                            <i data-lucide="user-x"></i> Tolak
                                        </button>
                                    </form>
                                @endif

                                {{-- Toggle Status --}}
                                @if($member->approval_status === 'approved')
                                <form method="POST" action="{{ route('admin.members.toggle-status', $member) }}"
                                      style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-secondary btn-sm"
                                            title="{{ $member->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}">
                                        <i data-lucide="{{ $member->is_active ? 'user-x' : 'user-check' }}"></i>
                                    </button>
                                </form>
                                @endif

                                {{-- Edit --}}
                                <a href="{{ route('admin.members.edit', $member) }}" class="btn btn-secondary btn-sm" title="Ubah Data Anggota">
                                    <i data-lucide="pencil"></i> Edit
                                </a>

                                {{-- Hapus --}}
                                <form method="POST" action="{{ route('admin.members.destroy', $member) }}"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus anggota \'{{ $member->name }}\'?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-soft btn-sm"
                                            title="{{ $member->active_loans_count > 0 ? 'Tidak dapat menghapus: masih memiliki peminjaman aktif' : 'Hapus Anggota' }}"
                                            {{ $member->active_loans_count > 0 ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }}>
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
                                    <i data-lucide="users"></i>
                                </div>
                                <h3 class="empty-state-title">Belum ada anggota terdaftar</h3>
                                <p class="empty-state-desc">
                                    @if(request()->anyFilled(['search', 'status']))
                                        Tidak ada anggota yang cocok dengan filter yang Anda gunakan. Coba ubah atau reset filter.
                                    @else
                                        Sistem belum memiliki data anggota. Daftarkan anggota pertama untuk memulai.
                                    @endif
                                </p>
                                <a href="{{ route('admin.members.create') }}" class="btn btn-primary btn-sm">
                                    <i data-lucide="user-plus"></i> Tambah Anggota Sekarang
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($members->hasPages())
        <div class="pagination-wrapper">
            {{ $members->links() }}
        </div>
    @endif
</div>
@endsection
