-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 17 Okt 2025 pada 14.47
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toko_agmstwn`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `nama`, `deskripsi`, `harga`, `stok`, `foto`) VALUES
(1, 'AGAM', 'anjayyy', 7000.00, 3, 'IMG_9787.jpeg'),
(2, 'ONE PIECE', 'hehe', 1000.00, 0, 'IMG_9445.jpeg'),
(3, 'IRVAN', 'maaf irvan', 2000.00, 2, 'produk_1760592815.jpeg'),
(8, 'VESPA', 'ihirrrr', 100000.00, 5, 'produk_1760687755.jpeg'),
(9, 'SOSIS EDIT', 'test edit', 1000.00, 100, 'produk_1760692899.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `transaksi_id` int(11) NOT NULL,
  `transaksi_group` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `status` enum('belum bayar','diproses','dikirim','selesai') DEFAULT 'belum bayar',
  `tanggal` datetime DEFAULT current_timestamp(),
  `bukti` varchar(255) DEFAULT NULL,
  `produk_id` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`transaksi_id`, `transaksi_group`, `user_id`, `total`, `status`, `tanggal`, `bukti`, `produk_id`, `jumlah`) VALUES
(43, 1760646594, 2, 7000.00, 'dikirim', '2025-10-17 03:29:54', 'bukti_1760646594.jpeg', 1, 1),
(44, 1760646594, 2, 1000.00, 'dikirim', '2025-10-17 03:29:54', 'bukti_1760646594.jpeg', 2, 1),
(45, 1760646594, 2, 2000.00, 'dikirim', '2025-10-17 03:29:54', 'bukti_1760646594.jpeg', 3, 1),
(58, 1760654936, 2, 7000.00, 'selesai', '2025-10-17 05:48:56', 'bukti_1760654936.png', 1, 1),
(59, 1760654936, 2, 1000.00, 'selesai', '2025-10-17 05:48:56', 'bukti_1760654936.png', 2, 1),
(60, 1760654936, 2, 2000.00, 'selesai', '2025-10-17 05:48:56', 'bukti_1760654936.png', 3, 1),
(67, 1760657188, 3, 1000.00, 'belum bayar', '2025-10-17 06:26:28', NULL, 2, 1),
(73, 1798, 2, 1000.00, 'belum bayar', '2025-10-17 14:19:50', NULL, 2, 1),
(74, 1798, 2, 2000.00, 'belum bayar', '2025-10-17 14:19:50', NULL, 3, 1),
(75, 251017408, 2, 1000.00, 'belum bayar', '2025-10-17 14:32:06', NULL, 2, 1),
(76, 251017408, 2, 4000.00, 'belum bayar', '2025-10-17 14:32:06', NULL, 3, 2),
(83, 251017573, 2, 1000.00, 'selesai', '2025-10-17 16:17:21', 'bukti_251017573.png', 2, 1),
(84, 251017573, 2, 7000.00, 'selesai', '2025-10-17 16:17:21', 'bukti_251017573.png', 1, 1),
(88, 251017282, 2, 200000.00, 'diproses', '2025-10-17 16:26:58', 'bukti_251017282.png', 8, 2),
(89, 251017282, 2, 7000.00, 'diproses', '2025-10-17 16:26:58', 'bukti_251017282.png', 1, 1),
(90, 251017391, 2, 100000.00, 'belum bayar', '2025-10-17 16:28:35', NULL, 8, 1),
(91, 25101751, 3, 21000.00, 'belum bayar', '2025-10-17 19:45:19', NULL, 1, 3),
(92, 25101751, 3, 100000.00, 'belum bayar', '2025-10-17 19:45:19', NULL, 8, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama`, `email`, `alamat`, `role`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'Administrator', 'admin@example.com', 'Lamongan', 'admin'),
(2, 'agam', '6ad14ba9986e3615423dfca256d04e3f', 'AGAM SETIAWAN', 'agam@example.com', 'Lamongan', 'user'),
(3, 'nali', 'a6ebd1230e2c150bfe6b99faf9fbc9b5', 'NALI SISWANTO', 'nali@gmail.com', 'Lamongan', 'user'),
(13, 'test1', '$2y$10$qe0S.q8/v/./AiQnyy7mEe6fyktReSXVXP1vptCa/3D7Vq8tDragO', 'TEST 1', 'test1@example.com', 'lamongan', 'user');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`transaksi_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_produk` (`produk_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `transaksi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `keranjang_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`);

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`),
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
