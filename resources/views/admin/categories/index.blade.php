@extends('layouts.admin')

@section('title', 'Kelola Kategori Monster')

@section('breadcrumbs')
    <span class="current">Kategori Monster</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Kelola Kategori Monster</h1>
        <p class="page-subtitle">Daftar kategori unit untuk klasifikasi dan pengelompokan inventaris (FR-006, BR-009).</p>
    </div>
    <div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i data-lucide="plus"></i> Tambah Kategori
        </a>
    </div>
</div>

<div class="card-panel">
    <div class="card-panel-header">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="filter-bar">
            <div class="search-input-group">
                <i data-lucide="search"></i>
                <input type="text" name="search" class="search-input"
                       placeholder="Cari nama atau deskripsi..."
                       value="{{ request('search') }}">
            </div>
            @if(request('search'))
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm">
                    <i data-lucide="x"></i> Reset
                </a>
            @endif
        </form>

        <div style="font-size: 13px; color: var(--muted);">
            Total: <strong>{{ $categories->total() }}</strong> kategori terdaftar
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th style="text-align: center;">Jumlah Unit</th>
                    <th>Tanggal Dibuat</th>
                    <th style="text-align: right; width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $index => $category)
                    <tr>
                        <td>{{ $categories->firstItem() + $index }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--green);"></span>
                                <strong style="color: var(--text);">{{ $category->name }}</strong>
                            </div>
                        </td>
                        <td>
                            <span style="color: var(--muted);">{{ $category->description ?? '-' }}</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge" style="background: var(--soft); border: 1px solid var(--border); color: var(--text);">
                                <i data-lucide="boxes" style="width: 12px; height: 12px; color: var(--green);"></i>
                                {{ $category->monsters_count }} unit
                            </span>
                        </td>
                        <td style="color: var(--muted); font-size: 12px;">
                            {{ $category->created_at ? $category->created_at->format('d M Y, H:i') : '-' }}
                        </td>
                        <td>
                            <div class="action-buttons" style="justify-content: flex-end;">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-secondary btn-sm" title="Ubah Kategori">
                                    <i data-lucide="pencil"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori \'{{ $category->name }}\'?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-soft btn-sm" title="Hapus Kategori">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i data-lucide="tags"></i>
                                </div>
                                <h3 class="empty-state-title">Belum ada kategori ditemukan</h3>
                                <p class="empty-state-desc">
                                    @if(request('search'))
                                        Tidak ada kategori yang cocok dengan kata kunci pencarian "{{ request('search') }}".
                                    @else
                                        Mulai tambahkan kategori pertama Anda untuk mengelompokkan monster di inventaris.
                                    @endif
                                </p>
                                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                                    <i data-lucide="plus"></i> Tambah Kategori Sekarang
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div class="pagination-wrapper">
            {{ $categories->links() }}
        </div>
    @endif
</div>
@endsection
