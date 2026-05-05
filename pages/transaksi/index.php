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
    <title>Transaksi - RamalToko</title>
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

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fa fa-cart-shopping"></i> Data Transaksi</h2>
        <div>
            <a href="beli.php" class="btn btn-success"><i class="fa fa-qrcode"></i> Simulasi Pembelian</a>
            <a href="tambah.php" class="btn btn-primary"><i class="fa fa-plus"></i> Tambah Transaksi</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <tr>
                    <th>No</th>
                    <th>Nama Pelanggan</th>
                    <th>Jumlah Barang</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                </tr>

<?php
$cek_fk = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'pelanggan_id'");
$ada_pelanggan_id = mysqli_num_rows($cek_fk) > 0;

if ($ada_pelanggan_id) {
    $data = mysqli_query($conn, "
        SELECT t.*, 
               COALESCE(p.nama, t.nama_pelanggan) AS nama_pelanggan_join
        FROM transaksi t
        LEFT JOIN pelanggan p ON t.pelanggan_id = p.id
        ORDER BY t.id DESC
    ");
} else {
    $data = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY id DESC");
}

$no = 1;
while($d = mysqli_fetch_assoc($data)){
    $nama = $ada_pelanggan_id ? ($d['nama_pelanggan_join'] ?? '-') : ($d['nama_pelanggan'] ?? '-');
?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($nama) ?></td>
                    <td><?= htmlspecialchars($d['jumlah_barang'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($d['tanggal'] ?? '-') ?></td>
                    <td>Rp <?= number_format($d['total'] ?? 0, 0, ',', '.') ?></td>
                </tr>
<?php } ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>
