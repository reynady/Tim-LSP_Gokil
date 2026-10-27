<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Kata sandi minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'member',
            'is_active' => false,
            'approval_status' => User::APPROVAL_PENDING,
        ]);

        return redirect()->route('login')
            ->with('status', 'Pendaftaran berhasil. Akun menunggu validasi admin; kamu bisa masuk setelah disetujui.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        if ($user->approval_status === User::APPROVAL_PENDING) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Akun masih menunggu persetujuan admin.',
            ]);
        }

        if ($user->approval_status !== User::APPROVAL_APPROVED) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Pendaftaran akun tidak disetujui. Hubungi administrator jika membutuhkan bantuan.',
            ]);
        }

        if (! $user->canLogin()) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Akun kamu tidak aktif.',
            ]);
        }

        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
