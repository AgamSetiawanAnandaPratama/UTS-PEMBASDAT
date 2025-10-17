<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// konfirmasi transaksi
if (isset($_GET['konfirmasi'])) {
    $id = intval($_GET['konfirmasi']);
    $trans = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM transaksi WHERE transaksi_id='$id'"));

    if ($trans) {
        if ($trans['status'] == 'diproses') {
            mysqli_query($conn, "UPDATE transaksi SET status='dikirim' WHERE transaksi_group='{$trans['transaksi_group']}'");

            // ambil semua produk di grup transaksi untuk update stok
            $produkList = mysqli_query($conn, "
                SELECT produk_id, jumlah FROM transaksi WHERE transaksi_group='{$trans['transaksi_group']}'
            ");
            while ($p = mysqli_fetch_assoc($produkList)) {
                mysqli_query($conn, "UPDATE produk SET stok = GREATEST(stok - {$p['jumlah']},0) WHERE id={$p['produk_id']}");
            }

        } elseif ($trans['status'] == 'dikirim') {
            mysqli_query($conn, "UPDATE transaksi SET status='selesai' WHERE transaksi_group='{$trans['transaksi_group']}'");
        }
    }
    header("Location: transaksi.php");
    exit;
}

// hapus dan kembalikan stok
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);

    // ambil transaksi untuk dapatkan transaksi_group
    $trans = mysqli_fetch_assoc(mysqli_query($conn, "SELECT transaksi_group FROM transaksi WHERE transaksi_id='$id'"));
    
    if ($trans) {
        $transaksi_group = $trans['transaksi_group'];

        // ambil semua produk di transaksi group
        $produkList = mysqli_query($conn, "SELECT produk_id, jumlah FROM transaksi WHERE transaksi_group='$transaksi_group'");
        while ($p = mysqli_fetch_assoc($produkList)) {
            $produk_id = intval($p['produk_id']);
            $jumlah = intval($p['jumlah']);
            if ($jumlah > 0) { // pastikan jumlah valid
                mysqli_query($conn, "UPDATE produk SET stok = stok + $jumlah WHERE id=$produk_id");
            }
        }

        // hapus seluruh transaksi dalam grup
        mysqli_query($conn, "DELETE FROM transaksi WHERE transaksi_group='$transaksi_group'");
    }

    header("Location: transaksi.php");
    exit;
}

// filter and search
$tgl_mulai = $_GET['tgl_mulai'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$nama_user = $_GET['nama_user'] ?? '';

$where = [];
if ($tgl_mulai && $tgl_akhir) {
    $where[] = "t.tanggal BETWEEN '$tgl_mulai 00:00:00' AND '$tgl_akhir 23:59:59'";
}
if ($nama_user) {
    $where[] = "u.nama LIKE '%" . mysqli_real_escape_string($conn, $nama_user) . "%'";
}
$where_sql = $where ? "WHERE " . implode(' AND ', $where) : "";

// ambil transaksi beserta nama user
$transaksi = mysqli_query($conn, "
    SELECT t.*, u.nama 
    FROM transaksi t 
    JOIN users u ON t.user_id = u.id 
    $where_sql 
    GROUP BY t.transaksi_group
    ORDER BY t.tanggal DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Transaksi | eCommerce Agmstwn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

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
        <li class="nav-item"><a class="nav-link text-white" href="produk.php"> Produk</a></li>
        <li class="nav-item"><a class="nav-link text-white fw-semibold active" href="transaksi.php"> Transaksi</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="laporan.php"> Laporan</a></li>
        <li class="nav-item ms-2">
          <a class="btn btn-light btn-sm text-danger fw-semibold px-3" href="../auth/logout.php"> Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-2 px-3">
    <div class="row g-4 mt-4 justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Transaksi</h5>
                    <form method="get" class="d-flex align-items-center">
                        <input type="text" name="nama_user" class="form-control form-control-sm me-2"
                               placeholder="Nama Pembeli" value="<?= htmlspecialchars($nama_user) ?>">
                        <button type="submit" class="btn btn-light btn-sm me-2">Filter</button>
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="window.print()">Cetak</button>
                    </form>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-primary">
                                <tr>
                                    <th>ID</th>
                                    <th>Pembeli</th>
                                    <th>Produk Dibeli</th>
                                    <th>Jumlah</th>
                                    <th>Harga Satuan</th>
                                    <th>Total Harga</th>
                                    <th>Status</th>
                                    <th>Bukti</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($transaksi) > 0): ?>
                                    <?php while ($t = mysqli_fetch_assoc($transaksi)):
                                        $status = $t['status'];
                                        switch($status){
                                            case 'belum bayar': $badgeClass='bg-secondary text-dark'; break;
                                            case 'diproses': $badgeClass='bg-warning text-dark'; break;
                                            case 'dikirim': $badgeClass='bg-info text-dark'; break;
                                            case 'selesai': $badgeClass='bg-success'; break;
                                            default: $badgeClass='bg-secondary';
                                        }

                                        // ambil semua produk di grup transaksi
                                        $produkList = mysqli_query($conn, "
                                            SELECT p.nama, p.harga, t.jumlah
                                            FROM transaksi t
                                            JOIN produk p ON t.produk_id = p.id
                                            WHERE t.transaksi_group='{$t['transaksi_group']}' AND t.jumlah > 0
                                        ");

                                        $totalHarga = 0;
                                        $produkArray = [];
                                        while ($p = mysqli_fetch_assoc($produkList)) {
                                            $produkArray[] = $p;
                                            $totalHarga += $p['harga'] * $p['jumlah'];
                                        }
                                        if (count($produkArray) == 0) continue;
                                    ?>
                                    <tr>
                                        <td><?= $t['transaksi_group'] ?></td>
                                        <td><?= strtoupper(htmlspecialchars($t['nama'])) ?></td>
                                        <td>
                                            <?php foreach($produkArray as $p): ?>
                                                <div><?= htmlspecialchars($p['nama']) ?></div>
                                            <?php endforeach; ?>
                                        </td>
                                        <td>
                                            <?php foreach($produkArray as $p): ?>
                                                <div><?= $p['jumlah'] ?></div>
                                            <?php endforeach; ?>
                                        </td>
                                        <td>
                                            <?php foreach($produkArray as $p): ?>
                                                <div>Rp <?= number_format($p['harga'],0,',','.') ?></div>
                                            <?php endforeach; ?>
                                        </td>
                                        <td>Rp <?= number_format($totalHarga,0,',','.') ?></td>
                                        <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span></td>
                                        <td>
                                            <?php if ($t['bukti']): ?>
                                                <img src="../assets/img/<?= htmlspecialchars($t['bukti']) ?>" width="70" class="rounded shadow-sm">
                                            <?php else: ?>
                                                <small class="text-muted">Belum ada</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($status === 'belum bayar'): ?>
                                                <a>-</a>
                                            <?php elseif ($status === 'diproses'): ?>
                                                <div class="d-flex flex-column gap-1">
                                                    <form method="get" onsubmit="return confirm('Konfirmasi transaksi ini?');">
                                                        <input type="hidden" name="konfirmasi" value="<?= $t['transaksi_id'] ?>">
                                                        <button type="submit" class="btn btn-success btn-sm">Konfirmasi</button>
                                                    </form>
                                                    <form method="get" onsubmit="return confirm('Hapus transaksi ini dan kembalikan stok?');">
                                                        <input type="hidden" name="hapus" value="<?= $t['transaksi_id'] ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                                    </form>
                                                </div>
                                            <?php elseif ($status === 'dikirim'): ?>
                                                <span class="btn btn-secondary btn-sm disabled">Sedang dikirim</span>
                                            <?php else: ?>
                                                <span class="text-success fw-semibold">Selesai ✅</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="9" class="text-muted">Tidak ada transaksi ditemukan</td></tr>
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