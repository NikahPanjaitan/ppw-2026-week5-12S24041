<x-layout title="Koleksi Buku & Dashboard Sirkulasi">
    <!-- Banner Sambutan Kampus Del -->
    <div class="card border-0 rounded-3 shadow-sm mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #4C1D95 0%, #3B0764 100%);">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-md-8">
                    <span class="badge bg-white bg-opacity-25 text-white mb-2 px-3 py-1">
                        Institut Teknologi Del &bull; <em>MarTuhan, Marroha, Marbisuk</em>
                    </span>
                    <h2 class="h3 fw-bold mb-1">Daftar Koleksi & Sirkulasi Perpustakaan</h2>
                    <p class="text-white-50 mb-0 small">
                        Kelola katalog inventaris buku, monitor statistik Query Builder, dan transaksi sirkulasi peminjaman ACID.
                    </p>
                </div>
                <div class="col-md-4 text-md-end d-flex justify-content-md-end gap-2">
                    <a href="{{ route('peminjaman.create') }}" class="btn btn-warning text-dark fw-semibold shadow-sm px-3 py-2">
                        <i class="bi bi-journal-arrow-up me-1"></i> Pinjam Buku
                    </a>
                    <a href="{{ route('buku.create') }}" class="btn btn-light fw-semibold text-del shadow-sm px-3 py-2">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Buku
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Kartu Metrik Statistik Perpustakaan (Fluent Query Builder DB::table) -->
    <div class="row g-3 mb-4">
        <!-- Total Judul Buku -->
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 p-3 text-white me-3" style="background-color: #4C1D95;">
                        <i class="bi bi-journal-bookmark fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Judul Buku</div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $stats['total_judul'] }} <small class="text-muted fs-6">Judul</small></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Stok Fisik Tersedia -->
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 p-3 text-white me-3 bg-primary">
                        <i class="bi bi-bookshelf fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Stok Tersedia</div>
                        <h4 class="fw-bold mb-0 text-primary">{{ $stats['total_stok'] }} <small class="text-muted fs-6">Eks</small></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Buku Sedang Dipinjam -->
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 p-3 text-white me-3 bg-warning">
                        <i class="bi bi-hourglass-split fs-3 text-dark"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Sedang Dipinjam</div>
                        <h4 class="fw-bold mb-0 text-warning text-dark">{{ $stats['total_dipinjam'] }} <small class="text-muted fs-6">Transaksi</small></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Buku Dikembalikan -->
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 p-3 text-white me-3 bg-success">
                        <i class="bi bi-check2-all fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Dikembalikan</div>
                        <h4 class="fw-bold mb-0 text-success">{{ $stats['total_kembali'] }} <small class="text-muted fs-6">Selesai</small></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Strip Analisis Kueri Raw SQL (DB::select) Sesuai Indikator Modul 1.2 -->
    @if(!empty($popularBooks) && empty($search))
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
            <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="small fw-bold text-del d-flex align-items-center">
                    <i class="bi bi-fire text-danger me-1 fs-6"></i> Koleksi Terpopuler (Kueri Agregasi Raw SQL <code>DB::select</code>):
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($popularBooks as $pb)
                        <span class="badge bg-white text-dark border small fw-normal">
                            <strong>{{ \Illuminate\Support\Str::limit($pb->judul, 35) }}</strong>
                            <span class="text-danger fw-bold ms-1">({{ $pb->total_dipinjam }}x)</span>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Filter & Pencarian Dinamis (when()) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('buku.index') }}" method="GET" class="row g-2 align-items-center">
                <!-- Dropdown Kategori -->
                <div class="col-md-3">
                    <select name="kategori_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($kategoris as $k)
                            <option value="{{ $k->id }}" {{ $kategoriId == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Input Search Keyword -->
                <div class="col-md-7">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text"
                               name="search"
                               class="form-control border-start-0"
                               placeholder="Cari judul buku, nama penulis, atau nomor ISBN..."
                               value="{{ $search }}">
                    </div>
                </div>

                <!-- Tombol Aksi Filter -->
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-del flex-grow-1">
                        <i class="bi bi-filter me-1"></i> Terapkan
                    </button>
                    @if($search || $kategoriId)
                        <a href="{{ route('buku.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Buku -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header card-header-del py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold">
                <i class="bi bi-book me-2"></i>Daftar Koleksi Inventaris Buku
            </h5>
            <span class="badge bg-white text-dark small">Menampilkan {{ $bukus->total() }} Koleksi</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr class="small text-uppercase text-secondary">
                            <th scope="col" class="text-center" style="width: 50px;">No</th>
                            <th scope="col" style="width: 140px;">ISBN</th>
                            <th scope="col">Judul Buku</th>
                            <th scope="col" style="width: 150px;">Penulis</th>
                            <th scope="col" style="width: 180px;">Kategori</th>
                            <th scope="col" class="text-center" style="width: 90px;">Dipinjam</th>
                            <th scope="col" class="text-center" style="width: 90px;">Stok</th>
                            <th scope="col" class="text-center pe-3" style="width: 170px;">Aksi</th>
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
                                <td>
                                    <div class="fw-bold text-dark">{{ $buku->judul }}</div>
                                    <div class="text-muted small">
                                        {{ $buku->penerbit }} &bull; {{ $buku->tahun_terbit }}
                                    </div>
                                </td>
                                <td class="small text-muted">
                                    {{ $buku->penulis }}
                                </td>
                                <td>
                                    <span class="badge badge-del px-2 py-1">
                                        {{ $buku->kategori->nama_kategori ?? 'Tanpa Kategori' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill" title="Total Riwayat Peminjaman">
                                        <i class="bi bi-arrow-left-right me-1"></i>{{ $buku->peminjamans_count }}x
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if ($buku->stok > 5)
                                        <span class="badge badge-del-purple px-2 py-1 rounded-pill">
                                            {{ $buku->stok }} eks
                                        </span>
                                    @elseif ($buku->stok > 0)
                                        <span class="badge bg-warning-subtle text-warning px-2 py-1 rounded-pill">
                                            {{ $buku->stok }} eks
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">
                                            Habis (0)
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center pe-3">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Tombol Pinjam Cepat -->
                                        @if($buku->stok > 0)
                                            <a href="{{ route('peminjaman.create', ['buku_id' => $buku->id]) }}"
                                               class="btn btn-outline-success"
                                               title="Pinjam Buku Ini">
                                                <i class="bi bi-journal-arrow-up"></i>
                                            </a>
                                        @else
                                            <button class="btn btn-outline-secondary" disabled title="Stok Habis - Tidak Dapat Dipinjam">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        @endif

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
                                    <p class="mb-2">Tidak ditemukan data koleksi buku yang sesuai dengan kriteria filter.</p>
                                    <a href="{{ route('buku.index') }}" class="btn btn-sm btn-outline-secondary">
                                        Reset Filter Pencarian
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
