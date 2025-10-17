<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// ambil id pembeli
$id = intval($_GET['id']);
$p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$id' AND role='user'"));

if (!$p) {
    echo "<script>alert('Pembeli tidak ditemukan!'); window.location='pembeli.php';</script>";
    exit;
}

// update pembeli
if (isset($_POST['update'])) {
    $nama = strtoupper(mysqli_real_escape_string($conn, trim($_POST['nama'])));
    $alamat = mysqli_real_escape_string($conn, trim($_POST['alamat']));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    mysqli_query($conn, "UPDATE users SET nama='$nama', alamat='$alamat', username='$username', email='$email' 
                         WHERE id='$id' AND role='user'");
    header("Location: pembeli.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Pembeli | eCommerce Agmstwn</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="bg-light">

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
        <li class="nav-item"><a class="nav-link text-white" href="index.php"> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-white fw-semibold active" href="pembeli.php">Pembeli</a></li>
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

<!-- edit -->
<div class="container my-5">
    <div class="card shadow p-4 col-lg-6 mx-auto">
        <h3 class="fw-bold text-center text-primary mb-4">Edit Pembeli</h3>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($p['nama']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Alamat</label>
                    <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($p['alamat']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($p['username']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($p['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Password (Hash)</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($p['password']) ?>" disabled>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="update" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>

    </div>
</div>

<footer class="text-center text-muted py-4 border-top mt-4">
    <small>© <?= date('Y') ?> eCommerce Agmstwn</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
