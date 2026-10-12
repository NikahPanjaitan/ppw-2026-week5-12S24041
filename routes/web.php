<?php

use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use Illuminate\Support\Facades\Route;

// Redirect root URL ke daftar buku (buku.index)
Route::get('/', fn () => redirect()->route('buku.index'));

// 7 RESTful Route Resource untuk Kategori dan Buku
Route::resource('kategori', KategoriController::class);
Route::resource('buku', BukuController::class);
