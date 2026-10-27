@extends('layouts.admin')

@section('title', 'Aturan Peminjaman')

@section('breadcrumbs')
    <span class="current">Aturan Peminjaman</span>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Aturan Peminjaman</h1>
        <p class="page-subtitle">Atur jumlah transaksi aktif dan durasi maksimal peminjaman anggota.</p>
    </div>
</div>

<div style="max-width: 760px;">
    <form method="POST" action="{{ route('admin.loan-policy.update') }}" class="form-card">
        @csrf
        @method('PATCH')

        <div class="form-grid-2">
            <div class="form-group">
                <label for="max_active_loans" class="form-label">Maksimal peminjaman aktif</label>
                <input id="max_active_loans" name="max_active_loans" type="number" min="1" step="1"
                       class="form-control @error('max_active_loans') is-invalid @enderror"
                       value="{{ old('max_active_loans', $policy->max_active_loans) }}" required>
                <span class="form-help">Jumlah pinjaman dan reservasi aktif yang boleh dimiliki setiap anggota. Masukkan bilangan bulat minimal 1.</span>
                @error('max_active_loans')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="max_duration_days" class="form-label">Durasi maksimal (hari)</label>
                <input id="max_duration_days" name="max_duration_days" type="number" min="1" step="1"
                       class="form-control @error('max_duration_days') is-invalid @enderror"
                       value="{{ old('max_duration_days', $policy->max_duration_days) }}" required>
                <span class="form-help">Lama maksimal satu transaksi peminjaman dalam hitungan hari. Masukkan bilangan bulat minimal 1.</span>
                @error('max_duration_days')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <p class="form-help">Perubahan berlaku untuk pengajuan baru. Jadwal transaksi yang sudah tercatat tidak diubah.</p>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save"></i> Simpan Aturan
            </button>
        </div>
    </form>
</div>
@endsection
