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

// Input
$nama_pelanggan = trim($_POST['nama_pelanggan'] ?? '');
$jumlah_barang = intval($_POST['jumlah_barang'] ?? 0);
$total_input = intval($_POST['total'] ?? 0);
$produk_id = intval($_POST['produk_id'] ?? 0);
$promo_id = intval($_POST['promo_id'] ?? 0);

if(empty($nama_pelanggan) || $jumlah_barang <= 0 || $total_input < 0){
    die("Data tidak valid");
}

$tanggal = date("Y-m-d");

// Hitung total transaksi dan cek stok
$grand_total = $total_input;
$stok_update_needed = false;

if($produk_id > 0){
    $produk_query = mysqli_query($conn, "SELECT * FROM produk WHERE id = $produk_id LIMIT 1");
    if(!$produk_query){
        die("Gagal membaca produk: " . mysqli_error($conn));
    }

    $produk = mysqli_fetch_assoc($produk_query);
    if(!$produk){
        die("Produk tidak ditemukan.");
    }

    $stok_produk = intval($produk['stok']);
    if($jumlah_barang > $stok_produk){
        die("Stok tidak cukup untuk pembelian ini.");
    }

    $subtotal = intval($produk['harga']) * $jumlah_barang;
    $diskon_persen = 0;

    if($promo_id > 0){
        $promo_query = mysqli_query($conn, "SELECT * FROM promosi WHERE id = $promo_id AND status = 'aktif' AND tanggal_mulai <= '$tanggal' AND tanggal_akhir >= '$tanggal' LIMIT 1");
        if($promo_query){
            $promo = mysqli_fetch_assoc($promo_query);
            if($promo){
                $diskon_persen = floatval($promo['diskon_persen']);
            }
        }
    }

    $grand_total = max(0, intval(round($subtotal * (100 - $diskon_persen) / 100)));
    $stok_update_needed = true;
}

// Pastikan kolom transaksi untuk produk dan promo ada
$cek_produk_col = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'produk_id'");
if($cek_produk_col && mysqli_num_rows($cek_produk_col) === 0){
    mysqli_query($conn, "ALTER TABLE transaksi ADD COLUMN produk_id INT NULL");
}
$cek_promo_col = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'promo_id'");
if($cek_promo_col && mysqli_num_rows($cek_promo_col) === 0){
    mysqli_query($conn, "ALTER TABLE transaksi ADD COLUMN promo_id INT NULL");
}

// Cek kolom transaksi
$cek_nama = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'nama_pelanggan'");
$ada_nama = $cek_nama && mysqli_num_rows($cek_nama) > 0;
$cek_jumlah = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'jumlah_barang'");
$ada_jumlah = $cek_jumlah && mysqli_num_rows($cek_jumlah) > 0;
$cek_pelanggan_id = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'pelanggan_id'");
$ada_pelanggan_id = $cek_pelanggan_id && mysqli_num_rows($cek_pelanggan_id) > 0;

$pelanggan_id = null;
if($ada_pelanggan_id){
    $nama_bersih = mysqli_real_escape_string($conn, $nama_pelanggan);
    $stmt = mysqli_prepare($conn, "SELECT id FROM pelanggan WHERE nama = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $nama_bersih);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if($row = mysqli_fetch_assoc($result)){
        $pelanggan_id = intval($row['id']);
    } else {
        mysqli_stmt_close($stmt);
        $stmt = mysqli_prepare($conn, "INSERT INTO pelanggan (nama) VALUES (?)");
        mysqli_stmt_bind_param($stmt, 's', $nama_bersih);
        mysqli_stmt_execute($stmt);
        $pelanggan_id = intval(mysqli_insert_id($conn));
    }
    mysqli_stmt_close($stmt);
}

// Build insert data
$columns = ['tanggal', 'total'];
$values = ["'" . mysqli_real_escape_string($conn, $tanggal) . "'", intval($grand_total)];

if($ada_pelanggan_id){
    $columns[] = 'pelanggan_id';
    $values[] = intval($pelanggan_id);
}
if($ada_nama){
    $columns[] = 'nama_pelanggan';
    $values[] = "'" . mysqli_real_escape_string($conn, $nama_pelanggan) . "'";
}
if($ada_jumlah){
    $columns[] = 'jumlah_barang';
    $values[] = intval($jumlah_barang);
}
if($produk_id > 0){
    $columns[] = 'produk_id';
    $values[] = intval($produk_id);
}
if($promo_id > 0){
    $columns[] = 'promo_id';
    $values[] = intval($promo_id);
}

$sql = "INSERT INTO transaksi (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";

mysqli_autocommit($conn, false);
$berhasil = mysqli_query($conn, $sql);
if(!$berhasil){
    $error = mysqli_error($conn);
    mysqli_rollback($conn);
    die("Gagal menyimpan data: " . $error);
}

if($stok_update_needed){
    $update = mysqli_query($conn, "UPDATE produk SET stok = stok - " . intval($jumlah_barang) . " WHERE id = " . intval($produk_id));
    if(!$update){
        $error = mysqli_error($conn);
        mysqli_rollback($conn);
        die("Gagal mengurangi stok produk: " . $error);
    }
}

mysqli_commit($conn);
header("Location: ../pages/transaksi/index.php");
exit;
?>
