<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Monster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * Menampilkan daftar peminjaman aktif yang perlu dikelola atau dikembalikan (FR-009, BR-013).
     */
    public function index(Request $request)
    {
        $query = Loan::with(['user', 'details.monster'])
            ->whereIn('status', ['active', 'borrowed', 'pending']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('loan_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('details.monster', function ($mq) use ($search) {
                      $mq->where('name', 'like', "%{$search}%")
                         ->orWhere('monster_code', 'like', "%{$search}%");
                  });
            });
        }

        $loans = $query->orderBy('due_date', 'asc')->paginate(10)->withQueryString();

        return view('admin.loans.index', compact('loans'));
    }

    /**
     * Menampilkan halaman formulir verifikasi "Proses Pengembalian" (Figma: Proses pengembalian).
     */
    public function returnProcess(Loan $loan)
    {
        $loan->load(['user', 'details.monster.categories']);

        $monster = $loan->details->first()?->monster;

        $today = now()->startOfDay();
        $dueDate = $loan->due_date ? $loan->due_date->startOfDay() : $today;
        $overdueDays = $today->gt($dueDate) ? $dueDate->diffInDays($today) : 0;
        $fineAmount = Fine::amountForOverdueDays($overdueDays);
        $isOverdue = $overdueDays > 0;

        return view('admin.loans.return', compact(
            'loan',
            'monster',
            'overdueDays',
            'fineAmount',
            'isOverdue',
            'today'
        ));
    }

    /**
     * Memproses serah terima fisik pengembalian unit monster (FR-009, BR-013).
     */
    public function processReturn(Request $request, Loan $loan)
    {
        $request->validate([
            'confirm_physical' => ['required', 'accepted'],
        ], [
            'confirm_physical.required' => 'Anda wajib mencentang konfirmasi penerimaan fisik unit sebelum menyelesaikan pengembalian.',
            'confirm_physical.accepted' => 'Anda wajib mencentang konfirmasi penerimaan fisik unit sebelum menyelesaikan pengembalian.',
        ]);

        $admin = auth()->user();
        $monsterIds = $loan->details()->orderBy('monster_id')->pluck('monster_id');

        DB::transaction(function () use ($loan, $admin, $monsterIds) {
            // Use the same unit lock as booking before changing reservation state.
            if (DB::connection()->getDriverName() === 'sqlite') {
                DB::table('users')->where('id', $admin->id)->update(['is_active' => DB::raw('is_active')]);
            }
            $monsters = Monster::whereIn('id', $monsterIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($loan->status, Loan::STOCK_LOCKING_STATUSES, true), 422, 'Peminjaman ini sudah selesai dan tidak dapat dikembalikan lagi.');

            $today = now()->startOfDay();
            $dueDate = $loan->due_date ? $loan->due_date->startOfDay() : $today;
            $overdueDays = $today->gt($dueDate) ? $dueDate->diffInDays($today) : 0;

            // 1. Update status peminjaman menjadi returned
            $loan->update([
                'status'      => 'returned',
                'returned_by' => $admin->id,
            ]);

            // 2. Update status tiap monster dan catat waktu kembali
            foreach ($loan->details as $detail) {
                $detail->update(['returned_at' => now()]);

                $monster = $monsters->get($detail->monster_id);
                if ($monster && in_array($monster->status, ['available', 'borrowed'], true)) {
                    $monster->update(['status' => $monster->blockingLoanDetails()->exists() ? 'borrowed' : 'available']);
                }

                // Jika terlambat, simpan pencatatan denda
                if ($overdueDays > 0) {
                    $fineAmount = Fine::amountForOverdueDays($overdueDays);
                    Fine::updateOrCreate(
                        ['loan_detail_id' => $detail->id],
                        [
                            'amount'       => $fineAmount,
                            'overdue_days' => $overdueDays,
                            'status'       => $fineAmount > 0 ? 'unpaid' : 'waived',
                        ]
                    );
                }
            }
        }, 5);

        return redirect()->route('admin.loans.index')
            ->with('success', "Pengembalian transaksi {$loan->loan_code} berhasil diselesaikan. Ketersediaan unit telah diperbarui sesuai reservasi yang masih aktif.");
    }

    /**
     * [DEMO] Simulasi keterlambatan — mengubah due_date transaksi aktif menjadi kemarin.
     * Digunakan khusus untuk keperluan demonstrasi denda keterlambatan.
     */
    public function simulateOverdue(Loan $loan)
    {
        if (!in_array($loan->status, ['active', 'borrowed', 'pending'])) {
            return redirect()->route('admin.loans.index')
                ->with('error', 'Simulasi hanya berlaku pada transaksi yang masih aktif.');
        }

        $loan->update(['due_date' => now()->subDay()->toDateString()]);

        return redirect()->route('admin.loans.index')
            ->with('success', "Demo: Batas pengembalian {$loan->loan_code} diubah ke kemarin. Transaksi sekarang terlambat 1 hari.");
    }


    /**
     * Menampilkan daftar riwayat seluruh transaksi peminjaman (FR-011).
     */
    public function history(Request $request)
    {
        $today = Carbon::today();
        $query = Loan::with(['user', 'details.monster', 'details.fine'])
            ->latest('loan_date');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('loan_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('details.monster', function ($mq) use ($search) {
                      $mq->where('name', 'like', "%{$search}%")
                         ->orWhere('monster_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'returned') {
                $query->where('status', 'returned');
            } elseif ($status === 'active') {
                $query->whereIn('status', ['active', 'borrowed', 'pending']);
            }
        }

        $loans = $query->paginate(15)->withQueryString();

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

        return view('admin.loans.history', compact('loans'));
    }

    /**
     * Menampilkan halaman pratinjau cetak riwayat sirkulasi institusi bagi Administrator.
     */
    public function printHistory(Request $request)
    {
        $today = Carbon::today();
        $query = Loan::with(['user', 'details.monster', 'details.fine'])
            ->latest('loan_date');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('loan_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('details.monster', function ($mq) use ($search) {
                      $mq->where('name', 'like', "%{$search}%")
                         ->orWhere('monster_code', 'like', "%{$search}%");
                  });
            });
        }

        $loans = $query->get();

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

        $backUrl = route('admin.loans.history');
        $scopeTitle = 'Seluruh Transaksi Sirkulasi Institusi';
        $referenceCode = 'REF-RWY-' . strtoupper(substr(md5('admin-' . now()->timestamp), 0, 8));
        $printedBy = auth()->user()->name ?? 'Administrator';
        $printedRole = 'Administrator';
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
}
