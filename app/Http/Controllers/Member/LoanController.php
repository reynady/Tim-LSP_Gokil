<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\Monster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * Tampilkan katalog monster dari database
     */
    public function catalog(Request $request)
    {
        $query = Monster::with('categories');

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'Tersedia') {
                $query->where('status', 'available');
            } elseif ($status === 'Dipinjam') {
                $query->where('status', 'borrowed');
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('monster_code', 'like', "%{$search}%");
            });
        }

        $monsters = $query->orderBy('id')->get();

        $user = auth()->user();
        $activeLoansCount = $user ? Loan::where('user_id', $user->id)
            ->whereIn('status', ['active', 'borrowed', 'approved', 'pending'])
            ->count() : 0;

        return view('member.catalog', compact('monsters', 'activeLoansCount'));
    }

    /**
     * Detail entitas monster sebelum diajukan peminjaman
     */
    public function detailUnit(Request $request)
    {
        $user = auth()->user();
        $code = $request->query('code') ?? $request->query('kode');

        $monster = null;
        if ($code) {
            $monster = Monster::with('categories')->where('monster_code', $code)->first();
        }

        if (!$monster && $request->filled('id')) {
            $monster = Monster::with('categories')->find($request->id);
        }

        if (!$monster) {
            $monster = Monster::with('categories')->first();
        }

        $activeLoansCount = $user ? Loan::where('user_id', $user->id)
            ->whereIn('status', ['active', 'borrowed', 'approved', 'pending'])
            ->count() : 0;

        $canBorrow = ($activeLoansCount < 2) && ($monster && $monster->status === 'available');

        return view('member.detail-unit', compact('monster', 'activeLoansCount', 'canBorrow'));
    }

    /**
     * Proses pengajuan peminjaman monster
     */
    public function store(Request $request)
    {
        $request->validate([
            'monster_id' => 'required|exists:monsters,id',
        ]);

        $user = auth()->user();

        // Validasi Aturan BRD: Maksimal 2 peminjaman aktif per anggota (VAL-L-03 / BReq-05)
        $activeLoansCount = Loan::where('user_id', $user->id)
            ->whereIn('status', ['active', 'borrowed', 'approved', 'pending'])
            ->count();

        if ($activeLoansCount >= 2) {
            return redirect()->route('loan.active')
                ->with('error', 'Peminjaman ditolak: Anda telah mencapai batas maksimal 2 entitas monster aktif (VAL-L-03).');
        }

        $monster = Monster::find($request->monster_id);
        if (!$monster || $monster->status !== 'available') {
            return back()->with('error', 'Peminjaman gagal: Entitas monster ini sedang tidak tersedia atau sudah dipinjam.');
        }

        $loan = DB::transaction(function () use ($user, $monster) {
            // Generate nomor transaksi unik: AT-PJM-ymd-XXX
            $datePrefix = 'AT-PJM-' . now()->format('ymd') . '-';
            $todayCount = Loan::whereDate('created_at', today())->count() + 1;
            $code = $datePrefix . str_pad($todayCount, 3, '0', STR_PAD_LEFT);
            while (Loan::where('loan_code', $code)->exists()) {
                $code = $datePrefix . rand(100, 999);
            }

            $newLoan = Loan::create([
                'loan_code' => $code,
                'user_id'   => $user->id,
                'loan_date' => now()->toDateString(),
                'due_date'  => now()->addDays(5)->toDateString(),
                'status'    => 'active',
                'notes'     => 'Peminjaman mandiri oleh anggota melalui portal inventaris.',
            ]);

            LoanDetail::create([
                'loan_id'    => $newLoan->id,
                'monster_id' => $monster->id,
            ]);

            $monster->update(['status' => 'borrowed']);

            return $newLoan;
        });

        return redirect()->route('loan.success', ['code' => $loan->loan_code])
            ->with('success', 'Peminjaman berhasil dicatat.');
    }

    /**
     * Halaman konfirmasi peminjaman berhasil
     */
    public function success(Request $request)
    {
        $user = auth()->user();
        $code = $request->query('code');

        $latestLoan = null;
        if ($code) {
            $latestLoan = Loan::with('details.monster')
                ->where('user_id', $user->id)
                ->where('loan_code', $code)
                ->first();
        }

        if (!$latestLoan) {
            $latestLoan = Loan::with('details.monster')
                ->where('user_id', $user->id)
                ->latest()
                ->first();
        }

        $dbActiveLoans = Loan::with('details.monster')
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'borrowed', 'approved', 'pending'])
            ->get();

        return view('member.loan-success', compact('latestLoan', 'dbActiveLoans'));
    }

    /**
     * Halaman daftar peminjaman aktif anggota
     */
    public function activeLoans()
    {
        return view('member.active-loans');
    }

    /**
     * Halaman riwayat peminjaman anggota
     */
    public function history()
    {
        return view('member.history');
    }

    /**
     * Tampilkan halaman pratinjau cetak riwayat sirkulasi untuk anggota
     */
    public function printHistory(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();

        $loans = Loan::with(['details.monster', 'details.fine'])
            ->where('user_id', $user->id)
            ->latest('loan_date')
            ->get();

        $loans->each(function ($loan) use ($today) {
            $detail = $loan->details->first();
            $dueDate = $loan->due_date ? $loan->due_date->startOfDay() : $today;

            if ($loan->status === 'returned') {
                if ($detail?->fine && $detail->fine->overdue_days > 0) {
                    $loan->calculated_overdue_days = $detail->fine->overdue_days;
                } elseif ($detail?->returned_at && $loan->due_date) {
                    $returnDate = $detail->returned_at->startOfDay();
                    $loan->calculated_overdue_days = $returnDate->gt($dueDate) ? $dueDate->diffInDays($returnDate) : 0;
                } else {
                    $loan->calculated_overdue_days = 0;
                }
            } else {
                $loan->calculated_overdue_days = $today->gt($dueDate) ? $dueDate->diffInDays($today) : 0;
            }
        });

        $totalLoans = $loans->count();
        $onTimeCount = $loans->filter(fn($l) => $l->status === 'returned' && ($l->calculated_overdue_days ?? 0) === 0)->count();
        $lateCount = $loans->filter(fn($l) => $l->status === 'returned' && ($l->calculated_overdue_days ?? 0) > 0)->count();
        $activeCount = $loans->filter(fn($l) => in_array($l->status, ['active', 'borrowed', 'pending']))->count();

        $backUrl = route('loan.history');
        $scopeTitle = 'Catatan Arsip Pribadi - ' . ($user->name ?? 'Anggota');
        $referenceCode = 'REF-RWY-' . strtoupper(substr(md5(($user->id ?? 1) . '-' . now()->timestamp), 0, 8));
        $printedBy = $user->name ?? 'Anggota';
        $printedRole = 'Anggota';
        $printedAt = now();

        return view('shared.print-history', compact(
            'loans',
            'totalLoans',
            'onTimeCount',
            'lateCount',
            'activeCount',
            'backUrl',
            'scopeTitle',
            'referenceCode',
            'printedBy',
            'printedRole',
            'printedAt'
        ));
    }

    /**
     * Kembalikan peminjaman unit (proses return)
     */
    public function returnLoan(Request $request, Loan $loan)
    {
        $user = auth()->user();

        // Pastikan hanya pemilik peminjaman atau admin yang bisa mengembalikan
        if (!$user->isAdmin() && $loan->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        DB::transaction(function () use ($loan, $user) {
            $loan->update([
                'status'      => 'returned',
                'returned_by' => $user->id,
            ]);

            foreach ($loan->details as $detail) {
                $detail->update(['returned_at' => now()]);
                if ($detail->monster) {
                    $detail->monster->update(['status' => 'available']);
                }
            }
        });

        return redirect()->route('loan.active')
            ->with('success', "Entitas monster pada transaksi {$loan->loan_code} berhasil dikembalikan. Kuota peminjaman Anda bertambah.");
    }
}
