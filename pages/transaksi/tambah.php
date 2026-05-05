<?php
session_start();

// Cek login
if(!isset($_SESSION['login'])){
    header("Location: ../../auth/login.php");
    exit;
}

// Koneksi langsung
$conn = mysqli_connect("localhost","root","","RamalToko_db");
if(!$conn){
    die("Koneksi gagal");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Transaksi - RamalToko</title>
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
    <a href="index.php"><i class="fa fa-cart-shopping"></i> Transaksi</a>
    <a href="../../auth/logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>

<!-- CONTENT -->
<div class="content">
    <h2><i class="fa fa-plus"></i> Tambah Transaksi</h2>
    
    <div class="card" style="max-width: 500px;">
        <div class="card-body">
            <form method="POST" action="../../proses/simpan_transaksi.php">
                <div class="mb-3">
                    <label>Nama Pelanggan</label>
                    <input type="text" name="nama_pelanggan" class="form-control" placeholder="Masukkan nama pelanggan" required>
                </div>
                <div class="mb-3">
                    <label>Jumlah Barang</label>
                    <input type="number" name="jumlah_barang" class="form-control" placeholder="Masukkan jumlah barang" min="1" required>
                </div>
                <div class="mb-3">
                    <label>Total Harga</label>
                    <input type="number" name="total" class="form-control" placeholder="Masukkan total harga" min="0" required>
                </div>
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
                <a href="index.php" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
