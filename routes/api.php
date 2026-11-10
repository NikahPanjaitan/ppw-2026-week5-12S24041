<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\BukuApiController;
use App\Http\Controllers\Api\V1\PeminjamanApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes SIPUS-Del (v1)
|--------------------------------------------------------------------------
| Mengikuti kaidah RESTful Roy Fielding, HATEOAS level 3, dan otentikasi
| stateless Bearer Token via Laravel Sanctum.
*/

Route::prefix('v1')->group(function () {

    // =========================================================================
    // 1. ENDPOINT PUBLIK (Tanpa Token Bearer)
    // =========================================================================
    // Otentikasi login pustakawan untuk mendapatkan token
    Route::post('/auth/login', [AuthTokenController::class, 'login']);

    // Katalog Buku Publik (Read-Only) & Integrasi Consumer Eksternal
    Route::get('/bukus', [BukuApiController::class, 'index']);
    Route::get('/bukus/{id}', [BukuApiController::class, 'show']);
    Route::get('/bukus/{id}/referensi-eksternal', [BukuApiController::class, 'referensiEksternal']);

    // =========================================================================
    // 2. ENDPOINT TERPROTEKSI STATELESS BEARER TOKEN (Wajib auth:sanctum)
    // =========================================================================
    Route::middleware('auth:sanctum')->group(function () {
        // Profil pustakawan & pencabutan token (logout)
        Route::get('/auth/me', [AuthTokenController::class, 'me']);
        Route::post('/auth/logout', [AuthTokenController::class, 'logout']);

        // Mutasi Katalog Buku (Semantik POST, PUT vs PATCH, DELETE)
        Route::post('/bukus', [BukuApiController::class, 'store']);
        Route::put('/bukus/{id}', [BukuApiController::class, 'update']);
        Route::patch('/bukus/{id}', [BukuApiController::class, 'update']);
        Route::delete('/bukus/{id}', [BukuApiController::class, 'destroy']);

        // Transaksi Sirkulasi ACID (Peminjaman & Pengembalian Buku)
        Route::get('/peminjamans', [PeminjamanApiController::class, 'index']);
        Route::post('/peminjamans', [PeminjamanApiController::class, 'store']);
        Route::patch('/peminjamans/{id}/kembalikan', [PeminjamanApiController::class, 'kembalikan']);
    });
});
