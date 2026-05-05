<?php
/**
 * Script untuk cek dan fix struktur tabel transaksi
 */

$conn = mysqli_connect("localhost", "root", "", "RamalToko_db");

if (!$conn) {
    die("Koneksi gagal");
}

// Cek struktur tabel
$hasil = mysqli_query($conn, "DESCRIBE transaksi");

echo "<h3>Struktur Tabel Transaksi:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";

while($row = mysqli_fetch_assoc($hasil)){
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Tambah kolom jika belum ada
$cek = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'nama_pelanggan'");
if(mysqli_num_rows($cek) == 0){
    echo "<p style='color:red'>Kolom nama_pelanggan TIDAK ada! Menambahkan...</p>";
    mysqli_query($conn, "ALTER TABLE transaksi ADD COLUMN nama_pelanggan VARCHAR(100) AFTER id");
    echo "<p style='color:green'>Kolom berhasil ditambahkan!</p>";
} else {
    echo "<p style='color:green'>Kolom nama_pelanggan SUDAH ada.</p>";
}

// Tampilkan data transaksi
echo "<h3>Data Transaksi:</h3>";
$data = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY id DESC LIMIT 5");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nama Pelanggan</th><th>Tanggal</th><th>Total</th></tr>";
while($d = mysqli_fetch_assoc($data)){
    echo "<tr>";
    echo "<td>" . $d['id'] . "</td>";
    echo "<td>" . ($d['nama_pelanggan'] ?? 'KOSONG') . "</td>";
    echo "<td>" . $d['tanggal'] . "</td>";
    echo "<td>" . $d['total'] . "</td>";
    echo "</tr>";
}
echo "</table>";

mysqli_close($conn);
?>
