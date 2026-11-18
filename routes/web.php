<?php

use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

// Redirect root URL ke daftar buku (buku.index)
Route::get('/', fn () => redirect()->route('buku.index'));

// 7 RESTful Route Resource untuk Kategori dan Buku
Route::resource('kategori', KategoriController::class);
Route::resource('buku', BukuController::class);

// Rute Sirkulasi Peminjaman & Pengembalian Buku Berbasis ACID
Route::resource('peminjaman', PeminjamanController::class)->only(['index', 'create', 'store']);
Route::post('peminjaman/{peminjaman}/kembalikan', [PeminjamanController::class, 'kembalikan'])->name('peminjaman.kembalikan');

