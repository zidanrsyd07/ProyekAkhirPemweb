<?php
// Konfigurasi Database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'pinjamin_library';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi untuk mengecek login
function cekLogin() {
    if (!isset($_SESSION['login'])) {
        header('login.php');
        exit;
    }
}

// Fungsi untuk mengecek login admin
function cekLoginAdmin() {
    if (!isset($_SESSION['admin_login'])) {
        header('admin_login.php');
        exit;
    }
}

// Fungsi untuk format tanggal Indonesia
function formatTanggal($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Fungsi untuk generate nomor anggota
function generateNomorAnggota() {
    return 'A' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Fungsi untuk generate kode buku
function generateKodeBuku() {
    return 'BK' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
}
?>
