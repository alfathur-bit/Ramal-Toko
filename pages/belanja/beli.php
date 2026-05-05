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

$produk_id = intval($_GET['produk_id'] ?? 0);
if($produk_id <= 0){
    die("Produk tidak ditemukan.");
}

$query = mysqli_query($conn, "SELECT p.*, k.nama as nama_kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id WHERE p.id = $produk_id");
$produk = mysqli_fetch_assoc($query);
if(!$produk){
    die("Produk tidak ditemukan.");
}

$harga = (int)$produk['harga'];
$stok_tersedia = max(0, (int)$produk['stok']);
$today = date("Y-m-d");
$promo_data = mysqli_query($conn, "SELECT * FROM promosi WHERE status='aktif' AND tanggal_mulai <= '$today' AND tanggal_akhir >= '$today' ORDER BY diskon_persen DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Beli <?= htmlspecialchars($produk['nama']) ?> - RamalToko</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .checkout-card { border-radius: 24px; overflow: hidden; }
        .checkout-image { width: 100%; height: 340px; object-fit: cover; background: rgba(255,255,255,0.05); }
        .summary-row { gap: 1rem; }
    </style>
    <link href="/RamalToko/assets/style.css" rel="stylesheet">
</head>
<body>

<div class="sidebar">
    <h4>RamalToko</h4>
    <a href="../dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
    <a href="index.php"><i class="fa fa-store"></i> Belanja</a>
    <a href="../produk/index.php"><i class="fa fa-box"></i> Produk</a>
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
        <h3><i class="fa fa-credit-card"></i> Checkout Produk</h3>
        <p>Lengkapi data pembeli lalu konfirmasi pembayaran.</p>
    </div>

    <div class="card checkout-card">
        <div class="row g-0">
            <div class="col-md-5">
                <img src="<?= '/RamalToko/assets/product-images/' . (!empty($produk['gambar']) ? htmlspecialchars($produk['gambar']) : 'placeholder.svg') ?>" alt="<?= htmlspecialchars($produk['nama']) ?>" class="checkout-image">
            </div>
            <div class="col-md-7">
                <div class="card-body">
                    <h4 class="mb-2"><?= htmlspecialchars($produk['nama']) ?></h4>
                    <p class="text-muted mb-3">Kategori: <?= htmlspecialchars($produk['nama_kategori'] ?? 'Umum') ?></p>
                    <h3 class="mb-4">Rp <?= number_format($harga, 0, ',', '.') ?></h3>
                    <div class="mb-4">
                        <span class="badge <?= ((int)$produk['stok'] > 0) ? 'bg-success' : 'bg-danger' ?>">Stok: <?= htmlspecialchars($produk['stok']) ?></span>
                    </div>
                    <form method="POST" action="../../proses/simpan_transaksi.php">
                        <input type="hidden" name="produk_id" value="<?= $produk['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label">Nama Pelanggan</label>
                            <input type="text" name="nama_pelanggan" class="form-control" placeholder="Masukkan nama pelanggan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Barang</label>
                            <input id="jumlahBarang" type="number" name="jumlah_barang" class="form-control" value="<?= $stok_tersedia > 0 ? 1 : 0 ?>" min="1" max="<?= max(1, (int)$produk['stok']) ?>" <?= $stok_tersedia === 0 ? 'disabled' : '' ?> required>
                        </div>
<?php if(mysqli_num_rows($promo_data) > 0): ?>
                        <div class="mb-3">
                            <label class="form-label">Pilih Promosi</label>
                            <select id="promoSelect" name="promo_id" class="form-select">
                                <option value="0" data-diskon="0">Tanpa Promo</option>
<?php while($promo = mysqli_fetch_assoc($promo_data)): ?>
                                <option value="<?= $promo['id'] ?>" data-diskon="<?= $promo['diskon_persen'] ?>"><?= htmlspecialchars($promo['nama']) ?> (<?= $promo['diskon_persen'] ?>% - <?= htmlspecialchars($promo['deskripsi']) ?>)</option>
<?php endwhile; ?>
                            </select>
                        </div>
<?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Diskon</label>
                            <input id="promoLabel" type="text" class="form-control" value="Diskon: 0%" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Harga</label>
                            <input id="totalHarga" type="text" class="form-control" value="Rp <?= number_format($harga, 0, ',', '.') ?>" disabled>
                            <input id="hiddenTotal" type="hidden" name="total" value="<?= $harga ?>">
                        </div>
                        <button type="submit" class="btn btn-success btn-lg" <?= $stok_tersedia === 0 ? 'disabled' : '' ?>><i class="fa fa-check"></i> Bayar Sekarang</button>
                        <a href="index.php" class="btn btn-outline-secondary btn-lg ms-2"><i class="fa fa-arrow-left"></i> Kembali ke Rak</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const qtyInput = document.getElementById('jumlahBarang');
    const totalInput = document.getElementById('totalHarga');
    const hiddenTotal = document.getElementById('hiddenTotal');
    const promoSelect = document.getElementById('promoSelect');
    const promoLabel = document.getElementById('promoLabel');
    const unitPrice = <?= $harga ?>;

    function updateTotal() {
        let qty = parseInt(qtyInput.value) || 1;
        if(qty < 1) qty = 1;
        qtyInput.value = qty;

        let diskon = 0;
        if(promoSelect){
            diskon = parseFloat(promoSelect.selectedOptions[0].getAttribute('data-diskon') || 0) || 0;
        }
        const subtotal = qty * unitPrice;
        const finalTotal = Math.max(0, Math.round(subtotal * (100 - diskon) / 100));

        promoLabel.value = 'Diskon: ' + diskon.toFixed(2) + '%';
        totalInput.value = 'Rp ' + finalTotal.toLocaleString('id-ID');
        hiddenTotal.value = finalTotal;
    }

    qtyInput?.addEventListener('input', updateTotal);
    promoSelect?.addEventListener('change', updateTotal);
    updateTotal();
</script>

</body>
</html>
