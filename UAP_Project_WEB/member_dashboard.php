<?php
session_start();
require_once 'database.php';
cekLogin();

// Ambil data statistik anggota
$stmt = $pdo->prepare("SELECT COUNT(*) as total_pinjam FROM peminjaman WHERE id_anggota = ?");
$stmt->execute([$_SESSION['id_anggota']]);
$total_pinjam = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) as sedang_pinjam FROM peminjaman WHERE id_anggota = ? AND status = 'dipinjam'");
$stmt->execute([$_SESSION['id_anggota']]);
$sedang_pinjam = $stmt->fetchColumn();

// Ambil buku yang tersedia
$stmt = $pdo->prepare("SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori WHERE b.jumlah_tersedia > 0 ORDER BY b.tanggal_ditambahkan DESC LIMIT 6");
$stmt->execute();
$buku_tersedia = $stmt->fetchAll();

// Ambil riwayat peminjaman terbaru
$stmt = $pdo->prepare("SELECT p.*, b.judul, b.pengarang FROM peminjaman p JOIN buku b ON p.id_buku = b.id_buku WHERE p.id_anggota = ? ORDER BY p.tanggal_pinjam DESC LIMIT 5");
$stmt->execute([$_SESSION['id_anggota']]);
$riwayat_pinjam = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Anggota - Pinjamin</title>
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
            background: var(--primary-blue) !important;
            box-shadow: 0 2px 10px rgba(37, 99, 235, 0.2);
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
                <i class="bi bi-book"></i> Pinjamin - Dashboard Anggota
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle"></i> <?= $_SESSION['nama_anggota'] ?>
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
                            <a class="nav-link active" href="member_dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                            <a class="nav-link" href="member_buku.php">
                                <i class="bi bi-book"></i> Daftar Buku
                            </a>
                            <a class="nav-link" href="member_peminjaman.php">
                                <i class="bi bi-calendar-check"></i> Peminjaman Saya
                            </a>
                            <a class="nav-link" href="member_profil.php">
                                <i class="bi bi-person"></i> Profil Saya
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
                            <h2 class="fw-bold text-primary">Selamat Datang, <?= $_SESSION['nama_anggota'] ?>!</h2>
                            <p class="text-muted">Nomor Anggota: <?= $_SESSION['nomor_anggota'] ?></p>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="bi bi-book-half display-4 mb-3"></i>
                                    <h3><?= $total_pinjam ?></h3>
                                    <p class="mb-0">Total Peminjaman</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="bi bi-clock-history display-4 mb-3"></i>
                                    <h3><?= $sedang_pinjam ?></h3>
                                    <p class="mb-0">Sedang Dipinjam</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buku Tersedia -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-book"></i> Buku Tersedia
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($buku_tersedia as $buku): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h6 class="card-title"><?= htmlspecialchars($buku['judul']) ?></h6>
                                                    <p class="card-text">
                                                        <small class="text-muted">
                                                            Pengarang: <?= htmlspecialchars($buku['pengarang']) ?><br>
                                                            Kategori: <?= htmlspecialchars($buku['nama_kategori']) ?><br>
                                                            Tersedia: <?= $buku['jumlah_tersedia'] ?> buku
                                                        </small>
                                                    </p>
                                                    <a href="member_pinjam.php?id=<?= $buku['id_buku'] ?>" class="btn btn-primary btn-sm">
                                                        <i class="bi bi-plus-circle"></i> Pinjam
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="text-center">
                                        <a href="member_buku.php" class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i> Lihat Semua Buku
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Peminjaman -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-clock-history"></i> Riwayat Peminjaman Terbaru
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (count($riwayat_pinjam) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Judul Buku</th>
                                                    <th>Tanggal Pinjam</th>
                                                    <th>Tanggal Kembali</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($riwayat_pinjam as $pinjam): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($pinjam['judul']) ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($pinjam['pengarang']) ?></small>
                                                    </td>
                                                    <td><?= formatTanggal($pinjam['tanggal_pinjam']) ?></td>
                                                    <td><?= formatTanggal($pinjam['tanggal_kembali_rencana']) ?></td>
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
                                        <a href="member_peminjaman.php" class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i> Lihat Semua Riwayat
                                        </a>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-inbox display-4 text-muted"></i>
                                        <p class="text-muted mt-2">Belum ada riwayat peminjaman</p>
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
