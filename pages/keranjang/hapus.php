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
    die("Koneksi gagal");
}

// Hapus dari keranjang
$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM keranjang WHERE id = $id");
header("Location: index.php");
exit;
?>
