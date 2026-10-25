<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.process');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'admin'])->name('admin.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/katalog', [\App\Http\Controllers\Member\LoanController::class, 'catalog'])->name('catalog');
    Route::get('/detail-unit', [\App\Http\Controllers\Member\LoanController::class, 'detailUnit'])->name('unit.detail');
    Route::post('/peminjaman', [\App\Http\Controllers\Member\LoanController::class, 'store'])->name('loan.store');
    Route::get('/peminjaman-berhasil', [\App\Http\Controllers\Member\LoanController::class, 'success'])->name('loan.success');
    Route::get('/peminjaman-aktif', [\App\Http\Controllers\Member\LoanController::class, 'activeLoans'])->name('loan.active');
    Route::post('/peminjaman/{loan}/kembalikan', [\App\Http\Controllers\Member\LoanController::class, 'returnLoan'])->name('loan.return');
    Route::post('/peminjaman/reset', [\App\Http\Controllers\Member\LoanController::class, 'resetLoans'])->name('loan.reset');
    Route::get('/riwayat', [\App\Http\Controllers\Member\LoanController::class, 'history'])->name('loan.history');
});

Route::get('/profil', function () {
    return view('member.profile');
})->middleware('auth')->name('profile');

Route::post('/profil', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $user = auth()->user();
    if ($user) {
        $user->name = $request->input('name');
        $user->save();
    }

    return redirect()->route('profile')->with('status', 'profile-updated');
})->middleware('auth')->name('profile.update');