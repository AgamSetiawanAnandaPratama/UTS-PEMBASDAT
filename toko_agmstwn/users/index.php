<?php
include '../config/koneksi.php';
session_start();
if (!isset($_SESSION['user'])) header("Location: ../auth/login.php");

$produk = mysqli_query($conn, "SELECT * FROM produk");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User | eCommerce Agmstwn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    
</head>
<body>

<!-- sidebar -->
<div class="sidebar">
    <a style="justify-content: center; margin: -37px 0 -30px 0;">
        <img src="../assets/logo.png" alt="eCommerce Agmstwn" style="width: 180px; height: auto;">
    </a>
    <a href="index.php" class="fw-semibold">Dashboard</a>
    <a href="keranjang.php" class="fw-semibold">Keranjang</a>
    <a href="riwayat.php" class="fw-semibold">Riwayat</a>
    <a href="../auth/logout.php" class="fw-semibold">Logout</a>
</div>

<!-- navbar -->
<nav class="navbar navbar-light bg-white shadow-sm px-4">
    <div class="container-fluid">
        <span class="navbar-text text-primary fw-semibold">
            Hai, <?= strtoupper(htmlspecialchars($_SESSION['user']['nama'])) ?>
        </span>
    </div>
</nav>

<!-- content -->
<div class="content">
    <div class="container">
        <h3 class="fw-semibold text-primary mb-4">Daftar Produk</h3>
        <div class="row g-4">
            <?php while ($p = mysqli_fetch_assoc($produk)) : ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card produk-card h-100">
                        <img src="../assets/img/<?= htmlspecialchars($p['foto']) ?>" alt="<?= htmlspecialchars($p['nama']) ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= htmlspecialchars($p['nama']) ?></h5>
                            <p class="card-text text-muted mb-2">Rp <?= number_format($p['harga'], 0, ',', '.') ?></p>
                            <a href="detail.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
