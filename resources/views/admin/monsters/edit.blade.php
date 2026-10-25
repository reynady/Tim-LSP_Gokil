@extends('layouts.admin')

@section('title', 'Ubah Monster')

@section('breadcrumbs')
    <a href="{{ route('admin.monsters.index') }}">Inventaris Monster</a>
    <span class="separator">/</span>
    <span class="current">Ubah {{ $monster->name }}</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Ubah Data Monster</h1>
        <p class="page-subtitle">Perbarui data spesimen <strong>{{ $monster->name }}</strong> [{{ $monster->monster_code }}] (FR-005, BR-005, BR-007).</p>
    </div>
    <div>
        <a href="{{ route('admin.monsters.index') }}" class="btn btn-secondary">
            <i data-lucide="arrow-left"></i> Kembali ke Inventaris
        </a>
    </div>
</div>

<div style="max-width: 900px;">
    <form method="POST" action="{{ route('admin.monsters.update', $monster) }}" class="form-card">
        @csrf
        @method('PUT')

        <div class="form-grid-2">
            {{-- Kode Monster (Unik - BR-007, VAL-N-02, VAL-N-03) --}}
            <div class="form-group">
                <label for="monster_code" class="form-label">
                    Kode Monster (Unik) <span class="required">*</span>
                </label>
                <input type="text" id="monster_code" name="monster_code"
                       class="form-control @error('monster_code') is-invalid @enderror"
                       value="{{ old('monster_code', $monster->monster_code) }}" required>
                <span class="form-help">Kode identitas unik monster di inventaris (BR-007).</span>
                @error('monster_code')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Nama Monster (Wajib, Boleh sama - BR-006, VAL-N-01) --}}
            <div class="form-group">
                <label for="name" class="form-label">
                    Nama Monster <span class="required">*</span>
                </label>
                <input type="text" id="name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $monster->name) }}" required>
                <span class="form-help">Nama spesimen monster (BR-006).</span>
                @error('name')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Tipe Asal Usul (Origin Type) --}}
            <div class="form-group">
                <label for="origin_type" class="form-label">
                    Tipe Asal Usul (Origin Type) <span class="required">*</span>
                </label>
                <input type="text" id="origin_type" name="origin_type"
                       class="form-control @error('origin_type') is-invalid @enderror"
                       value="{{ old('origin_type', $monster->origin_type) }}" required>
                <span class="form-help">Klasifikasi asal entitas monster.</span>
                @error('origin_type')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Kondisi / Penahanan (Condition) --}}
            <div class="form-group">
                <label for="condition" class="form-label">
                    Kondisi / Penahanan (Condition)
                </label>
                <input type="text" id="condition" name="condition"
                       class="form-control @error('condition') is-invalid @enderror"
                       value="{{ old('condition', $monster->condition) }}">
                <span class="form-help">Status penahanan dan kandang saat ini.</span>
                @error('condition')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Tingkat Ancaman (Threat Level) --}}
            <div class="form-group">
                <label for="threat_level" class="form-label">
                    Tingkat Ancaman (Threat Level) <span class="required">*</span>
                </label>
                <select id="threat_level" name="threat_level" class="form-control @error('threat_level') is-invalid @enderror" required>
                    @php $currThreat = old('threat_level', $monster->threat_level); @endphp
                    <option value="Low" {{ $currThreat === 'Low' ? 'selected' : '' }}>Low (Rendah - Aman)</option>
                    <option value="Medium" {{ $currThreat === 'Medium' ? 'selected' : '' }}>Medium (Menengah - Butuh Pengawasan)</option>
                    <option value="High" {{ $currThreat === 'High' ? 'selected' : '' }}>High (Tinggi - Hanya Pawang Bersertifikat)</option>
                    <option value="Extreme" {{ $currThreat === 'Extreme' ? 'selected' : '' }}>Extreme (Ekstrem - Berbahaya)</option>
                    <option value="Unknown" {{ $currThreat === 'Unknown' ? 'selected' : '' }}>Unknown (Belum teridentifikasi)</option>
                </select>
                @error('threat_level')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Status Unit (VAL-N-04) --}}
            <div class="form-group">
                <label for="status" class="form-label">
                    Status Ketersediaan <span class="required">*</span>
                </label>
                <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                    @php $currStatus = old('status', $monster->status); @endphp
                    <option value="available" {{ $currStatus === 'available' ? 'selected' : '' }}>Tersedia (Dapat Dipinjam)</option>
                    <option value="borrowed" {{ $currStatus === 'borrowed' ? 'selected' : '' }}>Dipinjam (Sedang Aktif Dipinjam)</option>
                    <option value="maintenance" {{ $currStatus === 'maintenance' ? 'selected' : '' }}>Perawatan / Karantina</option>
                </select>
                <span class="form-help">Status ketersediaan unit untuk peminjaman (VAL-N-04).</span>
                @error('status')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Kategori Multi-Select (BR-005, VAL-N-05) --}}
            <div class="form-group col-span-2">
                <label class="form-label">
                    Pilih Kategori <span class="required">*</span> (Minimal 1 Kategori - VAL-N-05, BR-005)
                </label>
                <span class="form-help" style="margin-bottom: 8px;">
                    Satu monster dapat memiliki lebih dari satu kategori (centang satu atau lebih).
                </span>

                @php
                    $selectedCategories = old('categories', $monster->categories->pluck('id')->toArray());
                @endphp

                <div class="category-checkbox-grid">
                    @foreach($categories as $category)
                        <label class="category-checkbox-item">
                            <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                   {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                            <span>{{ $category->name }}</span>
                        </label>
                    @endforeach
                </div>

                @error('categories')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Deskripsi Monster --}}
            <div class="form-group col-span-2">
                <label for="description" class="form-label">Deskripsi & Catatan Spesimen</label>
                <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror">{{ old('description', $monster->description) }}</textarea>
                <span class="form-help">Deskripsi detail entitas monster untuk panduan anggota/peminjam.</span>
                @error('description')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.monsters.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save"></i> Perbarui Data Monster
            </button>
        </div>
    </form>
</div>
@endsection
