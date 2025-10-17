<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'];
$user_id = $_SESSION['user']['id'];

// ambil data produk
$produk = mysqli_query($conn, "SELECT * FROM produk WHERE id='$id'");
$p = mysqli_fetch_assoc($produk);

if (!$p) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// jika tombol "Tambah ke Keranjang" ditekan
if (isset($_POST['beli'])) {
    $jumlah = (int) $_POST['jumlah'];
    $stok_tersedia = (int) $p['stok'];

    // cek apakah produk sudah ada di keranjang
    $cek = mysqli_query($conn, "SELECT jumlah FROM keranjang WHERE user_id='$user_id' AND produk_id='$id'");
    $dataKeranjang = mysqli_fetch_assoc($cek);
    $jumlah_di_keranjang = $dataKeranjang ? (int)$dataKeranjang['jumlah'] : 0;
    $total_jumlah = $jumlah_di_keranjang + $jumlah;

    // validasi stok
    if ($total_jumlah > $stok_tersedia) {
        echo "<script>
                alert('Jumlah total di keranjang melebihi stok produk! Stok tersedia hanya $stok_tersedia.');
                window.location='detail.php?id=$id';
              </script>";
        exit;
    }

    // tambah / update keranjang
    if ($dataKeranjang) {
        mysqli_query($conn, "UPDATE keranjang 
                             SET jumlah = jumlah + $jumlah 
                             WHERE user_id='$user_id' AND produk_id='$id'");
    } else {
        mysqli_query($conn, "INSERT INTO keranjang (user_id, produk_id, jumlah) 
                             VALUES ('$user_id', '$id', '$jumlah')");
    }

    echo "<script>alert('Produk berhasil ditambahkan ke keranjang!'); window.location='keranjang.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Produk | eCommerce Agmstwn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-lg border-0 rounded-4 p-4 mx-auto" style="max-width: 700px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary mb-3"><?= htmlspecialchars($p['nama']) ?></h3>
            <img src="../assets/img/<?= htmlspecialchars($p['foto']) ?>" 
                 alt="<?= htmlspecialchars($p['nama']) ?>" 
                 class="img-fluid rounded-4 shadow-sm mb-3" 
                 style="max-height: 300px; object-fit: cover;">
        </div>

        <div class="mb-3 text-center">
            <p class="text-muted product-description"><?= nl2br(htmlspecialchars($p['deskripsi'])) ?></p>
        </div>


        <div class="mb-4 text-center">
            <h5 class="text-success fw-semibold mb-1">
                Rp <?= number_format($p['harga'], 0, ',', '.') ?>
            </h5>
            <p class="text-secondary">Stok tersedia: <strong><?= $p['stok'] ?></strong></p>
        </div>

        <form method="POST" class="text-center">
            <div class="mb-3">
                <label for="jumlah" class="form-label fw-semibold">Jumlah</label>
                <input type="number" 
                       name="jumlah" 
                       id="jumlah" 
                       value="1" 
                       min="1" 
                       max="<?= $p['stok'] ?>" 
                       class="form-control text-center d-inline-block" 
                       style="width: 120px;">
            </div>
            <button type="submit" name="beli" class="btn btn-primary px-4 py-2 me-2">
                🛒 Tambah ke Keranjang
            </button>
            <a href="index.php" class="btn btn-outline-secondary px-4 py-2">Kembali</a>
        </form>
    </div>
</div>
</body>
</html>