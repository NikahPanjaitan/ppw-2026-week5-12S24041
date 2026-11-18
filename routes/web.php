<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

// Redirect root URL ke katalog buku
Route::get('/', fn () => redirect()->route('buku.index'));

// ==========================================
// 1. RUTE PUBLIK (Tamu & Mahasiswa - Read Only)
// ==========================================
Route::get('buku', [BukuController::class, 'index'])->name('buku.index');
Route::get('buku/{buku}', [BukuController::class, 'show'])->name('buku.show');
Route::get('kategori', [KategoriController::class, 'index'])->name('kategori.index');
Route::get('kategori/{kategori}', [KategoriController::class, 'show'])->name('kategori.show');

// ==========================================
// 2. RUTE AUTENTIKASI PUSTAKAWAN (Guest Only)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
});

// ==========================================
// 3. RUTE MUTASI TERPROTEKSI (Auth Pustakawan)
// ==========================================
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Mutasi Data Buku (Create, Store, Edit, Update, Destroy)
    Route::resource('buku', BukuController::class)->except(['index', 'show']);

    // Mutasi Data Kategori (Create, Store, Edit, Update, Destroy)
    Route::resource('kategori', KategoriController::class)->except(['index', 'show']);

    // Sirkulasi Transaksi Peminjaman & Pengembalian Buku ACID
    Route::resource('peminjaman', PeminjamanController::class)->only(['index', 'create', 'store']);
    Route::post('peminjaman/{peminjaman}/kembalikan', [PeminjamanController::class, 'kembalikan'])->name('peminjaman.kembalikan');
});
