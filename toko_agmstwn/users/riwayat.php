<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// delete
if (isset($_GET['hapus'])) {
    $group = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM transaksi WHERE transaksi_group='$group' AND user_id='$user_id' AND status='belum bayar'");
    header("Location: riwayat.php");
    exit;
}

// selesai
if (isset($_GET['selesai'])) {
    $group = $_GET['selesai'];
    mysqli_query($conn, "UPDATE transaksi SET status='selesai' WHERE transaksi_group='$group' AND user_id='$user_id' AND status='dikirim'");
    header("Location: riwayat.php");
    exit;
}

// filter and search
$filter_status = $_GET['status'] ?? 'semua';
$cari = trim($_GET['cari'] ?? '');

$where = "WHERE t.user_id='$user_id'";
if ($filter_status && $filter_status != 'semua') {
    $where .= " AND t.status='$filter_status'";
}
if ($cari) {
    $where .= " AND p.nama LIKE '%" . mysqli_real_escape_string($conn, $cari) . "%'";
}

// mengambil data transaksi per group
$query = "
    SELECT t.transaksi_group, MAX(t.tanggal) as tanggal, t.status
    FROM transaksi t
    JOIN produk p ON t.produk_id = p.id
    $where
    GROUP BY t.transaksi_group, t.status
    ORDER BY tanggal DESC
";
$transaksi = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pesanan | eCommerce Agmstwn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm p-4">
        <h4 class="text-primary fw-semibold mb-4 text-center">Riwayat Pesanan</h4>

        <!-- filter and search -->
        <form method="get" class="row g-3 align-items-center mb-4">
            <div class="col-md-5">
                <input type="text" name="cari" class="form-control" placeholder="Cari nama produk..." value="<?= htmlspecialchars($cari) ?>">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="semua" <?= $filter_status == 'semua' ? 'selected' : '' ?>>Semua Status</option>
                    <option value="belum bayar" <?= $filter_status == 'belum bayar' ? 'selected' : '' ?>>Belum Bayar</option>
                    <option value="diproses" <?= $filter_status == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                    <option value="dikirim" <?= $filter_status == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                    <option value="selesai" <?= $filter_status == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <button type="submit" class="btn btn-primary">Filter</button>
                <button type="button" onclick="window.print()" class="btn btn-outline-secondary">Cetak</button>
            </div>
        </form>

        <!-- tabel -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-center">
                <thead class="table-primary">
                    <tr>
                        <th>Transaksi</th>
                        <th>Produk Dibeli</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($transaksi) > 0): ?>
                        <?php while ($t = mysqli_fetch_assoc($transaksi)):
                            // ambil semua produk di grup transaksi ini
                            $produkList = [];
                            $detail = mysqli_query($conn, "
                                SELECT p.nama, p.harga, t.jumlah
                                FROM transaksi t
                                JOIN produk p ON t.produk_id = p.id
                                WHERE t.transaksi_group = '{$t['transaksi_group']}'
                            ");
                            $totalHarga = 0;
                            while ($p = mysqli_fetch_assoc($detail)) {
                                $produkList[] = $p;
                                $totalHarga += $p['harga'] * $p['jumlah'];
                            }

                            // set badge status
                            $statusClass = [
                                'belum bayar' => 'bg-warning text-dark',
                                'diproses' => 'bg-info text-dark',
                                'dikirim' => 'bg-primary text-white',
                                'selesai' => 'bg-success text-white'
                            ][$t['status']] ?? 'bg-secondary';
                        ?>
                        <tr>
                            <td class="fw-semibold">#<?= $t['transaksi_group'] ?></td>
                            <td>
                                <?php foreach($produkList as $p): ?>
                                    <div><?= htmlspecialchars($p['nama']) ?></div>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php foreach($produkList as $p): ?>
                                    <div><?= $p['jumlah'] ?></div>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php foreach($produkList as $p): ?>
                                    <div>Rp <?= number_format($p['harga'],0,',','.') ?></div>
                                <?php endforeach; ?>
                            </td>
                            <td>Rp <?= number_format($totalHarga,0,',','.') ?></td>
                            <td><span class="badge <?= $statusClass ?>"><?= ucfirst($t['status']) ?></span></td>
                            <td><?= $t['tanggal'] ?></td>
                            <td>
                                <?php if($t['status']=='belum bayar'): ?>
                                    <a href="bayar.php?id=<?= $t['transaksi_group'] ?>" class="btn btn-sm btn-primary">Bayar</a>
                                    <a href="?hapus=<?= $t['transaksi_group'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus transaksi ini?')">Hapus</a>
                                <?php elseif($t['status']=='dikirim'): ?>
                                    <a href="?selesai=<?= $t['transaksi_group'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Apakah pesanan sudah diterima dan ingin diselesaikan?')">Selesai</a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr><td colspan="8" class="text-muted">Tidak ada transaksi ditemukan</td></tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-outline-primary px-4">Kembali ke Beranda</a>
        </div>
    </div>
</div>
</body>
</html>