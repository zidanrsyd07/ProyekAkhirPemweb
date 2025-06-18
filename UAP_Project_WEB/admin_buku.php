<?php
session_start();
require_once 'database.php';
cekLoginAdmin();

$success = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'tambah':
                $kode_buku = generateKodeBuku();
                $judul = $_POST['judul'];
                $pengarang = $_POST['pengarang'];
                $penerbit = $_POST['penerbit'];
                $tahun_terbit = $_POST['tahun_terbit'];
                $id_kategori = $_POST['id_kategori'];
                $jumlah_total = $_POST['jumlah_total'];
                $deskripsi = $_POST['deskripsi'];
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO buku (kode_buku, judul, pengarang, penerbit, tahun_terbit, id_kategori, jumlah_total, jumlah_tersedia, deskripsi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$kode_buku, $judul, $pengarang, $penerbit, $tahun_terbit, $id_kategori, $jumlah_total, $jumlah_total, $deskripsi]);
                    $success = 'Buku berhasil ditambahkan dengan kode: ' . $kode_buku;
                } catch (Exception $e) {
                    $error = 'Gagal menambahkan buku!';
                }
                break;
                
            case 'edit':
                $id_buku = $_POST['id_buku'];
                $judul = $_POST['judul'];
                $pengarang = $_POST['pengarang'];
                $penerbit = $_POST['penerbit'];
                $tahun_terbit = $_POST['tahun_terbit'];
                $id_kategori = $_POST['id_kategori'];
                $jumlah_total = $_POST['jumlah_total'];
                $deskripsi = $_POST['deskripsi'];
                
                try {
                    // Hitung selisih jumlah total untuk update jumlah tersedia
                    $stmt = $pdo->prepare("SELECT jumlah_total, jumlah_tersedia FROM buku WHERE id_buku = ?");
                    $stmt->execute([$id_buku]);
                    $buku_lama = $stmt->fetch();
                    
                    $selisih = $jumlah_total - $buku_lama['jumlah_total'];
                    $jumlah_tersedia_baru = $buku_lama['jumlah_tersedia'] + $selisih;
                    
                    // Pastikan jumlah tersedia tidak negatif
                    if ($jumlah_tersedia_baru < 0) {
                        $jumlah_tersedia_baru = 0;
                    }
                    
                    $stmt = $pdo->prepare("UPDATE buku SET judul = ?, pengarang = ?, penerbit = ?, tahun_terbit = ?, id_kategori = ?, jumlah_total = ?, jumlah_tersedia = ?, deskripsi = ? WHERE id_buku = ?");
                    $stmt->execute([$judul, $pengarang, $penerbit, $tahun_terbit, $id_kategori, $jumlah_total, $jumlah_tersedia_baru, $deskripsi, $id_buku]);
                    $success = 'Buku berhasil diperbarui!';
                } catch (Exception $e) {
                    $error = 'Gagal memperbarui buku!';
                }
                break;
                
            case 'hapus':
                $id_buku = $_POST['id_buku'];
                
                try {
                    // Cek apakah buku sedang dipinjam
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_buku = ? AND status = 'dipinjam'");
                    $stmt->execute([$id_buku]);
                    $sedang_dipinjam = $stmt->fetchColumn();
                    
                    if ($sedang_dipinjam > 0) {
                        $error = 'Tidak dapat menghapus buku yang sedang dipinjam!';
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM buku WHERE id_buku = ?");
                        $stmt->execute([$id_buku]);
                        $success = 'Buku berhasil dihapus!';
                    }
                } catch (Exception $e) {
                    $error = 'Gagal menghapus buku!';
                }
                break;
        }
    }
}

// Ambil data buku dengan kategori
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';

$query = "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (b.judul LIKE ? OR b.pengarang LIKE ? OR b.kode_buku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($kategori_filter) {
    $query .= " AND b.id_kategori = ?";
    $params[] = $kategori_filter;
}

$query .= " ORDER BY b.tanggal_ditambahkan DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$buku_list = $stmt->fetchAll();

// Ambil daftar kategori
$stmt = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori");
$kategori_list = $stmt->fetchAll();

// Ambil data buku untuk edit jika ada parameter edit
$buku_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM buku WHERE id_buku = ?");
    $stmt->execute([$_GET['edit']]);
    $buku_edit = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku - Admin Pinjamin</title>
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
                            <a class="nav-link" href="admin_dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                            <a class="nav-link active" href="admin_buku.php">
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
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h2 class="fw-bold text-primary">Kelola Buku</h2>
                            <p class="text-muted">Tambah, edit, dan hapus data buku perpustakaan</p>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bi bi-check-circle"></i> <?= $success ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form Tambah/Edit Buku -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-<?= $buku_edit ? 'pencil' : 'plus-circle' ?>"></i> 
                                        <?= $buku_edit ? 'Edit Buku' : 'Tambah Buku Baru' ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="<?= $buku_edit ? 'edit' : 'tambah' ?>">
                                        <?php if ($buku_edit): ?>
                                            <input type="hidden" name="id_buku" value="<?= $buku_edit['id_buku'] ?>">
                                        <?php endif; ?>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="judul" class="form-label">Judul Buku</label>
                                                <input type="text" class="form-control" id="judul" name="judul" 
                                                       value="<?= $buku_edit ? htmlspecialchars($buku_edit['judul']) : '' ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="pengarang" class="form-label">Pengarang</label>
                                                <input type="text" class="form-control" id="pengarang" name="pengarang" 
                                                       value="<?= $buku_edit ? htmlspecialchars($buku_edit['pengarang']) : '' ?>" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="penerbit" class="form-label">Penerbit</label>
                                                <input type="text" class="form-control" id="penerbit" name="penerbit" 
                                                       value="<?= $buku_edit ? htmlspecialchars($buku_edit['penerbit']) : '' ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
                                                <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" 
                                                       min="1900" max="<?= date('Y') ?>" 
                                                       value="<?= $buku_edit ? $buku_edit['tahun_terbit'] : '' ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="id_kategori" class="form-label">Kategori</label>
                                                <select class="form-select" id="id_kategori" name="id_kategori" required>
                                                    <option value="">Pilih Kategori</option>
                                                    <?php foreach ($kategori_list as $kategori): ?>
                                                    <option value="<?= $kategori['id_kategori'] ?>" 
                                                            <?= ($buku_edit && $buku_edit['id_kategori'] == $kategori['id_kategori']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($kategori['nama_kategori']) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label for="jumlah_total" class="form-label">Jumlah Total Buku</label>
                                                <input type="number" class="form-control" id="jumlah_total" name="jumlah_total" 
                                                       min="1" value="<?= $buku_edit ? $buku_edit['jumlah_total'] : '1' ?>" required>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= $buku_edit ? htmlspecialchars($buku_edit['deskripsi']) : '' ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-<?= $buku_edit ? 'check-circle' : 'plus-circle' ?>"></i> 
                                                <?= $buku_edit ? 'Perbarui Buku' : 'Tambah Buku' ?>
                                            </button>
                                            <?php if ($buku_edit): ?>
                                                <a href="admin_buku.php" class="btn btn-secondary">
                                                    <i class="bi bi-x-circle"></i> Batal
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
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
                                                   placeholder="Judul, pengarang, atau kode buku..." value="<?= htmlspecialchars($search) ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="kategori" class="form-label">Filter Kategori</label>
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

                    <!-- Daftar Buku -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-list"></i> Daftar Buku (<?= count($buku_list) ?> buku)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (count($buku_list) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Kode</th>
                                                    <th>Judul</th>
                                                    <th>Pengarang</th>
                                                    <th>Kategori</th>
                                                    <th>Total</th>
                                                    <th>Tersedia</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($buku_list as $buku): ?>
                                                <tr>
                                                    <td><code><?= htmlspecialchars($buku['kode_buku']) ?></code></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($buku['judul']) ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($buku['penerbit']) ?> (<?= $buku['tahun_terbit'] ?>)</small>
                                                    </td>
                                                    <td><?= htmlspecialchars($buku['pengarang']) ?></td>
                                                    <td><?= htmlspecialchars($buku['nama_kategori']) ?></td>
                                                    <td><?= $buku['jumlah_total'] ?></td>
                                                    <td><?= $buku['jumlah_tersedia'] ?></td>
                                                    <td>
                                                        <?php if ($buku['jumlah_tersedia'] > 0): ?>
                                                            <span class="badge bg-success">Tersedia</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Habis</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="admin_buku.php?edit=<?= $buku['id_buku'] ?>" class="btn btn-outline-primary">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <form method="POST" style="display: inline;" 
                                                                  onsubmit="return confirm('Yakin ingin menghapus buku ini?')">
                                                                <input type="hidden" name="action" value="hapus">
                                                                <input type="hidden" name="id_buku" value="<?= $buku['id_buku'] ?>">
                                                                <button type="submit" class="btn btn-outline-danger">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-inbox display-4 text-muted"></i>
                                        <h5 class="mt-3">Tidak ada buku ditemukan</h5>
                                        <p class="text-muted">Silakan tambah buku baru atau ubah filter pencarian</p>
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
