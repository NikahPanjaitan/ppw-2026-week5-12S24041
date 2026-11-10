<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BukuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Hitung total riwayat peminjaman jika withCount('peminjamans') dipanggil
        $loanCount = $this->whenCounted('peminjamans', fn () => $this->peminjamans_count, 0);
        $isAvailable = $this->stok > 0;

        // Kontrol Hypermedia HATEOAS (Richardson Maturity Model Level 3)
        $links = [
            'self' => [
                'href'   => url("/api/v1/bukus/{$this->id}"),
                'method' => 'GET',
            ],
            'referensi_eksternal' => [
                'href'   => url("/api/v1/bukus/{$this->id}/referensi-eksternal"),
                'method' => 'GET',
            ],
            'update_full' => [
                'href'   => url("/api/v1/bukus/{$this->id}"),
                'method' => 'PUT',
            ],
            'update_partial' => [
                'href'   => url("/api/v1/bukus/{$this->id}"),
                'method' => 'PATCH',
            ],
            'delete' => [
                'href'   => url("/api/v1/bukus/{$this->id}"),
                'method' => 'DELETE',
            ],
        ];

        // HATEOAS dinamis: Tautan 'pinjam_buku' HANYA muncul jika stok masih tersedia (> 0)
        if ($isAvailable) {
            $links['pinjam_buku'] = [
                'href'   => url('/api/v1/peminjamans'),
                'method' => 'POST',
            ];
        }

        return [
            'id'                  => $this->id,
            'isbn'                => $this->isbn,
            'judul'               => $this->judul,
            'penulis'             => $this->penulis,
            'penerbit'            => $this->penerbit,
            'tahun_terbit'        => (int) $this->tahun_terbit,
            'kategori_id'         => $this->kategori_id,
            'kategori'            => new KategoriResource($this->whenLoaded('kategori')),
            'stok'                => (int) $this->stok,
            'status_ketersediaan' => $isAvailable ? 'TERSEDIA' : 'HABIS',
            'total_peminjaman'    => $loanCount,
            'sinopsis'            => $this->sinopsis,
            'riwayat_peminjaman'  => PeminjamanResource::collection($this->whenLoaded('peminjamans')),
            'updated_at'          => $this->updated_at?->toIso8601String(),
            '_links'              => $links,
        ];
    }
}
