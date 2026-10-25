@extends('layouts.admin')

@section('title', 'Tambah Anggota Baru')

@section('breadcrumbs')
    <a href="{{ route('admin.members.index') }}">Daftar Anggota</a>
    <span class="separator">/</span>
    <span class="current">Tambah Anggota</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Anggota Baru</h1>
        <p class="page-subtitle">Daftarkan anggota baru ke sistem ArsenalTrack sesuai aturan BRD (FR-004, BR-003, BR-004).</p>
    </div>
    <div>
        <a href="{{ route('admin.members.index') }}" class="btn btn-secondary">
            <i data-lucide="arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
</div>

<div style="max-width: 900px;">
    <form method="POST" action="{{ route('admin.members.store') }}" class="form-card">
        @csrf

        <div class="form-grid-2">
            {{-- Nama Lengkap --}}
            <div class="form-group">
                <label for="name" class="form-label">
                    Nama Lengkap <span class="required">*</span>
                </label>
                <input type="text" id="name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       placeholder="Contoh: Raka Pratama, Azka Fadhli"
                       value="{{ old('name') }}" required autofocus>
                <span class="form-help">Nama lengkap anggota yang akan didaftarkan ke sistem.</span>
                @error('name')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Alamat Email (Unik - FR-004 Alt A1) --}}
            <div class="form-group">
                <label for="email" class="form-label">
                    Alamat Email <span class="required">*</span>
                </label>
                <input type="email" id="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="Contoh: raka@arunika.ac.id"
                       value="{{ old('email') }}" required>
                <span class="form-help">Email unik untuk login anggota. Tidak boleh duplikat (FR-004 Alt A1).</span>
                @error('email')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Kata Sandi --}}
            <div class="form-group">
                <label for="password" class="form-label">
                    Kata Sandi <span class="required">*</span>
                </label>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Minimal 8 karakter" required>
                <span class="form-help">Kata sandi akun untuk login anggota (minimal 8 karakter).</span>
                @error('password')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Nomor Telepon --}}
            <div class="form-group">
                <label for="phone" class="form-label">
                    Nomor Telepon
                </label>
                <input type="text" id="phone" name="phone"
                       class="form-control @error('phone') is-invalid @enderror"
                       placeholder="Contoh: 081234567890"
                       value="{{ old('phone') }}">
                <span class="form-help">Nomor telepon kontak anggota (opsional).</span>
                @error('phone')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Status Keaktifan --}}
            <div class="form-group">
                <label for="is_active" class="form-label">
                    Status Keaktifan <span class="required">*</span>
                </label>
                <select id="is_active" name="is_active" class="form-control @error('is_active') is-invalid @enderror" required>
                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif (Dapat login & meminjam)</option>
                    <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Nonaktif (Akun dibekukan)</option>
                </select>
                <span class="form-help">Tentukan apakah anggota langsung aktif atau nonaktif saat didaftarkan.</span>
                @error('is_active')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Alamat --}}
            <div class="form-group col-span-2">
                <label for="address" class="form-label">Alamat</label>
                <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror"
                          placeholder="Alamat lengkap anggota (opsional)..." rows="3">{{ old('address') }}</textarea>
                <span class="form-help">Alamat domisili anggota untuk referensi administrasi.</span>
                @error('address')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.members.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="user-plus"></i> Daftarkan Anggota
            </button>
        </div>
    </form>
</div>
@endsection
