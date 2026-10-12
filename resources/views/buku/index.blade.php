<x-layout title="Koleksi Buku">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-del mb-1">
                <i class="bi bi-journal-bookmark me-2"></i>Daftar Koleksi Buku
            </h2>
            <p class="text-muted small mb-0">Kelola katalog inventaris buku perpustakaan Institut Teknologi Del</p>
        </div>
        <a href="{{ route('buku.create') }}" class="btn btn-del shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Buku
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th scope="col" class="text-center" style="width: 50px;">No</th>
                            <th scope="col" style="width: 150px;">ISBN</th>
                            <th scope="col">Judul</th>
                            <th scope="col" style="width: 150px;">Penulis</th>
                            <th scope="col" style="width: 180px;">Kategori</th>
                            <th scope="col" style="width: 150px;">Penerbit / Thn</th>
                            <th scope="col" class="text-center" style="width: 90px;">Stok</th>
                            <th scope="col" class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bukus as $index => $buku)
                            <tr>
                                <td class="text-center text-muted fw-semibold">
                                    {{ $bukus->firstItem() + $index }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                        {{ $buku->isbn }}
                                    </span>
                                </td>
                                <td class="fw-bold text-dark">
                                    {{ $buku->judul }}
                                </td>
                                <td class="small text-muted">
                                    {{ $buku->penulis }}
                                </td>
                                <td>
                                    <span class="badge badge-del px-2 py-1">
                                        {{ $buku->kategori->nama_kategori ?? 'Tanpa Kategori' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-secondary">{{ $buku->penerbit }}</div>
                                    <small class="text-muted">{{ $buku->tahun_terbit }}</small>
                                </td>
                                <td class="text-center">
                                    @if ($buku->stok > 5)
                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">
                                            {{ $buku->stok }} eks
                                        </span>
                                    @elseif ($buku->stok > 0)
                                        <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill">
                                            {{ $buku->stok }} eks
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">
                                            Habis
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('buku.show', $buku) }}" class="btn btn-outline-secondary" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('buku.edit', $buku) }}" class="btn btn-outline-primary" title="Ubah Data">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('buku.destroy', $buku) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku \'{{ $buku->judul }}\'?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus Data">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    <p class="mb-2">Belum ada koleksi buku yang terdaftar di sistem.</p>
                                    <a href="{{ route('buku.create') }}" class="btn btn-sm btn-del">
                                        <i class="bi bi-plus-lg me-1"></i> Tambah Buku Sekarang
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($bukus->hasPages())
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex justify-content-end">
                    {{ $bukus->links() }}
                </div>
            </div>
        @endif
    </div>
</x-layout>
