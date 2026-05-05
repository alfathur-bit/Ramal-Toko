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

// Filter kategori
$kategori_filter = $_GET['kategori'] ?? '';

// Query dengan filter
if($kategori_filter){
    $data = mysqli_query($conn,"SELECT p.*, k.nama as nama_kategori FROM produk p 
        LEFT JOIN kategori k ON p.kategori_id = k.id 
        WHERE p.kategori_id = '$kategori_filter' ORDER BY p.id DESC");
} else {
    $data = mysqli_query($conn,"SELECT p.*, k.nama as nama_kategori FROM produk p 
        LEFT JOIN kategori k ON p.kategori_id = k.id ORDER BY p.id DESC");
}

// Ambil semua kategori untuk dropdown
$kategori = mysqli_query($conn,"SELECT * FROM kategori ORDER BY nama");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Produk - RamalToko</title>
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
    <a href="index.php"><i class="fa fa-box"></i> Produk</a>
    <a href="../belanja/index.php"><i class="fa fa-store"></i> Belanja</a>
    <a href="../kategori/index.php"><i class="fa fa-tags"></i> Kategori</a>
    <a href="../pelanggan/index.php"><i class="fa fa-users"></i> Pelanggan</a>
    <a href="../keranjang/index.php"><i class="fa fa-shopping-cart"></i> Keranjang</a>
    <a href="../promosi/index.php"><i class="fa fa-percent"></i> Promosi</a>
    <a href="../laporan/index.php"><i class="fa fa-chart-bar"></i> Laporan</a>
    <a href="../transaksi/index.php"><i class="fa fa-cart-shopping"></i> Transaksi</a>
    <a href="../../auth/logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>

<!-- CONTENT -->
<div class="content">
    <h2><i class="fa fa-box"></i> Data Produk</h2>
    <a href="tambah.php" class="btn btn-primary mb-3"><i class="fa fa-plus"></i> Tambah Produk</a>

    <!-- Filter Kategori -->
    <form method="GET" class="row g-3 mb-3">
        <div class="col-md-4">
            <select name="kategori" class="form-select" onchange="this.form.submit()">
                <option value="">-- Semua Kategori --</option>
                <?php while($k = mysqli_fetch_assoc($kategori)): ?>
                <option value="<?= $k['id'] ?>" <?= ($kategori_filter == $k['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($k['nama']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <a href="index.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>

<?php
$no = 1;
while($d = mysqli_fetch_assoc($data)){
    $stok_class = ($d['stok'] <= 5) ? 'text-danger fw-bold' : '';
?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($d['nama']) ?></td>
                    <td><span class="badge bg-info"><?= htmlspecialchars($d['nama_kategori'] ?? '-') ?></span></td>
                    <td>Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
                    <td class="<?= $stok_class ?>"><?= htmlspecialchars($d['stok']) ?></td>
                    <td>
                        <a href="../belanja/beli.php?produk_id=<?= $d['id'] ?>" class="btn btn-sm btn-success"><i class="fa fa-shopping-cart"></i></a>
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
