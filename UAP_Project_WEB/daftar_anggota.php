<?php
session_start();
require_once 'database.php';

$success = '';
$error = '';

if ($_POST) {
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $alamat = $_POST['alamat'];
    $nomor_telepon = $_POST['nomor_telepon'];
    $nomor_anggota = generateNomorAnggota();
    
    // Cek email sudah ada atau belum
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM anggota WHERE email = ?");
    $stmt->execute([$email]);
    $email_exists = $stmt->fetchColumn();
    
    if ($email_exists > 0) {
        $error = 'Email sudah terdaftar!';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO anggota (nomor_anggota, nama_lengkap, email, password, alamat, nomor_telepon) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nomor_anggota, $nama_lengkap, $email, $password, $alamat, $nomor_telepon]);
            $success = 'Pendaftaran berhasil! Nomor anggota Anda: ' . $nomor_anggota;
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan saat mendaftar!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anggota - Pinjamin</title>
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
            padding: 20px 0;
        }
        
        .register-card {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }
        
        .register-header {
            background: var(--primary-blue);
            color: white;
            border-radius: 15px 15px 0 0;
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
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card register-card">
                    <div class="card-header register-header text-center py-4">
                        <h4 class="mb-0">
                            <i class="bi bi-person-plus"></i> Daftar Anggota Baru
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> <?= $success ?>
                                <div class="mt-2">
                                    <a href="member_login.php" class="btn btn-success btn-sm">Login Sekarang</a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                                    <input type="tel" class="form-control" id="nomor_telepon" name="nomor_telepon">
                                </div>
                                
                                <div class="col-md-12 mb-4">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-person-plus"></i> Daftar Sekarang
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-2">Sudah punya akun?</p>
                            <a href="member_login.php" class="btn btn-outline-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="index.php" class="text-decoration-none">
                            <i class="bi bi-arrow-left"></i> Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
