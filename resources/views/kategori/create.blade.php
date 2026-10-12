<x-layout title="Tambah Kategori">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Kategori
                </a>
                <span class="badge badge-del px-3 py-2">Formulir Kategori</span>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header card-header-del py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-tag me-2"></i>Tambah Kategori Buku Baru
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('kategori.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="kode_kategori" class="form-label fw-semibold">
                                Kode Kategori <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="kode_kategori"
                                   id="kode_kategori"
                                   class="form-control font-monospace @error('kode_kategori') is-invalid @enderror"
                                   value="{{ old('kode_kategori') }}"
                                   placeholder="Contoh: KTG-001"
                                   required>
                            @error('kode_kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Kode unik pengenal klasifikasi buku.</div>
                        </div>

                        <div class="mb-3">
                            <label for="nama_kategori" class="form-label fw-semibold">
                                Nama Kategori <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="nama_kategori"
                                   id="nama_kategori"
                                   class="form-control @error('nama_kategori') is-invalid @enderror"
                                   value="{{ old('nama_kategori') }}"
                                   placeholder="Contoh: Rekayasa Perangkat Lunak"
                                   required>
                            @error('nama_kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('kategori.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-del">
                                <i class="bi bi-save me-1"></i> Simpan Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
