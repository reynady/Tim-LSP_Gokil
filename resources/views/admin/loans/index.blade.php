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

@if(session('success'))
    <div style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="check-circle" style="width: 16px; height: 16px; flex-shrink: 0;"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div style="background: #fef2f2; border: 1px solid #f87171; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="alert-circle" style="width: 16px; height: 16px; flex-shrink: 0;"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

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
                    <th>Jadwal mulai</th>
                    <th>Jadwal selesai</th>
                    <th>Status</th>
                    <th style="text-align: right; width: 290px;">Aksi</th>
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
                            {{ $loan->scheduled_start ? $loan->scheduled_start->locale('id')->translatedFormat('d M Y H:i') . ' WIB' : '-' }}
                        </td>
                        <td style="font-size: 13px;">
                            <span style="font-weight: 500; color: {{ $isOverdue ? '#94651E' : 'var(--text)' }};">
                                {{ $loan->scheduled_end ? $loan->scheduled_end->locale('id')->translatedFormat('d M Y H:i') . ' WIB' : '-' }}
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
                            <div class="action-buttons" style="justify-content: flex-end; gap: 6px; flex-wrap: wrap;">
                                @if(!$isOverdue)
                                    <form method="POST" action="{{ route('admin.loans.simulate-overdue', $loan->id) }}"
                                          onsubmit="return confirm('Simulasikan keterlambatan untuk transaksi {{ $loan->loan_code }}? Due date akan diubah ke kemarin.');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 5px; font-size: 12px; color: #94651E; background: #FBF4E6; border: 1px solid #F5E5C9;" title="Demo: Ubah jatuh tempo ke kemarin">
                                            <i data-lucide="timer-off" style="width: 13px; height: 13px;"></i>
                                            <span>Simulasi Terlambat</span>
                                        </button>
                                    </form>
                                @endif
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
