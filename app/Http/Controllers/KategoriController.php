<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    // 1. Tampilkan Semua Kategori
    public function index()
    {
        $kategoris = Kategori::withCount('bukus')->latest()->paginate(10);

        return view('kategori.index', compact('kategoris'));
    }

    // 2. Tampilkan Form Tambah Kategori
    public function create()
    {
        return view('kategori.create');
    }

    // 3. Simpan Kategori Baru ke Database
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_kategori' => 'required|string|max:20|unique:kategoris,kode_kategori',
            'nama_kategori' => 'required|string|min:3|max:100',
        ]);

        Kategori::create($validated);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    // 4. Tampilkan Detail Kategori dan Buku di Dalamnya
    public function show(Kategori $kategori)
    {
        $kategori->load('bukus');

        return view('kategori.show', compact('kategori'));
    }

    // 5. Tampilkan Form Edit Kategori
    public function edit(Kategori $kategori)
    {
        return view('kategori.edit', compact('kategori'));
    }

    // 6. Update Data Kategori di Database
    public function update(Request $request, Kategori $kategori)
    {
        $validated = $request->validate([
            'kode_kategori' => 'required|string|max:20|unique:kategoris,kode_kategori,' . $kategori->id,
            'nama_kategori' => 'required|string|min:3|max:100',
        ]);

        $kategori->update($validated);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    // 7. Hapus Kategori dari Database
    public function destroy(Kategori $kategori)
    {
        $kategori->delete();

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus!');
    }
}
