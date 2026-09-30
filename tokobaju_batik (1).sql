-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 29, 2026 at 11:33 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tokobaju_batik`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_detail`
--

CREATE TABLE `tb_detail` (
  `id_detail` int NOT NULL,
  `id_transaksi` int NOT NULL,
  `id_produk` int NOT NULL,
  `jumlah` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tb_detail`
--

INSERT INTO `tb_detail` (`id_detail`, `id_transaksi`, `id_produk`, `jumlah`) VALUES
(28, 25, 28, 1),
(29, 26, 38, 3),
(31, 28, 39, 2),
(32, 28, 33, 1),
(33, 29, 23, 1),
(34, 29, 35, 1),
(35, 30, 33, 1),
(36, 31, 37, 1),
(37, 32, 34, 1),
(38, 33, 38, 1),
(39, 34, 35, 1),
(40, 35, 36, 1),
(41, 36, 28, 1),
(42, 37, 29, 1),
(43, 38, 34, 1),
(44, 39, 38, 1),
(45, 40, 40, 1),
(46, 40, 26, 1),
(47, 40, 37, 1),
(48, 40, 34, 1),
(49, 41, 40, 1),
(50, 42, 35, 1),
(51, 43, 28, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tb_kategori`
--

CREATE TABLE `tb_kategori` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tb_kategori`
--

INSERT INTO `tb_kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, ' baju batik cewe/cowo'),
(2, ' tunik batik'),
(3, 'baju batik anak'),
(4, 'baju batik couple keluarga'),
(5, 'tas batik'),
(6, 'aksesoris batik');

-- --------------------------------------------------------

--
-- Table structure for table `tb_produk`
--

CREATE TABLE `tb_produk` (
  `id` int NOT NULL,
  `nama` varchar(255) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL,
  `foto` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `id_kategori` int DEFAULT NULL,
  `deskripsi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tb_produk`
--

INSERT INTO `tb_produk` (`id`, `nama`, `harga`, `stok`, `foto`, `id_kategori`, `deskripsi`) VALUES
(23, 'Batik Wanita dengan Ikat Pinggang (Navy & Putih)', 300000, 150, 'WhatsApp Image 2026-09-11 at 11.44.01.jpeg', 1, 'Batik wanita dengan kombinasi warna biru dongker (navy) dan putih dengan desain burung serta flora. Dilengkapi dengan sabuk (belt) kain yang bisa diikat di pinggang untuk memberikan siluet tubuh yang lebih rapi dan elegan.'),
(24, 'Kemeja Batik Pria Lengan Panjang Motif Abstrak (Abu-Abu)', 320000, 20, 'WhatsApp Image 2026-09-11 at 11.44.01 (1).jpeg', 1, 'Kemeja batik pria lengan panjang dengan motif abstrak kontemporer bernuansa warna dasar abu-abu dan aksen biru tua. Memberikan kesan maskulin, modern, dan profesional, sangat pas untuk pakaian kerja kantoran maupun acara resmi.'),
(25, 'Tunik Batik Wanita (Sage Green)', 250000, 180, 'WhatsApp Image 2026-09-11 at 11.44.02 (1).jpeg', 2, 'Tunik wanita dengan kerah tinggi gaya Mandarin (Shanghai collar) dan motif batik simetris bernuansa warna hijau salvia (sage green). Nyaman dipakai sebagai luaran untuk tampilan yang anggun, sopan, dan kekinian.'),
(26, 'Tunik Batik Wanita Lengan Puff Elegan (Navy)', 320000, 200, 'WhatsApp Image 2026-09-11 at 11.44.02 (2).jpeg', 2, 'Tunik pendek wanita bermotif batik klasik warna biru dongker (navy) dengan aksen lengan puffy (balon) yang anggun serta detail pita di bagian leher. Memberikan kesan feminin, mewah, dan berkelas untuk acara pesta maupun semi-formal.'),
(27, 'Outer / Cardigan Batik Wanita  (Dusty Pink)', 245000, 250, 'WhatsApp Image 2026-09-11 at 11.44.01 (2).jpeg', 1, 'Pakaian luar (outer) wanita berkerah syal dengan motif batik kombinasi bunga dan burung bangau bernuansa warna dusty pink dan cokelat tua. Memiliki model lengan 3/4 yang elegan, sangat cocok dipadukan dengan inner polos untuk gaya semi formal atau kasual.'),
(28, 'Kemeja Batik Pria Lengan Panjang Motif Klasik  (Hitam & Emas)', 300000, 245, 'WhatsApp Image 2026-09-11 at 11.44.00.jpeg', 1, 'Kemeja batik pria lengan panjang dengan motif klasik elegan berwarna dasar hitam dipadukan corak keemasan/krem. Terbuat dari bahan katun berkualitas yang adem dan nyaman dipakai untuk acara formal, kerja kantoran, maupun kondangan.'),
(29, 'Setelan Baju Anak Motif Batik Etnik (Kuning & Cokelat Tua)', 245000, 150, '1790235591_WhatsApp_Image_2026-09-11_at_11.44.02__3_.jpeg', 3, 'Setelan serasi untuk anak laki-laki dan perempuan dengan kombinasi warna kuning cerah dan cokelat tua bermotif garis serta geometris batik. Memberikan kesan ceria namun tetap mempertahankan unsur tradisional, sangat cocok untuk berbagai acara.'),
(33, 'Setelan Baju Anak Motif Batik (Toska & Kuning)', 200000, 110, '1790332053_WhatsApp_Image_2026-09-11_at_11.44.02__4_.jpeg', 3, 'Setelan pakaian anak laki-laki dan perempuan dengan desain etnik modern berpotongan unik dipadukan dengan kain batik kombinasi warna toska cerah dan kuning motif parang serta bunga. Nyaman, adem, dan stylish untuk dikenakan anak-anak saat acara keluarga, perayaan, atau jalan-jalan.'),
(34, 'Set Busana Keluarga / Family Set Seragam Batik Etnik (Warna Cokelat & Putih)', 500000, 90, '1790332124_WhatsApp_Image_2026-09-11_at_11.44.03.jpeg', 4, 'Koleksi seragam keluarga bernuansa warna bumi (earth tone) yaitu cokelat tanah dan putih gading yang memberikan kesan hangat, elegan, dan kasual. Terdiri dari kemeja pria/anak dengan motif garis abstrak batik, serta tunik/outer panjang wanita bermotif floral batik yang dipadukan dengan celana senada. Sangat nyaman dipakai untuk berbagai acara santai hingga semi-formal bersama keluarga tercinta.'),
(35, 'Set Busana Keluarga / Family Set Seragam Batik Modern (Warna Biru Muda)', 550000, 100, '1790332201_WhatsApp_Image_2026-09-11_at_11.44.03__1_.jpeg', 4, 'Koleksi pakaian seragam keluarga dengan dominasi warna biru muda yang cerah dan segar. Desain memadukan kain polos berkualitas tinggi dengan aksen motif batik pada bagian dada, kerah, dan lengan untuk menciptakan tampilan yang serasi dan harmonis. Sangat cocok dikenakan saat acara keluarga besar, hari raya, atau acara formal lainnya.'),
(36, 'Tote Bag Batik Kanvas Kombinasi Kulit Sintetis', 90000, 60, '1790332336_WhatsApp_Image_2026-09-11_at_11.44.03__2_.jpeg', 5, 'Tas jinjing semi formal dengan desain elegan memadukan kain bermotif batik bunga bernuansa cokelat dan ungu tua/magenta. Dilengkapi dengan tali pegangan (handles) berbahan kulit sintetis cokelat yang kuat dan memberikan kesan mewah. Memiliki kapasitas luas yang pas untuk membawa perlengkapan kerja, laptop tipis, atau dokumen penting.'),
(37, 'Tote Bag Kain Batik Patchwork Aksen Bunga', 85000, 50, '1790332396_WhatsApp_Image_2026-09-11_at_11.44.04.jpeg', 5, 'Tas jinjing (tote bag) kasual yang dibuat dengan teknik patchwork memadukan berbagai motif kain batik klasik berwarna cokelat dan krem. Dipercantik dengan hiasan detail bunga kain dan kancing batok kelapa di bagian depan. Tas ini memiliki ruang yang cukup luas dan tali bahu yang nyaman, sangat cocok digunakan untuk aktivitas sehari-hari, kuliah, kerja, atau jalan-jalan santai dengan sentuhan etnik modern.'),
(38, 'Gantungan Kunci  Pouch Mini Amplop Motif Batik', 15000, 25, '1790332699_WhatsApp_Image_2026-09-11_at_11.44.04__1_.jpeg', 6, 'Aksesoris gantungan kunci unik berbentuk amplop mini yang terbuat dari sisa potongan kain batik aneka motif dan warna. Dilengkapi dengan penutup berperekat, hiasan kancing permata kecil yang elegan, serta cincin gantungan besi yang kuat. Sangat cocok dijadikan sebagai suvenir pernikahan, cinderamata, atau hiasan tas.'),
(40, 'Kipas Tangan Lipat Motif Batik Etnik', 35000, 30, '1790385283_WhatsApp_Image_2026-09-11_at_11.44.04__2_.jpeg', 6, 'Kipas tangan tradisional yang elegan dengan rangka kayu berkualitas dan kain bermotif batik khas Nusantara (perpaduan warna hitam, cokelat, dan krem). Ringan, kokoh, dan fungsional untuk digunakan saat cuaca panas, serta sangat ideal dijadikan sebagai suvenir acara atau aksesoris pelengkap busana etnik.');

-- --------------------------------------------------------

--
-- Table structure for table `tb_transaksi`
--

CREATE TABLE `tb_transaksi` (
  `id_transaksi` int NOT NULL,
  `id_pelanggan` int NOT NULL,
  `tanggal` date NOT NULL,
  `total_harga` int NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `nama_penerima` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat_pengiriman` text,
  `catatan` varchar(255) DEFAULT NULL,
  `total` int NOT NULL DEFAULT '0',
  `status` varchar(20) NOT NULL DEFAULT 'Menunggu',
  `metode_pembayaran` varchar(50) DEFAULT 'COD',
  `kurir` varchar(100) DEFAULT NULL,
  `ongkir` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tb_transaksi`
--

INSERT INTO `tb_transaksi` (`id_transaksi`, `id_pelanggan`, `tanggal`, `total_harga`, `username`, `nama_penerima`, `no_hp`, `alamat_pengiriman`, `catatan`, `total`, `status`, `metode_pembayaran`, `kurir`, `ongkir`) VALUES
(25, 56, '2026-09-25', 340000, 'pelanggan', 'pelanggan', '0821232610745', 'Jl. Melati 2', '', 340000, 'Menunggu', 'Transfer Bank', NULL, 0),
(26, 61, '2026-09-25', 75000, 'lovi', 'lovi', '08193425678', 'Jl. Sitameng 2', 'lempar aja', 75000, 'Menunggu', 'COD', NULL, 0),
(28, 69, '2026-09-26', 295000, 'pia', 'pia', '4536278989', 'jl. bt 2', '', 295000, 'Selesai', 'QRIS', NULL, 0),
(29, 2, '2026-09-26', 1050000, 'ajeng', 'ajeng', '082113121175', 'jl.melati 2', '', 1050000, 'Menunggu', 'Transfer Bank', NULL, 0),
(30, 68, '2026-09-26', 245000, 'queenzi', 'queenzi', '564732819', 'jl. perjuangan 2', '', 245000, 'Menunggu', 'COD', NULL, 0),
(31, 2, '2026-09-26', 85000, 'ajeng', 'ajeng', '082113121175', 'jl.melati 2', '', 85000, 'Menunggu', 'QRIS', NULL, 0),
(32, 68, '2026-09-26', 500000, 'queenzi', 'queenzi', '564732819', 'jl. perjuangan 2', '', 500000, 'Menunggu', 'Transfer Bank', NULL, 0),
(33, 68, '2026-09-26', 43000, 'queenzi', 'queenzi', '564732819', 'jl. perjuangan 2', '', 25000, 'Menunggu', 'COD', 'J&T Express', 18000),
(34, 69, '2026-09-26', 562000, 'pia', 'pia', '4536278989', 'jl. bt 2', '', 550000, 'Menunggu', 'QRIS', 'SiCepat HALU', 12000),
(35, 69, '2026-09-26', 90000, 'pia', 'pia', '4536278989', 'jl. bt 2', '', 90000, 'Diproses', 'Transfer Bank', 'Ambil di Toko', 0),
(36, 2, '2026-09-26', 355000, 'ajeng', 'ajeng', '082113121175', 'jl.melati 2', '', 340000, 'Menunggu', 'Transfer Bank', 'JNE Regular', 15000),
(37, 2, '2026-09-26', 260000, 'ajeng', 'ajeng', '082113121175', 'jl.melati 2', '', 245000, 'Menunggu', 'QRIS', 'JNE Regular', 15000),
(38, 70, '2026-09-26', 518000, 'abil', 'abil', '089507986543', 'jl. taman kirana surya', 'titip di satpam ya.', 500000, 'Menunggu', 'Transfer Bank', 'J&T Express', 18000),
(39, 56, '2026-09-28', 30000, 'pelanggan', 'pelanggan', '0821232610745', 'Jl. Melati 2', '', 15000, 'Menunggu', 'Transfer Bank', 'JNE Regular', 15000),
(40, 71, '2026-09-28', 955000, 'aina', 'aina', '088291900391', 'jl.perjuangan', 'lempar aja', 940000, 'Menunggu', 'QRIS', 'JNE Regular', 15000),
(41, 2, '2026-09-29', 35000, 'ajeng', 'ajeng', '082113121175', 'jl.melati 2', '', 35000, 'Menunggu', 'QRIS', 'Ambil di Toko', 0),
(42, 72, '2026-09-29', 565000, 'chika', 'chika', '0987654321', 'JL.cempaka', '', 550000, 'Menunggu', 'QRIS', 'JNE Regular', 15000),
(43, 73, '2026-09-29', 312000, 'Ayah adi', 'Ayah adi', '092123261074', 'JL.perjuangan2', '', 300000, 'Menunggu', 'Transfer Bank', 'SiCepat HALU', 12000);

-- --------------------------------------------------------

--
-- Table structure for table `tb_user`
--

CREATE TABLE `tb_user` (
  `id` int NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `no_hp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `alamat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `role` enum('admin','pelanggan') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tb_user`
--

INSERT INTO `tb_user` (`id`, `nama`, `email`, `username`, `password`, `no_hp`, `alamat`, `role`) VALUES
(2, 'ajeng halimun', 'ajenghalimun@gmail.com', 'ajeng', '0709', '082113121175', 'jl.melati 2', 'pelanggan'),
(32, 'admin toko', 'admintoko@gmail.com', 'admin', 'admin123', '089234561234', 'JL.bahagia 2', 'admin'),
(56, 'pelanggann', 'pelanggan123@gmail.com', 'pelanggan', 'pelanggan123', '0821232610745', 'Jl. Melati 2 ', 'pelanggan'),
(61, 'ervina lovi yanda', 'ervinalovi@gmail.com', 'lovi', 'lovi123', '08193425678', 'Jl. Sitameng 2', 'pelanggan'),
(63, 'zifarah indah', 'zifarahindah@gmail.com', 'indah', 'indah123', '0972134567', 'Jl. kalikoa 1', 'pelanggan'),
(68, 'kimora almahyra queenzi', 'kimoraa@gmail.com', 'queenzi', 'kimora123', '564732819', 'jl. perjuangan 2', 'pelanggan'),
(69, NULL, 'pia@gmail.com', 'pia', 'pia123', '4536278989', 'jl. bt 2', 'pelanggan'),
(70, NULL, 'abilliensyah@gmail.com', 'abil', 'abil123', '089507986543', 'jl. taman kirana surya', 'pelanggan'),
(71, 'Aina kamalati islam', 'ainakmltislm@gmail', 'aina', '191919', '088291900391', 'jl.perjuangan', 'pelanggan'),
(72, NULL, 'chika@gmail.com', 'chika', 'chika123', '0987654321', 'JL.cempaka', 'pelanggan'),
(73, NULL, 'adisuryo@gmail.com', 'Ayah adi', 'ayahadi123', '092123261074', 'JL.perjuangan2', 'pelanggan');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_detail`
--
ALTER TABLE `tb_detail`
  ADD PRIMARY KEY (`id_detail`);

--
-- Indexes for table `tb_kategori`
--
ALTER TABLE `tb_kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `tb_produk`
--
ALTER TABLE `tb_produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_pelanggan` (`id_pelanggan`);

--
-- Indexes for table `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_detail`
--
ALTER TABLE `tb_detail`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `tb_kategori`
--
ALTER TABLE `tb_kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tb_produk`
--
ALTER TABLE `tb_produk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  MODIFY `id_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
