<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BukuController extends Controller
{
    // 1. Tampilkan Semua Buku (Eager Loading + withCount + Filter Dinamis + Fluent Query Builder Stats)
    public function index(Request $request)
    {
        $search = $request->query('search');
        $kategoriId = $request->query('kategori_id');

        $bukus = Buku::with('kategori')
            ->withCount('peminjamans')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%")
                      ->orWhere('penulis', 'like', "%{$search}%")
                      ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when($kategoriId, function ($query, $kategoriId) {
                $query->where('kategori_id', $kategoriId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // 1. Pendekatan Raw SQL (DB::select) - Analisis agregasi koleksi terpopuler
        $popularBooks = DB::select(
            "SELECT bukus.judul, COUNT(peminjamans.id) as total_dipinjam 
             FROM bukus 
             LEFT JOIN peminjamans ON bukus.id = peminjamans.buku_id 
             GROUP BY bukus.id, bukus.judul 
             ORDER BY total_dipinjam DESC 
             LIMIT 3"
        );

        // 2. Pendekatan Fluent Query Builder (DB::table) - Rekapitulasi metrik statistik
        $stats = [
            'total_judul'    => DB::table('bukus')->count(),
            'total_stok'     => (int) DB::table('bukus')->sum('stok'),
            'total_dipinjam' => DB::table('peminjamans')->where('status', 'dipinjam')->count(),
            'total_kembali'  => DB::table('peminjamans')->where('status', 'dikembalikan')->count(),
        ];

        // 3. Pendekatan Eloquent ORM (Model) - Pengambilan entitas kategori
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('buku.index', compact('bukus', 'stats', 'kategoris', 'search', 'kategoriId', 'popularBooks'));
    }

    // 2. Form Tambah Buku
    public function create()
    {
        $kategoris = Kategori::all();

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

    // 4. Detail Buku (Route Model Binding)
    public function show(Buku $buku)
    {
        $buku->load('kategori');

        return view('buku.show', compact('buku'));
    }

    // 5. Form Edit Buku
    public function edit(Buku $buku)
    {
        $kategoris = Kategori::all();

        return view('buku.edit', compact('buku', 'kategoris'));
    }

    // 6. Update Data Buku (Route Model Binding + Validasi)
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

    // 7. Hapus Buku (Route Model Binding)
    public function destroy(Buku $buku)
    {
        $buku->delete();

        return redirect()->route('buku.index')->with('success', 'Buku berhasil dihapus!');
    }
}
