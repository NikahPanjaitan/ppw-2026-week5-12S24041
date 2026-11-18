<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'SIPUS-Del' }} — Institut Teknologi Del</title>
    <link rel="icon" href="{{ asset('images/logo-del.png') }}" type="image/png">

    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Styling Kustom Del Purple (#4C1D95) -->
    <style>
        :root {
            --del-purple: #4C1D95;
            --del-purple-hover: #3B0764;
            --del-purple-light: #EDE9FE;
        }

        .navbar-del {
            background-color: var(--del-purple);
        }

        .btn-del {
            background-color: var(--del-purple);
            color: #ffffff;
            border-color: var(--del-purple);
        }

        .btn-del:hover {
            background-color: var(--del-purple-hover);
            color: #ffffff;
            border-color: var(--del-purple-hover);
        }

        .btn-outline-del {
            color: var(--del-purple);
            border-color: var(--del-purple);
        }

        .btn-outline-del:hover {
            background-color: var(--del-purple);
            color: #ffffff;
        }

        .badge-del {
            background-color: var(--del-purple);
            color: #ffffff;
        }

        .badge-del-purple {
            background-color: var(--del-purple-light);
            color: var(--del-purple);
            border: 1px solid #DDD6FE;
            font-weight: 600;
        }

        .text-del {
            color: var(--del-purple);
        }

        .card-header-del {
            background-color: var(--del-purple);
            color: #ffffff;
        }

        .page-item.active .page-link {
            background-color: var(--del-purple);
            border-color: var(--del-purple);
        }

        .page-link {
            color: var(--del-purple);
        }
    </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Navbar Navigasi Utama -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-del shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('buku.index') }}">
                <img src="{{ asset('images/logo-del.png') }}" alt="Logo IT Del" width="30" height="30" class="d-inline-block rounded bg-white p-0.5">
                SIPUS-Del Perpustakaan
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('buku.*') ? 'active fw-semibold' : '' }}" href="{{ route('buku.index') }}">
                            <i class="bi bi-journal-album me-1"></i> Koleksi Buku
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('kategori.*') ? 'active fw-semibold' : '' }}" href="{{ route('kategori.index') }}">
                            <i class="bi bi-tags me-1"></i> Kategori
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('peminjaman.*') ? 'active fw-semibold' : '' }}" href="{{ route('peminjaman.index') }}">
                            <i class="bi bi-arrow-left-right me-1"></i> Sirkulasi Peminjaman
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    @auth
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-badge"></i>
                                <span>{{ Auth::user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><h6 class="dropdown-header small">Pustakawan Terautentikasi</h6></li>
                                <li><span class="dropdown-item-text small text-muted"><i class="bi bi-envelope me-1"></i>{{ Auth::user()->email }}</span></li>
                                @if(session('login_timestamp'))
                                    <li><span class="dropdown-item-text small text-muted"><i class="bi bi-clock me-1"></i>{{ session('login_timestamp') }}</span></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="px-2">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger w-100 text-start">
                                            <i class="bi bi-box-arrow-right me-1"></i> Logout Sesi
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-sm btn-light fw-semibold text-del shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login Pustakawan
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Konten Utama Halaman -->
    <main class="container pb-5 flex-grow-1">
        <!-- Flash Message Session untuk Status Sukses -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Slot Blade Component -->
        {{ $slot }}
    </main>

    <!-- Footer Master Shell -->
    <footer class="bg-white border-top py-3 mt-auto text-center text-muted small">
        <div class="container">
            <div class="fw-semibold text-dark">
                &copy; {{ date('Y') }} <strong>SIPUS-Del</strong> — Sistem Informasi Perpustakaan Institut Teknologi Del
            </div>
            <div class="text-secondary" style="font-size: 0.8rem;">
                Jl. Sisingamangaraja, Sitoluama, Laguboti, Toba, Sumatera Utara 22381 &bull; <em>MarTuhan, Marroha, Marbisuk</em>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
