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

// Pastikan tabel barang_masuk ada agar laporan tidak error
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS barang_masuk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produk_id INT,
    jumlah INT NOT NULL,
    tanggal DATE NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$download = isset($_GET['download']) && $_GET['download'] == '1';
$jenis = $_GET['jenis'] ?? 'pembelian';
if($jenis === 'penjualan'){
    $jenis = 'pembelian';
}
$allowed_reports = ['pembelian', 'stok', 'barang_masuk', 'pelanggan', 'produk', 'promosi'];
if(!in_array($jenis, $allowed_reports)){
    $jenis = 'pembelian';
}

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$kategori_filter = intval($_GET['kategori'] ?? 0);
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$today = date('Y-m-d');
$kategori_options = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama");

if($jenis == 'pembelian'){
    $filters = [];
    if($start){
        $filters[] = "tanggal >= '" . mysqli_real_escape_string($conn, $start) . "'";
    }
    if($end){
        $filters[] = "tanggal <= '" . mysqli_real_escape_string($conn, $end) . "'";
    }
    if($search){
        $safe = mysqli_real_escape_string($conn, $search);
        $filters[] = "(nama_pelanggan LIKE '%$safe%' OR tanggal LIKE '%$safe%')";
    }
    $where = $filters ? 'WHERE ' . implode(' AND ', $filters) : '';
    $query = "SELECT tanggal, nama_pelanggan, IFNULL(jumlah_barang, 0) as jumlah_barang, total FROM transaksi $where ORDER BY tanggal DESC";
    $summary_query = "SELECT COUNT(*) AS total_transaksi, IFNULL(SUM(jumlah_barang),0) AS total_item, IFNULL(SUM(total),0) AS total_penjualan FROM transaksi $where";
    $data = mysqli_query($conn, $query);
    if(!$data) die("Query Error: " . mysqli_error($conn));
    $summary = mysqli_fetch_assoc(mysqli_query($conn, $summary_query)) ?: ['total_transaksi'=>0, 'total_item'=>0, 'total_penjualan'=>0];
    $title = 'Laporan Pembelian';
    $headers = ['Tanggal', 'Nama Pelanggan', 'Jumlah Barang', 'Total Harga'];
} elseif($jenis == 'stok'){
    $filters = [];
    if($kategori_filter){
        $filters[] = "p.kategori_id = $kategori_filter";
    }
    if($search){
        $safe = mysqli_real_escape_string($conn, $search);
        $filters[] = "(p.nama LIKE '%$safe%' OR k.nama LIKE '%$safe%')";
    }
    $where = $filters ? 'WHERE ' . implode(' AND ', $filters) : '';
    $query = "SELECT p.nama, p.stok, k.nama as kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id $where ORDER BY p.stok ASC";
    $summary_query = "SELECT COUNT(*) AS produk_count, IFNULL(SUM(p.stok),0) AS total_stok, SUM(CASE WHEN p.stok <= 5 THEN 1 ELSE 0 END) AS low_stock FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id $where";
    $data = mysqli_query($conn, $query);
    if(!$data) die("Query Error: " . mysqli_error($conn));
    $summary = mysqli_fetch_assoc(mysqli_query($conn, $summary_query)) ?: ['produk_count'=>0, 'total_stok'=>0, 'low_stock'=>0];
    $title = 'Laporan Stok';
    $headers = ['Nama Produk', 'Kategori', 'Stok'];
} elseif($jenis == 'barang_masuk'){
    $filters = [];
    if($start){
        $filters[] = "bm.tanggal >= '" . mysqli_real_escape_string($conn, $start) . "'";
    }
    if($end){
        $filters[] = "bm.tanggal <= '" . mysqli_real_escape_string($conn, $end) . "'";
    }
    if($search){
        $safe = mysqli_real_escape_string($conn, $search);
        $filters[] = "(p.nama LIKE '%$safe%' OR bm.keterangan LIKE '%$safe%' OR bm.tanggal LIKE '%$safe%')";
    }
    $where = $filters ? 'WHERE ' . implode(' AND ', $filters) : '';
    $query = "SELECT bm.tanggal, p.nama as produk, bm.jumlah, bm.keterangan FROM barang_masuk bm LEFT JOIN produk p ON bm.produk_id = p.id $where ORDER BY bm.tanggal DESC";
    $summary_query = "SELECT COUNT(*) AS total_record, IFNULL(SUM(bm.jumlah),0) AS total_masuk FROM barang_masuk bm LEFT JOIN produk p ON bm.produk_id = p.id $where";
    $data = mysqli_query($conn, $query);
    if(!$data) die("Query Error: " . mysqli_error($conn));
    $summary = mysqli_fetch_assoc(mysqli_query($conn, $summary_query)) ?: ['total_record'=>0, 'total_masuk'=>0];
    $title = 'Laporan Barang Masuk';
    $headers = ['Tanggal', 'Produk', 'Jumlah Masuk', 'Keterangan'];
    $summary = mysqli_fetch_assoc(mysqli_query($conn, $summary_query));
    $title = 'Laporan Barang Masuk';
    $headers = ['Tanggal', 'Produk', 'Jumlah Masuk', 'Keterangan'];
} elseif($jenis == 'pelanggan'){
    $search_filter = '';
    if($search){
        $safe = mysqli_real_escape_string($conn, $search);
        $search_filter = "WHERE p.nama LIKE '%$safe%'";
    }
    $query = "SELECT p.id, p.nama, COUNT(t.id) AS transaksi, IFNULL(SUM(t.total),0) AS total_belanja, MAX(t.tanggal) AS terakhir FROM pelanggan p LEFT JOIN transaksi t ON p.id = t.pelanggan_id $search_filter GROUP BY p.id, p.nama ORDER BY total_belanja DESC";
    $summary_query = "SELECT COUNT(p.id) AS total_pelanggan, IFNULL(SUM(t.total),0) AS total_belanja FROM pelanggan p LEFT JOIN transaksi t ON p.id = t.pelanggan_id $search_filter";
    
    $data = mysqli_query($conn, $query);
    if(!$data){
        die("Query Error: " . mysqli_error($conn) . "<br>Query: " . htmlspecialchars($query));
    }
    
    $summary = mysqli_fetch_assoc(mysqli_query($conn, $summary_query));
    if(!$summary){
        $summary = ['total_pelanggan' => 0, 'total_belanja' => 0];
    }
    $title = 'Laporan Pelanggan';
    $headers = ['Nama Pelanggan', 'Total Transaksi', 'Total Belanja', 'Terakhir'];
} elseif($jenis == 'produk'){
    $filters = [];
    if($kategori_filter){
        $filters[] = "p.kategori_id = $kategori_filter";
    }
    if($search){
        $safe = mysqli_real_escape_string($conn, $search);
        $filters[] = "(p.nama LIKE '%$safe%' OR k.nama LIKE '%$safe%')";
    }
    $where = $filters ? 'WHERE ' . implode(' AND ', $filters) : '';
    $query = "SELECT p.nama, p.harga, p.stok, k.nama as kategori FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id $where ORDER BY p.nama ASC";
    $summary_query = "SELECT COUNT(*) AS produk_count, IFNULL(SUM(p.stok),0) AS total_stok, SUM(CASE WHEN p.stok <= 5 THEN 1 ELSE 0 END) AS low_stock FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id $where";
    $data = mysqli_query($conn, $query);
    if(!$data){
        die("Query Error: " . mysqli_error($conn) . "<br>Query: " . htmlspecialchars($query));
    }
    $summary = mysqli_fetch_assoc(mysqli_query($conn, $summary_query));
    if(!$summary){
        $summary = ['produk_count' => 0, 'total_stok' => 0, 'low_stock' => 0];
    }
    $title = 'Laporan Produk';
    $headers = ['Nama Produk', 'Harga', 'Stok', 'Kategori'];
} else {
    $status_filter = in_array($status_filter, ['aktif','nonaktif']) ? $status_filter : '';
    $filters = [];
    if($status_filter){
        $filters[] = "status = '" . mysqli_real_escape_string($conn,$status_filter) . "'";
    }
    if($search){
        $safe = mysqli_real_escape_string($conn, $search);
        $filters[] = "(nama LIKE '%$safe%' OR deskripsi LIKE '%$safe%')";
    }
    $where = $filters ? 'WHERE ' . implode(' AND ', $filters) : '';
    $query = "SELECT *, CASE WHEN status = 'aktif' AND tanggal_mulai <= '$today' AND tanggal_akhir >= '$today' THEN 'Sedang Berlangsung' ELSE 'Berakhir' END AS kondisi FROM promosi $where ORDER BY tanggal_mulai DESC";
    $summary_query = "SELECT COUNT(*) AS total_promosi, SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) AS aktif FROM promosi $where";
    $data = mysqli_query($conn, $query);
    if(!$data) die("Query Error: " . mysqli_error($conn));
    $summary = mysqli_fetch_assoc(mysqli_query($conn, $summary_query)) ?: ['total_promosi'=>0, 'aktif'=>0];
    $title = 'Laporan Promosi';
    $headers = ['Nama Promosi', 'Diskon', 'Periode', 'Status', 'Kondisi'];
}

if($download){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="laporan_' . $jenis . '_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, $headers);
    while($row = mysqli_fetch_assoc($data)){
        if($jenis == 'pembelian'){
            fputcsv($out, [$row['tanggal'], $row['nama_pelanggan'], $row['jumlah_barang'], $row['total']]);
        } elseif($jenis == 'stok'){
            fputcsv($out, [$row['nama'], $row['kategori'], $row['stok']]);
        } elseif($jenis == 'barang_masuk'){
            fputcsv($out, [$row['tanggal'], $row['produk'], $row['jumlah'], $row['keterangan']]);
        } elseif($jenis == 'pelanggan'){
            fputcsv($out, [$row['nama'], $row['transaksi'], $row['total_belanja'], $row['terakhir']]);
        } elseif($jenis == 'produk'){
            fputcsv($out, [$row['nama'], $row['harga'], $row['stok'], $row['kategori']]);
        } elseif($jenis == 'promosi'){
            fputcsv($out, [$row['nama'], $row['diskon_persen'] . '%', $row['tanggal_mulai'] . ' s/d ' . $row['tanggal_akhir'], $row['status'], $row['kondisi']]);
        }
    }
    fclose($out);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan - RamalToko</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background:#f1f5f9; }
        .sidebar { width:220px; height:100vh; position:fixed; background:#1e293b; color:white; padding:20px; }
        .sidebar a { display:block; color:white; padding:10px; text-decoration:none; margin-top:10px; }
        .sidebar a:hover { background:#334155; }
        .content { margin-left:240px; padding:20px; }
        .report-actions { display:flex; justify-content:flex-end; gap:10px; }
        .no-print { display:flex; justify-content:space-between; align-items:center; gap:10px; }
        @media print {
            .sidebar, .no-print, .btn { display:none !important; }
            .content { margin-left:0; padding:0; }
        }
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
    <a href="../keranjang/index.php"><i class="fa fa-shopping-cart"></i> Keranjang</a>
    <a href="../promosi/index.php"><i class="fa fa-percent"></i> Promosi</a>
    <a href="index.php"><i class="fa fa-chart-bar"></i> Laporan</a>
    <a href="../transaksi/index.php"><i class="fa fa-cart-shopping"></i> Transaksi</a>
    <a href="../../auth/logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>

<!-- CONTENT -->
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h2><i class="fa fa-chart-bar"></i> <?= htmlspecialchars($title) ?></h2>
            <p class="text-muted mb-0">Pilih jenis laporan dan cetak data langsung atau unduh file CSV.</p>
        </div>
        <div class="report-actions">
            <?php
            $downloadParams = $_GET;
            $downloadParams['download'] = 1;
            ?>
            <a href="?<?= htmlspecialchars(http_build_query($downloadParams)) ?>" class="btn btn-outline-secondary"><i class="fa fa-download"></i> Download CSV</a>
            <button type="button" class="btn btn-outline-primary" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </div>

    <form method="GET" class="row g-3 mb-3 no-print">
        <div class="col-md-3">
            <select name="jenis" class="form-select" onchange="this.form.submit()">
                <option value="pembelian" <?= ($jenis == 'pembelian') ? 'selected' : '' ?>>Laporan Pembelian</option>
                <option value="stok" <?= ($jenis == 'stok') ? 'selected' : '' ?>>Laporan Stok</option>
                <option value="barang_masuk" <?= ($jenis == 'barang_masuk') ? 'selected' : '' ?>>Laporan Barang Masuk</option>
                <option value="pelanggan" <?= ($jenis == 'pelanggan') ? 'selected' : '' ?>>Laporan Pelanggan</option>
                <option value="produk" <?= ($jenis == 'produk') ? 'selected' : '' ?>>Laporan Produk</option>
                <option value="promosi" <?= ($jenis == 'promosi') ? 'selected' : '' ?>>Laporan Promosi</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Cari..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <?php if(in_array($jenis, ['pembelian', 'barang_masuk'])): ?>
            <div class="col-md-2">
                <input type="date" name="start" class="form-control" value="<?= htmlspecialchars($start) ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="end" class="form-control" value="<?= htmlspecialchars($end) ?>">
            </div>
        <?php endif; ?>
        <?php if(in_array($jenis, ['stok', 'produk'])): ?>
            <div class="col-md-2">
                <select name="kategori" class="form-select">
                    <option value="0">Semua Kategori</option>
                    <?php while($k = mysqli_fetch_assoc($kategori_options)): ?>
                        <option value="<?= $k['id'] ?>" <?= ($kategori_filter == $k['id']) ? 'selected' : '' ?>><?= htmlspecialchars($k['nama']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        <?php endif; ?>
        <?php if($jenis == 'promosi'): ?>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="aktif" <?= ($status_filter == 'aktif') ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= ($status_filter == 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
        <?php endif; ?>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fa fa-filter"></i> Tampilkan</button>
        </div>
        <div class="col-md-2">
            <a href="index.php" class="btn btn-secondary w-100"><i class="fa fa-rotate-left"></i> Reset</a>
        </div>
    </form>

    <div class="row g-3 mb-3 no-print">
        <?php if($jenis == 'pembelian'): ?>
            <div class="col-md-4"><div class="card p-3"><strong>Total Transaksi</strong><div class="fs-4"><?= htmlspecialchars($summary['total_transaksi']) ?></div></div></div>
            <div class="col-md-4"><div class="card p-3"><strong>Total Item</strong><div class="fs-4"><?= htmlspecialchars($summary['total_item']) ?></div></div></div>
            <div class="col-md-4"><div class="card p-3"><strong>Total Penjualan</strong><div class="fs-4">Rp <?= number_format($summary['total_penjualan'],0,',','.') ?></div></div></div>
        <?php elseif($jenis == 'stok'): ?>
            <div class="col-md-4"><div class="card p-3"><strong>Produk</strong><div class="fs-4"><?= htmlspecialchars($summary['produk_count']) ?></div></div></div>
            <div class="col-md-4"><div class="card p-3"><strong>Total Stok</strong><div class="fs-4"><?= htmlspecialchars($summary['total_stok']) ?></div></div></div>
            <div class="col-md-4"><div class="card p-3"><strong>Stok Rendah</strong><div class="fs-4"><?= htmlspecialchars($summary['low_stock']) ?></div></div></div>
        <?php elseif($jenis == 'barang_masuk'): ?>
            <div class="col-md-6"><div class="card p-3"><strong>Catatan Masuk</strong><div class="fs-4"><?= htmlspecialchars($summary['total_record']) ?></div></div></div>
            <div class="col-md-6"><div class="card p-3"><strong>Total Masuk</strong><div class="fs-4"><?= htmlspecialchars($summary['total_masuk']) ?></div></div></div>
        <?php elseif($jenis == 'pelanggan'): ?>
            <div class="col-md-6"><div class="card p-3"><strong>Total Pelanggan</strong><div class="fs-4"><?= htmlspecialchars($summary['total_pelanggan']) ?></div></div></div>
            <div class="col-md-6"><div class="card p-3"><strong>Total Belanja</strong><div class="fs-4">Rp <?= number_format($summary['total_belanja'],0,',','.') ?></div></div></div>
        <?php elseif($jenis == 'produk'): ?>
            <div class="col-md-4"><div class="card p-3"><strong>Produk</strong><div class="fs-4"><?= htmlspecialchars($summary['produk_count']) ?></div></div></div>
            <div class="col-md-4"><div class="card p-3"><strong>Total Stok</strong><div class="fs-4"><?= htmlspecialchars($summary['total_stok']) ?></div></div></div>
            <div class="col-md-4"><div class="card p-3"><strong>Stok Rendah</strong><div class="fs-4"><?= htmlspecialchars($summary['low_stock']) ?></div></div></div>
        <?php else: ?>
            <div class="col-md-6"><div class="card p-3"><strong>Total Promosi</strong><div class="fs-4"><?= htmlspecialchars($summary['total_promosi']) ?></div></div></div>
            <div class="col-md-6"><div class="card p-3"><strong>Aktif</strong><div class="fs-4"><?= htmlspecialchars($summary['aktif']) ?></div></div></div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if($jenis == 'pembelian'): ?>
                <table class="table table-striped">
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Pelanggan</th>
                        <th>Jumlah Barang</th>
                        <th>Total Harga</th>
                    </tr>
                    <?php while($d = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['tanggal']) ?></td>
                        <td><?= htmlspecialchars($d['nama_pelanggan'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($d['jumlah_barang']) ?></td>
                        <td>Rp <?= number_format($d['total'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            <?php elseif($jenis == 'stok'): ?>
                <table class="table table-striped">
                    <tr>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                    </tr>
                    <?php while($d = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['nama']) ?></td>
                        <td><?= htmlspecialchars($d['kategori'] ?? '-') ?></td>
                        <td class="<?= ($d['stok'] <= 5) ? 'text-danger fw-bold' : '' ?>"><?= htmlspecialchars($d['stok']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            <?php elseif($jenis == 'barang_masuk'): ?>
                <table class="table table-striped">
                    <tr>
                        <th>Tanggal</th>
                        <th>Produk</th>
                        <th>Jumlah Masuk</th>
                        <th>Keterangan</th>
                    </tr>
                    <?php while($d = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['tanggal']) ?></td>
                        <td><?= htmlspecialchars($d['produk'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($d['jumlah']) ?></td>
                        <td><?= htmlspecialchars($d['keterangan'] ?? '-') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            <?php elseif($jenis == 'pelanggan'): ?>
                <table class="table table-striped">
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>Transaksi</th>
                        <th>Total Belanja</th>
                        <th>Terakhir</th>
                    </tr>
                    <?php while($d = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['nama']) ?></td>
                        <td><?= htmlspecialchars($d['transaksi']) ?></td>
                        <td>Rp <?= number_format($d['total_belanja'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($d['terakhir'] ?? '-') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            <?php elseif($jenis == 'produk'): ?>
                <table class="table table-striped">
                    <tr>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Kategori</th>
                    </tr>
                    <?php while($d = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['nama']) ?></td>
                        <td>Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
                        <td class="<?= ($d['stok'] <= 5) ? 'text-danger fw-bold' : '' ?>"><?= htmlspecialchars($d['stok']) ?></td>
                        <td><?= htmlspecialchars($d['kategori'] ?? '-') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <table class="table table-striped">
                    <tr>
                        <th>Nama Promosi</th>
                        <th>Diskon</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Kondisi</th>
                    </tr>
                    <?php while($d = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['nama']) ?></td>
                        <td><?= htmlspecialchars($d['diskon_persen']) ?>%</td>
                        <td><?= htmlspecialchars($d['tanggal_mulai']) ?> s/d <?= htmlspecialchars($d['tanggal_akhir']) ?></td>
                        <td><?= htmlspecialchars($d['status']) ?></td>
                        <td><?= htmlspecialchars($d['kondisi']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
