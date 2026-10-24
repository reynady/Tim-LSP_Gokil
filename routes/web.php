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

Route::get('/katalog', function () {
    return view('member.catalog');
})->middleware('auth')->name('catalog');

Route::get('/detail-unit', function () {
    return view('member.detail-unit');
})->middleware('auth')->name('unit.detail');

Route::get('/peminjaman-berhasil', function () {
    return view('member.loan-success');
})->middleware('auth')->name('loan.success');

Route::get('/peminjaman-aktif', function () {
    return view('member.active-loans');
})->middleware('auth')->name('loan.active');

Route::get('/riwayat', function () {
    return view('member.history');
})->middleware('auth')->name('loan.history');

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