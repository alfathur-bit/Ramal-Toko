<?php
/**
 * Script setup database untuk 10 fitur
 */

$conn = mysqli_connect("localhost", "root", "", "RamalToko_db");

if (!$conn) {
    die("Koneksi gagal");
}

echo "<h2>Setup Database RamalToko</h2>";
echo "<hr>";

// 1. Buat tabel kategori
$query1 = "CREATE TABLE IF NOT EXISTS kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $query1);
echo "✅ Tabel kategori<br>";

// 2. Tambah kolom kategori_id di produk (buat FK juga kalau belum ada)
$cek = mysqli_query($conn, "SHOW COLUMNS FROM produk LIKE 'kategori_id'");
if (mysqli_num_rows($cek) == 0) {
    mysqli_query($conn, "ALTER TABLE produk ADD COLUMN kategori_id INT AFTER nama");
    mysqli_query($conn, "ALTER TABLE produk ADD FOREIGN KEY (kategori_id) REFERENCES kategori(id)");
    echo "✅ Kolom kategori_id di produk<br>";
} else {
    echo "✅ Kolom kategori_id sudah ada<br>";
}

// 4. Buat tabel pelanggan
$query4 = "CREATE TABLE IF NOT EXISTS pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $query4);
echo "✅ Tabel pelanggan<br>";

// 4b. Buat tabel transaksi + relasi ke pelanggan
$query4b = "CREATE TABLE IF NOT EXISTS transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelanggan_id INT NULL,
    tanggal DATE NOT NULL,
    nama_pelanggan VARCHAR(100) NOT NULL,
    jumlah_barang INT DEFAULT 0,
    total INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id)
)";
mysqli_query($conn, $query4b);
echo "✅ Tabel transaksi<br>";

// Pastikan kolom yang dipakai aplikasi ada (untuk kompatibilitas database lama)
$cek_tp = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'total'");
if (mysqli_num_rows($cek_tp) == 0) {
    mysqli_query($conn, "ALTER TABLE transaksi ADD COLUMN total INT DEFAULT 0 AFTER jumlah_barang");
}

$cek_tp2 = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'jumlah_barang'");
if (mysqli_num_rows($cek_tp2) == 0) {
    mysqli_query($conn, "ALTER TABLE transaksi ADD COLUMN jumlah_barang INT DEFAULT 0 AFTER nama_pelanggan");
}

$cek_tp3 = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'tanggal'");
if (mysqli_num_rows($cek_tp3) == 0) {
    mysqli_query($conn, "ALTER TABLE transaksi ADD COLUMN tanggal DATE NOT NULL AFTER id");
}

$cek_tp4 = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'pelanggan_id'");
if (mysqli_num_rows($cek_tp4) == 0) {
    mysqli_query($conn, "ALTER TABLE transaksi ADD COLUMN pelanggan_id INT NULL AFTER id");
    mysqli_query($conn, "ALTER TABLE transaksi ADD FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id)");
}

$cek_tp5 = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'nama_pelanggan'");
if (mysqli_num_rows($cek_tp5) == 0) {
    mysqli_query($conn, "ALTER TABLE transaksi ADD COLUMN nama_pelanggan VARCHAR(100) NOT NULL AFTER tanggal");
}

echo "✅ Kolom transaksi tervalidasi<br>";

// 5. Buat tabel settings
$query5 = "CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
mysqli_query($conn, $query5);
echo "✅ Tabel settings<br>";

// 6. Buat tabel keranjang
$query6 = "CREATE TABLE IF NOT EXISTS keranjang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelanggan_id INT,
    produk_id INT,
    jumlah INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id),
    FOREIGN KEY (produk_id) REFERENCES produk(id)
)";
mysqli_query($conn, $query6);
echo "✅ Tabel keranjang<br>";

// 9. Buat tabel promosi
$query9 = "CREATE TABLE IF NOT EXISTS promosi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    diskon_persen DECIMAL(5,2),
    tanggal_mulai DATE,
    tanggal_akhir DATE,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $query9);
echo "✅ Tabel promosi<br>";

// 10. Buat tabel barang_masuk
$query10 = "CREATE TABLE IF NOT EXISTS barang_masuk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produk_id INT,
    jumlah INT NOT NULL,
    tanggal DATE NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $query10);
echo "✅ Tabel barang_masuk<br>";

// 11. Buat tabel laporan
$query11 = "CREATE TABLE IF NOT EXISTS laporan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jenis ENUM('penjualan', 'stok', 'pelanggan', 'barang_masuk') NOT NULL,
    tanggal DATE NOT NULL,
    data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $query11);
echo "✅ Tabel laporan<br>";

// Insert kategori default
$cek_kat = mysqli_query($conn, "SELECT COUNT(*) as total FROM kategori");
$jml = mysqli_fetch_assoc($cek_kat)['total'];
if ($jml == 0) {
    mysqli_query($conn, "INSERT INTO kategori (nama, deskripsi) VALUES ('Makanan', 'Produk makanan ringan')");
    mysqli_query($conn, "INSERT INTO kategori (nama, deskripsi) VALUES ('Minuman', 'Produk minuman')");
    mysqli_query($conn, "INSERT INTO kategori (nama, deskripsi) VALUES ('Snack', 'Kudapan ringan')");
    echo "✅ Data kategori default<br>";
}

// Insert settings default
$cek_set = mysqli_query($conn, "SELECT COUNT(*) as total FROM settings");
$jml_set = mysqli_fetch_assoc($cek_set)['total'];
if ($jml_set == 0) {
    mysqli_query($conn, "INSERT INTO settings (setting_key, setting_value) VALUES ('nama_toko', 'RamalToko')");
    mysqli_query($conn, "INSERT INTO settings (setting_key, setting_value) VALUES ('alamat_toko', 'Jl. Contoh No. 123')");
    mysqli_query($conn, "INSERT INTO settings (setting_key, setting_value) VALUES ('no_telp', '0812-3456-7890')");
    echo "✅ Data settings default<br>";
}

echo "<hr>";
echo "<h3 style='color:green'>🎉 Setup database selesai!</h3>";
echo "<p>Sekarang Anda bisa menggunakan 10 fitur.</p>";

mysqli_close($conn);
?>
