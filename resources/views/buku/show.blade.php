<x-layout title="Detail Buku: {{ $buku->judul }}">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Buku
                </a>
                <div class="d-flex gap-2">
                    <a href="{{ route('buku.edit', $buku) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil-square me-1"></i> Edit Data
                    </a>
                    <form action="{{ route('buku.destroy', $buku) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash me-1"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header card-header-del py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-book me-2"></i>Informasi Detail Buku
                    </h5>
                    <span class="badge bg-light text-dark font-monospace">
                        ISBN: {{ $buku->isbn }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <span class="badge badge-del mb-2">
                            {{ $buku->kategori->nama_kategori ?? 'Tanpa Kategori' }}
                        </span>
                        <h3 class="fw-bold text-dark">{{ $buku->judul }}</h3>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-muted d-block mb-1">Penulis</small>
                                <strong class="text-dark fs-6">
                                    <i class="bi bi-person me-1"></i>{{ $buku->penulis }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-muted d-block mb-1">Penerbit & Tahun Terbit</small>
                                <strong class="text-dark fs-6">
                                    <i class="bi bi-building me-1"></i>{{ $buku->penerbit }} ({{ $buku->tahun_terbit }})
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-muted d-block mb-1">Kode Kategori</small>
                                <strong class="text-dark fs-6">
                                    <i class="bi bi-tag me-1"></i>{{ $buku->kategori->kode_kategori ?? '-' }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-muted d-block mb-1">Ketersediaan Stok</small>
                                @if($buku->stok > 0)
                                    <span class="badge badge-del-blue fs-6 px-3 py-1 rounded-pill">
                                        <i class="bi bi-check-circle me-1"></i>Tersedia: {{ $buku->stok }} Eksemplar
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger fs-6 px-3 py-1 rounded-pill">
                                        <i class="bi bi-x-circle me-1"></i>Stok Habis
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <h6 class="fw-bold text-secondary mb-2">
                            <i class="bi bi-card-text me-1"></i>Sinopsis & Ringkasan Buku
                        </h6>
                        @if($buku->sinopsis)
                            <p class="text-secondary lh-base mb-0" style="text-align: justify;">
                                {{ $buku->sinopsis }}
                            </p>
                        @else
                            <p class="text-muted fst-italic mb-0">
                                Sinopsis belum tersedia untuk judul buku ini.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
