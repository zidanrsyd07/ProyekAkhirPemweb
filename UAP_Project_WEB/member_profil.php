<?php
session_start();
require_once 'database.php';
cekLogin();

$success = '';
$error = '';

// Ambil data anggota
$stmt = $pdo->prepare("SELECT * FROM anggota WHERE id_anggota = ?");
$stmt->execute([$_SESSION['id_anggota']]);
$anggota = $stmt->fetch();

// Handle form submission
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profil':
                $nama_lengkap = $_POST['nama_lengkap'];
                $email = $_POST['email'];
                $alamat = $_POST['alamat'];
                $nomor_telepon = $_POST['nomor_telepon'];
                
                try {
                    // Cek email sudah ada atau belum (kecuali email anggota ini sendiri)
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM anggota WHERE email = ? AND id_anggota != ?");
                    $stmt->execute([$email, $_SESSION['id_anggota']]);
                    $email_exists = $stmt->fetchColumn();
                    
                    if ($email_exists > 0) {
                        $error = 'Email sudah digunakan oleh anggota lain!';
                    } else {
                        $stmt = $pdo->prepare("UPDATE anggota SET nama_lengkap = ?, email = ?, alamat = ?, nomor_telepon = ? WHERE id_anggota = ?");
                        $stmt->execute([$nama_lengkap, $email, $alamat, $nomor_telepon, $_SESSION['id_anggota']]);
                        
                        // Update session
                        $_SESSION['nama_anggota'] = $nama_lengkap;
                        
                        $success = 'Profil berhasil diperbarui!';
                        
                        // Refresh data anggota
                        $stmt = $pdo->prepare("SELECT * FROM anggota WHERE id_anggota = ?");
                        $stmt->execute([$_SESSION['id_anggota']]);
                        $anggota = $stmt->fetch();
                    }
                } catch (Exception $e) {
                    $error = 'Gagal memperbarui profil!';
                }
                break;
                
            case 'ganti_password':
                $password_lama = md5($_POST['password_lama']);
                $password_baru = md5($_POST['password_baru']);
                $konfirmasi_password = md5($_POST['konfirmasi_password']);
                
                try {
                    // Cek password lama
                    $stmt = $pdo->prepare("SELECT password FROM anggota WHERE id_anggota = ?");
                    $stmt->execute([$_SESSION['id_anggota']]);
                    $password_db = $stmt->fetchColumn();
                    
                    if ($password_lama != $password_db) {
                        $error = 'Password lama tidak sesuai!';
                    } elseif ($password_baru != $konfirmasi_password) {
                        $error = 'Konfirmasi password tidak sesuai!';
                    } else {
                        $stmt = $pdo->prepare("UPDATE anggota SET password = ? WHERE id_anggota = ?");
                        $stmt->execute([$password_baru, $_SESSION['id_anggota']]);
                        $success = 'Password berhasil diubah!';
                    }
                } catch (Exception $e) {
                    $error = 'Gagal mengubah password!';
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Pinjamin</title>
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
                            <a class="nav-link active" href="member_profil.php">
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
                            <h2 class="fw-bold text-primary">Profil Saya</h2>
                            <p class="text-muted">Kelola informasi profil dan keamanan akun Anda</p>
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
                        <!-- Informasi Profil -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-person-circle"></i> Informasi Profil
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_profil">
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                                       value="<?= htmlspecialchars($anggota['nama_lengkap']) ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?= htmlspecialchars($anggota['email']) ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                                                <input type="tel" class="form-control" id="nomor_telepon" name="nomor_telepon" 
                                                       value="<?= htmlspecialchars($anggota['nomor_telepon']) ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nomor Anggota</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($anggota['nomor_anggota']) ?>" readonly>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label for="alamat" class="form-label">Alamat</label>
                                                <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= htmlspecialchars($anggota['alamat']) ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> Perbarui Profil
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Akun -->
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="bi bi-info-circle"></i> Informasi Akun
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <?php if ($anggota['status'] == 'aktif'): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Non-aktif</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal Daftar:</strong></td>
                                            <td><?= formatTanggal(date('Y-m-d', strtotime($anggota['tanggal_daftar']))) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Ganti Password -->
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">
                                        <i class="bi bi-shield-lock"></i> Ganti Password
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="ganti_password">
                                        
                                        <div class="mb-3">
                                            <label for="password_lama" class="form-label">Password Lama</label>
                                            <input type="password" class="form-control" id="password_lama" name="password_lama" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="password_baru" class="form-label">Password Baru</label>
                                            <input type="password" class="form-control" id="password_baru" name="password_baru" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                                            <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-warning w-100">
                                            <i class="bi bi-key"></i> Ganti Password
                                        </button>
                                    </form>
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
