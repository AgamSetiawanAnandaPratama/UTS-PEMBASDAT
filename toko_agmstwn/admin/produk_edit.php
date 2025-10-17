<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id = intval($_GET['id']);
$p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM produk WHERE id='$id'"));

if (!$p) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location='produk.php';</script>";
    exit;
}

if (isset($_POST['update'])) {
    $nama = strtoupper(trim($_POST['nama']));
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = intval($_POST['harga']);
    $stok = intval($_POST['stok']);

    if ($stok < 0 || $harga < 0) {
        echo "<script>alert('❌ Stok dan harga tidak boleh bernilai negatif!'); window.history.back();</script>";
        exit;
    }

    $foto = $_FILES['foto']['name'];
    if ($foto) {
        $tmp = $_FILES['foto']['tmp_name'];
        $ext = pathinfo($foto, PATHINFO_EXTENSION);
        $newname = 'produk_' . time() . '.' . $ext;

        move_uploaded_file($tmp, '../assets/img/' . $newname);

        if (!empty($p['foto']) && file_exists('../assets/img/' . $p['foto'])) {
            unlink('../assets/img/' . $p['foto']);
        }

        $query = "UPDATE produk 
                  SET nama='$nama', deskripsi='$deskripsi', harga='$harga', stok='$stok', foto='$newname' 
                  WHERE id='$id'";
    } else {
        $query = "UPDATE produk 
                  SET nama='$nama', deskripsi='$deskripsi', harga='$harga', stok='$stok' 
                  WHERE id='$id'";
    }

    mysqli_query($conn, $query);
    header("Location: produk.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="bg-light">

<!-- NAVBAR -->
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
        <li class="nav-item"><a class="nav-link text-white" href="pembeli.php">Pembeli</a></li>
        <li class="nav-item"><a class="nav-link text-white fw-semibold active" href="produk.php"> Produk</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="transaksi.php"> Transaksi</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="laporan.php"> Laporan</a></li>
        <li class="nav-item ms-2">
          <a class="btn btn-light btn-sm text-danger fw-semibold px-3" href="../auth/logout.php"> Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- edit produk -->
<div class="container my-5">
    <div class="card shadow p-4 col-lg-6 mx-auto">
        <h3 class="fw-bold text-center text-primary mb-4">Edit Produk</h3>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Produk</label>
                <input type="text" name="nama" class="form-control" value="<?= strtoupper(htmlspecialchars($p['nama'])) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="3" required><?= htmlspecialchars($p['deskripsi']) ?></textarea>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Harga</label>
                    <input type="number" name="harga" class="form-control" value="<?= $p['harga'] ?>" min="0" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Stok</label>
                    <input type="number" name="stok" class="form-control" value="<?= $p['stok'] ?>" min="0" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Foto Produk</label>
                <input type="file" name="foto" class="form-control" accept="image/*">
                <?php if ($p['foto']): ?>
                    <div class="mt-3 text-center">
                        <img src="../assets/img/<?= htmlspecialchars($p['foto']) ?>" width="120" class="rounded shadow-sm border">
                    </div>
                <?php endif; ?>
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