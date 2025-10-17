<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$nama_user = $_GET['nama_user'] ?? '';
$dari = $_GET['dari'] ?? '';
$sampai = $_GET['sampai'] ?? '';

// hapus transaksi yang jumlah produknya 0 di semua baris
mysqli_query($conn, "
    DELETE FROM transaksi
    WHERE transaksi_id IN (
        SELECT transaksi_id FROM (
            SELECT transaksi_id
            FROM transaksi
            GROUP BY transaksi_id
            HAVING SUM(IFNULL(jumlah,0)) = 0
        ) AS sub
    )
");

// ambil transaksi (group per transaksi_id)
$query = "
SELECT t.transaksi_group, t.status, t.tanggal, u.nama AS user
FROM transaksi t
JOIN users u ON t.user_id = u.id
WHERE 1=1
";

if ($nama_user) $query .= " AND u.nama LIKE '%".mysqli_real_escape_string($conn,$nama_user)."%'";
if ($dari) $query .= " AND t.tanggal >= '$dari 00:00:00'";
if ($sampai) $query .= " AND t.tanggal <= '$sampai 23:59:59'";
$query .= " GROUP BY t.transaksi_group
            ORDER BY t.tanggal DESC";

$transaksi = mysqli_query($conn, $query);

// total pemasukan
$total_query = "
SELECT SUM(t.total) AS total
FROM transaksi t
JOIN users u ON t.user_id=u.id
WHERE t.status='selesai'
";
if ($nama_user) $total_query .= " AND u.nama LIKE '%".mysqli_real_escape_string($conn,$nama_user)."%'";
if ($dari) $total_query .= " AND t.tanggal >= '$dari 00:00:00'";
if ($sampai) $total_query .= " AND t.tanggal <= '$sampai 23:59:59'";
$total_pemasukan = mysqli_fetch_assoc(mysqli_query($conn, $total_query))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Penjualan | eCommerce Agmstwn</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm w-100" style="margin:0; padding:0;">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="index.php"><img src="../assets/logo.png" alt="eCommerce Agmstwn"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link text-white" href="index.php"> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="pembeli.php">Pembeli</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="produk.php"> Produk</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="transaksi.php"> Transaksi</a></li>
        <li class="nav-item"><a class="nav-link text-white fw-semibold active" href="laporan.php"> Laporan</a></li>
        <li class="nav-item ms-2"><a class="btn btn-light btn-sm text-danger fw-semibold px-3" href="../auth/logout.php"> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container my-5">
    <div class="card shadow p-4">
        <h3 class="fw-bold mb-4 text-primary text-center">Laporan Penjualan</h3>

        <!-- filter -->
        <form method="get" class="row g-3 align-items-center justify-content-center mb-4">
            <div class="col-md-3">
                <input type="text" name="nama_user" class="form-control" placeholder="Nama Pembeli" value="<?= htmlspecialchars($nama_user) ?>">
            </div>
            <div class="col-md-3">
                <input type="date" name="dari" class="form-control" value="<?= $dari ?>">
            </div>
            <div class="col-md-3">
                <input type="date" name="sampai" class="form-control" value="<?= $sampai ?>">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">Cetak</button>
            </div>
        </form>

        <div class="alert alert-success text-center fs-5 fw-semibold">
            Total Pendapatan: Rp <?= number_format($total_pemasukan,0,',','.') ?>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">

                <thead class="table-primary">
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Pembeli</th>
                        <th>Produk Dibeli</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if(mysqli_num_rows($transaksi) > 0): ?>
                        <?php while($t = mysqli_fetch_assoc($transaksi)):
                            // ambil detail produk per transaksi_id
                            $details = mysqli_query($conn, "
                                SELECT p.nama, p.harga, t.jumlah
                                FROM transaksi t
                                JOIN produk p ON t.produk_id = p.id
                                WHERE t.transaksi_group = '{$t['transaksi_group']}' AND t.jumlah > 0
                            ");

                            $produkList = [];
                            $totalHarga = 0;
                            $totalProduk = 0;
                            while($p = mysqli_fetch_assoc($details)){
                                $produkList[] = $p;
                                $totalHarga += $p['harga'] * $p['jumlah'];
                                $totalProduk += $p['jumlah'];
                            }
                            if(count($produkList) == 0) continue;

                            $status = $t['status'];
                            $badgeClass = $status==='diproses'?'bg-warning text-dark':($status==='dikirim'?'bg-info text-dark':($status==='selesai'?'bg-success text-white':'bg-secondary'));
                        ?>
                        <tr>
                            <td><?= $t['transaksi_group'] ?></td>
                            <td><?= strtoupper(htmlspecialchars($t['user'])) ?></td>
                            <td>
                                <?php foreach($produkList as $p): ?>
                                    <div><?= htmlspecialchars($p['nama']) ?></div>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php foreach($produkList as $p): ?>
                                    <div><?= $p['jumlah'] ?></div>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php foreach($produkList as $p): ?>
                                    <div>Rp <?= number_format($p['harga'],0,',','.') ?></div>
                                <?php endforeach; ?>
                            </td>
                            <td>Rp <?= number_format($totalHarga,0,',','.') ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span></td>
                            <td><?= date('d-m-Y H:i', strtotime($t['tanggal'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-muted">Tidak ada data ditemukan</td></tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>

<footer class="text-center text-muted py-4 border-top mt-4">
    <small>© <?= date('Y') ?> eCommerce Agmstwn</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>