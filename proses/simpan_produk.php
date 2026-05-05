<?php
session_start();

// Cek login
if(!isset($_SESSION['login'])){
    header("Location: ../auth/login.php");
    exit;
}

// Koneksi langsung
$conn = mysqli_connect("localhost","root","","RamalToko_db");
if(!$conn){
    die("Koneksi gagal");
}

// Validasi input
$nama = trim($_POST['nama'] ?? '');
$kategori_id = (int)($_POST['kategori_id'] ?? 0);
$harga = (int)($_POST['harga'] ?? 0);
$stok = (int)($_POST['stok'] ?? 0);

if(empty($nama) || $kategori_id <= 0 || $harga <= 0 || $stok < 0){
    die("Data tidak valid");
}

// Gunakan prepared statement
$stmt = mysqli_prepare($conn, "INSERT INTO produk (nama, kategori_id, harga, stok) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "siii", $nama, $kategori_id, $harga, $stok);

if(mysqli_stmt_execute($stmt)){
    header("Location: ../pages/produk/index.php");
} else {
    die("Gagal menyimpan data");
}

mysqli_stmt_close($stmt);
?>
