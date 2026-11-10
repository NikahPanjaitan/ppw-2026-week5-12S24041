<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BukuResource;
use App\Models\Buku;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Controller REST API Katalog Buku (Provider & Consumer OpenLibrary)
class BukuApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/bukus
     * Ambil koleksi buku dengan eager loading kategori, withCount peminjaman (anti N+1),
     * filter search, filter kategori, filter ketersediaan stok, sorting, & paginasi.
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Batasi sorting agar aman dari SQL injection
        $allowedSorts = ['judul', 'penulis', 'tahun_terbit', 'stok', 'created_at'];
        $sortBy  = in_array($request->query('sort'), $allowedSorts) ? $request->query('sort') : 'created_at';
        $sortDir = strtolower($request->query('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);

        // 2. Kueri Eloquent bebas N+1 problem
        $paginator = Buku::query()
            ->with('kategori')
            ->withCount('peminjamans')
            // Filter pencarian judul, penulis, atau ISBN
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            // Filter kategori spesifik
            ->when($request->query('kategori_id'), fn ($q, $catId) => $q->where('kategori_id', $catId))
            // Filter stok tersedia (?tersedia=1)
            ->when($request->boolean('tersedia'), fn ($q) => $q->where('stok', '>', 0))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        // 3. Kembalikan respons terstandar dengan metadata pagination
        return $this->successResponse(
            data: BukuResource::collection($paginator->items()),
            message: 'Daftar koleksi buku berhasil diambil.',
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
     * GET /api/v1/bukus/{id}
     * Ambil detail 1 buku beserta kategori dan riwayat peminjamannya
     */
    public function show(string $id): JsonResponse
    {
        $buku = Buku::with(['kategori', 'peminjamans'])
            ->withCount('peminjamans')
            ->find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan di sistem perpustakaan.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        return $this->successResponse(
            data: new BukuResource($buku),
            message: 'Detail buku berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/bukus
     * Tambah data buku baru -> Wajib return 201 Created + Header Location
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'isbn'         => 'required|string|max:30|unique:bukus,isbn',
            'judul'        => 'required|string|min:3|max:255',
            'penulis'      => 'required|string|max:150',
            'penerbit'     => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 1),
            'kategori_id'  => 'required|exists:kategoris,id',
            'stok'         => 'required|integer|min:0|max:1000',
            'sinopsis'     => 'nullable|string',
        ]);

        $buku = Buku::create($validated);
        $buku->load('kategori');
        $buku->loadCount('peminjamans');

        return $this->createdResponse(
            data: new BukuResource($buku),
            locationUrl: url("/api/v1/bukus/{$buku->id}"),
            message: 'Buku baru berhasil ditambahkan ke katalog.'
        );
    }

    /**
     * PUT / PATCH /api/v1/bukus/{id}
     * Membuktikan perbedaan semantik PUT (Full Replacement) vs PATCH (Partial Update)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $buku = Buku::find($id);
        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        $isPut = $request->isMethod('put');

        if ($isPut) {
            // SEMANTIK PUT: Seluruh atribut wajib dikirim lengkap untuk menimpa representasi lama
            $validated = $request->validate([
                'isbn'         => "required|string|max:30|unique:bukus,isbn,{$buku->id}",
                'judul'        => 'required|string|min:3|max:255',
                'penulis'      => 'required|string|max:150',
                'penerbit'     => 'required|string|max:150',
                'tahun_terbit' => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 1),
                'kategori_id'  => 'required|exists:kategoris,id',
                'stok'         => 'required|integer|min:0|max:1000',
                'sinopsis'     => 'nullable|string',
            ]);

            // Pada PUT murni, jika field opsional sinopsis tidak dikirim, direset ke null
            $validated['sinopsis'] = $request->input('sinopsis', null);
        } else {
            // SEMANTIK PATCH: Hanya memvalidasi field yang dikirimkan saja (sometimes)
            $validated = $request->validate([
                'isbn'         => "sometimes|required|string|max:30|unique:bukus,isbn,{$buku->id}",
                'judul'        => 'sometimes|required|string|min:3|max:255',
                'penulis'      => 'sometimes|required|string|max:150',
                'penerbit'     => 'sometimes|required|string|max:150',
                'tahun_terbit' => 'sometimes|required|integer|digits:4|min:1900|max:' . (date('Y') + 1),
                'kategori_id'  => 'sometimes|required|exists:kategoris,id',
                'stok'         => 'sometimes|required|integer|min:0|max:1000',
                'sinopsis'     => 'sometimes|nullable|string',
            ]);

            if (empty($validated)) {
                return $this->problemResponse(
                    title: 'Bad Request',
                    detail: 'Permintaan PATCH wajib menyertakan minimal satu atribut yang ingin diperbarui.',
                    status: 400,
                    errorSlug: 'empty-patch-payload'
                );
            }
        }

        $buku->update($validated);
        $buku->load('kategori');
        $buku->loadCount('peminjamans');

        return $this->successResponse(
            data: new BukuResource($buku),
            message: $isPut
                ? 'Seluruh representasi buku berhasil diganti (PUT).'
                : 'Atribut buku berhasil diperbarui sebagian (PATCH).'
        );
    }

    /**
     * DELETE /api/v1/bukus/{id}
     * Menghapus buku -> Tolak 409 Conflict jika buku masih sedang dipinjam
     */
    public function destroy(string $id): JsonResponse
    {
        $buku = Buku::withCount([
            'peminjamans as active_loans_count' => fn ($q) => $q->where('status', 'dipinjam'),
        ])->find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        // Aturan bisnis: Cegah penghapusan jika ada mahasiswa yang masih meminjam buku ini
        if ($buku->active_loans_count > 0) {
            return $this->problemResponse(
                title: 'Conflict — Book Has Active Loans',
                detail: "Buku '{$buku->judul}' tidak dapat dihapus karena masih sedang dipinjam oleh {$buku->active_loans_count} mahasiswa.",
                status: 409,
                errorSlug: 'book-has-active-loans'
            );
        }

        $buku->delete();

        // 204 No Content untuk penghapusan yang sukses
        return $this->noContentResponse();
    }

    /**
     * GET /api/v1/bukus/{id}/referensi-eksternal
     * REST Consumer: Memanggil OpenLibrary API untuk memperkaya metadata buku eksternal
     */
    public function referensiEksternal(string $id): JsonResponse
    {
        $buku = Buku::find($id);
        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan di katalog lokal.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        // Bersihkan ISBN dari tanda strip
        $cleanIsbn = str_replace(['-', ' '], '', $buku->isbn);
        $openLibraryUrl = "https://openlibrary.org/api/books?bibkeys=ISBN:{$cleanIsbn}&format=json&jscmd=data";

        try {
            // Panggil API eksternal dengan timeout 8 detik dan bypass verifikasi SSL lokal Windows
            $response = Http::withoutVerifying()
                ->timeout(8)
                ->acceptJson()
                ->get($openLibraryUrl);

            if ($response->failed()) {
                return $this->problemResponse(
                    title: 'External Service Unavailable',
                    detail: 'Layanan eksternal OpenLibrary sedang mengalami kendala respons.',
                    status: 502,
                    errorSlug: 'external-service-error'
                );
            }

            $json = $response->json();
            $key  = "ISBN:{$cleanIsbn}";

            // Jika buku belum tercatat di database publik OpenLibrary
            if (empty($json) || !isset($json[$key])) {
                return $this->successResponse(
                    data: [
                        'sumber'          => 'OpenLibrary API (https://openlibrary.org)',
                        'isbn'            => $buku->isbn,
                        'judul_lokal'     => $buku->judul,
                        'status_eksternal'=> 'Data tidak ditemukan di katalog publik OpenLibrary.',
                        'metadata'        => null,
                    ],
                    message: "Metadata eksternal untuk ISBN {$buku->isbn} belum terdaftar di OpenLibrary."
                );
            }

            $meta = $json[$key];

            $publishers = isset($meta['publishers']) ? collect($meta['publishers'])->pluck('name')->implode(', ') : null;
            $authors    = isset($meta['authors']) ? collect($meta['authors'])->pluck('name')->implode(', ') : null;

            return $this->successResponse(
                data: [
                    'sumber'           => 'OpenLibrary API (https://openlibrary.org)',
                    'isbn'             => $buku->isbn,
                    'judul_eksternal'  => $meta['title'] ?? $buku->judul,
                    'penulis'          => $authors ?? $buku->penulis,
                    'penerbit'         => $publishers ?? $buku->penerbit,
                    'tahun_terbit'     => $meta['publish_date'] ?? $buku->tahun_terbit,
                    'jumlah_halaman'   => $meta['number_of_pages'] ?? null,
                    'tautan_pratinjau' => $meta['url'] ?? null,
                    'cover_url'        => $meta['cover']['medium'] ?? ($meta['cover']['small'] ?? null),
                ],
                message: 'Metadata buku eksternal berhasil diambil dari OpenLibrary.'
            );
        } catch (\Throwable $e) {
            Log::warning('[OPENLIBRARY CONSUMER ERROR]: ' . $e->getMessage());

            // Kembalikan fallback yang anggun jika koneksi internet offline/timeout
            return $this->successResponse(
                data: [
                    'sumber'          => 'OpenLibrary API (Offline/Timeout Fallback)',
                    'isbn'            => $buku->isbn,
                    'judul_lokal'     => $buku->judul,
                    'status_koneksi'  => 'Koneksi ke OpenLibrary timeout atau jaringan lokal terbatas.',
                    'pesan'           => 'Data katalog lokal tetap aman dan dapat diakses normal.',
                ],
                message: 'Gagal menghubungi OpenLibrary (fallback graceful aktif).'
            );
        }
    }
}
