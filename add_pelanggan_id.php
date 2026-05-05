<?php
$conn = mysqli_connect('localhost','root','','RamalToko_db');

// Cek apakah kolom pelanggan_id sudah ada
$result = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'pelanggan_id'");
if(mysqli_num_rows($result) == 0) {
    echo "Menambahkan kolom pelanggan_id ke tabel transaksi...\n";
    $add_col = mysqli_query($conn, "ALTER TABLE transaksi ADD COLUMN pelanggan_id INT NULL AFTER id");
    if($add_col) {
        echo "✓ Kolom berhasil ditambahkan\n";
    } else {
        echo "✗ Error: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Kolom pelanggan_id sudah ada\n";
}

// Tampilkan struktur tabel terbaru
echo "\n=== TRANSAKSI TABLE STRUCTURE TERBARU ===\n";
$result = mysqli_query($conn, 'DESCRIBE transaksi');
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Key'] . "\n";
}
?>
