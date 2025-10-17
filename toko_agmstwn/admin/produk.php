<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// tambah produk
if (isset($_POST['tambah'])) {
    $nama = strtoupper(mysqli_real_escape_string($conn, trim($_POST['nama'])));
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $harga = intval($_POST['harga']);
    $stok = intval($_POST['stok']);

    if ($stok < 0 || $harga < 0) {
        echo "<script>alert('❌ Stok dan harga tidak boleh bernilai negatif!'); window.location='produk.php';</script>";
        exit;
    }

    $foto = $_FILES['foto']['name'];
    $tmp = $_FILES['foto']['tmp_name'];

    if ($foto) {
        $ext = pathinfo($foto, PATHINFO_EXTENSION);
        $newname = 'produk_' . time() . '.' . $ext;
        move_uploaded_file($tmp, '../assets/img/' . $newname);
    } else {
        $newname = '';
    }

    mysqli_query($conn, "INSERT INTO produk (nama, deskripsi, harga, stok, foto) 
                         VALUES ('$nama', '$deskripsi', '$harga', '$stok', '$newname')");
    header("Location: produk.php");
    exit;
}

// hapus produk
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id='$id'"));
    if ($p && $p['foto'] && file_exists('../assets/img/' . $p['foto'])) {
        unlink('../assets/img/' . $p['foto']);
    }
    mysqli_query($conn, "DELETE FROM produk WHERE id='$id'");
    header("Location: produk.php");
    exit;
}

// search
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
if ($cari != '') {
    $produk = mysqli_query($conn, "SELECT * FROM produk WHERE nama LIKE '%$cari%' ORDER BY id ASC");
} else {
    $produk = mysqli_query($conn, "SELECT * FROM produk ORDER BY id ASC");
}

// nomor urut mulai dari 1
$no = 1;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen Produk | eCommerce Agmstwn</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body style="font-family:'Poppins', sans-serif; background:#f7f9ff;">

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
        <li class="nav-item"><a class="nav-link text-white fw-semibold active" href="produk.php"> Produk</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="transaksi.php"> Transaksi</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="laporan.php"> Laporan</a></li>
        <li class="nav-item ms-2"><a class="btn btn-light btn-sm text-danger fw-semibold px-3" href="../auth/logout.php"> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-2 px-3">
  <div class="row g-4 mt-4">
    <!-- tambah produk -->
    <div class="col-md-4">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white"><h5 class="mb-0">Tambah Produk</h5></div>
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data">
            <div class="mb-3"><label class="form-label fw-semibold">Nama Produk</label>
              <input type="text" name="nama" class="form-control" required></div>
            <div class="mb-3"><label class="form-label fw-semibold">Deskripsi</label>
              <textarea name="deskripsi" class="form-control" rows="3" required></textarea></div>
            <div class="mb-3"><label class="form-label fw-semibold">Harga</label>
              <input type="number" name="harga" class="form-control" min="0" required></div>
            <div class="mb-3"><label class="form-label fw-semibold">Stok</label>
              <input type="number" name="stok" class="form-control" min="0" required></div>
            <div class="mb-3"><label class="form-label fw-semibold">Foto Produk</label>
              <input type="file" name="foto" class="form-control" accept="image/*" required></div>
            <div class="d-grid"><button type="submit" name="tambah" class="btn btn-primary">+ Tambah Produk</button></div>
          </form>
        </div>
      </div>
    </div>

    <!-- daftar produk -->
    <div class="col-md-8">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Daftar Produk</h5>
          <form method="GET" class="d-flex">
            <input type="text" name="cari" class="form-control form-control-sm me-2" placeholder="Cari produk..." value="<?= htmlspecialchars($cari) ?>">
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
                  <th>Harga</th>
                  <th>Stok</th>
                  <th>Foto</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
              <?php if (mysqli_num_rows($produk) > 0): ?>
                <?php while ($p = mysqli_fetch_assoc($produk)): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= strtoupper(htmlspecialchars($p['nama'])) ?></td>
                  <td>Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                  <td><?= $p['stok'] ?></td>
                  <td><img src="../assets/img/<?= htmlspecialchars($p['foto']) ?>" width="60" class="rounded"></td>
                  <td>
                    <a href="produk_edit.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="?hapus=<?= $p['id'] ?>" onclick="return confirm('Yakin ingin hapus produk ini?');" class="btn btn-danger btn-sm">Hapus</a>
                  </td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="6" class="text-muted">Tidak ada produk ditemukan</td></tr>
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