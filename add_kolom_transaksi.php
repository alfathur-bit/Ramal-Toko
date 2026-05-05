<?php
/**
 * Script untuk menambahkan kolom nama_pelanggan ke tabel transaksi
 * Jalankan sekali saja, lalu hapus!
 */

$conn = mysqli_connect("localhost", "root", "", "RamalToko_db");

if (!$conn) {
    die("Koneksi gagal");
}

// Tambah kolom jika belum ada
$query = "ALTER TABLE transaksi ADD COLUMN nama_pelanggan VARCHAR(100) AFTER id";

if(mysqli_query($conn, $query)){
    echo "Kolom nama_pelanggan berhasil ditambahkan!";
} else {
    echo "Kolom sudah ada atau terjadi error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
