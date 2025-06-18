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
            case 'kembalikan':
                $id_peminjaman = $_POST['id_peminjaman'];
                $tanggal_kembali = date('Y-m-d');
                $denda = $_POST['denda'] ?? 0;
                $catatan = $_POST['catatan'] ?? '';
                
                try {
                    $pdo->beginTransaction();
                    
                    // Update status peminjaman
                    $stmt = $pdo->prepare("UPDATE peminjaman SET tanggal_kembali_aktual = ?, status = 'dikembalikan', denda = ?, catatan = ? WHERE id_peminjaman = ?");
                    $stmt->execute([$tanggal_kembali, $denda, $catatan, $id_peminjaman]);
                    
                    // Ambil data buku untuk update stok
                    $stmt = $pdo->prepare("SELECT id_buku FROM peminjaman WHERE id_peminjaman = ?");
                    $stmt->execute([$id_peminjaman]);
                    $id_buku = $stmt->fetchColumn();
                    
                    // Update jumlah tersedia
                    $stmt = $pdo->prepare("UPDATE buku SET jumlah_tersedia = jumlah_tersedia + 1 WHERE id_buku = ?");
                    $stmt->execute([$id_buku]);
                    
                    $pdo->commit();
                    $success = 'Buku berhasil dikembalikan!';
                } catch (Exception $e) {
                    $pdo->rollback();
                    $error = 'Gagal memproses pengembalian buku!';
                }
                break;
                
            case 'perpanjang':
                $id_peminjaman = $_POST['id_peminjaman'];
                $tanggal_kembali_baru = $_POST['tanggal_kembali_baru'];
                
                try {
                    $stmt = $pdo->prepare("UPDATE peminjaman SET tanggal_kembali_rencana = ? WHERE id_peminjaman = ?");
                    $stmt->execute([$tanggal_kembali_baru, $id_peminjaman]);
                    $success = 'Peminjaman berhasil diperpanjang!';
                } catch (Exception $e) {
                    $error = 'Gagal memperpanjang peminjaman!';
                }
                break;
        }
    }
}

// Pencarian dan filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$tanggal_filter = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

$query = "SELECT p.*, a.nama_lengkap, a.nomor_anggota, b.judul, b.kode_buku, b.pengarang 
          FROM peminjaman p 
          JOIN anggota a ON p.id_anggota = a.id_anggota 
          JOIN buku b ON p.id_buku = b.id_buku 
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (a.nama_lengkap LIKE ? OR a.nomor_anggota LIKE ? OR b.judul LIKE ? OR b.kode_buku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $query .= " AND p.status = ?";
    $params[] = $status_filter;
}

if ($tanggal_filter) {
    $query .= " AND p.tanggal_pinjam = ?";
    $params[] = $tanggal_filter;
}

$query .= " ORDER BY p.tanggal_pinjam DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$peminjaman_list = $stmt->fetchAll();

// Update status terlambat otomatis
$stmt = $pdo->prepare("UPDATE peminjaman SET status = 'terlambat' WHERE tanggal_kembali_rencana < CURDATE() AND status = 'dipinjam'");
$stmt->execute();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Peminjaman - Admin Pinjamin</title>
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
                            <a class="nav-link" href="admin_buku.php">
                                <i class="bi bi-book"></i> Kelola Buku
                            </a>
                            <a class="nav-link" href="admin_kategori.php">
                                <i class="bi bi-tags"></i> Kelola Kategori
                            </a>
                            <a class="nav-link" href="admin_anggota.php">
                                <i class="bi bi-people"></i> Kelola Anggota
                            </a>
                            <a class="nav-link active" href="admin_peminjaman.php">
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
                            <h2 class="fw-bold text-primary">Kelola Peminjaman</h2>
                            <p class="text-muted">Monitor dan kelola peminjaman buku perpustakaan</p>
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

                    <!-- Search and Filter -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form method="GET" class="row g-3">
                                        <div class="col-md-4">
                                            <label for="search" class="form-label">Cari Peminjaman</label>
                                            <input type="text" class="form-control" id="search" name="search" 
                                                   placeholder="Nama anggota, judul buku, atau kode..." value="<?= htmlspecialchars($search) ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="status" class="form-label">Filter Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="">Semua Status</option>
                                                <option value="dipinjam" <?= $status_filter == 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                                                <option value="dikembalikan" <?= $status_filter == 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
                                                <option value="terlambat" <?= $status_filter == 'terlambat' ? 'selected' : '' ?>>Terlambat</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="tanggal" class="form-label">Tanggal Pinjam</label>
                                            <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal_filter) ?>">
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

                    <!-- Daftar Peminjaman -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-list"></i> Daftar Peminjaman (<?= count($peminjaman_list) ?> peminjaman)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (count($peminjaman_list) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Anggota</th>
                                                    <th>Buku</th>
                                                    <th>Tanggal Pinjam</th>
                                                    <th>Tanggal Kembali</th>
                                                    <th>Status</th>
                                                    <th>Denda</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($peminjaman_list as $pinjam): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($pinjam['nama_lengkap']) ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($pinjam['nomor_anggota']) ?></small>
                                                    </td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($pinjam['judul']) ?></strong><br>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars($pinjam['pengarang']) ?><br>
                                                            Kode: <?= htmlspecialchars($pinjam['kode_buku']) ?>
                                                        </small>
                                                    </td>
                                                    <td><?= formatTanggal($pinjam['tanggal_pinjam']) ?></td>
                                                    <td>
                                                        <strong>Rencana:</strong> <?= formatTanggal($pinjam['tanggal_kembali_rencana']) ?><br>
                                                        <?php if ($pinjam['tanggal_kembali_aktual']): ?>
                                                            <strong>Aktual:</strong> <?= formatTanggal($pinjam['tanggal_kembali_aktual']) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($pinjam['status'] == 'dipinjam'): ?>
                                                            <span class="badge bg-warning">Dipinjam</span>
                                                        <?php elseif ($pinjam['status'] == 'dikembalikan'): ?>
                                                            <span class="badge bg-success">Dikembalikan</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Terlambat</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($pinjam['denda'] > 0): ?>
                                                            <span class="text-danger">Rp <?= number_format($pinjam['denda'], 0, ',', '.') ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($pinjam['status'] == 'dipinjam' || $pinjam['status'] == 'terlambat'): ?>
                                                        <div class="btn-group btn-group-sm">
                                                            <!-- Button Kembalikan -->
                                                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalKembalikan<?= $pinjam['id_peminjaman'] ?>">
                                                                <i class="bi bi-check-circle"></i> Kembalikan
                                                            </button>
                                                            
                                                            <!-- Button Perpanjang -->
                                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalPerpanjang<?= $pinjam['id_peminjaman'] ?>">
                                                                <i class="bi bi-calendar-plus"></i> Perpanjang
                                                            </button>
                                                        </div>

                                                        <!-- Modal Kembalikan -->
                                                        <div class="modal fade" id="modalKembalikan<?= $pinjam['id_peminjaman'] ?>" tabindex="-1">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Kembalikan Buku</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <form method="POST">
                                                                        <div class="modal-body">
                                                                            <input type="hidden" name="action" value="kembalikan">
                                                                            <input type="hidden" name="id_peminjaman" value="<?= $pinjam['id_peminjaman'] ?>">
                                                                            
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Buku</label>
                                                                                <p class="form-control-plaintext"><?= htmlspecialchars($pinjam['judul']) ?></p>
                                                                            </div>
                                                                            
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Peminjam</label>
                                                                                <p class="form-control-plaintext"><?= htmlspecialchars($pinjam['nama_lengkap']) ?></p>
                                                                            </div>
                                                                            
                                                                            <?php
                                                                            $hari_terlambat = 0;
                                                                            $tanggal_kembali = new DateTime($pinjam['tanggal_kembali_rencana']);
                                                                            $tanggal_sekarang = new DateTime();
                                                                            if ($tanggal_sekarang > $tanggal_kembali) {
                                                                                $hari_terlambat = $tanggal_sekarang->diff($tanggal_kembali)->days;
                                                                            }
                                                                            $denda_otomatis = $hari_terlambat * 1000; // Rp 1000 per hari
                                                                            ?>
                                                                            
                                                                            <?php if ($hari_terlambat > 0): ?>
                                                                            <div class="alert alert-warning">
                                                                                <strong>Terlambat <?= $hari_terlambat ?> hari</strong><br>
                                                                                Denda otomatis: Rp <?= number_format($denda_otomatis, 0, ',', '.') ?>
                                                                            </div>
                                                                            <?php endif; ?>
                                                                            
                                                                            <div class="mb-3">
                                                                                <label for="denda" class="form-label">Denda (Rp)</label>
                                                                                <input type="number" class="form-control" name="denda" value="<?= $denda_otomatis ?>" min="0">
                                                                            </div>
                                                                            
                                                                            <div class="mb-3">
                                                                                <label for="catatan" class="form-label">Catatan</label>
                                                                                <textarea class="form-control" name="catatan" rows="3"></textarea>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                            <button type="submit" class="btn btn-success">Kembalikan Buku</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Modal Perpanjang -->
                                                        <div class="modal fade" id="modalPerpanjang<?= $pinjam['id_peminjaman'] ?>" tabindex="-1">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Perpanjang Peminjaman</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <form method="POST">
                                                                        <div class="modal-body">
                                                                            <input type="hidden" name="action" value="perpanjang">
                                                                            <input type="hidden" name="id_peminjaman" value="<?= $pinjam['id_peminjaman'] ?>">
                                                                            
                                                                            <div class="mb-3">
                                                                                <label class="form-label">Tanggal Kembali Saat Ini</label>
                                                                                <p class="form-control-plaintext"><?= formatTanggal($pinjam['tanggal_kembali_rencana']) ?></p>
                                                                            </div>
                                                                            
                                                                            <div class="mb-3">
                                                                                <label for="tanggal_kembali_baru" class="form-label">Tanggal Kembali Baru</label>
                                                                                <input type="date" class="form-control" name="tanggal_kembali_baru" 
                                                                                       value="<?= date('Y-m-d', strtotime($pinjam['tanggal_kembali_rencana'] . ' +7 days')) ?>" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                            <button type="submit" class="btn btn-primary">Perpanjang</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php else: ?>
                                                        <span class="text-muted">Sudah dikembalikan</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-inbox display-4 text-muted"></i>
                                        <h5 class="mt-3">Tidak ada peminjaman ditemukan</h5>
                                        <p class="text-muted">Silakan ubah filter pencarian</p>
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
