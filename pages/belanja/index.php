<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../auth/login.php");
    exit;
}

$conn = mysqli_connect("localhost","root","","RamalToko_db");
if(!$conn){
    die("Koneksi gagal");
}

$data = mysqli_query($conn, "SELECT p.*, k.nama as nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id ORDER BY p.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Belanja - RamalToko</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .product-card { border-radius: 22px; overflow: hidden; transition: transform .25s ease, box-shadow .25s ease; }
        .product-card:hover { transform: translateY(-6px); box-shadow: 0 28px 80px rgba(0,0,0,0.18); }
        .product-image { width: 100%; height: 240px; object-fit: cover; background: rgba(255,255,255,0.05); }
        .product-meta { gap: 0.5rem; display: flex; flex-wrap: wrap; }
        .badge-stock { font-size: 0.85rem; }
    </style>
    <link href="/RamalToko/assets/style.css" rel="stylesheet">
</head>
<body>

<div class="sidebar">
    <h4>RamalToko</h4>
    <a href="../dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
    <a href="../produk/index.php"><i class="fa fa-box"></i> Produk</a>
    <a href="index.php" class="active"><i class="fa fa-store"></i> Belanja</a>
    <a href="../kategori/index.php"><i class="fa fa-tags"></i> Kategori</a>
    <a href="../pelanggan/index.php"><i class="fa fa-users"></i> Pelanggan</a>
    <a href="../keranjang/index.php"><i class="fa fa-shopping-cart"></i> Keranjang</a>
    <a href="../promosi/index.php"><i class="fa fa-percent"></i> Promosi</a>
    <a href="../laporan/index.php"><i class="fa fa-chart-bar"></i> Laporan</a>
    <a href="../transaksi/index.php"><i class="fa fa-cart-shopping"></i> Transaksi</a>
    <a href="../../auth/logout.php" style="margin-top: 50px;"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <div class="page-header">
        <h3><i class="fa fa-store"></i> Rak Produk</h3>
        <p>Temukan produk unggulan dan checkout langsung dari rak produk.</p>
    </div>

    <div class="row g-4">
        <?php while($d = mysqli_fetch_assoc($data)): ?>
        <?php
            $stok = (int)$d['stok'];
            $imagePath = '/RamalToko/assets/product-images/' . (!empty($d['gambar']) ? htmlspecialchars($d['gambar']) : 'placeholder.svg');
            $stockLabel = $stok > 0 ? 'Tersedia' : 'Kosong';
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="card product-card h-100">
                <!-- Ganti gambar produk di folder assets/product-images/ jika ingin menampilkan foto produk nyata -->
                <img src="images/Aspose.Words.f208b693-1de6-41d8-9775-0fca10e25d26.001.jpeg>"class="product-image">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-2"><?= htmlspecialchars($d['nama']) ?></h5>
                            <div class="product-meta">
                                <span class="badge bg-primary">Rp <?= number_format($d['harga'], 0, ',', '.') ?></span>
                                <span class="badge bg-secondary badge-stock"><?= htmlspecialchars($d['nama_kategori'] ?? 'Umum') ?></span>
                                <span class="badge <?= $stok > 0 ? 'bg-success' : 'bg-danger' ?> badge-stock"><?= $stockLabel ?></span>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted mb-4" style="min-height: 54px;"><?= htmlspecialchars($d['deskripsi'] ?? 'Deskripsi produk singkat akan tampil di sini.') ?></p>

                    <form method="GET" action="beli.php" class="mt-auto">
                        <input type="hidden" name="produk_id" value="<?= $d['id'] ?>">
                        <div class="input-group mb-3">
                            <span class="input-group-text">Jumlah</span>
                            <input type="number" name="jumlah_barang" min="1" max="<?= max(1, $stok) ?>" value="1" class="form-control" <?= $stok === 0 ? 'disabled' : '' ?> >
                        </div>
                        <button type="submit" class="btn btn-success w-100" <?= $stok === 0 ? 'disabled' : '' ?>><i class="fa fa-cart-shopping"></i> Beli Sekarang</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
