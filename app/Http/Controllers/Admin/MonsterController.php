<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Monster;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MonsterController extends Controller
{
    /**
     * Menampilkan daftar monster dengan filter pencarian nama (BR-008), status, kategori, dan level ancaman (FR-005).
     */
    public function index(Request $request)
    {
        $query = Monster::with('categories');

        // Pencarian berdasarkan nama monster (BR-008) atau kode monster (BR-007)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('monster_code', 'like', "%{$search}%");
            });
        }

        // Filter status unit
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter kategori
        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Filter level ancaman
        if ($request->filled('threat_level')) {
            $query->where('threat_level', $request->threat_level);
        }

        $monsters = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $categories = Category::orderBy('name', 'asc')->get();

        return view('admin.monsters.index', compact('monsters', 'categories'));
    }

    /**
     * Menampilkan form tambah monster baru.
     * Mengirimkan data kategori untuk relasi multi-kategori (BR-005).
     */
    public function create()
    {
        $categories = Category::orderBy('name', 'asc')->get();

        return view('admin.monsters.create', compact('categories'));
    }

    /**
     * Menyimpan data monster baru dan menyinkronkan kategori ke tabel pivot category_monster.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Kode monster wajib dan harus unik (BR-007, VAL-N-02, VAL-N-03)
            'monster_code' => ['required', 'string', 'max:50', 'unique:monsters,monster_code'],
            // Nama monster wajib diisi; nama boleh sama antar monster (BR-006, VAL-N-01)
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'condition' => ['nullable', 'string', 'max:100'],
            'threat_level' => ['required', 'string', Rule::in(['Low', 'Medium', 'High', 'Extreme', 'Unknown'])],
            'origin_type' => ['required', 'string', 'max:100'],
            // Status harus bernilai valid (VAL-N-04)
            'status' => ['required', 'string', Rule::in(['available', 'borrowed', 'maintenance'])],
            'image' => ['nullable', 'string', 'max:500'],
            // Monster harus memiliki minimal satu kategori (VAL-N-05, BR-005)
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['exists:categories,id'],
        ], [
            'monster_code.required' => 'Kode monster wajib diisi (VAL-N-02).',
            'monster_code.unique' => 'Kode monster sudah terdaftar, kode harus unik (BR-007, VAL-N-03).',
            'name.required' => 'Nama monster wajib diisi (VAL-N-01).',
            'threat_level.required' => 'Tingkat ancaman wajib dipilih.',
            'threat_level.in' => 'Tingkat ancaman tidak valid.',
            'origin_type.required' => 'Tipe asal usul (origin type) wajib diisi.',
            'status.required' => 'Status ketersediaan monster wajib dipilih (VAL-N-04).',
            'status.in' => 'Status yang dipilih tidak valid.',
            'categories.required' => 'Monster wajib memiliki minimal 1 (satu) kategori (VAL-N-05).',
            'categories.min' => 'Pilih minimal 1 kategori untuk monster ini (VAL-N-05).',
            'categories.*.exists' => 'Kategori yang dipilih tidak valid di database.',
        ]);

        $categoryIds = $validated['categories'];
        unset($validated['categories']);

        $monster = Monster::create($validated);
        // Menyinkronkan relasi multi-kategori ke tabel pivot category_monster (BR-005)
        $monster->categories()->sync($categoryIds);

        return redirect()->route('admin.monsters.index')
            ->with('success', "Monster '{$monster->name}' [{$monster->monster_code}] berhasil ditambahkan ke inventaris.");
    }

    /**
     * Menampilkan form edit monster beserta relasi kategorinya.
     */
    public function edit(Monster $monster)
    {
        $monster->load('categories');
        $categories = Category::orderBy('name', 'asc')->get();

        return view('admin.monsters.edit', compact('monster', 'categories'));
    }

    /**
     * Memperbarui data monster dan relasi multi-kategorinya.
     */
    public function update(Request $request, Monster $monster)
    {
        $validated = $request->validate([
            // Kode unik kecuali untuk monster ini sendiri (BR-007)
            'monster_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('monsters', 'monster_code')->ignore($monster->id),
            ],
            // Nama monster wajib diisi (BR-006: boleh sama dengan monster lain)
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'condition' => ['nullable', 'string', 'max:100'],
            'threat_level' => ['required', 'string', Rule::in(['Low', 'Medium', 'High', 'Extreme', 'Unknown'])],
            'origin_type' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', Rule::in(['available', 'borrowed', 'maintenance'])],
            'image' => ['nullable', 'string', 'max:500'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['exists:categories,id'],
        ], [
            'monster_code.required' => 'Kode monster wajib diisi (VAL-N-02).',
            'monster_code.unique' => 'Kode monster sudah digunakan oleh monster lain (BR-007, VAL-N-03).',
            'name.required' => 'Nama monster wajib diisi (VAL-N-01).',
            'threat_level.required' => 'Tingkat ancaman wajib dipilih.',
            'threat_level.in' => 'Tingkat ancaman tidak valid.',
            'origin_type.required' => 'Tipe asal usul (origin type) wajib diisi.',
            'status.required' => 'Status monster wajib dipilih (VAL-N-04).',
            'categories.required' => 'Monster wajib memiliki minimal 1 (satu) kategori (VAL-N-05).',
            'categories.min' => 'Pilih minimal 1 kategori untuk monster ini (VAL-N-05).',
            'categories.*.exists' => 'Kategori yang dipilih tidak valid di database.',
        ]);

        $categoryIds = $validated['categories'];
        unset($validated['categories']);

        $monster->update($validated);
        // Sinkronisasi kategori ke tabel pivot category_monster (BR-005)
        $monster->categories()->sync($categoryIds);

        return redirect()->route('admin.monsters.index')
            ->with('success', "Data monster '{$monster->name}' [{$monster->monster_code}] berhasil diperbarui.");
    }

    /**
     * Menghapus monster dengan pengecekan status peminjaman (FR-005 Alternative Flow A3).
     */
    public function destroy(Monster $monster)
    {
        // Aturan bisnis FR-005 Alt A3: Monster berstatus dipinjam atau memiliki transaksi aktif ditolak penghapusannya
        if ($monster->status === 'borrowed') {
            return redirect()->route('admin.monsters.index')
                ->with('error', "Monster '{$monster->name}' [{$monster->monster_code}] tidak dapat dihapus karena saat ini sedang berstatus 'Dipinjam' (FR-005 Alt A3).");
        }

        if ($monster->loanDetails()->whereNull('returned_at')->exists()) {
            return redirect()->route('admin.monsters.index')
                ->with('error', "Monster '{$monster->name}' [{$monster->monster_code}] masih memiliki transaksi peminjaman aktif yang belum dikembalikan (FR-005 Alt A3).");
        }

        $monsterName = $monster->name;
        $monsterCode = $monster->monster_code;

        // Lepas relasi pivot kategori terlebih dahulu
        $monster->categories()->detach();
        $monster->delete();

        return redirect()->route('admin.monsters.index')
            ->with('success', "Monster '{$monsterName}' [{$monsterCode}] berhasil dihapus dari inventaris.");
    }
}
