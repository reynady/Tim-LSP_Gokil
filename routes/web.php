<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FineController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\LoanPolicyController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MonsterController;
use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.process');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.process');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [
            DashboardController::class,
            'index',
        ])->name('dashboard');

        Route::resource(
            'monsters',
            MonsterController::class
        );

        Route::resource(
            'categories',
            CategoryController::class
        );

        Route::resource(
            'members',
            MemberController::class
        );

        Route::patch(
            'members/{member}/toggle-status',
            [
                MemberController::class,
                'toggleStatus',
            ]
        )->name('members.toggle-status');

        Route::patch('members/{member}/review-registration', [
            MemberController::class,
            'reviewRegistration',
        ])->name('members.review-registration');

        Route::get('/pengaturan-peminjaman', [
            LoanPolicyController::class,
            'edit',
        ])->name('loan-policy.edit');

        Route::patch('/pengaturan-peminjaman', [
            LoanPolicyController::class,
            'update',
        ])->name('loan-policy.update');

        Route::get('/loans', [
            LoanController::class,
            'index',
        ])->name('loans.index');

        Route::get('/loans/{loan}/return', [
            LoanController::class,
            'returnProcess',
        ])->name('loans.return');

        Route::post('/loans/{loan}/return', [
            LoanController::class,
            'processReturn',
        ])->name('loans.process-return');

        Route::patch('/loans/{loan}/simulate-overdue', [
            LoanController::class,
            'simulateOverdue',
        ])->name('loans.simulate-overdue');

        Route::get('/keterlambatan-denda', [
            FineController::class,
            'index',
        ])->name('fines.index');

        Route::get('/riwayat', [
            LoanController::class,
            'history',
        ])->name('loans.history');

        Route::get('/riwayat/cetak', [
            LoanController::class,
            'printHistory',
        ])->name('loans.print');
    });

Route::middleware('auth')->group(function () {
    Route::get('/katalog', [App\Http\Controllers\Member\LoanController::class, 'catalog'])->name('catalog');
    Route::get('/detail-unit', [App\Http\Controllers\Member\LoanController::class, 'detailUnit'])->name('unit.detail');
    Route::get('/peminjaman/ketersediaan', [App\Http\Controllers\Member\LoanController::class, 'availability'])->name('loan.availability');
    Route::post('/peminjaman', [App\Http\Controllers\Member\LoanController::class, 'store'])->name('loan.store');
    Route::get('/peminjaman-berhasil', [App\Http\Controllers\Member\LoanController::class, 'success'])->name('loan.success');
    Route::get('/peminjaman-aktif', [App\Http\Controllers\Member\LoanController::class, 'activeLoans'])->name('loan.active');
    Route::get('/riwayat', [App\Http\Controllers\Member\LoanController::class, 'history'])->name('loan.history');
    Route::get('/riwayat/cetak', [App\Http\Controllers\Member\LoanController::class, 'printHistory'])->name('loan.history.print');
    Route::get('/mata-uang/konversi', [FineController::class, 'convertCurrency'])->name('currency.convert');
});

Route::get('/profil', function () {
    return view('member.profile');
})->middleware('auth')->name('profile');

Route::post('/profil', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    /** @var User|null $user */
    $user = $request->user();
    if ($user) {
        $user->name = $request->input('name');
        $user->save();
    }

    return redirect()->route('profile')->with('status', 'profile-updated');
})->middleware('auth')->name('profile.update');
