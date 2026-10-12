<x-layout title="Daftar Kategori">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-del mb-1">
                <i class="bi bi-tags me-2"></i>Daftar Kategori Buku
            </h2>
            <p class="text-muted small mb-0">Kelola klasifikasi bidang ilmu dan kategori koleksi buku perpustakaan</p>
        </div>
        <a href="{{ route('kategori.create') }}" class="btn btn-del shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th scope="col" class="text-center" style="width: 60px;">No</th>
                            <th scope="col" style="width: 180px;">Kode Kategori</th>
                            <th scope="col">Nama Kategori</th>
                            <th scope="col" class="text-center" style="width: 150px;">Jumlah Buku</th>
                            <th scope="col" class="text-center" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kategoris as $index => $kategori)
                            <tr>
                                <td class="text-center text-muted fw-semibold">
                                    {{ $kategoris->firstItem() + $index }}
                                </td>
                                <td>
                                    <span class="badge badge-del font-monospace px-3 py-2">
                                        {{ $kategori->kode_kategori }}
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-dark">{{ $kategori->nama_kategori }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill">
                                        {{ $kategori->bukus_count }} Judul
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('kategori.show', $kategori) }}" class="btn btn-outline-secondary" title="Lihat Detail & Buku">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-outline-primary" title="Ubah Kategori">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('kategori.destroy', $kategori) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori \'{{ $kategori->nama_kategori }}\'? Seluruh buku di bawah kategori ini juga akan terhapus.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus Kategori">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-tag fs-1 d-block mb-2 text-secondary"></i>
                                    <p class="mb-2">Belum ada data kategori yang terdaftar.</p>
                                    <a href="{{ route('kategori.create') }}" class="btn btn-sm btn-del">
                                        <i class="bi bi-plus-lg me-1"></i> Tambah Kategori Baru
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($kategoris->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Menampilkan {{ $kategoris->firstItem() }} sampai {{ $kategoris->lastItem() }} dari {{ $kategoris->total() }} kategori
                    </small>
                    <div>
                        {{ $kategoris->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layout>
