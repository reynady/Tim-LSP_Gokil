@extends('layouts.admin')

@section('title', 'Riwayat Sirkulasi Peminjaman')

@section('breadcrumbs')
    <span class="current">Riwayat</span>
@endsection

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
    <div>
        <h1 class="page-title" style="font-size: 26px; font-weight: 600; color: #1C3029; margin: 0 0 6px;">Riwayat Sirkulasi Peminjaman</h1>
        <p class="page-subtitle" style="font-size: 14px; color: #6D7972; margin: 0;">Arsip rekapitulasi seluruh transaksi sirkulasi peminjaman dan pengembalian unit di institusi.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('admin.loans.print') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; background: #187653; color: #FFFFFF; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px;">
            <i data-lucide="printer" style="width: 16px; height: 16px;"></i>
            <span>Unduh PDF Riwayat</span>
        </a>
    </div>
</div>

<div class="card-panel" style="background: #FFFFFF; border: 1px solid #DFE5DE; border-radius: 10px; overflow: hidden;">
    {{-- Bar Filter & Pencarian --}}
    <div class="card-panel-header" style="padding: 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #DFE5DE;">
        <form method="GET" action="{{ route('admin.loans.history') }}" style="display: flex; align-items: center; gap: 12px; margin: 0; flex: 1; max-width: 600px;">
            <div class="search-input-group" style="display: flex; align-items: center; gap: 10px; border: 1px solid #DFE5DE; border-radius: 6px; padding: 0 12px; height: 42px; width: 100%; max-width: 360px;">
                <i data-lucide="search" style="width: 16px; height: 16px; color: #6D7972;"></i>
                <input type="text" name="search" class="search-input"
                       placeholder="Cari kode transaksi, anggota, atau unit..."
                       value="{{ request('search') }}"
                       style="border: none; outline: none; width: 100%; font-size: 13px; color: #1C3029;">
            </div>

            <select name="status" onchange="this.form.submit()" style="height: 42px; border: 1px solid #DFE5DE; border-radius: 6px; padding: 0 12px; font-size: 13px; color: #1C3029; background: #FFFFFF; outline: none;">
                <option value="">Semua Status</option>
                <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Selesai</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif Dipinjam</option>
            </select>

            @if(request('search') || request('status'))
                <a href="{{ route('admin.loans.history') }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; border: 1px solid #DFE5DE; border-radius: 6px; font-size: 13px; color: #6D7972; text-decoration: none;">
                    <i data-lucide="x" style="width: 14px; height: 14px;"></i> Reset
                </a>
            @endif
        </form>

        <div style="font-size: 13px; color: #6D7972;">
            Total: <strong>{{ $loans->total() }}</strong> transaksi tercatat
        </div>
    </div>

    {{-- Tabel Data --}}
    <div style="overflow-x: auto;">
        <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #F8F9F6; height: 44px;">
                    <th style="padding: 12px 20px; font-size: 10px; font-weight: 600; text-transform: uppercase; color: #6D7972;">KODE TRANSAKSI</th>
                    <th style="padding: 12px 20px; font-size: 10px; font-weight: 600; text-transform: uppercase; color: #6D7972;">PEMINJAM</th>
                    <th style="padding: 12px 20px; font-size: 10px; font-weight: 600; text-transform: uppercase; color: #6D7972;">UNIT</th>
                    <th style="padding: 12px 20px; font-size: 10px; font-weight: 600; text-transform: uppercase; color: #6D7972;">JADWAL MULAI</th>
                    <th style="padding: 12px 20px; font-size: 10px; font-weight: 600; text-transform: uppercase; color: #6D7972;">JADWAL SELESAI</th>
                    <th style="padding: 12px 20px; font-size: 10px; font-weight: 600; text-transform: uppercase; color: #6D7972;">TGL KEMBALI</th>
                    <th style="padding: 12px 20px; font-size: 10px; font-weight: 600; text-transform: uppercase; color: #6D7972;">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loans as $loan)
                    @php
                        $detail = $loan->details->first();
                        $monster = $detail?->monster;
                        $returnDate = $detail?->returned_at 
                            ? \Carbon\Carbon::parse($detail->returned_at) 
                            : ($loan->status === 'returned' && $loan->updated_at ? \Carbon\Carbon::parse($loan->updated_at) : null);
                    @endphp
                    <tr style="border-top: 1px solid #DFE5DE;">
                        <td style="padding: 16px 20px;">
                            <span style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; font-weight: 600; color: #1C3029;">{{ $loan->loan_code }}</span>
                        </td>
                        <td style="padding: 16px 20px;">
                            <div style="display: flex; flex-direction: column;">
                                <strong style="font-size: 13px; color: #1C3029;">{{ $loan->user->name ?? 'Anggota' }}</strong>
                                <small style="font-size: 11px; color: #6D7972;">{{ $loan->user->email ?? '-' }}</small>
                            </div>
                        </td>
                        <td style="padding: 16px 20px;">
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-size: 13px; font-weight: 500; color: #1C3029;">{{ $monster->name ?? 'Unit' }}</span>
                                <small style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: #6D7972;">{{ $monster->monster_code ?? '-' }}</small>
                            </div>
                        </td>
                        <td style="padding: 16px 20px; font-size: 13px; color: #1C3029;">
                            {{ $loan->scheduled_start ? $loan->scheduled_start->locale('id')->translatedFormat('d M Y H:i') . ' WIB' : '-' }}
                        </td>
                        <td style="padding: 16px 20px; font-size: 13px; color: #1C3029;">
                            {{ $loan->scheduled_end ? $loan->scheduled_end->locale('id')->translatedFormat('d M Y H:i') . ' WIB' : '-' }}
                        </td>
                        <td style="padding: 16px 20px; font-size: 13px; color: #1C3029;">
                            {{ $returnDate ? $returnDate->locale('id')->translatedFormat('d M Y') : 'Belum kembali' }}
                        </td>
                        <td style="padding: 16px 20px;">
                            @if($loan->status === 'returned')
                                @if(($loan->calculated_overdue_days ?? 0) > 0)
                                    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: #FBF4E6; color: #94651E; border-radius: 100px; font-size: 12px;">
                                        <span style="width: 4px; height: 4px; border-radius: 50%; background: #94651E;"></span>
                                        Selesai terlambat ({{ $loan->calculated_overdue_days }}h)
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: #E9F3EC; color: #187653; border-radius: 100px; font-size: 12px;">
                                        <span style="width: 4px; height: 4px; border-radius: 50%; background: #187653;"></span>
                                        Selesai tepat waktu
                                    </span>
                                @endif
                            @elseif(in_array($loan->status, ['active', 'borrowed', 'pending']))
                                @if(($loan->calculated_overdue_days ?? 0) > 0)
                                    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: #FEE2E2; color: #B91C1C; border-radius: 100px; font-size: 12px;">
                                        <span style="width: 4px; height: 4px; border-radius: 50%; background: #B91C1C;"></span>
                                        Aktif terlambat ({{ $loan->calculated_overdue_days }}h)
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: #FEF3C7; color: #92400E; border-radius: 100px; font-size: 12px;">
                                        <span style="width: 4px; height: 4px; border-radius: 50%; background: #92400E;"></span>
                                        Sedang dipinjam
                                    </span>
                                @endif
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: #F5F6F2; color: #6D7972; border-radius: 100px; font-size: 12px;">
                                    {{ ucfirst($loan->status) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 36px; color: #6D7972;">
                            Tidak ada rekaman transaksi sirkulasi yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($loans->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid #DFE5DE;">
            {{ $loans->links() }}
        </div>
    @endif
</div>
@endsection
