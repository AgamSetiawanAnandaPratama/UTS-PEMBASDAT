<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// tambah pembeli
if (isset($_POST['tambah'])) {
    $nama = strtoupper(trim($_POST['nama']));
    $alamat = trim($_POST['alamat']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    mysqli_query($conn, "INSERT INTO users (nama, alamat, username, email, password, role) 
                         VALUES ('$nama', '$alamat', '$username', '$email', '$password', 'user')");
    header("Location: pembeli.php");
    exit;
}

// hapus pembeli
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM users WHERE id='$id' AND role='user'");
    header("Location: pembeli.php");
    exit;
}

// search
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
if ($cari != '') {
    $pembeli = mysqli_query($conn, "SELECT * FROM users WHERE role='user' 
                    AND (nama LIKE '%$cari%' OR alamat LIKE '%$cari%' OR username LIKE '%$cari%' OR email LIKE '%$cari%') 
                    ORDER BY id ASC");
} else {
    $pembeli = mysqli_query($conn, "SELECT * FROM users WHERE role='user' ORDER BY id ASC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen Pembeli | eCommerce Agmstwn</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="bg-light" style="font-family:'Poppins', sans-serif;">

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

<!-- tambah pembeli -->
<div class="container py-2 px-3">
  <div class="row g-4 mt-4">
    <div class="col-md-4">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Tambah Pembeli</h5>
        </div>
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Alamat</label>
                <input type="text" name="alamat" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="d-grid">
                <button type="submit" name="tambah" class="btn btn-primary">+ Tambah</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- daftar pembeli -->
    <div class="col-md-8">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Daftar Pembeli</h5>
          <form method="GET" class="d-flex">
            <input type="text" name="cari" class="form-control form-control-sm me-2" placeholder="Cari..." value="<?= htmlspecialchars($cari) ?>">
            <button class="btn btn-light btn-sm" type="submit">🔍</button>
          </form>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
              <thead class="table-primary">
                <tr>
                  <th>No</th>
                  <th>Nama</th>
                  <th>Alamat</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
              <?php if (mysqli_num_rows($pembeli) > 0): ?>
                <?php $no=1; while($p = mysqli_fetch_assoc($pembeli)): ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td><?= strtoupper(htmlspecialchars($p['nama'])) ?></td>
                    <td><?= htmlspecialchars($p['alamat']) ?></td>
                    <td><?= htmlspecialchars($p['username']) ?></td>
                    <td><?= htmlspecialchars($p['email']) ?></td>
                    <td>
                      <a href="pembeli_edit.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                      <a href="?hapus=<?= $p['id'] ?>" onclick="return confirm('Yakin hapus pembeli?')" class="btn btn-danger btn-sm">Hapus</a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="6" class="text-muted">Tidak ada pembeli ditemukan</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<footer class="text-center text-muted py-4 border-top mt-4">
  <small>© <?= date('Y') ?> eCommerce Agmstwn</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
