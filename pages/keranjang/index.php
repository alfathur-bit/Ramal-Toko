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

// Filter pelanggan
$pelanggan_filter = $_GET['pelanggan'] ?? '';

// Query keranjang
if($pelanggan_filter && is_numeric($pelanggan_filter)){
    $stmt = mysqli_prepare($conn, "SELECT k.*, p.nama as nama_produk, p.harga, pl.nama as nama_pelanggan 
        FROM keranjang k 
        LEFT JOIN produk p ON k.produk_id = p.id 
        LEFT JOIN pelanggan pl ON k.pelanggan_id = pl.id 
        WHERE k.pelanggan_id = ? ORDER BY k.id DESC");
    mysqli_stmt_bind_param($stmt, "i", $pelanggan_filter);
    mysqli_stmt_execute($stmt);
    $data = mysqli_stmt_get_result($stmt);
} else {
    $data = mysqli_query($conn, "SELECT k.*, p.nama as nama_produk, p.harga, pl.nama as nama_pelanggan 
        FROM keranjang k 
        LEFT JOIN produk p ON k.produk_id = p.id 
        LEFT JOIN pelanggan pl ON k.pelanggan_id = pl.id ORDER BY k.id DESC");
}

// Ambil semua pelanggan untuk dropdown
$pelanggan = mysqli_query($conn,"SELECT * FROM pelanggan ORDER BY nama");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Keranjang Belanja - RamalToko</title>
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
    <h2><i class="fa fa-shopping-cart"></i> Keranjang Belanja</h2>
    <a href="tambah.php" class="btn btn-primary mb-3"><i class="fa fa-plus"></i> Tambah ke Keranjang</a>

    <!-- Filter Pelanggan -->
    <form method="GET" class="row g-3 mb-3">
        <div class="col-md-4">
            <select name="pelanggan" class="form-select" onchange="this.form.submit()">
                <option value="">-- Semua Pelanggan --</option>
                <?php while($p = mysqli_fetch_assoc($pelanggan)): ?>
                <option value="<?= $p['id'] ?>" <?= ($pelanggan_filter == $p['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nama']) ?>
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
                    <th>Pelanggan</th>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>

<?php
$no = 1;
while($d = mysqli_fetch_assoc($data)){
    $total = $d['harga'] * $d['jumlah'];
?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($d['nama_pelanggan'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['nama_produk'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($d['jumlah']) ?></td>
                    <td>Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($total, 0, ',', '.') ?></td>
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
