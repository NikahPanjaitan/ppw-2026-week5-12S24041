<x-layout title="Form Transaksi Peminjaman Buku">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat Sirkulasi
                </a>
                <span class="badge badge-del px-3 py-2">Sirkulasi ACID</span>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header card-header-del py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-journal-arrow-up me-2"></i>Transaksi Peminjaman Koleksi Buku
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-shield-check fs-4 me-3"></i>
                        <div>
                            <strong>Transaksi ACID Terproteksi:</strong> Pengurangan stok dan pencatatan transaksi diproses secara atomik di dalam <code>DB::transaction()</code> dengan penguncian baris <code>lockForUpdate()</code> untuk menjamin integritas data sirkulasi.
                        </div>
                    </div>

                    <form action="{{ route('peminjaman.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <!-- Pilihan Buku -->
                            <div class="col-12">
                                <label for="buku_id" class="form-label fw-semibold">
                                    Buku yang Dipinjam <span class="text-danger">*</span>
                                </label>
                                <select name="buku_id"
                                        id="buku_id"
                                        class="form-select @error('buku_id') is-invalid @enderror"
                                        required>
                                    <option value="" disabled {{ old('buku_id', $selectedBuku?->id) ? '' : 'selected' }}>
                                        -- Pilih Buku yang Tersedia --
                                    </option>
                                    @foreach($bukus as $b)
                                        <option value="{{ $b->id }}"
                                            {{ old('buku_id', $selectedBuku?->id) == $b->id ? 'selected' : '' }}>
                                            {{ $b->judul }} (Sisa Stok: {{ $b->stok }} eks) - ISBN: {{ $b->isbn }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('buku_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- NIM Mahasiswa Peminjam -->
                            <div class="col-md-6">
                                <label for="nim_peminjam" class="form-label fw-semibold">
                                    NIM Peminjam <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="nim_peminjam"
                                       id="nim_peminjam"
                                       class="form-control @error('nim_peminjam') is-invalid @enderror"
                                       value="{{ old('nim_peminjam') }}"
                                       placeholder="Contoh: 12S24041"
                                       required>
                                @error('nim_peminjam')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nama Mahasiswa Peminjam -->
                            <div class="col-md-6">
                                <label for="nama_peminjam" class="form-label fw-semibold">
                                    Nama Lengkap Peminjam <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="nama_peminjam"
                                       id="nama_peminjam"
                                       class="form-control @error('nama_peminjam') is-invalid @enderror"
                                       value="{{ old('nama_peminjam') }}"
                                       placeholder="Contoh: Nikah Suchia Panjaitan"
                                       required>
                                @error('nama_peminjam')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Informasi Durasi Pinjam Otomatis -->
                            <div class="col-12">
                                <div class="p-3 bg-light rounded-2 border">
                                    <div class="row text-muted small">
                                        <div class="col-md-6">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            <strong>Tanggal Pinjam:</strong> {{ now()->translatedFormat('d F Y') }} (Hari Ini)
                                        </div>
                                        <div class="col-md-6">
                                            <i class="bi bi-calendar-check me-1"></i>
                                            <strong>Jatuh Tempo:</strong> {{ now()->addDays(7)->translatedFormat('d F Y') }} (Maks. 7 Hari)
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-del px-4">
                                <i class="bi bi-check2-circle me-1"></i> Proses Peminjaman (Atomik)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
