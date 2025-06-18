<?php
session_start();
require_once 'database.php';
cekLogin();

$success = '';
$error = '';

// Ambil data buku
if (!isset($_GET['id'])) {
    header('member_buku.php');
    exit;
}

$id_buku = $_GET['id'];
$stmt = $pdo->prepare("SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori WHERE b.id_buku = ?");
$stmt->execute([$id_buku]);
$buku = $stmt->fetch();

if (!$buku) {
    header('member_buku.php');
    exit;
}

// Cek apakah buku tersedia
if ($buku['jumlah_tersedia'] <= 0) {
    $error = 'Maaf, buku ini sedang tidak tersedia!';
}

// Cek apakah anggota sudah meminjam buku yang sama dan belum dikembalikan
$stmt = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_anggota = ? AND id_buku = ? AND status = 'dipinjam'");
$stmt->execute([$_SESSION['id_anggota'], $id_buku]);
$sudah_pinjam = $stmt->fetchColumn();

if ($sudah_pinjam > 0) {
    $error = 'Anda sudah meminjam buku ini dan belum mengembalikannya!';
}

// Proses peminjaman
if ($_POST && !$error) {
    $tanggal_pinjam = date('Y-m-d');
    $tanggal_kembali = date('Y-m-d', strtotime('+7 days')); // 7 hari dari sekarang
    
    try {
        $pdo->beginTransaction();
        
        // Insert peminjaman
        $stmt = $pdo->prepare("INSERT INTO peminjaman (id_anggota, id_buku, tanggal_pinjam, tanggal_kembali_rencana) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['id_anggota'], $id_buku, $tanggal]);
        $stmt->execute([$_SESSION['id_anggota'], $id_buku, $tanggal_pinjam, $tanggal_kembali]);
        
        // Update jumlah tersedia
        $stmt = $pdo->prepare("UPDATE buku SET jumlah_tersedia = jumlah_tersedia - 1 WHERE id_buku = ?");
        $stmt->execute([$id_buku]);
        
        $pdo->commit();
        $success = 'Peminjaman berhasil! Buku harus dikembalikan pada tanggal ' . formatTanggal($tanggal_kembali);
        
        // Update data buku setelah peminjaman
        $stmt = $pdo->prepare("SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori WHERE b.id_buku = ?");
        $stmt->execute([$id_buku]);
        $buku = $stmt->fetch();
        
    } catch (Exception $e) {
        $pdo->rollback();
        $error = 'Terjadi kesalahan saat memproses peminjaman!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjam Buku - Pinjamin</title>
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
        
        .sidebar .nav-link:hover {
            background: var(--light-blue);
            color: var(--primary-blue);
        }
        
        .card {
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
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
                            <a class="nav-link" href="member_dashboard.php">
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
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h2 class="fw-bold text-primary">Pinjam Buku</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="member_dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="member_buku.php">Daftar Buku</a></li>
                                    <li class="breadcrumb-item active">Pinjam Buku</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bi bi-check-circle"></i> <?= $success ?>
                            <div class="mt-2">
                                <a href="member_peminjaman.php" class="btn btn-success btn-sm">Lihat Peminjaman Saya</a>
                                <a href="member_buku.php" class="btn btn-outline-success btn-sm">Pinjam Buku Lain</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <!-- Book Details -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-book"></i> Detail Buku
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h4><?= htmlspecialchars($buku['judul']) ?></h4>
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td width="150"><strong>Kode Buku</strong></td>
                                                    <td>: <?= htmlspecialchars($buku['kode_buku']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Pengarang</strong></td>
                                                    <td>: <?= htmlspecialchars($buku['pengarang']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Penerbit</strong></td>
                                                    <td>: <?= htmlspecialchars($buku['penerbit']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tahun Terbit</strong></td>
                                                    <td>: <?= $buku['tahun_terbit'] ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Kategori</strong></td>
                                                    <td>: <?= htmlspecialchars($buku['nama_kategori']) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Buku</strong></td>
                                                    <td>: <?= $buku['jumlah_total'] ?> eksemplar</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tersedia</strong></td>
                                                    <td>: 
                                                        <?php if ($buku['jumlah_tersedia'] > 0): ?>
                                                            <span class="badge bg-success"><?= $buku['jumlah_tersedia'] ?> eksemplar</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Tidak tersedia</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </table>
                                            
                                            <?php if ($buku['deskripsi']): ?>
                                            <div class="mt-3">
                                                <strong>Deskripsi:</strong>
                                                <p class="mt-2"><?= htmlspecialchars($buku['deskripsi']) ?></p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-info-circle"></i> Informasi Peminjaman
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Nama Peminjam:</strong><br>
                                        <?= $_SESSION['nama_anggota'] ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Nomor Anggota:</strong><br>
                                        <?= $_SESSION['nomor_anggota'] ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Tanggal Pinjam:</strong><br>
                                        <?= formatTanggal(date('Y-m-d')) ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Tanggal Kembali:</strong><br>
                                        <?= formatTanggal(date('Y-m-d', strtotime('+7 days'))) ?>
                                    </div>
                                    <div class="alert alert-warning">
                                        <small>
                                            <i class="bi bi-exclamation-triangle"></i>
                                            Buku harus dikembalikan dalam 7 hari. Keterlambatan akan dikenakan denda.
                                        </small>
                                    </div>
                                    
                                    <?php if (!$error && $buku['jumlah_tersedia'] > 0): ?>
                                    <form method="POST">
                                        <button type="submit" class="btn btn-primary w-100" 
                                                onclick="return confirm('Apakah Anda yakin ingin meminjam buku ini?')">
                                            <i class="bi bi-plus-circle"></i> Konfirmasi Peminjaman
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <a href="member_buku.php" class="btn btn-outline-secondary w-100 mt-2">
                                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Buku
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
