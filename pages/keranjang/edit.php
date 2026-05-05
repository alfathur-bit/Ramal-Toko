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

// Ambil data keranjang
$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT k.*, p.nama as nama_produk, pl.nama as nama_pelanggan FROM keranjang k LEFT JOIN produk p ON k.produk_id = p.id LEFT JOIN pelanggan pl ON k.pelanggan_id = pl.id WHERE k.id = $id");
$d = mysqli_fetch_assoc($data);

// Ambil data pelanggan dan produk untuk dropdown
$pelanggan = mysqli_query($conn,"SELECT * FROM pelanggan ORDER BY nama");
$produk = mysqli_query($conn,"SELECT * FROM produk ORDER BY nama");

// Proses update
if(isset($_POST['update'])){
    $pelanggan_id = $_POST['pelanggan_id'];
    $produk_id = $_POST['produk_id'];
    $jumlah = $_POST['jumlah'];
    
    mysqli_query($conn, "UPDATE keranjang SET pelanggan_id='$pelanggan_id', produk_id='$produk_id', jumlah='$jumlah' WHERE id=$id");
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Keranjang - RamalToko</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background:#f1f5f9; }
        .sidebar { width:220px; height:100vh; position:fixed; background:#1e293b; color:white; padding:20px; }
        .sidebar a { display:block; color:white; padding:10px; text-decoration:none; margin-top:10px; }
        .sidebar a:hover { background:#334155; }
        .content { margin-left:240px; padding:20px; }
    </style>
    <link href="/RamalToko/assets/style.css" rel="stylesheet">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h4>RamalToko</h4>
    <a href="../dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
    <a href="../produk/index.php"><i class="fa fa-box"></i> Produk</a>
    <a href="../kategori/index.php"><i class="fa fa-tags"></i> Kategori</a>
    <a href="../pelanggan/index.php"><i class="fa fa-users"></i> Pelanggan</a>
    <a href="index.php"><i class="fa fa-shopping-cart"></i> Keranjang</a>
    <a href="../transaksi/index.php"><i class="fa fa-cart-shopping"></i> Transaksi</a>
    <a href="../../auth/logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>

<!-- CONTENT -->
<div class="content">
    <h2><i class="fa fa-edit"></i> Edit Keranjang</h2>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Pelanggan</label>
                    <select name="pelanggan_id" class="form-select" required>
                        <option value="">-- Pilih Pelanggan --</option>
                        <?php while($p = mysqli_fetch_assoc($pelanggan)): ?>
                        <option value="<?= $p['id'] ?>" <?= ($p['id'] == $d['pelanggan_id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nama']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Produk</label>
                    <select name="produk_id" class="form-select" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php while($pr = mysqli_fetch_assoc($produk)): ?>
                        <option value="<?= $pr['id'] ?>" <?= ($pr['id'] == $d['produk_id']) ? 'selected' : '' ?>><?= htmlspecialchars($pr['nama']) ?> - Rp <?= number_format($pr['harga'], 0, ',', '.') ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Jumlah</label>
                    <input type="number" name="jumlah" class="form-control" value="<?= $d['jumlah'] ?>" min="1" required>
                </div>
                <button type="submit" name="update" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
                <a href="index.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
