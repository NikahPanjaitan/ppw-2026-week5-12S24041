<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    // 1. Tampilkan Semua Buku (Eager Loading with('kategori') + Pagination untuk atasi N+1 problem)
    public function index(Request $request)
    {
        $query = Buku::with('kategori')->latest();

        // Fitur pencarian sederhana untuk kemudahan eksplorasi katalog buku
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', "%{$keyword}%")
                  ->orWhere('penulis', 'like', "%{$keyword}%")
                  ->orWhere('isbn', 'like', "%{$keyword}%");
            });
        }

        $bukus = $query->paginate(10)->withQueryString();

        return view('buku.index', compact('bukus'));
    }

    // 2. Tampilkan Form Tambah Buku
    public function create()
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('buku.create', compact('kategoris'));
    }

    // 3. Simpan Buku Baru ke Database (Validasi Sisi Server)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'isbn'         => 'required|string|max:30|unique:bukus,isbn',
            'judul'        => 'required|string|min:5|max:255',
            'penulis'      => 'required|string|max:150',
            'penerbit'     => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 1),
            'kategori_id'  => 'required|exists:kategoris,id',
            'stok'         => 'required|integer|min:0',
            'sinopsis'     => 'nullable|string',
        ]);

        Buku::create($validated);

        return redirect()->route('buku.index')->with('success', 'Buku berhasil ditambahkan!');
    }

    // 4. Tampilkan Detail Buku (Route Model Binding)
    public function show(Buku $buku)
    {
        $buku->load('kategori');

        return view('buku.show', compact('buku'));
    }

    // 5. Tampilkan Form Edit Buku
    public function edit(Buku $buku)
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('buku.edit', compact('buku', 'kategoris'));
    }

    // 6. Update Data Buku di Database (Route Model Binding + Validasi)
    public function update(Request $request, Buku $buku)
    {
        $validated = $request->validate([
            'isbn'         => 'required|string|max:30|unique:bukus,isbn,' . $buku->id,
            'judul'        => 'required|string|min:5|max:255',
            'penulis'      => 'required|string|max:150',
            'penerbit'     => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 1),
            'kategori_id'  => 'required|exists:kategoris,id',
            'stok'         => 'required|integer|min:0',
            'sinopsis'     => 'nullable|string',
        ]);

        $buku->update($validated);

        return redirect()->route('buku.index')->with('success', 'Buku berhasil diperbarui!');
    }

    // 7. Hapus Buku dari Database (Route Model Binding)
    public function destroy(Buku $buku)
    {
        $buku->delete();

        return redirect()->route('buku.index')->with('success', 'Buku berhasil dihapus!');
    }
}
