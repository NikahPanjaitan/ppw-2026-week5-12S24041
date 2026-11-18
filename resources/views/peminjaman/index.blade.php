<x-layout title="Sirkulasi Peminjaman Buku">
    <!-- Header Halaman -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #4C1D95;">
                <i class="bi bi-arrow-left-right me-2"></i>Sirkulasi Peminjaman Buku
            </h2>
            <p class="text-muted mb-0">
                Pencatatan mutasi peminjaman dan pengembalian buku perpustakaan berbasis transaksi ACID.
            </p>
        </div>
        <div>
            <a href="{{ route('peminjaman.create') }}" class="btn btn-del shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Transaksi Pinjam Baru
            </a>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center justify-content-between">
                <!-- Filter Status Tab Buttons -->
                <div class="col-md-auto">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('peminjaman.index', array_merge(request()->except('status', 'page'), [])) }}"
                           class="btn {{ empty($status) ? 'btn-del' : 'btn-outline-secondary' }}">
                            Semua ({{ \App\Models\Peminjaman::count() }})
                        </a>
                        <a href="{{ route('peminjaman.index', array_merge(request()->except('status', 'page'), ['status' => 'dipinjam'])) }}"
                           class="btn {{ $status === 'dipinjam' ? 'btn-warning text-dark fw-semibold' : 'btn-outline-secondary' }}">
                            Sedang Dipinjam ({{ \App\Models\Peminjaman::where('status', 'dipinjam')->count() }})
                        </a>
                        <a href="{{ route('peminjaman.index', array_merge(request()->except('status', 'page'), ['status' => 'dikembalikan'])) }}"
                           class="btn {{ $status === 'dikembalikan' ? 'btn-success fw-semibold' : 'btn-outline-secondary' }}">
                            Dikembalikan ({{ \App\Models\Peminjaman::where('status', 'dikembalikan')->count() }})
                        </a>
                    </div>
                </div>

                <!-- Form Search -->
                <div class="col-md-5">
                    <form action="{{ route('peminjaman.index') }}" method="GET" class="d-flex gap-2">
                        @if($status)
                            <input type="hidden" name="status" value="{{ $status }}">
                        @endif
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text"
                                   name="search"
                                   class="form-control border-start-0"
                                   placeholder="Cari kode, NIM, nama peminjam, judul..."
                                   value="{{ $search }}">
                            <button type="submit" class="btn btn-del">Cari</button>
                            @if($search)
                                <a href="{{ route('peminjaman.index', $status ? ['status' => $status] : []) }}"
                                   class="btn btn-outline-secondary" title="Reset Pencarian">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Data Sirkulasi -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header card-header-del py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold">
                <i class="bi bi-table me-2"></i>Daftar Riwayat Transaksi Peminjaman
            </h5>
            <span class="badge bg-white text-dark small">Total: {{ $peminjamans->total() }} Transaksi</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-uppercase text-secondary">
                            <th class="ps-4" style="width: 50px;">No</th>
                            <th>Kode Transaksi</th>
                            <th>Judul Koleksi Buku</th>
                            <th>Identitas Peminjam</th>
                            <th>Periode Pinjam</th>
                            <th>Tanggal Kembali</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peminjamans as $index => $pjm)
                            <tr>
                                <td class="ps-4 fw-semibold text-secondary">
                                    {{ $peminjamans->firstItem() + $index }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        {{ $pjm->kode_pinjam }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $pjm->buku->judul ?? 'Buku Telah Dihapus' }}</span>
                                    <div class="text-muted small">
                                        <i class="bi bi-upc-scan me-1"></i>ISBN: {{ $pjm->buku->isbn ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $pjm->nama_peminjam }}</div>
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace small">
                                        NIM: {{ $pjm->nim_peminjam }}
                                    </span>
                                </td>
                                <td class="small">
                                    <div><i class="bi bi-box-arrow-right text-primary me-1"></i>{{ $pjm->tanggal_pinjam->format('d/m/Y') }}</div>
                                    <div class="text-muted"><i class="bi bi-clock-history text-danger me-1"></i>s/d {{ $pjm->tanggal_jatuh_tempo->format('d/m/Y') }}</div>
                                </td>
                                <td class="small">
                                    @if($pjm->tanggal_kembali)
                                        <span class="text-success fw-semibold">
                                            <i class="bi bi-check-circle me-1"></i>{{ $pjm->tanggal_kembali->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted fst-italic">- Belum Kembali -</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($pjm->status === 'dipinjam')
                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                            <i class="bi bi-hourglass-split me-1"></i> Dipinjam
                                        </span>
                                    @else
                                        <span class="badge bg-success px-3 py-2 rounded-pill">
                                            <i class="bi bi-check2-all me-1"></i> Dikembalikan
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center pe-4">
                                    @if($pjm->status === 'dipinjam')
                                        <form action="{{ route('peminjaman.kembalikan', $pjm) }}" method="POST"
                                              onsubmit="return confirm('Apakah Anda yakin ingin memproses pengembalian buku untuk transaksi \'{{ $pjm->kode_pinjam }}\'?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Kembalikan Buku (Restore Stok)">
                                                <i class="bi bi-arrow-return-left me-1"></i> Kembalikan
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-light text-muted border">Tuntas</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    <p class="mb-2">Belum ada riwayat transaksi sirkulasi peminjaman buku.</p>
                                    <a href="{{ route('peminjaman.create') }}" class="btn btn-sm btn-del">
                                        <i class="bi bi-plus-lg me-1"></i> Catat Transaksi Baru
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($peminjamans->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-center">
                    {{ $peminjamans->links() }}
                </div>
            </div>
        @endif
    </div>
</x-layout>
