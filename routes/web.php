<?php

use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\Soap\LibrarySoapController;
use Illuminate\Support\Facades\Route;

// Redirect root URL ke daftar buku (buku.index)
Route::get('/', fn () => redirect()->route('buku.index'));

// 7 RESTful Route Resource untuk Kategori dan Buku (web views)
Route::resource('kategori', KategoriController::class);
Route::resource('buku', BukuController::class);

// Layanan SOAP Clearance Bebas Pustaka Yudisium (Modul W9 Part V)
Route::get('/soap/sipus.wsdl', [LibrarySoapController::class, 'wsdl']);
Route::post('/soap/sipus', [LibrarySoapController::class, 'handle']);

