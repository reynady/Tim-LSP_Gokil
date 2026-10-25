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

<<<<<<< Updated upstream
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'admin'])->name('admin.dashboard');
=======
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('monsters', \App\Http\Controllers\Admin\MonsterController::class);
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
    Route::resource('members', \App\Http\Controllers\Admin\MemberController::class);
    Route::patch('members/{member}/toggle-status', [\App\Http\Controllers\Admin\MemberController::class, 'toggleStatus'])->name('members.toggle-status');
});
>>>>>>> Stashed changes

Route::middleware('auth')->group(function () {
    Route::get('/katalog', [\App\Http\Controllers\Member\LoanController::class, 'catalog'])->name('catalog');
    Route::get('/detail-unit', [\App\Http\Controllers\Member\LoanController::class, 'detailUnit'])->name('unit.detail');
    Route::post('/peminjaman', [\App\Http\Controllers\Member\LoanController::class, 'store'])->name('loan.store');
    Route::get('/peminjaman-berhasil', [\App\Http\Controllers\Member\LoanController::class, 'success'])->name('loan.success');
    Route::get('/peminjaman-aktif', [\App\Http\Controllers\Member\LoanController::class, 'activeLoans'])->name('loan.active');
    Route::post('/peminjaman/{loan}/kembalikan', [\App\Http\Controllers\Member\LoanController::class, 'returnLoan'])->name('loan.return');
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