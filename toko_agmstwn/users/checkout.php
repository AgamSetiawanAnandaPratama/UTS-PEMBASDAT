<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// ambil data keranjang
$result = mysqli_query($conn, "
    SELECT k.produk_id, k.jumlah, p.nama, p.harga, p.stok
    FROM keranjang k
    JOIN produk p ON k.produk_id = p.id
    WHERE k.user_id = '$user_id'
");

$keranjang = [];
$total = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $keranjang[] = $row;
    $total += $row['harga'] * $row['jumlah'];
}

// jika keranjang kosong
if (empty($keranjang)) {
    echo "<div class='text-center mt-5'>
            <p class='text-muted'>Keranjang belanja kosong.</p>
            <a href='index.php' class='btn btn-primary mt-3'>Belanja Sekarang</a>
          </div>";
    exit;
}

// checkout
if (isset($_POST['checkout'])) {

    // cek stok setiap produk
    foreach ($keranjang as $item) {
        if ($item['jumlah'] > $item['stok']) {
            echo "<script>
                    alert('Stok untuk produk {$item['nama']} tidak mencukupi!');
                    window.location='keranjang.php';
                  </script>";
            exit;
        }
    }

// buat id grup unik untuk transaksi ini
$transaksi_group = date('ymd') . rand(1, 999);

foreach ($keranjang as $item) {
    $subtotal = $item['harga'] * $item['jumlah'];

    mysqli_query($conn, "
        INSERT INTO transaksi 
        (transaksi_group, user_id, produk_id, jumlah, total, status, tanggal)
        VALUES
        ('$transaksi_group', '$user_id', '{$item['produk_id']}', '{$item['jumlah']}', '$subtotal', 'belum bayar', NOW())
    ");
}

// kosongkan keranjang
mysqli_query($conn, "DELETE FROM keranjang WHERE user_id='$user_id'");

// redirect langsung berdasarkan transaksi_group
header("Location: bayar.php?id=$transaksi_group");
exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout | eCommerce Agmstwn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm p-4">
        <h4 class="text-center text-primary fw-semibold mb-4">Checkout</h4>

        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-primary">
                    <tr>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($keranjang as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nama']) ?></td>
                        <td><?= $item['jumlah'] ?></td>
                        <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th class="text-success">Rp <?= number_format($total, 0, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <form method="POST" class="mt-4 text-center">
            <button type="submit" name="checkout" class="btn btn-primary px-4 py-2 me-2">Bayar Sekarang</button>
            <a href="keranjang.php" class="btn btn-secondary px-4 py-2">Kembali</a>
        </form>
    </div>
</div>

</body>
</html>
