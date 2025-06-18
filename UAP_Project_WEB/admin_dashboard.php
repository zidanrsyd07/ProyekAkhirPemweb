<?php
session_start();
require_once 'database.php';
cekLoginAdmin();

// Ambil statistik
$stmt = $pdo->query("SELECT COUNT(*) as total_buku FROM buku");
$total_buku = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total_anggota FROM anggota WHERE status = 'aktif'");
$total_anggota = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total_kategori FROM kategori");
$total_kategori = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as sedang_dipinjam FROM peminjaman WHERE status = 'dipinjam'");
$sedang_dipinjam = $stmt->fetchColumn();

// Peminjaman terbaru
$stmt = $pdo->query("SELECT p.*, a.nama_lengkap, b.judul FROM peminjaman p JOIN anggota a ON p.id_anggota = a.id_anggota JOIN buku b ON p.id_buku = b.id_buku ORDER BY p.tanggal_pinjam DESC LIMIT 5");
$peminjaman_terbaru = $stmt->fetchAll();

// Buku yang hampir habis
$stmt = $pdo->query("SELECT * FROM buku WHERE jumlah_tersedia <= 2 ORDER BY jumlah_tersedia ASC LIMIT 5");
$buku_hampir_habis = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Pinjamin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #2563eb;
            --light-blue: #dbeafe;
            --dark-blue: #1e40af;
        }
        
        body {
            background: #f8fafc;
        }
        
        .navbar {
            background: var(--dark-blue) !important;
            box-shadow: 0 2px 10px rgba(30, 64, 175, 0.2);
        }
        
        .sidebar {
            background: white;
            min-height: calc(100vh - 56px);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar .nav-link {
            color: #64748b;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--light-blue);
            color: var(--primary-blue);
        }
        
        .card {
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
            color: white;
        }
        
        .btn-primary {
            background: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .btn-primary:hover {
            background: var(--dark-blue);
            border-color: var(--dark-blue);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-shield-check"></i> Pinjamin - Dashboard Admin
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle"></i> <?= $_SESSION['nama_admin'] ?>
                </span>
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-3">
                        <h6 class="text-muted">MENU UTAMA</h6>
                        <nav class="nav flex-column">
                            <a class="nav-link active" href="admin_dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                            <a class="nav-link" href="admin_buku.php">
                                <i class="bi bi-book"></i> Kelola Buku
                            </a>
                            <a class="nav-link" href="admin_kategori.php">
                                <i class="bi bi-tags"></i> Kelola Kategori
                            </a>
                            <a class="nav-link" href="admin_anggota.php">
                                <i class="bi bi-people"></i> Kelola Anggota
                            </a>
                            <a class="nav-link" href="admin_peminjaman.php">
                                <i class="bi bi-calendar-check"></i> Kelola Peminjaman
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="p-4">
                    <!-- Welcome Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h2 class="fw-bold text-primary">Selamat Datang, <?= $_SESSION['nama_admin'] ?>!</h2>
                            <p class="text-muted">Dashboard Admin Perpustakaan Pinjamin</p>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="bi bi-book display-4 mb-3"></i>
                                    <h3><?= $total_buku ?></h3>
                                    <p class="mb-0">Total Buku</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="bi bi-people display-4 mb-3"></i>
                                    <h3><?= $total_anggota ?></h3>
                                    <p class="mb-0">Anggota Aktif</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="bi bi-tags display-4 mb-3"></i>
                                    <h3><?= $total_kategori ?></h3>
                                    <p class="mb-0">Kategori</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="bi bi-clock-history display-4 mb-3"></i>
                                    <h3><?= $sedang_dipinjam ?></h3>
                                    <p class="mb-0">Sedang Dipinjam</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Peminjaman Terbaru -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-clock-history"></i> Peminjaman Terbaru
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (count($peminjaman_terbaru) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Anggota</th>
                                                    <th>Buku</th>
                                                    <th>Tanggal Pinjam</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($peminjaman_terbaru as $pinjam): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($pinjam['nama_lengkap']) ?></td>
                                                    <td><?= htmlspecialchars($pinjam['judul']) ?></td>
                                                    <td><?= formatTanggal($pinjam['tanggal_pinjam']) ?></td>
                                                    <td>
                                                        <?php if ($pinjam['status'] == 'dipinjam'): ?>
                                                            <span class="badge bg-warning">Dipinjam</span>
                                                        <?php elseif ($pinjam['status'] == 'dikembalikan'): ?>
                                                            <span class="badge bg-success">Dikembalikan</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Terlambat</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-center">
                                        <a href="admin_peminjaman.php" class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i> Lihat Semua
                                        </a>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-inbox display-4 text-muted"></i>
                                        <p class="text-muted mt-2">Belum ada peminjaman</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Buku Hampir Habis -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">
                                        <i class="bi bi-exclamation-triangle"></i> Stok Menipis
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (count($buku_hampir_habis) > 0): ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($buku_hampir_habis as $buku): ?>
                                        <div class="list-group-item border-0 px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="me-auto">
                                                    <h6 class="mb-1"><?= htmlspecialchars($buku['judul']) ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($buku['pengarang']) ?></small>
                                                </div>
                                                <span class="badge bg-warning rounded-pill"><?= $buku['jumlah_tersedia'] ?></span>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="text-center mt-3">
                                        <a href="admin_buku.php" class="btn btn-outline-warning">
                                            <i class="bi bi-eye"></i> Kelola Buku
                                        </a>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-check-circle display-4 text-success"></i>
                                        <p class="text-muted mt-2">Semua buku stoknya aman</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
