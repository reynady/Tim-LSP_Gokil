@extends('layouts.admin')

@section('title', 'Peminjaman Aktif')

@section('breadcrumbs')
    <span class="current">Peminjaman aktif</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Peminjaman Aktif</h1>
        <p class="page-subtitle">Daftar transaksi peminjaman berjalan yang memerlukan monitoring atau penyelesaian pengembalian fisik.</p>
    </div>
</div>

<div class="card-panel">
    <div class="card-panel-header">
        <form method="GET" action="{{ route('admin.loans.index') }}" class="filter-bar">
            <div class="search-input-group">
                <i data-lucide="search"></i>
                <input type="text" name="search" class="search-input"
                       placeholder="Cari kode transaksi, anggota, atau unit..."
                       value="{{ request('search') }}">
            </div>
            @if(request('search'))
                <a href="{{ route('admin.loans.index') }}" class="btn btn-secondary btn-sm">
                    <i data-lucide="x"></i> Reset
                </a>
            @endif
        </form>

        <div style="font-size: 13px; color: var(--muted);">
            Total: <strong>{{ $loans->total() }}</strong> transaksi aktif
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Kode Transaksi</th>
                    <th>Peminjam</th>
                    <th>Unit Monster</th>
                    <th>Tanggal Pinjam</th>
                    <th>Batas Kembali</th>
                    <th>Status</th>
                    <th style="text-align: right; width: 180px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loans as $index => $loan)
                    @php
                        $monster = $loan->details->first()?->monster;
                        $today = now()->startOfDay();
                        $due = $loan->due_date ? $loan->due_date->startOfDay() : $today;
                        $isOverdue = $today->gt($due);
                        $overdueDays = $isOverdue ? $due->diffInDays($today) : 0;
                    @endphp
                    <tr>
                        <td>{{ $loans->firstItem() + $index }}</td>
                        <td>
                            <strong style="font-family: 'IBM Plex Mono', monospace; color: var(--text);">{{ $loan->loan_code }}</strong>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column;">
                                <strong style="color: var(--text);">{{ $loan->user->name ?? 'Anggota' }}</strong>
                                <small style="color: var(--muted);">{{ $loan->user->email ?? '-' }}</small>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-weight: 500; color: var(--text);">{{ $monster->name ?? 'Entitas Monster' }}</span>
                                <small style="font-family: 'IBM Plex Mono', monospace; color: var(--muted);">{{ $monster->monster_code ?? '-' }}</small>
                            </div>
                        </td>
                        <td style="color: var(--muted); font-size: 13px;">
                            {{ $loan->loan_date ? $loan->loan_date->translatedFormat('d M Y') : '-' }}
                        </td>
                        <td style="font-size: 13px;">
                            <span style="font-weight: 500; color: {{ $isOverdue ? '#94651E' : 'var(--text)' }};">
                                {{ $loan->due_date ? $loan->due_date->translatedFormat('d M Y') : '-' }}
                            </span>
                        </td>
                        <td>
                            @if($isOverdue)
                                <span class="badge" style="background: #FBF4E6; color: #94651E; border: 1px solid #F5E5C9;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #94651E; display: inline-block;"></span>
                                    Terlambat ({{ $overdueDays }}h)
                                </span>
                            @else
                                <span class="badge" style="background: var(--green-light); color: var(--green); border: 1px solid #D2E7D8;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--green); display: inline-block;"></span>
                                    Aktif
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons" style="justify-content: flex-end;">
                                <a href="{{ route('admin.loans.return', $loan->id) }}" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                                    <i data-lucide="corner-down-left" style="width: 14px; height: 14px;"></i>
                                    <span>Proses Pengembalian</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i data-lucide="clipboard-check"></i>
                                </div>
                                <h3 class="empty-state-title">Tidak ada peminjaman aktif</h3>
                                <p class="empty-state-desc">
                                    Saat ini semua unit berada di tempat atau belum ada transaksi peminjaman yang aktif berjalan.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($loans->hasPages())
        <div class="card-panel-footer">
            {{ $loans->links() }}
        </div>
    @endif
</div>
@endsection
