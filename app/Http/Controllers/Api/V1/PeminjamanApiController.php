<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Controller REST API Transaksi Sirkulasi Peminjaman & Pengembalian Buku Berbasis ACID
class PeminjamanApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/peminjamans
     * Ambil riwayat peminjaman terpaginasi dengan filter status dan NIM
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);

        $paginator = Peminjaman::query()
            ->with('buku')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('nim'), fn ($q, $nim) => $q->where('nim_peminjam', $nim))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: PeminjamanResource::collection($paginator->items()),
            message: 'Daftar riwayat peminjaman buku berhasil diambil.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total_items'  => $paginator->total(),
                'total_pages'  => $paginator->lastPage(),
                'links'        => [
                    'first' => $paginator->url(1),
                    'prev'  => $paginator->previousPageUrl(),
                    'next'  => $paginator->nextPageUrl(),
                    'last'  => $paginator->url($paginator->lastPage()),
                ],
            ]
        );
    }

    /**
     * POST /api/v1/peminjamans
     * Transaksi peminjaman buku baru dengan jaminan ACID + lockForUpdate()
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'buku_id'       => 'required|exists:bukus,id',
            'nim_peminjam'  => 'required|string|min:6|max:15',
            'nama_peminjam' => 'required|string|min:3|max:150',
            'durasi_hari'   => 'nullable|integer|min:1|max:30',
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. lockForUpdate kunci baris buku biar ga kena race condition pas stok 1 materi w6
            $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->first();
            if (!$buku) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: "Buku dengan ID {$validated['buku_id']} tidak ditemukan.",
                    status: 404,
                    errorSlug: 'book-not-found'
                );
            }

            // 2. Cek apakah mahasiswa masih punya peminjaman aktif untuk buku yang sama -> 409 Conflict
            $nimBersih = strtoupper(trim($validated['nim_peminjam']));
            $cekPinjaman = Peminjaman::where('buku_id', $buku->id)
                ->where('nim_peminjam', $nimBersih)
                ->where('status', 'dipinjam')
                ->exists();

            if ($cekPinjaman) {
                return $this->problemResponse(
                    title: 'Duplicate Active Loan Conflict',
                    detail: "Mahasiswa dengan NIM '{$nimBersih}' masih meminjam buku '{$buku->judul}' dan belum mengembalikannya.",
                    status: 409,
                    errorSlug: 'duplicate-loan-conflict'
                );
            }

            // 3. Cek sisa stok buku -> jika stok habis, tolak dengan 422 Unprocessable Content
            if ($buku->stok < 1) {
                return $this->problemResponse(
                    title: 'Book Stock Exhausted',
                    detail: "Maaf, stok untuk buku '{$buku->judul}' sedang habis (0 tersisa).",
                    status: 422,
                    errorSlug: 'stock-exhausted'
                );
            }

            // 4. Kurangi stok buku secara atomik (-1)
            $buku->decrement('stok');

            // 5. Catat transaksi peminjaman baru
            $durasiHari = (int) ($validated['durasi_hari'] ?? 7);
            $kodeUnik   = 'PINJAM-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $peminjaman = Peminjaman::create([
                'kode_pinjam'         => $kodeUnik,
                'buku_id'             => $buku->id,
                'nim_peminjam'        => $nimBersih,
                'nama_peminjam'       => $validated['nama_peminjam'],
                'tanggal_pinjam'      => now()->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays($durasiHari)->toDateString(),
                'tanggal_kembali'     => null,
                'status'              => 'dipinjam',
            ]);

            $peminjaman->load('buku');

            return $this->createdResponse(
                data: new PeminjamanResource($peminjaman),
                locationUrl: url("/api/v1/peminjamans/{$peminjaman->id}"),
                message: "Peminjaman buku '{$buku->judul}' berhasil dicatat. Sisa stok kini: {$buku->fresh()->stok}."
            );
        });
    }

    /**
     * PATCH /api/v1/peminjamans/{id}/kembalikan
     * Transaksi pengembalian buku dengan jaminan ACID + lockForUpdate()
     */
    public function kembalikan(string $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            // Kunci baris peminjaman saat operasi pengembalian
            $peminjaman = Peminjaman::where('id', $id)->lockForUpdate()->first();
            if (!$peminjaman) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: "Data peminjaman dengan ID '{$id}' tidak ditemukan.",
                    status: 404,
                    errorSlug: 'loan-not-found'
                );
            }

            // Cegah pengembalian ganda jika status sudah 'dikembalikan' -> 409 Conflict
            if ($peminjaman->status === 'dikembalikan') {
                return $this->problemResponse(
                    title: 'Conflict — Book Already Returned',
                    detail: "Buku pada transaksi peminjaman ID '{$id}' sudah dikembalikan sebelumnya pada {$peminjaman->tanggal_kembali?->format('d M Y')}.",
                    status: 409,
                    errorSlug: 'loan-already-returned'
                );
            }

            // Ubah status transaksi jadi 'dikembalikan' dan catat tanggal kembali hari ini
            $peminjaman->update([
                'status'          => 'dikembalikan',
                'tanggal_kembali' => now()->toDateString(),
            ]);

            // Tambah stok buku (+1) secara aman dengan penguncian baris
            $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->first();
            if ($buku) {
                $buku->increment('stok');
            }

            return $this->successResponse(
                data: new PeminjamanResource($peminjaman->fresh(['buku'])),
                message: "Buku berhasil dikembalikan. Stok buku '{$buku?->judul}' kini bertambah menjadi {$buku?->fresh()->stok}."
            );
        });
    }
}
