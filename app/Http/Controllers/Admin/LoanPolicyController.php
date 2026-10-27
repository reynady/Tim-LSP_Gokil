<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanPolicy;
use Illuminate\Http\Request;

class LoanPolicyController extends Controller
{
    public function edit()
    {
        $policy = LoanPolicy::current();

        return view('admin.loan-policy.edit', compact('policy'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'max_active_loans' => ['required', 'integer', 'min:1'],
            'max_duration_days' => ['required', 'integer', 'min:1'],
        ], [
            'max_active_loans.required' => 'Batas peminjaman aktif wajib diisi.',
            'max_active_loans.integer' => 'Batas peminjaman aktif harus berupa angka bulat.',
            'max_active_loans.min' => 'Batas peminjaman aktif minimal 1.',
            'max_duration_days.required' => 'Durasi maksimal wajib diisi.',
            'max_duration_days.integer' => 'Durasi maksimal harus berupa angka bulat.',
            'max_duration_days.min' => 'Durasi maksimal minimal 1 hari.',
        ]);

        LoanPolicy::current()->update($validated);

        return redirect()->route('admin.loan-policy.edit')
            ->with('success', 'Batas peminjaman berhasil diperbarui.');
    }
}
