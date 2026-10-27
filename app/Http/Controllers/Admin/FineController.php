<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Loan;
use App\Services\CurrencyConverter;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use UnexpectedValueException;

class FineController extends Controller
{
    /**
     * Menampilkan halaman Keterlambatan & Denda (FR-010, BR-012).
     */
    public function index(Request $request)
    {
        $today = Carbon::today();

        // 1. Query Peminjaman Aktif yang Terlambat
        $activeQuery = Loan::with(['user', 'details.monster'])
            ->whereIn('status', ['active', 'borrowed', 'pending'])
            ->whereDate('due_date', '<', $today);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $activeQuery->where(function ($q) use ($search) {
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

        $activeOverdueLoans = $activeQuery->orderBy('due_date', 'asc')->get();

        // Hitung hari keterlambatan untuk setiap pinjaman aktif
        $activeOverdueLoans->each(function ($loan) use ($today) {
            $dueDate = $loan->due_date ? $loan->due_date->startOfDay() : $today;
            $loan->calculated_overdue_days = max(1, $dueDate->diffInDays($today));
            $loan->calculated_fine_amount = Fine::amountForOverdueDays($loan->calculated_overdue_days);
        });

        // 2. Query Peminjaman Selesai yang Memiliki Catatan Keterlambatan
        $returnedQuery = Loan::with(['user', 'details.monster', 'details.fine'])
            ->where('status', 'returned')
            ->where(function ($q) {
                $q->whereHas('details.fine', function ($fq) {
                    $fq->where('overdue_days', '>', 0);
                })->orWhereHas('details', function ($dq) {
                    $dq->whereNotNull('returned_at')
                       ->whereRaw('DATE(returned_at) > DATE(loans.due_date)');
                });
            });

        if ($request->filled('search')) {
            $search = trim($request->search);
            $returnedQuery->where(function ($q) use ($search) {
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

        $returnedOverdueLoans = $returnedQuery->orderBy('updated_at', 'desc')->get();

        // Hitung hari keterlambatan untuk pinjaman selesai
        $returnedOverdueLoans->each(function ($loan) {
            $detail = $loan->details->first();
            if ($detail?->fine && $detail->fine->overdue_days > 0) {
                $loan->calculated_overdue_days = $detail->fine->overdue_days;
            } elseif ($detail?->returned_at && $loan->due_date) {
                $loan->calculated_overdue_days = max(1, $loan->due_date->startOfDay()->diffInDays($detail->returned_at->startOfDay()));
            } else {
                $loan->calculated_overdue_days = 0;
            }

            $loan->calculated_fine_amount = $detail?->fine
                ? $detail->fine->amount
                : Fine::amountForOverdueDays($loan->calculated_overdue_days);
        });

        // 3. Ringkasan KPI Cards
        // Card 1: Aktif terlambat
        $activeOverdueCount = $activeOverdueLoans->count();
        $activeOverdueMembersCount = $activeOverdueLoans->pluck('user_id')->unique()->count();
        $activeOverdueUnitsCount = $activeOverdueLoans->sum(function ($loan) {
            return $loan->details->count() ?: 1;
        });

        // Card 2: Keterlambatan berjalan
        $totalActiveOverdueDays = $activeOverdueLoans->sum('calculated_overdue_days');
        
        // Buat string rincian anggota (contoh: "2 hari Nadia · 1 hari Dimas")
        $breakdownParts = [];
        foreach ($activeOverdueLoans as $loan) {
            $firstName = explode(' ', trim($loan->user->name ?? 'Anggota'))[0];
            $breakdownParts[] = "{$loan->calculated_overdue_days} hari {$firstName}";
        }
        $activeBreakdownText = !empty($breakdownParts) 
            ? implode(' · ', array_slice($breakdownParts, 0, 3))
            : 'Belum ada hari keterlambatan berjalan';

        // Card 3: Sudah kembali terlambat
        $returnedOverdueCount = $returnedOverdueLoans->count();
        $totalReturnedOverdueDays = $returnedOverdueLoans->sum('calculated_overdue_days');

        return view('admin.fines.index', compact(
            'activeOverdueLoans',
            'returnedOverdueLoans',
            'activeOverdueCount',
            'activeOverdueMembersCount',
            'activeOverdueUnitsCount',
            'totalActiveOverdueDays',
            'activeBreakdownText',
            'returnedOverdueCount',
            'totalReturnedOverdueDays',
            'today'
        ));
    }

    public function convertCurrency(Request $request, CurrencyConverter $converter)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0', 'max:1000000000000'],
            'currency' => ['required', 'string', Rule::in(CurrencyConverter::SUPPORTED_CURRENCIES)],
        ]);

        try {
            return response()->json($converter->convertFromRupiah(
                (float) $validated['amount'],
                $validated['currency'],
            ));
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            Log::warning('Currency conversion request failed.', [
                'currency' => $validated['currency'],
                'exception' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Kurs mata uang tidak dapat diambil saat ini. Silakan coba lagi.',
            ], 503);
        }
    }
}
