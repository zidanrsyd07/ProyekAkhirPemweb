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
                $nomor_anggota = generateNomorAnggota();
                $nama_lengkap = $_POST['nama_lengkap'];
                $email = $_POST['email'];
                $password = md5($_POST['password']);
                $alamat = $_POST['alamat'];
                $nomor_telepon = $_POST['nomor_telepon'];
                $status = $_POST['status'];
                
                try {
                    // Cek email sudah ada atau belum
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM anggota WHERE email = ?");
                    $stmt->execute([$email]);
                    $email_exists = $stmt->fetchColumn();
                    
                    if ($email_exists > 0) {
                        $error = 'Email sudah terdaftar!';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO anggota (nomor_anggota, nama_lengkap, email, password, alamat, nomor_telepon, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$nomor_anggota, $nama_lengkap, $email, $password, $alamat, $nomor_telepon, $status]);
                        $success = 'Anggota berhasil ditambahkan dengan nomor: ' . $nomor_anggota;
                    }
                } catch (Exception $e) {
                    $error = 'Gagal menambahkan anggota!';
                }
                break;
                
            case 'edit':
                $id_anggota = $_POST['id_anggota'];
                $nama_lengkap = $_POST['nama_lengkap'];
                $email = $_POST['email'];
                $alamat = $_POST['alamat'];
                $nomor_telepon = $_POST['nomor_telepon'];
                $status = $_POST['status'];
                
                try {
                    // Cek email sudah ada atau belum (kecuali email anggota ini sendiri)
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM anggota WHERE email = ? AND id_anggota != ?");
                    $stmt->execute([$email, $id_anggota]);
                    $email_exists = $stmt->fetchColumn();
                    
                    if ($email_exists > 0) {
                        $error = 'Email sudah digunakan oleh anggota lain!';
                    } else {
                        $stmt = $pdo->prepare("UPDATE anggota SET nama_lengkap = ?, email = ?, alamat = ?, nomor_telepon = ?, status = ? WHERE id_anggota = ?");
                        $stmt->execute([$nama_lengkap, $email, $alamat, $nomor_telepon, $status, $id_anggota]);
                        $success = 'Data anggota berhasil diperbarui!';
                    }
                } catch (Exception $e) {
                    $error = 'Gagal memperbarui data anggota!';
                }
                break;
                
            case 'reset_password':
                $id_anggota = $_POST['id_anggota'];
                $password_baru = md5('123456'); // Password default
                
                try {
                    $stmt = $pdo->prepare("UPDATE anggota SET password = ? WHERE id_anggota = ?");
                    $stmt->execute([$password_baru, $id_anggota]);
                    $success = 'Password berhasil direset menjadi: 123456';
                } catch (Exception $e) {
                    $error = 'Gagal mereset password!';
                }
                break;
                
            case 'hapus':
                $id_anggota = $_POST['id_anggota'];
                
                try {
                    // Cek apakah anggota masih memiliki peminjaman aktif
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_anggota = ? AND status = 'dipinjam'");
                    $stmt->execute([$id_anggota]);
                    $peminjaman_aktif = $stmt->fetchColumn();
                    
                    if ($peminjaman_aktif > 0) {
                        $error = 'Tidak dapat menghapus anggota yang masih memiliki peminjaman aktif!';
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM anggota WHERE id_anggota = ?");
                        $stmt->execute([$id_anggota]);
                        $success = 'Anggota berhasil dihapus!';
                    }
                } catch (Exception $e) {
                    $error = 'Gagal menghapus anggota!';
                }
                break;
        }
    }
}

// Pencarian dan filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT a.*, COUNT(p.id_peminjaman) as total_pinjam FROM anggota a LEFT JOIN peminjaman p ON a.id_anggota = p.id_anggota WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (a.nama_lengkap LIKE ? OR a.email LIKE ? OR a.nomor_anggota LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $query .= " AND a.status = ?";
    $params[] = $status_filter;
}

$query .= " GROUP BY a.id_anggota ORDER BY a.tanggal_daftar DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$anggota_list = $stmt->fetchAll();

// Ambil data anggota untuk edit jika ada parameter edit
$anggota_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM anggota WHERE id_anggota = ?");
    $stmt->execute([$_GET['edit']]);
    $anggota_edit = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Anggota - Admin Pinjamin</title>
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
                            <a class="nav-link active" href="admin_anggota.php">
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
                            <h2 class="fw-bold text-primary">Kelola Anggota</h2>
                            <p class="text-muted">Tambah, edit, dan kelola data anggota perpustakaan</p>
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

                    <!-- Form Tambah/Edit Anggota -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-<?= $anggota_edit ? 'pencil' : 'person-plus' ?>"></i> 
                                        <?= $anggota_edit ? 'Edit Anggota' : 'Tambah Anggota Baru' ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="<?= $anggota_edit ? 'edit' : 'tambah' ?>">
                                        <?php if ($anggota_edit): ?>
                                            <input type="hidden" name="id_anggota" value="<?= $anggota_edit['id_anggota'] ?>">
                                        <?php endif; ?>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                                       value="<?= $anggota_edit ? htmlspecialchars($anggota_edit['nama_lengkap']) : '' ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?= $anggota_edit ? htmlspecialchars($anggota_edit['email']) : '' ?>" required>
                                            </div>
                                            <?php if (!$anggota_edit): ?>
                                            <div class="col-md-6 mb-3">
                                                <label for="password" class="form-label">Password</label>
                                                <input type="password" class="form-control" id="password" name="password" required>
                                            </div>
                                            <?php endif; ?>
                                            <div class="col-md-6 mb-3">
                                                <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                                                <input type="tel" class="form-control" id="nomor_telepon" name="nomor_telepon" 
                                                       value="<?= $anggota_edit ? htmlspecialchars($anggota_edit['nomor_telepon']) : '' ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select" id="status" name="status" required>
                                                    <option value="aktif" <?= ($anggota_edit && $anggota_edit['status'] == 'aktif') ? 'selected' : '' ?>>Aktif</option>
                                                    <option value="nonaktif" <?= ($anggota_edit && $anggota_edit['status'] == 'nonaktif') ? 'selected' : '' ?>>Non-aktif</option>
                                                </select>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label for="alamat" class="form-label">Alamat</label>
                                                <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= $anggota_edit ? htmlspecialchars($anggota_edit['alamat']) : '' ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-<?= $anggota_edit ? 'check-circle' : 'person-plus' ?>"></i> 
                                                <?= $anggota_edit ? 'Perbarui Anggota' : 'Tambah Anggota' ?>
                                            </button>
                                            <?php if ($anggota_edit): ?>
                                                <a href="admin_anggota.php" class="btn btn-secondary">
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
                                            <label for="search" class="form-label">Cari Anggota</label>
                                            <input type="text" class="form-control" id="search" name="search" 
                                                   placeholder="Nama, email, atau nomor anggota..." value="<?= htmlspecialchars($search) ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="status" class="form-label">Filter Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="">Semua Status</option>
                                                <option value="aktif" <?= $status_filter == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                                <option value="nonaktif" <?= $status_filter == 'nonaktif' ? 'selected' : '' ?>>Non-aktif</option>
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

                    <!-- Daftar Anggota -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-people"></i> Daftar Anggota (<?= count($anggota_list) ?> anggota)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (count($anggota_list) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nomor Anggota</th>
                                                    <th>Nama & Email</th>
                                                    <th>Kontak</th>
                                                    <th>Total Pinjam</th>
                                                    <th>Status</th>
                                                    <th>Tanggal Daftar</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($anggota_list as $anggota): ?>
                                                <tr>
                                                    <td><code><?= htmlspecialchars($anggota['nomor_anggota']) ?></code></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($anggota['nama_lengkap']) ?></strong><br>
                                                        <small class="text-muted"><?= htmlspecialchars($anggota['email']) ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if ($anggota['nomor_telepon']): ?>
                                                            <?= htmlspecialchars($anggota['nomor_telepon']) ?><br>
                                                        <?php endif; ?>
                                                        <?php if ($anggota['alamat']): ?>
                                                            <small class="text-muted"><?= htmlspecialchars(substr($anggota['alamat'], 0, 30)) ?>...</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?= $anggota['total_pinjam'] ?> kali</span>
                                                    </td>
                                                    <td>
                                                        <?php if ($anggota['status'] == 'aktif'): ?>
                                                            <span class="badge bg-success">Aktif</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Non-aktif</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= formatTanggal(date('Y-m-d', strtotime($anggota['tanggal_daftar']))) ?></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="admin_anggota.php?edit=<?= $anggota['id_anggota'] ?>" class="btn btn-outline-primary">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <form method="POST" style="display: inline;" 
                                                                  onsubmit="return confirm('Reset password menjadi 123456?')">
                                                                <input type="hidden" name="action" value="reset_password">
                                                                <input type="hidden" name="id_anggota" value="<?= $anggota['id_anggota'] ?>">
                                                                <button type="submit" class="btn btn-outline-warning" title="Reset Password">
                                                                    <i class="bi bi-key"></i>
                                                                </button>
                                                            </form>
                                                            <form method="POST" style="display: inline;" 
                                                                  onsubmit="return confirm('Yakin ingin menghapus anggota ini?')">
                                                                <input type="hidden" name="action" value="hapus">
                                                                <input type="hidden" name="id_anggota" value="<?= $anggota['id_anggota'] ?>">
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
                                        <h5 class="mt-3">Tidak ada anggota ditemukan</h5>
                                        <p class="text-muted">Silakan tambah anggota baru atau ubah filter pencarian</p>
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
