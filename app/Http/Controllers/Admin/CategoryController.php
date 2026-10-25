<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori monster dengan pencarian dan pagination (FR-006, BR-009).
     */
    public function index(Request $request)
    {
        $query = Category::withCount('monsters');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Menampilkan form penambahan kategori baru (FR-006).
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Menyimpan data kategori baru dengan validasi nama unik dan required (VAL-C-01, VAL-C-02).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Nama kategori wajib diisi (VAL-C-01).',
            'name.unique' => 'Nama kategori ini sudah terdaftar, gunakan nama yang lain (VAL-C-02).',
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'description.max' => 'Deskripsi kategori maksimal 1000 karakter.',
        ]);

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori '{$validated['name']}' berhasil ditambahkan.");
    }

    /**
     * Menampilkan form edit kategori.
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Memperbarui data kategori dengan validasi nama unik selain id sendiri (VAL-C-01, VAL-C-02).
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Nama kategori wajib diisi (VAL-C-01).',
            'name.unique' => 'Nama kategori ini sudah terdaftar pada kategori lain (VAL-C-02).',
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'description.max' => 'Deskripsi kategori maksimal 1000 karakter.',
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    /**
     * Menghapus kategori jika tidak sedang digunakan oleh unit monster (FR-006 Alternative Flow A2).
     */
    public function destroy(Category $category)
    {
        $monsterCount = $category->monsters()->count();

        // Validasi aturan bisnis FR-006 Alt A2: Tolak penghapusan jika kategori masih dipakai oleh monster
        if ($monsterCount > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', "Kategori '{$category->name}' tidak dapat dihapus karena masih digunakan oleh {$monsterCount} unit monster (FR-006). Lepaskan relasi kategori dari unit terkait terlebih dahulu.");
        }

        $categoryName = $category->name;
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori '{$categoryName}' berhasil dihapus.");
    }
}
