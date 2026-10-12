<x-layout title="Tambah Buku Baru">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
                <span class="badge badge-del px-3 py-2">Formulir Tambah Buku</span>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header card-header-del py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Data Buku Baru
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('buku.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <!-- ISBN -->
                            <div class="col-md-6">
                                <label for="isbn" class="form-label fw-semibold">
                                    ISBN <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="isbn"
                                       id="isbn"
                                       class="form-control @error('isbn') is-invalid @enderror"
                                       value="{{ old('isbn') }}"
                                       placeholder="Contoh: 978-602-001-101-1"
                                       required>
                                @error('isbn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Kategori -->
                            <div class="col-md-6">
                                <label for="kategori_id" class="form-label fw-semibold">
                                    Kategori Buku <span class="text-danger">*</span>
                                </label>
                                <select name="kategori_id"
                                        id="kategori_id"
                                        class="form-select @error('kategori_id') is-invalid @enderror"
                                        required>
                                    <option value="" disabled {{ old('kategori_id') ? '' : 'selected' }}>-- Pilih Kategori --</option>
                                    @foreach($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                            {{ $kategori->kode_kategori }} - {{ $kategori->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Judul Buku -->
                            <div class="col-12">
                                <label for="judul" class="form-label fw-semibold">
                                    Judul Buku <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="judul"
                                       id="judul"
                                       class="form-control @error('judul') is-invalid @enderror"
                                       value="{{ old('judul') }}"
                                       placeholder="Minimal 5 karakter"
                                       required>
                                @error('judul')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Penulis -->
                            <div class="col-md-6">
                                <label for="penulis" class="form-label fw-semibold">
                                    Penulis <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="penulis"
                                       id="penulis"
                                       class="form-control @error('penulis') is-invalid @enderror"
                                       value="{{ old('penulis') }}"
                                       placeholder="Nama penulis buku"
                                       required>
                                @error('penulis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Penerbit -->
                            <div class="col-md-6">
                                <label for="penerbit" class="form-label fw-semibold">
                                    Penerbit <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       name="penerbit"
                                       id="penerbit"
                                       class="form-control @error('penerbit') is-invalid @enderror"
                                       value="{{ old('penerbit') }}"
                                       placeholder="Nama penerbit buku"
                                       required>
                                @error('penerbit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tahun Terbit -->
                            <div class="col-md-6">
                                <label for="tahun_terbit" class="form-label fw-semibold">
                                    Tahun Terbit <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                       name="tahun_terbit"
                                       id="tahun_terbit"
                                       class="form-control @error('tahun_terbit') is-invalid @enderror"
                                       value="{{ old('tahun_terbit', date('Y')) }}"
                                       min="1900"
                                       max="{{ date('Y') + 1 }}"
                                       required>
                                @error('tahun_terbit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Stok -->
                            <div class="col-md-6">
                                <label for="stok" class="form-label fw-semibold">
                                    Jumlah Stok (Eksemplar) <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                       name="stok"
                                       id="stok"
                                       class="form-control @error('stok') is-invalid @enderror"
                                       value="{{ old('stok', 0) }}"
                                       min="0"
                                       required>
                                @error('stok')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sinopsis -->
                            <div class="col-12">
                                <label for="sinopsis" class="form-label fw-semibold">
                                    Sinopsis / Deskripsi Ringkas <span class="text-muted fw-normal">(Opsional)</span>
                                </label>
                                <textarea name="sinopsis"
                                          id="sinopsis"
                                          rows="4"
                                          class="form-control @error('sinopsis') is-invalid @enderror"
                                          placeholder="Tuliskan ringkasan sinopsis atau gambaran isi buku...">{{ old('sinopsis') }}</textarea>
                                @error('sinopsis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('buku.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-del">
                                <i class="bi bi-save me-1"></i> Simpan Buku
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
