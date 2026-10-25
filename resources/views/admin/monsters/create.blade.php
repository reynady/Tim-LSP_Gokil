@extends('layouts.admin')

@section('title', 'Tambah Monster Baru')

@section('breadcrumbs')
    <a href="{{ route('admin.monsters.index') }}">Inventaris Monster</a>
    <span class="separator">/</span>
    <span class="current">Tambah Monster</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Monster Baru</h1>
        <p class="page-subtitle">Daftarkan unit monster baru ke inventaris ArsenalTrack sesuai aturan BRD (FR-005, BR-005, BR-006, BR-007).</p>
    </div>
    <div>
        <a href="{{ route('admin.monsters.index') }}" class="btn btn-secondary">
            <i data-lucide="arrow-left"></i> Kembali ke Inventaris
        </a>
    </div>
</div>

<div style="max-width: 900px;">
    <form method="POST" action="{{ route('admin.monsters.store') }}" class="form-card">
        @csrf

        <div class="form-grid-2">
            {{-- Kode Monster (Wajib & Unik - BR-007, VAL-N-02, VAL-N-03) --}}
            <div class="form-group">
                <label for="monster_code" class="form-label">
                    Kode Monster (Unik) <span class="required">*</span>
                </label>
                <input type="text" id="monster_code" name="monster_code"
                       class="form-control @error('monster_code') is-invalid @enderror"
                       placeholder="Contoh: KJ-002, AN-003, MY-001"
                       value="{{ old('monster_code') }}" required autofocus>
                <span class="form-help">Kode identitas unik monster, tidak boleh duplikat (BR-007, VAL-N-03).</span>
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
                       placeholder="Contoh: Titanus Gojira, Pale Wanderer, Mecha Hydra"
                       value="{{ old('name') }}" required>
                <span class="form-help">Nama spesimen monster (diizinkan memiliki nama sama jika beda kode - BR-006).</span>
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
                       placeholder="Contoh: Kaiju, Anomaly, Mythical, Extraterrestrial"
                       value="{{ old('origin_type', 'Kaiju') }}" required>
                <span class="form-help">Klasifikasi asal entitas monster.</span>
                @error('origin_type')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Kondisi / Fasilitas Pengamanan (Condition) --}}
            <div class="form-group">
                <label for="condition" class="form-label">
                    Kondisi / Penahanan (Condition)
                </label>
                <input type="text" id="condition" name="condition"
                       class="form-control @error('condition') is-invalid @enderror"
                       placeholder="Contoh: Contained, Stable, Active Observation"
                       value="{{ old('condition', 'Contained') }}">
                <span class="form-help">Status fisik dan ruang kandang/pengamanan saat ini.</span>
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
                    <option value="">-- Pilih Tingkat Ancaman --</option>
                    <option value="Low" {{ old('threat_level') === 'Low' ? 'selected' : '' }}>Low (Rendah - Aman untuk pemula)</option>
                    <option value="Medium" {{ old('threat_level') === 'Medium' ? 'selected' : '' }}>Medium (Menengah - Butuh pengawasan)</option>
                    <option value="High" {{ old('threat_level') === 'High' ? 'selected' : '' }}>High (Tinggi - Hanya pawang bersertifikat)</option>
                    <option value="Extreme" {{ old('threat_level') === 'Extreme' ? 'selected' : '' }}>Extreme (Ekstrem - Berbahaya)</option>
                    <option value="Unknown" {{ old('threat_level') === 'Unknown' ? 'selected' : '' }}>Unknown (Belum teridentifikasi)</option>
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
                    <option value="available" {{ old('status', 'available') === 'available' ? 'selected' : '' }}>Tersedia (Dapat Dipinjam)</option>
                    <option value="borrowed" {{ old('status') === 'borrowed' ? 'selected' : '' }}>Dipinjam (Sedang Aktif Dipinjam)</option>
                    <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>Perawatan / Karantina (Tidak Dapat Dipinjam)</option>
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

                @if($categories->isEmpty())
                    <div class="alert alert-danger">
                        <i data-lucide="alert-circle"></i>
                        <div>
                            Belum ada kategori yang terdaftar di database. Anda wajib membuat minimal 1 kategori sebelum menambahkan monster.
                            <br>
                            <a href="{{ route('admin.categories.create') }}" style="text-decoration: underline; font-weight: 600;">
                                Buat kategori sekarang &rarr;
                            </a>
                        </div>
                    </div>
                @else
                    <div class="category-checkbox-grid">
                        @foreach($categories as $category)
                            <label class="category-checkbox-item">
                                <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                       {{ in_array($category->id, old('categories', [])) ? 'checked' : '' }}>
                                <span>{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif

                @error('categories')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Deskripsi Monster --}}
            <div class="form-group col-span-2">
                <label for="description" class="form-label">Deskripsi & Catatan Spesimen</label>
                <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                          placeholder="Penjelasan latar belakang, kemampuan khusus, kelemahan, atau instruksi penanganan...">{{ old('description') }}</textarea>
                <span class="form-help">Deskripsi detail entitas monster untuk panduan anggota/peminjam.</span>
                @error('description')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.monsters.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="check"></i> Simpan ke Inventaris
            </button>
        </div>
    </form>
</div>
@endsection
