<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// delete item dari keranjang
if (isset($_GET['delete'])) {
    $produk_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM keranjang WHERE user_id='$user_id' AND produk_id='$produk_id'");
    header("Location: keranjang.php");
    exit;
}

// update jumlah item
if (isset($_POST['update'])) {
    foreach ($_POST['jumlah'] as $produk_id => $jumlah) {
        $jumlah = (int)$jumlah;
        $stokData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stok FROM produk WHERE id='$produk_id'"));
        $stok = $stokData ? (int)$stokData['stok'] : 0;

        if ($jumlah <= 0) {
            mysqli_query($conn, "DELETE FROM keranjang WHERE user_id='$user_id' AND produk_id='$produk_id'");
        } elseif ($jumlah > $stok) {
            mysqli_query($conn, "UPDATE keranjang SET jumlah='$stok' WHERE user_id='$user_id' AND produk_id='$produk_id'");
        } else {
            mysqli_query($conn, "UPDATE keranjang SET jumlah='$jumlah' WHERE user_id='$user_id' AND produk_id='$produk_id'");
        }
    }
    header("Location: keranjang.php");
    exit;
}

// ambil isi keranjang
$result = mysqli_query($conn, "
    SELECT k.produk_id, k.jumlah, p.nama, p.harga, p.stok 
    FROM keranjang k
    JOIN produk p ON k.produk_id = p.id
    WHERE k.user_id = '$user_id'
");

$keranjang = [];
while ($row = mysqli_fetch_assoc($result)) {
    $keranjang[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja | eCommerce Agmstwn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm p-4">
        <h4 class="text-center text-primary fw-semibold mb-4">🛒 Keranjang Belanja</h4>

        <?php if (empty($keranjang)): ?>
            <div class="text-center my-5">
                <p class="text-muted mb-3">Keranjang belanja Anda kosong.</p>
                <a href="index.php" class="btn btn-primary">Kembali Belanja</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead class="table-primary">
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Jumlah</th>
                                <th>Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            foreach ($keranjang as $item):
                                $subtotal = $item['harga'] * $item['jumlah'];
                                $total += $subtotal;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nama']) ?></td>
                                <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                <td><?= $item['stok'] ?></td>
                                <td style="width:100px;">
                                    <input 
                                        type="number" 
                                        name="jumlah[<?= $item['produk_id'] ?>]" 
                                        value="<?= $item['jumlah'] ?>" 
                                        min="1" 
                                        max="<?= $item['stok'] ?>" 
                                        class="form-control text-center">
                                </td>
                                <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                <td>
                                    <a href="keranjang.php?delete=<?= $item['produk_id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Hapus produk ini dari keranjang?')">
                                       Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Total</th>
                                <th colspan="2" class="text-start text-success">Rp <?= number_format($total, 0, ',', '.') ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-secondary">Kembali Belanja</a>
                    <div>
                        <button type="submit" name="update" class="btn btn-warning me-2">Update Jumlah</button>
                        <a href="checkout.php" class="btn btn-primary">Lanjut ke Pembayaran</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>

    </div>
</div>
</body>
</html>