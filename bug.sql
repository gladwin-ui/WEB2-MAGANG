-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Jun 2026 pada 09.18
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mfg_record`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bug`
--

CREATE TABLE `bug` (
  `idbug` int(11) NOT NULL,
  `idproject` int(11) DEFAULT NULL,
  `bug_title` varchar(255) NOT NULL,
  `severity` enum('Critical','Major','Minor') NOT NULL DEFAULT 'Major',
  `sn_code` varchar(100) DEFAULT NULL,
  `id_sn` int(11) DEFAULT NULL,
  `tipe_pelapor` enum('produk','sub') DEFAULT 'produk',
  `iddevice` int(11) DEFAULT NULL,
  `bugdesc` varchar(255) DEFAULT NULL,
  `bugversion` varchar(50) DEFAULT NULL,
  `bugenvi` varchar(255) DEFAULT NULL,
  `bugreproduce` text DEFAULT NULL,
  `rootcause` text DEFAULT NULL,
  `repair_action` text DEFAULT NULL,
  `is_rework` tinyint(1) NOT NULL DEFAULT 0,
  `bugfile` varchar(255) DEFAULT NULL,
  `bugexpected` text DEFAULT NULL,
  `bugcreatedby` varchar(255) DEFAULT NULL,
  `bugstatus` varchar(50) DEFAULT NULL,
  `bugfixby` varchar(255) DEFAULT NULL,
  `bugclosesavedate` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bug`
--

INSERT INTO `bug` (`idbug`, `idproject`, `bug_title`, `severity`, `sn_code`, `id_sn`, `tipe_pelapor`, `iddevice`, `bugdesc`, `bugversion`, `bugenvi`, `bugreproduce`, `rootcause`, `repair_action`, `is_rework`, `bugfile`, `bugexpected`, `bugcreatedby`, `bugstatus`, `bugfixby`, `bugclosesavedate`, `created_at`, `updated_at`) VALUES
(17, 27, 'DCDC short hubung singkat', 'Major', 'SN_UNIT_TACA#OPSHYB_2026-001', 4, 'produk', NULL, 'DCDC short karena hubung singkat', 'v.20260224', 'penggunaan di posko, kondisi panas', NULL, 'root cause', 'diperbaiki', 0, NULL, NULL, 'Admin', 'CLOSED', 'Admin', '2026-03-04 16:36:06', '2026-02-24 16:52:52', '2026-03-16 09:19:49'),
(18, 1, 'taca terbakar', 'Major', 'SUB_PN_TACA#OPSHYB_2026-1-001', 15, 'sub', NULL, 'dcdc short terbakar', 'v.20260224', 'penggunaan di posko, kondisi panas', 'sfasdfadfadf', 'rtououououou', 'repapaiaiaiaia', 1, NULL, 'adfadfafdadf', 'Admin', 'CLOSED', 'Admin', '2026-03-02 00:00:00', '2026-02-24 17:03:44', '2026-03-02 10:15:40'),
(19, 3, 'Kapasitor Meledak', 'Critical', 'SUB_PN_TACA#OPSHYB_2026-2-001', 13, 'sub', NULL, 'kapasitor meledak karena terlalu panas ', 'versi optim 20260221', 'vvvadadfad', NULL, NULL, '', 0, NULL, NULL, 'Admin', 'OPEN', NULL, NULL, '2026-02-25 17:17:07', '2026-03-16 09:18:50'),
(21, 1, 'Kapasitor Meledak', 'Major', 'SUB_PN_TACA#OPSHYB_2026-1-003', 21, 'sub', NULL, 'kapasitor meledak karena kembung', 'versi optim 20260221', 'penggunaan di posko, kondisi panas', NULL, NULL, '', 0, NULL, NULL, 'Admin', 'OPEN', NULL, NULL, '2026-02-26 10:31:42', '2026-03-16 09:18:02'),
(22, 1, 'lensa berembun', 'Major', 'SUB_PN_TACA#OPSHYB_2026-1-001', 15, 'sub', NULL, 'lensa berembun', 'versi optim 20260221', 'digunakan di luar ruangan', NULL, '', '', 0, '1772616270_725ff09cd85df70bbc3a.mp4', NULL, 'Admin', 'OPEN', NULL, NULL, '2026-02-26 11:52:55', '2026-03-16 09:17:35'),
(23, 1, 'Kapasitor Meledak', 'Critical', 'SUB_PN_TACA#OPSHYB_2026-1-001', 15, 'sub', NULL, 'kapasitor meledak karena terlalu panas', 'versi optim 20260221', 'penggunaan di posko, kondisi panas', 'asdfasdfasd', '', '', 1, NULL, 'asdfasdfasdfasdf', 'Admin', 'CLOSED', 'Fioni Agriyani', '2026-03-05 11:39:32', '2026-02-27 14:58:37', '2026-03-05 11:39:32'),
(24, 176, 'Short', 'Major', '1.1', 10300, 'sub', NULL, 'asdfg', '123', '-', 'asdf', 'qqqq', 'aaaa', 0, NULL, 'asdf', 'Fioni Agriyani', 'CLOSED', 'Fioni Agriyani', '2026-03-03 00:00:00', '2026-03-03 14:36:08', '2026-03-03 14:37:59'),
(25, 176, 'Short', 'Major', '1.1', 10300, 'sub', NULL, 'qwerty', '111', '111', 'asdfg', 'poiuy', 'lkjhg', 1, NULL, 'zxcvb', 'Fioni Agriyani', 'CLOSED', 'Fioni Agriyani', '2026-03-03 00:00:00', '2026-03-03 14:40:27', '2026-03-03 14:40:52'),
(26, 31, 'tidak bisa charge', 'Critical', 'SN-SLR-10W-01', 12, 'produk', NULL, 'adsfasdfasdfa', 'versi optim 20260221', 'aadfasdfadfa', 'beberbebrebr', NULL, NULL, 0, NULL, 'alakdslakdlfakdl', 'Admin', 'OPEN', NULL, NULL, '2026-03-09 20:31:32', '2026-03-09 20:31:32'),
(27, 27, 'Kapasitor Meledak', 'Critical', NULL, 4, 'produk', NULL, 'kmkkmkmkmaksdmfka', 'v.20260315', 'penggunaan di posko, kondisi panas', 'tinggal diulangi saja', NULL, NULL, 0, '1773533739_d56affc8390e9e88a980.jpeg', 'sesuai yang diharapkan', 'Maneng', 'OPEN', NULL, NULL, '2026-03-15 07:15:39', '2026-03-15 07:15:39'),
(28, 27, 'Kapasitor Meledak', 'Major', NULL, 17, 'produk', NULL, 'kjkjkjkjkjkjkjkj', 'versi optim 20260221', 'okokokokokoko', NULL, NULL, '', 0, '1773584494_47b05dc31ac7787bcc31.mp4', NULL, 'manufacture', 'CLOSED', NULL, NULL, '2026-03-15 21:21:34', '2026-03-30 11:27:08'),
(29, 27, 'Kapasitor Meledak', 'Major', NULL, 10527, 'produk', NULL, 'detailllllllll', 'v.20260315', 'asdfadfasdfadfadfad', NULL, NULL, '111111', 0, NULL, NULL, 'program', 'CLOSED', NULL, NULL, '2026-03-15 23:02:24', '2026-03-16 10:18:23'),
(30, 176, 'aaaaaaaaaaaaaaaaa', 'Minor', NULL, 10300, 'sub', NULL, 'aaaaaaaaaaaaaaa', 'aaaaaaaaaaaaaa', 'aaaaaaaaaaaaa', NULL, NULL, 'bbbbbbbbbbbbb', 0, NULL, NULL, 'maneng', 'CLOSED', NULL, NULL, '2026-03-16 10:21:22', '2026-03-16 10:21:45'),
(31, 177, 'ccccccccccccccccc', 'Minor', NULL, 10302, 'sub', NULL, 'cccccccccccccc', 'cccccccccccccc', 'ccccccccccccc', NULL, NULL, 'dddddddddddddddd', 0, NULL, NULL, 'manufacture', 'CLOSED', NULL, NULL, '2026-03-16 10:30:51', '2026-03-16 10:31:33'),
(32, 177, 'eeeeeeeeeeeeeeee', 'Major', NULL, 10303, 'sub', NULL, 'eeeeeeeeeeee', 'eeeeeeeeee', 'eeeeeeeeeeeee', NULL, NULL, 'fffffffffffffffff', 0, NULL, NULL, 'program', 'CLOSED', NULL, NULL, '2026-03-16 10:37:26', '2026-03-16 10:37:45'),
(33, 176, 'aaaaaaaaaaaaaaaaa', 'Major', NULL, 10300, 'sub', NULL, 'bcbcbcbc', 'bbbbbbbbbbbbbbbbbbbbbbbb', 'etetwt', NULL, NULL, 'fffffffffffffffffff', 0, '1774598453_064f2e72d0348089b19b.png', NULL, 'Admin', 'CLOSED', NULL, NULL, '2026-03-27 15:00:53', '2026-03-27 16:15:34'),
(34, 177, 'Kapasitor Meledak', 'Major', NULL, 10302, 'sub', NULL, 'sgtgrgtrgr', '111', 'eeeeeeeeeeeee', NULL, NULL, 'ewygyye', 0, NULL, NULL, 'Admin', 'CLOSED', NULL, NULL, '2026-03-27 15:05:12', '2026-03-27 16:11:00'),
(35, 176, 'Kapasitor Meledak', 'Major', NULL, 10301, 'sub', NULL, 'WEAFEAFrfrf', 'adAD', 'AFWFEAF', NULL, NULL, 'WRQFERFrfrwqff', 0, NULL, NULL, 'Fioni Agriyani', 'CLOSED', 'admin', '2026-04-09 12:35:12', '2026-03-27 16:03:02', '2026-03-27 16:04:00'),
(36, 189, 'Solderan Retak (Cold Solder)', 'Major', NULL, 10541, 'sub', NULL, 'Jalur solderan pada PCB mengalami keretakan.', 'V1.0.0', 'Baik', NULL, 'Panas berlebih, kelembapan (korosi) dan penggunaan daya yang terlalu tinggi..', 'Resoldering pada bagian yang mengalami keretakan.', 1, NULL, NULL, 'Admin', 'CLOSED', 'Fioni Agriyani', '2026-04-10 16:09:00', '2026-03-30 11:15:46', '2026-04-09 12:38:46'),
(37, 27, 'judul trouble', 'Major', NULL, 17, 'produk', NULL, 'desc 20260409', 'vvvvvvv', 'jkjkjk', NULL, NULL, 'berbagai hal dilakukan ', 0, NULL, NULL, NULL, 'CLOSED', NULL, '2026-04-09 12:41:34', '2026-04-09 12:40:25', '2026-04-09 12:41:34'),
(38, 27, 'judul trouble 20290409', 'Major', NULL, 4, 'produk', NULL, 'klklklkalsdkfalsdk', 'versi optim 20260221', 'penggunaan di posko, kondisi panas', NULL, NULL, 'ini adalah perbaikan', 0, NULL, NULL, NULL, 'CLOSED', NULL, '2026-04-09 13:09:23', '2026-04-09 12:48:58', '2026-04-09 13:09:23'),
(39, 27, 'Kapasitor Meledak', 'Major', NULL, 4, 'produk', NULL, 'desksksksksksksk', 'versi optim 20260221', 'penggunaan di posko, kondisi panas', NULL, NULL, 'tindakakanana', 0, NULL, NULL, NULL, 'CLOSED', NULL, '2026-04-09 13:20:48', '2026-04-09 13:20:26', '2026-04-09 13:20:48'),
(40, 27, 'Kapasitor Meledak 02', 'Major', NULL, 4, 'produk', NULL, 'desedsedsedsdseesds', 'versi optim 20260221', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 'OPEN', NULL, NULL, '2026-04-09 14:04:35', '2026-04-09 14:04:35'),
(41, 27, 'Kapasitor Meledak 03', 'Major', NULL, 4, 'produk', NULL, 'desdeededdeedededed', 'versi optim 20260221', 'enviviiviviivvii', NULL, NULL, 'perbaikaaaaaaaaaa', 0, NULL, NULL, '1', 'CLOSED', '1', '2026-04-09 14:51:45', '2026-04-09 14:50:30', '2026-04-09 14:51:45'),
(42, 27, 'Kapasitor Meledak 04', 'Major', NULL, 4, 'produk', NULL, 'dededededesdedsdsedsedsedes', 'versi optim 20260221', 'penggunaan di posko, kondisi panas', NULL, NULL, 'repairiririririri', 0, NULL, NULL, 'Admin', 'CLOSED', 'Admin', '2026-04-09 15:05:26', '2026-04-09 15:01:18', '2026-04-09 15:05:26'),
(43, 27, 'Kapasitor Meledak 05', 'Major', NULL, 4, 'produk', NULL, 'dedededdedeedesssdsedse', 'versi optim 20260221', 'penggunaan di posko, kondisi panas', NULL, NULL, 'repepepepepepepe', 0, NULL, NULL, 'Admin', 'CLOSED', 'Admin', '2026-04-09 15:17:32', '2026-04-09 15:16:17', '2026-04-09 15:17:32'),
(44, 27, 'Kapasitor Meledak 06', 'Major', NULL, 4, 'produk', NULL, 'dedededededededededesss', 'versi optim 20260221', 'penggunaan di posko, kondisi panas', 'hohohohohohoho', NULL, NULL, 0, NULL, 'exexexexxexex', 'Admin', 'OPEN', NULL, NULL, '2026-04-10 07:03:54', '2026-04-10 07:03:54'),
(45, 27, 'Kapasitor Meledak 07', 'Major', 'SN_UNIT_TACA#OPSHYB_2026-001', 4, 'produk', NULL, 'dedededsdsedsedsdsde', 'versi optim 20260221', 'penggunaan di posko, kondisi panas', NULL, NULL, 'perbaiaiaiaiaiaiaiakan', 0, NULL, NULL, 'Admin', 'CLOSED', 'Admin', '2026-04-10 07:18:41', '2026-04-10 07:15:08', '2026-04-10 07:18:41'),
(46, 27, 'Kapasitor Meledak 08', 'Major', 'SN_UNIT_TACA#OPSHYB_2026-001', 4, 'produk', NULL, 'dededededededes', 'versi optim 20260221', 'penggunaan di posko, kondisi panas', NULL, NULL, 'tctctctctctctctctctindakan', 0, NULL, NULL, 'Admin', 'CLOSED', 'Admin', '2026-04-10 07:31:55', '2026-04-10 07:31:16', '2026-04-10 07:31:55'),
(47, 27, 'Kapasitor Meledak 09', 'Major', 'SN_UNIT_TACA#OPSHYB_2026-001', 4, 'produk', NULL, 'dedededededdeesss', 'versi optim 20260221', 'penggunaan di posko, kondisi panas', NULL, NULL, 'tintitnitntintitnitn', 0, NULL, NULL, 'Admin', 'CLOSED', 'Admin', '2026-04-10 07:46:45', '2026-04-10 07:45:31', '2026-04-10 07:46:45'),
(48, 27, 'Kapasitor Meledak 010', 'Major', 'SN_UNIT_TACA#OPSHYB_2026-001', 4, 'produk', NULL, 'dedsedsedsdsdsed', 'versi optim 20260221', 'penggunaan di posko, kondisi panas', NULL, NULL, 'prprprprprprprprp', 0, NULL, NULL, 'Admin', 'CLOSED', 'Admin', '2026-04-10 07:55:24', '2026-04-10 07:55:04', '2026-04-10 07:55:24'),
(49, 27, 'Kapasitor Meledak 011', 'Major', 'SN_UNIT_TACA#OPSHYB_2026-001', 4, 'produk', NULL, 'ini edit deskripsi', 'vevevevevev', 'penggunaan di posko, kondisi panas', 'hohohohohohoho', 'akakrkrkrkrkrkkrrkk', 'tintitnitnitntinti', 0, NULL, 'edxexexexexexexexexexe', 'Admin', 'CLOSED', 'Admin', '2026-04-10 09:45:27', '2026-04-10 08:06:22', '2026-04-10 08:06:22'),
(50, 27, 'Kapasitor Meledak 012', 'Major', 'SN_UNIT_TACA#OPSHYB_2026-001', 4, 'produk', NULL, 'desdsedsefsefsef', 'versi optim 20260221', 'penggunaan di posko, kondisi panas', 'ohohohohohohsodfhsod', 'rorororororoor', 'arerererererer', 0, '1775788069_e4bcadc95b8a1ad558fa.jpeg', 'exsexesxerxsrda', 'Admin', 'CLOSED', 'Admin', '2026-04-10 09:37:14', '2026-04-10 09:27:49', '2026-04-10 09:27:49'),
(51, 27, 'Kapasitor Meledak 013', 'Major', 'SN_UNIT_TACA#OPSHYB_2026-001', 4, 'produk', NULL, 'adsfadsfasdfasdfasdf', 'vadsasdvadsv', 'asdfadfadfadsfa', 'asdfadfadfadfa', 'contoh', 'contoh', 1, '1775789300_be186a679ce6be5c9709.jpeg', 'adfasdfadfadfdf', 'Admin', 'CLOSED', 'Fioni Agriyani', '2026-04-10 11:21:41', '2026-04-10 09:48:20', '2026-04-10 09:48:20'),
(52, 192, 'contoh', 'Major', 'sn-contoh-001.1', 10559, 'sub', NULL, 'contoh', 'V1.0.0', 'contoh', 'contoh', 'contoh', 'contoh', 0, NULL, 'contoh', 'Fioni Agriyani', 'CLOSED', 'Fioni Agriyani', '2026-04-10 11:21:03', '2026-04-10 11:20:09', '2026-04-10 11:20:09'),
(53, 27, 'Kapasitor Meledak 014', 'Critical', 'SN_UNIT_TACA#OPSHYB_2026-001', 4, 'produk', NULL, 'desdsedsedseds', 'versi optim 20260221', 'penggunaan di posko, kondisi panas', 'hohohohohohoho', 'rororoaorofaroatoar', 'reaoekaoekaokeraoekaok', 0, '1775799776_7483b5da4974c42044f8.jpeg', 'saeroaroaseoaseorao', 'Admin', 'CLOSED', 'Admin', '2026-04-10 12:43:18', '2026-04-10 12:42:56', '2026-04-10 12:42:56'),
(54, 27, 'Kapasitor Meledak 015-edit', 'Minor', 'SN_UNIT_TACA#OPSHYB_2026-001', 4, 'produk', NULL, 'asdfadfasdfadfaaf', 'versi optim 20260221', 'enviviiviviivvii', 'adfadflkaodfkadofkaok', 'oidfoiaosdifaodifaoidfoaio', 'piarpipipipipipipoiad', 1, '1775800040_e7b3d0fbdc9ab591802c.jpeg', 'okokokokokoaskdfoakfoa', 'Admin', 'CLOSED', 'Admin', '2026-04-10 12:48:05', '2026-04-10 12:47:20', '2026-04-10 12:47:20'),
(55, 192, 'contoh', 'Major', 'sn-contoh-001.2', 10560, 'sub', NULL, 'contoh', 'V1.0.0', 'contoh', 'contoh', 'contoh', 'contoh', 1, NULL, 'contoh', 'Fioni Agriyani', 'CLOSED', 'Fioni Agriyani', '2026-04-10 13:41:49', '2026-04-10 13:40:54', '2026-04-10 13:40:54'),
(56, 192, 'contoh', 'Major', 'sn-contoh-001.3', 10561, 'sub', NULL, 'contoh', 'V1.0.0', 'contoh', 'contoh', 'contoh', 'contoh', 0, NULL, 'contoh', 'Fioni Agriyani', 'CLOSED', 'Fioni Agriyani', '2026-04-10 13:42:02', '2026-04-10 13:41:15', '2026-04-10 13:41:15');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bug`
--
ALTER TABLE `bug`
  ADD PRIMARY KEY (`idbug`),
  ADD KEY `idproject` (`idproject`),
  ADD KEY `iddevice` (`iddevice`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bug`
--
ALTER TABLE `bug`
  MODIFY `idbug` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
