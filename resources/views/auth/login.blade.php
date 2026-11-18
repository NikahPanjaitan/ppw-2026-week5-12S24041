<x-layout title="Masuk Pustakawan — SIPUS-Del">
    <div class="row justify-content-center py-4">
        <div class="col-md-6 col-lg-5">
            <!-- Header Card -->
            <div class="text-center mb-4">
                <div class="d-inline-flex p-3 rounded-circle shadow-sm mb-3" style="background-color: #4C1D95; color: #ffffff;">
                    <i class="bi bi-person-badge fs-1"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">Login Pustakawan</h3>
                <p class="text-muted small">
                    Masuk ke Sistem Informasi Perpustakaan Institut Teknologi Del untuk mengelola sirkulasi dan katalog.
                </p>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4 p-sm-5">
                    <form action="{{ route('login.post') }}" method="POST">
                        @csrf

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                Alamat Email Pustakawan <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email"
                                       name="email"
                                       id="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $lastEmail) }}"
                                       placeholder="nama@del.ac.id"
                                       required
                                       autofocus>
                            </div>
                            @if($lastEmail && !old('email'))
                                <div class="form-text text-muted small">
                                    <i class="bi bi-cookie me-1"></i>Email terisi otomatis dari riwayat cookie browser.
                                </div>
                            @endif
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kata Sandi -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                Kata Sandi <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password"
                                       name="password"
                                       id="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Masukkan kata sandi akun"
                                       required>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ingat Saya -->
                        <div class="mb-4 form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember" value="1" checked>
                            <label class="form-check-label text-muted small" for="remember">
                                Ingat sesi login pada perangkat ini
                            </label>
                        </div>

                        <!-- Tombol Masuk -->
                        <button type="submit" class="btn btn-del w-100 py-2 fw-semibold shadow-sm mb-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                        </button>
                    </form>

                    <!-- Info Kredensial Uji Coba -->
                    <div class="p-3 bg-light rounded-3 border mt-3">
                        <div class="d-flex align-items-center mb-1">
                            <i class="bi bi-info-circle-fill text-primary me-2"></i>
                            <span class="fw-semibold small text-dark">Akun Pustakawan Uji (Sesuai Modul):</span>
                        </div>
                        <div class="font-monospace small text-secondary">
                            <div><strong>Email:</strong> pustakawan@del.ac.id</div>
                            <div><strong>Password:</strong> password123</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('buku.index') }}" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog Publik
                </a>
            </div>
        </div>
    </div>
</x-layout>
