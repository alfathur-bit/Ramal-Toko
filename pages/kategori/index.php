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
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil data kategori
$data = mysqli_query($conn,"SELECT * FROM kategori ORDER BY nama");
if(!$data){
    die("Query gagal: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kategori - RamalToko</title>
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
    <h2><i class="fa fa-tags"></i> Data Kategori</h2>
    
    <?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    
    <?php if(isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    
    <a href="tambah.php" class="btn btn-primary mb-3"><i class="fa fa-plus"></i> Tambah Kategori</a>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Aksi</th>
                </tr>

<?php
$no = 1;
while($d = mysqli_fetch_assoc($data)){
?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($d['nama']) ?></td>
                    <td><?= htmlspecialchars($d['deskripsi'] ?? '') ?></td>
                    <td>
                        <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>
                        <a href="hapus.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
<?php } ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>
