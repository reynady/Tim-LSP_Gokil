@extends('layouts.admin')

@section('title', 'Ubah Kategori Monster')

@section('breadcrumbs')
    <a href="{{ route('admin.categories.index') }}">Kategori Monster</a>
    <span class="separator">/</span>
    <span class="current">Ubah {{ $category->name }}</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Ubah Kategori Monster</h1>
        <p class="page-subtitle">Perbarui informasi kategori <strong>{{ $category->name }}</strong> (VAL-C-01, VAL-C-02).</p>
    </div>
    <div>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
            <i data-lucide="arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<div style="max-width: 640px;">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="form-card">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name" class="form-label">
                Nama Kategori <span class="required">*</span>
            </label>
            <input type="text" id="name" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $category->name) }}" required autofocus>
            <span class="form-help">Nama kategori wajib diisi dan harus bersifat unik (VAL-C-01, VAL-C-02).</span>
            @error('name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description" class="form-label">
                Deskripsi Kategori (Opsional)
            </label>
            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
            <span class="form-help">Deskripsi tambahan untuk memberikan konteks klasifikasi monster.</span>
            @error('description')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save"></i> Perbarui Kategori
            </button>
        </div>
    </form>
</div>
@endsection
