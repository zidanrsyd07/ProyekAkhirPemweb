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
                $nama_kategori = $_POST['nama_kategori'];
                $deskripsi = $_POST['deskripsi'];
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)");
                    $stmt->execute([$nama_kategori, $deskripsi]);
                    $success = 'Kategori berhasil ditambahkan!';
                } catch (Exception $e) {
                    $error = 'Gagal menambahkan kategori!';
                }
                break;
                
            case 'edit':
                $id_kategori = $_POST['id_kategori'];
                $nama_kategori = $_POST['nama_kategori'];
                $deskripsi = $_POST['deskripsi'];
                
                try {
                    $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori = ?, deskripsi = ? WHERE id_kategori = ?");
                    $stmt->execute([$nama_kategori, $deskripsi, $id_kategori]);
                    $success = 'Kategori berhasil diperbarui!';
                } catch (Exception $e) {
                    $error = 'Gagal memperbarui kategori!';
                }
                break;
                
            case 'hapus':
                $id_kategori = $_POST['id_kategori'];
                
                try {
                    // Cek apakah kategori masih digunakan oleh buku
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM buku WHERE id_kategori = ?");
                    $stmt->execute([$id_kategori]);
                    $jumlah_buku = $stmt->fetchColumn();
                    
                    if ($jumlah_buku > 0) {
                        $error = 'Tidak dapat menghapus kategori yang masih digunakan oleh ' . $jumlah_buku . ' buku!';
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM kategori WHERE id_kategori = ?");
                        $stmt->execute([$id_kategori]);
                        $success = 'Kategori berhasil dihapus!';
                    }
                } catch (Exception $e) {
                    $error = 'Gagal menghapus kategori!';
                }
                break;
        }
    }
}

// Ambil data kategori dengan jumlah buku
$stmt = $pdo->query("SELECT k.*, COUNT(b.id_buku) as jumlah_buku FROM kategori k LEFT JOIN buku b ON k.id_kategori = b.id_kategori GROUP BY k.id_kategori ORDER BY k.nama_kategori");
$kategori_list = $stmt->fetchAll();

// Ambil data kategori untuk edit jika ada parameter edit
$kategori_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM kategori WHERE id_kategori = ?");
    $stmt->execute([$_GET['edit']]);
    $kategori_edit = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Admin Pinjamin</title>
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
                            <a class="nav-link active" href="admin_kategori.php">
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
                            <h2 class="fw-bold text-primary">Kelola Kategori</h2>
                            <p class="text-muted">Tambah, edit, dan hapus kategori buku</p>
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

                    <div class="row">
                        <!-- Form Tambah/Edit Kategori -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-<?= $kategori_edit ? 'pencil' : 'plus-circle' ?>"></i> 
                                        <?= $kategori_edit ? 'Edit Kategori' : 'Tambah Kategori' ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="<?= $kategori_edit ? 'edit' : 'tambah' ?>">
                                        <?php if ($kategori_edit): ?>
                                            <input type="hidden" name="id_kategori" value="<?= $kategori_edit['id_kategori'] ?>">
                                        <?php endif; ?>
                                        
                                        <div class="mb-3">
                                            <label for="nama_kategori" class="form-label">Nama Kategori</label>
                                            <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" 
                                                   value="<?= $kategori_edit ? htmlspecialchars($kategori_edit['nama_kategori']) : '' ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="deskripsi" class="form-label">Deskripsi</label>
                                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= $kategori_edit ? htmlspecialchars($kategori_edit['deskripsi']) : '' ?></textarea>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-<?= $kategori_edit ? 'check-circle' : 'plus-circle' ?>"></i> 
                                                <?= $kategori_edit ? 'Perbarui' : 'Tambah' ?>
                                            </button>
                                            <?php if ($kategori_edit): ?>
                                                <a href="admin_kategori.php" class="btn btn-secondary">
                                                    <i class="bi bi-x-circle"></i> Batal
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Kategori -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-list"></i> Daftar Kategori (<?= count($kategori_list) ?> kategori)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (count($kategori_list) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nama Kategori</th>
                                                    <th>Deskripsi</th>
                                                    <th>Jumlah Buku</th>
                                                    <th>Tanggal Dibuat</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($kategori_list as $kategori): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($kategori['nama_kategori']) ?></strong>
                                                    </td>
                                                    <td>
                                                        <?php if ($kategori['deskripsi']): ?>
                                                            <?= htmlspecialchars(substr($kategori['deskripsi'], 0, 50)) ?>
                                                            <?= strlen($kategori['deskripsi']) > 50 ? '...' : '' ?>
                                                        <?php else: ?>
                                                            <em class="text-muted">Tidak ada deskripsi</em>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?= $kategori['jumlah_buku'] ?> buku</span>
                                                    </td>
                                                    <td><?= formatTanggal(date('Y-m-d', strtotime($kategori['tanggal_dibuat']))) ?></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="admin_kategori.php?edit=<?= $kategori['id_kategori'] ?>" class="btn btn-outline-primary">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <?php if ($kategori['jumlah_buku'] == 0): ?>
                                                            <form method="POST" style="display: inline;" 
                                                                  onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                                                <input type="hidden" name="action" value="hapus">
                                                                <input type="hidden" name="id_kategori" value="<?= $kategori['id_kategori'] ?>">
                                                                <button type="submit" class="btn btn-outline-danger">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                            <?php else: ?>
                                                            <button class="btn btn-outline-secondary" disabled title="Tidak dapat dihapus karena masih digunakan">
                                                                <i class="bi bi-lock"></i>
                                                            </button>
                                                            <?php endif; ?>
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
                                        <h5 class="mt-3">Belum ada kategori</h5>
                                        <p class="text-muted">Silakan tambah kategori baru</p>
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
