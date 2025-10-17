<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$totalTransaksi = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(DISTINCT transaksi_group) AS total 
    FROM transaksi
"))['total'];

$total_query = "SELECT SUM(t.total) AS total 
                FROM transaksi t 
                JOIN users u ON t.user_id=u.id 
                WHERE t.status='selesai'";

$total_pemasukan = mysqli_fetch_assoc(mysqli_query($conn, $total_query))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body style="background-color:#f7f9ff; font-family:'Poppins', sans-serif;">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm w-100" style="margin:0; padding:0;">
  <div class="container-fluid px-4">
    <a class="navbar-brand" href="index.php">
        <img src="../assets/logo.png" alt="eCommerce Agmstwn">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link text-white fw-semibold active" href="index.php"> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="pembeli.php">Pembeli</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="produk.php"> Produk</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="transaksi.php"> Transaksi</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="laporan.php"> Laporan</a></li>
        <li class="nav-item ms-2">
          <a class="btn btn-light btn-sm text-danger fw-semibold px-3" href="../auth/logout.php"> Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- dashboard -->
<div class="container py-4 px-5" style="max-width: 100%;">
    <h2 class="fw-bold text-primary mb-4">
        Selamat Datang, <?= htmlspecialchars($_SESSION['user']['nama']) ?>
    </h2>

    <!-- statistik -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Produk</h5>
                    <?php
                    $totalProduk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM produk"))['total'];
                    ?>
                    <p class="fs-3 fw-bold text-primary"><?= $totalProduk ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Transaksi</h5>
                    <?php
                    $totalTransaksi = mysqli_fetch_assoc(mysqli_query($conn, "
                        SELECT COUNT(DISTINCT transaksi_group) AS total 
                        FROM transaksi
                    "))['total'];
                    ?>
                    <p class="fs-3 fw-bold text-primary"><?= $totalTransaksi ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Pembeli</h5>
                    <?php
                    $totalUser = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='user'"))['total'];
                    ?>
                    <p class="fs-3 fw-bold text-primary"><?= $totalUser ?></p>
                </div>
            </div>
        </div>
    </div>

        <!-- total Pendapatan -->
        <div class="alert alert-success text-center fs-5 fw-semibold">
            Total Pendapatan: Rp <?= number_format($total_pemasukan, 0, ',', '.') ?>
        </div>

    <!-- barang terlaris -->
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Barang Terlaris</h5>
        </div>
        <div class="card-body">
            <?php
            $queryTerlaris = "
            SELECT 
                p.id AS id, 
                p.nama, 
                p.foto, 
                p.harga, 
                SUM(t.jumlah) AS total_terjual,
                SUM(t.jumlah * p.harga) AS pendapatan
            FROM transaksi t
            JOIN produk p ON t.produk_id = p.id
            WHERE t.status = 'selesai'
            GROUP BY t.produk_id
            ORDER BY total_terjual DESC
            LIMIT 5
            ";
            $result = mysqli_query($conn, $queryTerlaris);

            if (mysqli_num_rows($result) > 0):
            ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Jumlah Terjual</th>
                            <th>Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><img src="../assets/img/<?= htmlspecialchars($row['foto']) ?>" 
                                     alt="<?= htmlspecialchars($row['nama']) ?>" 
                                     width="60" height="60" class="rounded"></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                            <td><?= $row['total_terjual'] ?></td>
                            <td>Rp <?= number_format($row['pendapatan'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="text-muted text-center my-3">Belum ada transaksi dengan status selesai.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="text-center text-muted py-4 border-top mt-4">
    <small>© <?= date('Y') ?> eCommerce Agmstwn</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
