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

// Proses simpan
if(isset($_POST['simpan'])){
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);
    
    if(empty($nama)){
        $error = "Nama kategori tidak boleh kosong";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO kategori (nama, deskripsi) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $nama, $deskripsi);
        if(mysqli_stmt_execute($stmt)){
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal menyimpan: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Kategori - RamalToko</title>
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
    <a href="index.php"><i class="fa fa-tags"></i> Kategori</a>
    <a href="../pelanggan/index.php"><i class="fa fa-users"></i> Pelanggan</a>
    <a href="../keranjang/index.php"><i class="fa fa-shopping-cart"></i> Keranjang</a>
    <a href="../promosi/index.php"><i class="fa fa-percent"></i> Promosi</a>
    <a href="../laporan/index.php"><i class="fa fa-chart-bar"></i> Laporan</a>
    <a href="../transaksi/index.php"><i class="fa fa-cart-shopping"></i> Transaksi</a>
    <a href="../../auth/logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>

<!-- CONTENT -->
<div class="content">
    <h2><i class="fa fa-plus"></i> Tambah Kategori</h2>
    
    <?php if(isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Nama Kategori</label>
                    <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                </div>
                <button type="submit" name="simpan" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                <a href="index.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
