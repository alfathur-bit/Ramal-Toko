<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// cek login
if(!isset($_SESSION['login'])){
    header("Location: ../auth/login.php");
    exit;
}

// koneksi database
$conn = mysqli_connect("localhost","root","","RamalToko_db");
if(!$conn){
    die("Koneksi gagal");
}

// ambil data
$produk = mysqli_query($conn,"SELECT COUNT(*) as total FROM produk");
$produk = mysqli_fetch_assoc($produk)['total'];

$transaksi = mysqli_query($conn,"SELECT COUNT(*) as total FROM transaksi");
$transaksi = mysqli_fetch_assoc($transaksi)['total'];

$pendapatan = mysqli_query($conn,"SELECT SUM(total) as total FROM transaksi");
$pendapatan = mysqli_fetch_assoc($pendapatan)['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard RamalToko</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body { background:#f8f9fa; }
        .sidebar {
            width:240px;
            height:100vh;
            position:fixed;
            background:linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
            color:white;
            padding:20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar h4 {
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 30px;
        }
        .sidebar a {
            display:block;
            color:rgba(255,255,255,0.8);
            padding:12px 15px;
            text-decoration:none;
            margin-top:8px;
            border-radius:8px;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background:rgba(255,255,255,0.2);
            color:white;
            transform: translateX(5px);
        }
        .sidebar a i { width: 25px; }
        .content { margin-left:240px; padding:30px; }
        .stat-card {
            border:none;
            border-radius:15px;
            padding:25px;
            color:white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card i { font-size: 40px; opacity: 0.8; }
        .stat-card h2 { font-size: 32px; font-weight: bold; margin: 10px 0 5px 0; }
        .stat-card p { margin: 0; opacity: 0.9; font-size: 14px; }
        .bg-primary { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); }
        .bg-success { background: linear-gradient(135deg, #1cc88f 0%, #13855e 100%); }
        .bg-danger { background: linear-gradient(135deg, #e74a3b 0%, #be3d31 100%); }
        .bg-warning { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); }
        .page-header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .table-card { border:none; border-radius:15px; box-shadow: 0 2px 15px rgba(0,0,0,0.05); }
    </style>
    <link href="/RamalToko/assets/style.css" rel="stylesheet">
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h4><i class="fa fa-store"></i> RamalToko</h4>

    <a href="dashboard.php" class="active"><i class="fa fa-home"></i> Dashboard</a>
    <a href="produk/index.php"><i class="fa fa-box"></i> Produk</a>
    <a href="belanja/index.php"><i class="fa fa-store"></i> Belanja</a>
    <a href="kategori/index.php"><i class="fa fa-tags"></i> Kategori</a>
    <a href="pelanggan/index.php"><i class="fa fa-users"></i> Pelanggan</a>
    <a href="keranjang/index.php"><i class="fa fa-shopping-cart"></i> Keranjang</a>
    <a href="promosi/index.php"><i class="fa fa-percent"></i> Promosi</a>
    <a href="laporan/index.php"><i class="fa fa-chart-bar"></i> Laporan</a>
    <a href="transaksi/index.php"><i class="fa fa-cart-shopping"></i> Transaksi</a>
    <a href="../auth/logout.php" style="margin-top: 50px;"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>

<!-- CONTENT -->
<div class="content">

    <div class="page-header">
        <h3 style="margin:0; color:#f8fafc;"><i class="fa fa-chart-line"></i> Dashboard</h3>
        <p style="margin:5px 0 0 0; color:rgba(248,250,252,0.75);">Selamat datang di halaman utama</p>
    </div>

    <!-- STATISTIK -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card bg-primary">
                <i class="fa fa-box"></i>
                <h2><?= $produk ?: 0 ?></h2>
                <p>Total Produk</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card bg-success">
                <i class="fa fa-shopping-cart"></i>
                <h2><?= $transaksi ?: 0 ?></h2>
                <p>Total Transaksi</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card bg-danger">
                <i class="fa fa-money-bill-wave"></i>
                <h2>Rp <?= number_format($pendapatan ?: 0, 0, ',', '.') ?></h2>
                <p>Total Pendapatan</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card bg-warning">
                <i class="fa fa-calendar-check"></i>
                <h2><?= date('d') ?></h2>
                <p>Hari Ini</p>
            </div>
        </div>
    </div>

    <!-- TRANSAKSI TERAKHIR -->
    <div class="row">
        <div class="col-md-12">
            <div class="card table-card">
                <div class="card-header py-3" style="background: rgba(255,255,255,0.06); border-bottom:1px solid rgba(255,255,255,0.10);">
                    <h5 style="margin:0; color:#f8fafc;"><i class="fa fa-history"></i> Transaksi Terbaru</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead style="background: rgba(255,255,255,0.05); color: #f8fafc;">
                            <tr>
                                <th class="px-4">No</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $transaksi_terbaru = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY id DESC LIMIT 5");
                            $no = 1;
                            while($t = mysqli_fetch_assoc($transaksi_terbaru)):
                            ?>
                            <tr>
                                <td class="px-4"><?= $no++ ?></td>
                                <td><?= date('d M Y', strtotime($t['tanggal'])) ?></td>
                                <td>Rp <?= number_format($t['total'], 0, ',', '.') ?></td>
                                <td><span class="badge bg-success">Selesai</span></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(mysqli_num_rows($transaksi_terbaru) == 0): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Belum ada transaksi</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

</body>
</html>
