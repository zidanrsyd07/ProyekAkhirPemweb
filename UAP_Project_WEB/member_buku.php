<?php
session_start();
require_once 'database.php';
cekLogin();

// Pencarian dan filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Query untuk mengambil buku
$query = "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (b.judul LIKE ? OR b.pengarang LIKE ? OR b.penerbit LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($kategori_filter) {
    $query .= " AND b.id_kategori = ?";
    $params[] = $kategori_filter;
}

$query .= " ORDER BY b.judul ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$buku_list = $stmt->fetchAll();

// Ambil daftar kategori untuk filter
$stmt = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori");
$kategori_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Buku - Pinjamin</title>
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
        
        .sidebar .nav-link.active {
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
        
        .book-card {
            transition: transform 0.3s ease;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
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
                            <a class="nav-link active" href="member_buku.php">
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
                            <h2 class="fw-bold text-primary">Daftar Buku</h2>
                            <p class="text-muted">Temukan dan pinjam buku favorit Anda</p>
                        </div>
                    </div>

                    <!-- Search and Filter -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form method="GET" class="row g-3">
                                        <div class="col-md-6">
                                            <label for="search" class="form-label">Cari Buku</label>
                                            <input type="text" class="form-control" id="search" name="search" 
                                                   placeholder="Judul, pengarang, atau penerbit..." value="<?= htmlspecialchars($search) ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="kategori" class="form-label">Kategori</label>
                                            <select class="form-select" id="kategori" name="kategori">
                                                <option value="">Semua Kategori</option>
                                                <?php foreach ($kategori_list as $kategori): ?>
                                                <option value="<?= $kategori['id_kategori'] ?>" 
                                                        <?= $kategori_filter == $kategori['id_kategori'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($kategori['nama_kategori']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-search"></i> Cari
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Book List -->
                    <div class="row">
                        <?php if (count($buku_list) > 0): ?>
                            <?php foreach ($buku_list as $buku): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card book-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($buku['judul']) ?></h5>
                                        <p class="card-text">
                                            <strong>Pengarang:</strong> <?= htmlspecialchars($buku['pengarang']) ?><br>
                                            <strong>Penerbit:</strong> <?= htmlspecialchars($buku['penerbit']) ?><br>
                                            <strong>Tahun:</strong> <?= $buku['tahun_terbit'] ?><br>
                                            <strong>Kategori:</strong> <?= htmlspecialchars($buku['nama_kategori']) ?><br>
                                            <strong>Kode Buku:</strong> <?= htmlspecialchars($buku['kode_buku']) ?>
                                        </p>
                                        
                                        <?php if ($buku['deskripsi']): ?>
                                        <p class="card-text">
                                            <small class="text-muted"><?= htmlspecialchars(substr($buku['deskripsi'], 0, 100)) ?>...</small>
                                        </p>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <?php if ($buku['jumlah_tersedia'] > 0): ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i> Tersedia (<?= $buku['jumlah_tersedia'] ?>)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-x-circle"></i> Tidak Tersedia
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if ($buku['jumlah_tersedia'] > 0): ?>
                                                <a href="member_pinjam.php?id=<?= $buku['id_buku'] ?>" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-plus-circle"></i> Pinjam
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-secondary btn-sm" disabled>
                                                    <i class="bi bi-x-circle"></i> Tidak Tersedia
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="bi bi-search display-4 text-muted"></i>
                                        <h5 class="mt-3">Buku Tidak Ditemukan</h5>
                                        <p class="text-muted">Coba ubah kata kunci pencarian atau filter kategori</p>
                                        <a href="member_buku.php" class="btn btn-primary">
                                            <i class="bi bi-arrow-clockwise"></i> Reset Pencarian
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
