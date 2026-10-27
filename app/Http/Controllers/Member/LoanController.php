<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\LoanPolicy;
use App\Models\Monster;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

        /** @var User|null $user */
        $user = $request->user();
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
        /** @var User|null $user */
        $user = $request->user();
        $policy = LoanPolicy::current();
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

        $canBorrow = ($activeLoansCount < $policy->max_active_loans) && ($monster && $monster->canSchedule());

        return view('member.detail-unit', compact('monster', 'activeLoansCount', 'canBorrow', 'policy'));
    }

    /**
     * Proses pengajuan peminjaman monster
     */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->role === 'member', 403, 'Hanya member yang dapat mengajukan peminjaman.');
        $validated = $this->validateSchedule($request);

        $loan = DB::transaction(function () use ($user, $validated) {
            // SQLite ignores FOR UPDATE; acquire its writer lock before any reads.
            if (DB::connection()->getDriverName() === 'sqlite') {
                DB::table('users')->where('id', $user->id)->update(['is_active' => DB::raw('is_active')]);
            }

            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $monster = Monster::whereKey($validated['monster_id'])->lockForUpdate()->firstOrFail();
            $start = CarbonImmutable::parse($validated['starts_at'], config('app.timezone'));
            $end = CarbonImmutable::parse($validated['ends_at'], config('app.timezone'));

            if ($start->lt(now())) {
                throw ValidationException::withMessages(['starts_at' => 'Waktu mulai peminjaman tidak boleh di masa lalu.']);
            }

            $policy = LoanPolicy::current();
            if (Loan::where('user_id', $user->id)->whereIn('status', Loan::STOCK_LOCKING_STATUSES)->count() >= $policy->max_active_loans) {
                throw ValidationException::withMessages([
                    'monster_id' => "Peminjaman ditolak: batas maksimal {$policy->max_active_loans} peminjaman aktif, termasuk reservasi mendatang, telah tercapai.",
                ]);
            }

            if ($monster->availableUnitsDuring($start, $end) === 0) {
                throw ValidationException::withMessages(['starts_at' => 'Produk tidak tersedia pada jadwal yang dipilih. Pilih jadwal lain atau produk lain.']);
            }

            $newLoan = Loan::create([
                // ULID avoids counter collisions between concurrent bookings of different units.
                'loan_code' => 'AT-PJM-'.now()->format('ymd').'-'.Str::ulid(),
                'user_id' => $user->id,
                'loan_date' => $start->toDateString(),
                'due_date' => $end->toDateString(),
                'starts_at' => $start,
                'ends_at' => $end,
                'status' => 'active',
                'notes' => 'Peminjaman mandiri oleh anggota melalui portal inventaris.',
            ]);

            LoanDetail::create([
                'loan_id' => $newLoan->id,
                'monster_id' => $monster->id,
            ]);

            $monster->update(['status' => 'borrowed']);

            return $newLoan;
        }, 5);

        return redirect()->route('loan.success', ['code' => $loan->loan_code])
            ->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function availability(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->role === 'member', 403, 'Hanya member yang dapat memeriksa jadwal peminjaman.');
        $validated = $this->validateSchedule($request);
        $monster = Monster::findOrFail($validated['monster_id']);
        $available = $monster->availableUnitsDuring(
            CarbonImmutable::parse($validated['starts_at'], config('app.timezone')),
            CarbonImmutable::parse($validated['ends_at'], config('app.timezone')),
        );

        return response()->json([
            'available_quantity' => $available,
            'message' => $available
                ? '1 unit tersedia pada jadwal ini. Ketersediaan akan diperiksa ulang saat konfirmasi.'
                : 'Produk tidak tersedia pada jadwal yang dipilih. Pilih jadwal lain atau produk lain.',
        ]);
    }

    private function validateSchedule(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'monster_id' => ['required', 'integer', 'exists:monsters,id'],
            'starts_at' => ['bail', 'required', 'date_format:Y-m-d\\TH:i', 'after_or_equal:'.now()->format('Y-m-d H:i:s')],
            'ends_at' => ['bail', 'required', 'date_format:Y-m-d\\TH:i', 'after:starts_at'],
        ], [
            'monster_id.required' => 'Produk wajib dipilih.',
            'monster_id.integer' => 'Produk yang dipilih tidak valid.',
            'monster_id.exists' => 'Produk yang dipilih tidak ditemukan.',
            'starts_at.required' => 'Tanggal dan jam mulai peminjaman wajib diisi.',
            'starts_at.date_format' => 'Tanggal dan jam mulai peminjaman tidak valid.',
            'starts_at.after_or_equal' => 'Waktu mulai peminjaman tidak boleh di masa lalu.',
            'ends_at.required' => 'Tanggal dan jam selesai peminjaman wajib diisi.',
            'ends_at.date_format' => 'Tanggal dan jam selesai peminjaman tidak valid.',
            'ends_at.after' => 'Waktu selesai harus lebih besar daripada waktu mulai peminjaman.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($validator->errors()->has('starts_at') || $validator->errors()->has('ends_at')) {
                return;
            }

            $start = CarbonImmutable::parse($request->input('starts_at'), config('app.timezone'));
            $end = CarbonImmutable::parse($request->input('ends_at'), config('app.timezone'));
            $maxDurationDays = LoanPolicy::current()->max_duration_days;
            if ($end->gt($start->addDays($maxDurationDays))) {
                $maxDurationHours = $maxDurationDays * 24;
                $validator->errors()->add(
                    'ends_at',
                    "Durasi peminjaman maksimal {$maxDurationDays} hari ({$maxDurationHours} jam).",
                );
            }
        });

        return $validator->validate();
    }

    /**
     * Halaman konfirmasi peminjaman berhasil
     */
    public function success(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
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
        $maxActiveLoans = LoanPolicy::current()->max_active_loans;

        return view('member.loan-success', compact('latestLoan', 'dbActiveLoans', 'maxActiveLoans'));
    }

    /**
     * Halaman daftar peminjaman aktif anggota
     */
    public function activeLoans()
    {
        $maxActiveLoans = LoanPolicy::current()->max_active_loans;

        return view('member.active-loans', compact('maxActiveLoans'));
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
        /** @var User $user */
        $user = $request->user();
        $today = Carbon::today();

        $loans = Loan::with(['user', 'details.monster', 'details.fine'])
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

        return Pdf::loadView('shared.pdf-history', compact(
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
        ))
            ->setPaper('a4', 'landscape')
            ->download('riwayat-peminjaman-anggota.pdf');
    }

}
