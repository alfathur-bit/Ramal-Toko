<?php
session_start();

// Cek login
if(!isset($_SESSION['login'])){
    header("Location: ../../auth/login.php");
    exit;
}

// Koneksi database
$conn = mysqli_connect("localhost","root","","RamalToko_db");
if(!$conn){
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Validasi ID
$id = $_GET['id'] ?? null;
if(!$id || !is_numeric($id)){
    header("Location: index.php");
    exit;
}

// Cek apakah kategori digunakan di produk
$cek = mysqli_query($conn, "SELECT COUNT(*) as total FROM produk WHERE kategori_id = $id");
$jumlah = mysqli_fetch_assoc($cek)['total'];

if($jumlah > 0){
    // Jika masih ada produk yang menggunakan kategori ini, redirect dengan pesan error
    header("Location: index.php?error=Kategori masih digunakan oleh $jumlah produk");
    exit;
}

// Hapus kategori
$stmt = mysqli_prepare($conn, "DELETE FROM kategori WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
if(mysqli_stmt_execute($stmt)){
    header("Location: index.php?success=Kategori berhasil dihapus");
} else {
    header("Location: index.php?error=Gagal menghapus kategori");
}
exit;
?>
