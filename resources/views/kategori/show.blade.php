<x-layout title="Detail Kategori: {{ $kategori->nama_kategori }}">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kategori
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil-square me-1"></i> Edit Kategori
            </a>
            <form action="{{ route('kategori.destroy', $kategori) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini beserta seluruh bukunya?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-trash me-1"></i> Hapus
                </button>
            </form>
        </div>
    </div>

    <!-- Info Kategori Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <span class="badge badge-del font-monospace fs-6 px-3 py-2 mb-2">
                        {{ $kategori->kode_kategori }}
                    </span>
                    <h3 class="fw-bold text-dark mb-1">{{ $kategori->nama_kategori }}</h3>
                    <p class="text-muted small mb-0">Total Buku Terdaftar: <strong>{{ $kategori->bukus->count() }} judul</strong></p>
                </div>
                <a href="{{ route('buku.create') }}" class="btn btn-del btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Buku ke Kategori Ini
                </a>
            </div>
        </div>
    </div>

    <!-- Koleksi Buku dalam Kategori Ini -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 fw-bold text-del">
                <i class="bi bi-collection me-2"></i>Daftar Buku dalam Kategori Ini
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th scope="col" class="text-center" style="width: 50px;">No</th>
                            <th scope="col" style="width: 140px;">ISBN</th>
                            <th scope="col">Judul Buku</th>
                            <th scope="col">Penulis</th>
                            <th scope="col">Penerbit (Thn)</th>
                            <th scope="col" class="text-center" style="width: 80px;">Stok</th>
                            <th scope="col" class="text-center" style="width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kategori->bukus as $index => $buku)
                            <tr>
                                <td class="text-center text-muted fw-semibold">{{ $index + 1 }}</td>
                                <td><span class="badge bg-light text-dark border font-monospace">{{ $buku->isbn }}</span></td>
                                <td class="fw-bold text-dark">{{ $buku->judul }}</td>
                                <td class="small text-muted">{{ $buku->penulis }}</td>
                                <td class="small">{{ $buku->penerbit }} ({{ $buku->tahun_terbit }})</td>
                                <td class="text-center">
                                    <span class="badge bg-info-subtle text-info px-2 py-1 rounded-pill">
                                        {{ $buku->stok }} eks
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('buku.show', $buku) }}" class="btn btn-sm btn-outline-secondary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('buku.edit', $buku) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <p class="mb-0">Belum ada buku yang terdaftar di bawah kategori ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layout>
