<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    /**
     * Tampilkan daftar anggota (FR-004)
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'member')
            ->with('profile')
            ->withCount(['loans as active_loans_count' => function ($q) {
                $q->whereIn('status', ['active', 'borrowed', 'approved', 'pending']);
            }]);

        // Pencarian nama atau email (BR-003, BR-004)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter status keaktifan akun
        if ($request->filled('status')) {
            if ($request->status === 'active' || $request->status === 'Aktif') {
                $query->where('approval_status', User::APPROVAL_APPROVED)
                    ->where('is_active', true);
            } elseif ($request->status === 'pending') {
                $query->where('approval_status', User::APPROVAL_PENDING);
            } elseif ($request->status === 'rejected') {
                $query->where('approval_status', User::APPROVAL_REJECTED);
            } elseif ($request->status === 'inactive' || $request->status === 'Nonaktif') {
                $query->where('approval_status', User::APPROVAL_APPROVED)
                    ->where('is_active', false);
            }
        }

        $members = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();

        return view('admin.members.index', compact('members'));
    }

    /**
     * Form tambah anggota baru (FR-004)
     */
    public function create()
    {
        return view('admin.members.create');
    }

    /**
     * Simpan anggota baru (FR-004)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users,email',
            'password'  => 'required|string|min:8',
            'phone'     => 'nullable|string|max:30',
            'address'   => 'nullable|string|max:500',
            'is_active' => 'required|boolean',
        ], [
            'name.required'      => 'Nama anggota wajib diisi.',
            'email.required'     => 'Email anggota wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah terdaftar di sistem (FR-004 Alt A1).',
            'password.required'  => 'Kata sandi akun wajib diisi.',
            'password.min'       => 'Kata sandi minimal terdiri dari 8 karakter.',
            'is_active.required' => 'Status keaktifan anggota wajib dipilih.',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => 'member',
                'is_active' => (bool)$request->is_active,
                'approved_at' => now(),
            ]);

            Profile::create([
                'user_id' => $user->id,
                'phone'   => $request->phone,
                'address' => $request->address,
            ]);
        });

        return redirect()->route('admin.members.index')
            ->with('success', "Anggota baru '{$request->name}' berhasil ditambahkan ke sistem.");
    }

    /**
     * Form edit data anggota (FR-004)
     */
    public function edit(User $member)
    {
        if ($member->role !== 'member') {
            abort(404, 'Data anggota tidak ditemukan.');
        }

        $member->load('profile');

        return view('admin.members.edit', compact('member'));
    }

    /**
     * Update data anggota (FR-004)
     */
    public function update(Request $request, User $member)
    {
        if ($member->role !== 'member') {
            abort(404, 'Data anggota tidak ditemukan.');
        }

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users,email,' . $member->id,
            'password'  => 'nullable|string|min:8',
            'phone'     => 'nullable|string|max:30',
            'address'   => 'nullable|string|max:500',
            'is_active' => 'required|boolean',
        ], [
            'name.required'      => 'Nama anggota wajib diisi.',
            'email.required'     => 'Email anggota wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah digunakan oleh pengguna lain (FR-004 Alt A1).',
            'password.min'       => 'Kata sandi minimal terdiri dari 8 karakter.',
            'is_active.required' => 'Status keaktifan anggota wajib dipilih.',
        ]);

        DB::transaction(function () use ($request, $member) {
            $userData = [
                'name'      => $request->name,
                'email'     => $request->email,
                'is_active' => (bool)$request->is_active,
            ];

            if ((bool) $request->is_active && ! $member->approved_at) {
                $userData['approved_at'] = now();
            }

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $member->update($userData);

            $member->profile()->updateOrCreate(
                ['user_id' => $member->id],
                [
                    'phone'   => $request->phone,
                    'address' => $request->address,
                ]
            );
        });

        return redirect()->route('admin.members.index')
            ->with('success', "Data anggota '{$member->name}' berhasil diperbarui.");
    }

    /**
     * Toggle status aktif/nonaktif anggota
     */
    public function toggleStatus(User $member)
    {
        if ($member->role !== 'member') {
            abort(404, 'Data anggota tidak ditemukan.');
        }

        if ($member->approval_status !== User::APPROVAL_APPROVED) {
            return redirect()->route('admin.members.index')
                ->with('error', 'Validasi pendaftaran harus diselesaikan sebelum mengubah status akun.');
        }

        $member->is_active = !$member->is_active;
        if ($member->is_active && ! $member->approved_at) {
            $member->approved_at = now();
        }
        $member->save();

        $statusText = $member->is_active
            ? ($member->wasChanged('approved_at') ? 'disetujui dan diaktifkan' : 'diaktifkan')
            : 'dinonaktifkan';

        return redirect()->route('admin.members.index')
            ->with('success', "Status akun '{$member->name}' berhasil {$statusText}.");
    }

    public function reviewRegistration(Request $request, User $member)
    {
        if ($member->role !== 'member') {
            abort(404, 'Data anggota tidak ditemukan.');
        }

        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
        ]);

        $canReapproveRejected = $validated['decision'] === 'approve'
            && $member->approval_status === User::APPROVAL_REJECTED;

        if ($member->approval_status !== User::APPROVAL_PENDING && !$canReapproveRejected) {
            return redirect()->route('admin.members.index')
                ->with('error', 'Pendaftaran anggota ini sudah pernah divalidasi.');
        }

        if ($validated['decision'] === 'approve') {
            $member->update([
                'approval_status' => User::APPROVAL_APPROVED,
                'is_active' => true,
            ]);

            return redirect()->route('admin.members.index')
                ->with('success', "Pendaftaran '{$member->name}' disetujui. Anggota sekarang dapat login.");
        }

        $member->update([
            'approval_status' => User::APPROVAL_REJECTED,
            'is_active' => false,
        ]);

        return redirect()->route('admin.members.index')
            ->with('success', "Pendaftaran '{$member->name}' ditolak.");
    }

    /**
     * Hapus data anggota (FR-004)
     */
    public function destroy(User $member)
    {
        if ($member->role !== 'member') {
            abort(404, 'Data anggota tidak ditemukan.');
        }

        // FR-004 Alt A2: Cek apakah masih memiliki peminjaman aktif
        $hasActiveLoans = $member->loans()
            ->whereIn('status', ['active', 'borrowed', 'approved', 'pending'])
            ->exists();

        if ($hasActiveLoans) {
            return redirect()->route('admin.members.index')
                ->with('error', "Gagal menghapus anggota '{$member->name}': Anggota masih memiliki transaksi peminjaman aktif yang belum dikembalikan (FR-004 Alt A2).");
        }

        // FR-004 Alt A3: Jika memiliki riwayat transaksi lampau, nonaktifkan akun agar integritas riwayat tetap utuh (NFR-008)
        if ($member->loans()->exists()) {
            $member->update(['is_active' => false]);

            return redirect()->route('admin.members.index')
                ->with('success', "Anggota '{$member->name}' memiliki riwayat transaksi, sehingga akun dinonaktifkan (arsip transaksi tetap tersimpan sesuai NFR-008 / FR-004 Alt A3).");
        }

        // Jika tidak memiliki peminjaman sama sekali, hapus permanen
        DB::transaction(function () use ($member) {
            $member->profile()?->delete();
            $member->delete();
        });

        return redirect()->route('admin.members.index')
            ->with('success', "Anggota '{$member->name}' berhasil dihapus dari sistem.");
    }
}
