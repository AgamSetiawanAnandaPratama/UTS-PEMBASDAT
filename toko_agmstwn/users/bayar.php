<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// ambil transaksi_group dari URL
$transaksi_group = $_GET['id'] ?? null;
if (!$transaksi_group) {
    header("Location: riwayat.php");
    exit;
}

// ambil total dari semua transaksi dalam grup
$groupTotal = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(total) AS total_bayar 
    FROM transaksi 
    WHERE transaksi_group='$transaksi_group' 
      AND user_id='$user_id'
"));

if (!$groupTotal || $groupTotal['total_bayar'] === null) {
    header("Location: riwayat.php");
    exit;
}

$totalBayar = $groupTotal['total_bayar'];

// ambil semua transaksi dalam grup (untuk update stok & bukti)
$detail = mysqli_query($conn, "
    SELECT * FROM transaksi 
    WHERE transaksi_group='$transaksi_group' 
      AND user_id='$user_id'
");

$produkList = [];
while ($d = mysqli_fetch_assoc($detail)) {
    $produkList[] = $d;
}

if (isset($_POST['upload'])) {
    if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] != 0) {
        $_SESSION['msg'] = "Gagal upload file.";
        header("Location: bayar.php?id=$transaksi_group");
        exit;
    }

    $filename = $_FILES['bukti']['name'];
    $tmp = $_FILES['bukti']['tmp_name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

    if (!in_array($ext, $allowed)) {
        $_SESSION['msg'] = "Format file tidak diperbolehkan. Hanya JPG, PNG, PDF.";
        header("Location: bayar.php?id=$transaksi_group");
        exit;
    }

    if ($_FILES['bukti']['size'] > 2 * 1024 * 1024) {
        $_SESSION['msg'] = "File terlalu besar. Maksimal 2MB.";
        header("Location: bayar.php?id=$transaksi_group");
        exit;
    }

    $newname = 'bukti_' . $transaksi_group . '.' . $ext;
    if (!move_uploaded_file($tmp, '../assets/img/' . $newname)) {
        $_SESSION['msg'] = "Gagal menyimpan file.";
        header("Location: bayar.php?id=$transaksi_group");
        exit;
    }

    // cek stok semua produk
    $stokCukup = true;
    foreach ($produkList as $d) {
        $produk_id = intval($d['produk_id']);
        $jumlah = intval($d['jumlah']);
        $produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stok FROM produk WHERE id='$produk_id'"));
        if ($produk['stok'] < $jumlah) {
            $stokCukup = false;
            break;
        }
    }

    if ($stokCukup) {
        // kurangi stok produk
        foreach ($produkList as $d) {
            $produk_id = intval($d['produk_id']);
            $jumlah = intval($d['jumlah']);
            mysqli_query($conn, "UPDATE produk SET stok = stok - $jumlah WHERE id = $produk_id");
        }

        // update semua transaksi dalam grup
        mysqli_query($conn, "
            UPDATE transaksi 
            SET bukti='$newname', status='diproses' 
            WHERE transaksi_group='$transaksi_group'
        ");

        $_SESSION['msg'] = "Pembayaran berhasil. Stok telah dikurangi.";
    } else {
        $_SESSION['msg'] = "Pembayaran gagal. Stok produk tidak cukup!";
        unlink('../assets/img/' . $newname);
    }

    header("Location: bayar.php?id=$transaksi_group");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran | eCommerce Agmstwn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm p-4 mx-auto" style="max-width:600px;">
        <h4 class="text-center text-primary fw-semibold mb-4">Pembayaran</h4>

        <?php if(isset($_SESSION['msg'])): ?>
            <script>
                var msg = "<?= addslashes($_SESSION['msg']); ?>";
                alert(msg);
                <?php if (strpos($_SESSION['msg'], 'berhasil') !== false): ?>
                    window.location.href = 'riwayat.php';
                <?php endif; ?>
            </script>
        <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

        <div class="text-center mb-3">
            <p class="mb-1 text-muted">ID Transaksi:</p>
            <h5 class="fw-bold text-dark">#<?= htmlspecialchars($transaksi_group) ?></h5>
        </div>

        <div class="bg-light border rounded p-3 mb-4">
            <p class="mb-1">Total Bayar:</p>
            <h4 class="text-success fw-bold">Rp <?= number_format($totalBayar, 0, ',', '.') ?></h4>
        </div>

        <div class="alert alert-info">
            <p class="mb-0">Silakan transfer ke rekening berikut:</p>
            <strong>Bank BRI — 834301008141500 a.n. PT eCommerce Agmstwn</strong>
        </div>

        <form method="POST" enctype="multipart/form-data" class="mt-4">
            <div class="mb-3">
                <label for="bukti" class="form-label fw-semibold">Upload Bukti Pembayaran:</label>
                <input type="file" name="bukti" id="bukti" class="form-control" required>
            </div>

            <div class="d-flex justify-content-center gap-2 mt-4">
                <a href="riwayat.php" class="btn btn-secondary px-4">Kembali</a>
                <button type="submit" name="upload" class="btn btn-primary px-4">Upload Bukti</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
