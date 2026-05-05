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

// Ambil data pelanggan
$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM pelanggan WHERE id = $id");
$d = mysqli_fetch_assoc($data);

// Proses update
if(isset($_POST['update'])){
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    mysqli_query($conn, "UPDATE pelanggan SET nama='$nama', no_hp='$no_hp', alamat='$alamat' WHERE id=$id");
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Pelanggan - RamalToko</title>
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
    <a href="index.php"><i class="fa fa-users"></i> Pelanggan</a>
    <a href="../transaksi/index.php"><i class="fa fa-cart-shopping"></i> Transaksi</a>
    <a href="../../auth/logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>

<!-- CONTENT -->
<div class="content">
    <h2><i class="fa fa-edit"></i> Edit Pelanggan</h2>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Nama Pelanggan</label>
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($d['nama']) ?>" required>
                </div>
                <div class="mb-3">
                    <label>No HP</label>
                    <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($d['no_hp'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label>Alamat</label>
                    <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($d['alamat'] ?? '') ?></textarea>
                </div>
                <button type="submit" name="update" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
                <a href="index.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
