<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjamin - Perpustakaan Mini</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #2563eb;
            --light-blue: #dbeafe;
            --dark-blue: #1e40af;
        }
        
        body {
            background: linear-gradient(135deg, var(--light-blue) 0%, #ffffff 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background: var(--primary-blue) !important;
            box-shadow: 0 2px 10px rgba(37, 99, 235, 0.2);
        }
        
        .hero-section {
            padding: 100px 0;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-blue) 100%);
            color: white;
            margin-top: -56px;
            padding-top: 156px;
        }
        
        .card {
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .btn-primary {
            background: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .btn-primary:hover {
            background: var(--dark-blue);
            border-color: var(--dark-blue);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--light-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--primary-blue);
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-book"></i> Pinjamin
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="member_login.php">Login Anggota</a>
                <a class="nav-link" href="admin_login.php">Login Admin</a>
                <a class="nav-link" href="daftar_anggota.php">Daftar Anggota</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">Selamat Datang di Pinjamin</h1>
                    <p class="lead mb-5">Sistem Perpustakaan Mini yang memudahkan Anda dalam meminjam dan mengelola buku-buku favorit</p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="daftar_anggota.php" class="btn btn-light btn-lg">
                            <i class="bi bi-person-plus"></i> Daftar Sekarang
                        </a>
                        <a href="member_login.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Login Anggota
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="fw-bold text-primary">Fitur Unggulan</h2>
                    <p class="text-muted">Nikmati kemudahan dalam mengelola perpustakaan</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="feature-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <h5 class="fw-bold">Pencarian Buku</h5>
                        <p class="text-muted">Temukan buku yang Anda cari dengan mudah dan cepat</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="feature-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h5 class="fw-bold">Peminjaman Online</h5>
                        <p class="text-muted">Pinjam buku secara online dan pantau status peminjaman</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h5 class="fw-bold">Riwayat Lengkap</h5>
                        <p class="text-muted">Lihat riwayat peminjaman dan pengembalian buku</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="card border-0 bg-transparent">
                        <div class="card-body">
                            <h3 class="text-primary fw-bold">500+</h3>
                            <p class="text-muted">Koleksi Buku</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-transparent">
                        <div class="card-body">
                            <h3 class="text-primary fw-bold">200+</h3>
                            <p class="text-muted">Anggota Aktif</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-transparent">
                        <div class="card-body">
                            <h3 class="text-primary fw-bold">50+</h3>
                            <p class="text-muted">Peminjaman/Bulan</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 bg-transparent">
                        <div class="card-body">
                            <h3 class="text-primary fw-bold">24/7</h3>
                            <p class="text-muted">Akses Online</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-primary text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 Pinjamin - Perpustakaan Mini. Semua hak dilindungi.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
