-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 06 Nov 2026 pada 15.30
-- Versi server: 8.0.30
-- Versi PHP: 8.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `surveilink_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `role` varchar(50) NOT NULL,
  `aktivitas` varchar(100) NOT NULL,
  `modul` varchar(50) NOT NULL,
  `detail` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `waktu` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `username`, `role`, `aktivitas`, `modul`, `detail`, `ip_address`, `waktu`) VALUES
(1, 2, 'petugas_rm', 'petugas_rekam_medis', 'Reset Data Master', 'System', 'Semua data operasional dan hitungan surveilans direset ke 0.', '::1', '2026-11-05 22:21:22'),
(2, 5, 'hakimm', 'admin_surveilans', 'Login Berhasil', 'Autentikasi', 'User hakimm (Dr.MUH.Hakim) berhasil login ke sistem.', '::1', '2026-11-06 13:39:23'),
(3, 5, 'hakimm', 'admin_surveilans', 'Login Berhasil', 'Autentikasi', 'User hakimm (Dr.MUH.Hakim) berhasil login ke sistem.', '::1', '2026-11-06 13:39:42'),
(4, 5, 'hakimm', 'admin_surveilans', 'Login Berhasil', 'Autentikasi', 'User hakimm (Dr.MUH.Hakim) berhasil login ke sistem.', '::1', '2026-11-06 18:51:34'),
(5, 1, 'admin', 'admin_surveilans', 'Login Berhasil', 'Autentikasi', 'User admin (dr. Hendra Pratama, Sp.A, M.Kes) berhasil login ke sistem.', '::1', '2026-11-06 18:51:54'),
(6, 1, 'admin', 'admin_surveilans', 'Input Pasien Manual', 'Data Masuk', 'Input manual pasien No. RM RM-123 (HARISTA) oleh admin', '::1', '2026-11-06 18:57:30'),
(7, 1, 'admin', 'admin_surveilans', 'Validasi Pasien', 'Validasi Data', 'Data No RM RM-123 (HARISTA) tervalidasi & dipromosikan ke Data Terpadu (Single Source of Truth).', '::1', '2026-11-06 19:04:07'),
(8, 1, 'admin', 'admin_surveilans', 'Kirim SIRS Kemenkes', 'Kirim ke SIRS', 'Transmisi TRX-SIRS-20261106-120424 (Surveilans Kasus KLB / Penyakit Menular (EWARS)) berisi 1 record. No Ref: KEMKES-SIRS-RL-2026-956660', '::1', '2026-11-06 19:04:24'),
(9, 1, 'admin', 'admin_surveilans', 'Input Pasien Manual', 'Data Masuk', 'Input manual pasien No. RM 009727 (Nina) oleh admin', '::1', '2026-11-06 19:14:55'),
(10, 1, 'admin', 'admin_surveilans', 'Validasi Pasien', 'Validasi Data', 'Data No RM 009727 (Nina) tervalidasi & dipromosikan ke Data Terpadu (Single Source of Truth).', '::1', '2026-11-06 19:15:13'),
(11, 1, 'admin', 'admin_surveilans', 'Kirim SIRS Kemenkes', 'Kirim ke SIRS', 'Transmisi TRX-SIRS-20261106-121549 (Surveilans Kasus KLB / Penyakit Menular (EWARS)) berisi 1 record. No Ref: KEMKES-SIRS-RL-2026-535352', '::1', '2026-11-06 19:15:49'),
(12, 6, 'dwi mawarni', 'petugas_rekam_medis', 'Registrasi Petugas', 'Autentikasi', 'Pendaftaran akun baru dwi mawarni (dwi mawar) sebagai petugas_rekam_medis di unit Instalasi Gawat Darurat (IGD).', '::1', '2026-11-06 19:22:33'),
(13, 6, 'dwi mawarni', 'petugas_rekam_medis', 'Login Berhasil', 'Autentikasi', 'User dwi mawarni (dwi mawar) berhasil login ke sistem.', '::1', '2026-11-06 19:22:49'),
(14, 1, 'admin', 'admin_surveilans', 'Login Berhasil', 'Autentikasi', 'User admin (dr. Hendra Pratama, Sp.A, M.Kes) berhasil login ke sistem.', '::1', '2026-11-06 19:25:47'),
(15, 1, 'admin', 'admin_surveilans', 'Edit Pasien', 'Data Masuk', 'Perubahan data pasien No. RM RM-123 (HARISTA PRATAMA) oleh admin', '::1', '2026-11-06 19:37:09'),
(16, 1, 'admin', 'admin_surveilans', 'Hapus Pasien', 'Data Masuk', 'Hapus data pasien No. RM 009727 (Nina) dari unit RAJAL', '::1', '2026-11-06 19:45:37'),
(17, 1, 'admin', 'admin_surveilans', 'Login Berhasil', 'Autentikasi', 'User admin (dr. Hendra Pratama, Sp.A, M.Kes) berhasil login ke sistem.', '::1', '2026-11-06 21:37:53'),
(18, 1, 'admin', 'admin_surveilans', 'Input Pasien Manual', 'Data Masuk', 'Input manual pasien No. RM RM-9901 (Budi Santoso) unit IGD oleh admin', '::1', '2026-11-06 21:46:18'),
(19, 1, 'admin', 'admin_surveilans', 'Input Pasien Manual', 'Data Masuk', 'Input manual pasien No. RM RM-778899 (Budi Santoso) [Pembayaran Umum] unit IGD oleh admin', '::1', '2026-11-06 21:57:52'),
(20, 1, 'admin', 'admin_surveilans', 'Validasi Pasien', 'Validasi Data', 'Data No RM RM-778899 (Budi Santoso) tervalidasi & dipromosikan ke Data Terpadu (Single Source of Truth).', '::1', '2026-11-06 21:58:12'),
(21, 1, 'admin', 'admin_surveilans', 'Edit Pasien', 'Data Masuk', 'Perubahan data pasien No. RM RM-778899 (Budi Santoso) oleh admin', '::1', '2026-11-06 21:58:47'),
(22, 1, 'admin', 'admin_surveilans', 'Hapus Pasien', 'Data Masuk', 'Hapus data pasien No. RM RM-778899 (Budi Santoso) dari unit IGD', '::1', '2026-11-06 21:59:06'),
(23, 1, 'admin', 'admin_surveilans', 'Login Berhasil', 'Autentikasi', 'User admin (dr. Hendra Pratama, Sp.A, M.Kes) berhasil login ke sistem.', '::1', '2026-11-06 22:04:25'),
(24, 1, 'admin', 'admin_surveilans', 'Input Pasien Manual', 'Data Masuk', 'Input manual pasien No. RM RM-554433 (Anisa Rahmawati) [Pembayaran BPJS] unit IGD oleh admin', '::1', '2026-11-06 22:11:29'),
(25, 1, 'admin', 'admin_surveilans', 'Validasi Pasien', 'Validasi Data', 'Data No RM RM-554433 (Anisa Rahmawati) tervalidasi & dipromosikan ke Data Terpadu (Single Source of Truth).', '::1', '2026-11-06 22:12:00'),
(26, 1, 'admin', 'admin_surveilans', 'Edit Pasien', 'Data Masuk', 'Perubahan data pasien No. RM RM-554433 (Anisa Rahmawati) oleh admin', '::1', '2026-11-06 22:12:20'),
(27, 1, 'admin', 'admin_surveilans', 'Hapus Pasien', 'Data Masuk', 'Hapus data pasien No. RM RM-554433 (Anisa Rahmawati) dari unit IGD', '::1', '2026-11-06 22:13:38'),
(28, 1, 'admin', 'admin_surveilans', 'Input Pasien Manual', 'Data Masuk', 'Input manual pasien No. RM 00998888 (lusiana) [Pembayaran BPJS] unit IGD oleh admin', '::1', '2026-11-06 22:15:17'),
(29, 1, 'admin', 'admin_surveilans', 'Validasi Pasien', 'Validasi Data', 'Data No RM 00998888 (lusiana) tervalidasi & dipromosikan ke Data Terpadu (Single Source of Truth).', '::1', '2026-11-06 22:15:44'),
(30, 1, 'admin', 'admin_surveilans', 'Edit Pasien', 'Data Masuk', 'Perubahan data pasien No. RM 00998888 (Lusiana Putri) oleh admin', '::1', '2026-11-06 22:19:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_masuk`
--

CREATE TABLE `data_masuk` (
  `id` int NOT NULL,
  `no_registrasi` varchar(50) NOT NULL,
  `unit_sumber` varchar(50) NOT NULL,
  `no_rm` varchar(20) NOT NULL,
  `nik` varchar(20) NOT NULL,
  `nama_pasien` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL DEFAULT 'L',
  `cara_pembayaran` varchar(50) NOT NULL DEFAULT 'Pembayaran BPJS',
  `tempat_lahir` varchar(100) NOT NULL DEFAULT 'Jakarta',
  `tanggal_lahir` date NOT NULL,
  `usia` int NOT NULL,
  `agama` varchar(50) NOT NULL DEFAULT 'Islam',
  `status_perkawinan` varchar(50) NOT NULL DEFAULT 'Kawin',
  `alamat` text NOT NULL,
  `kelurahan` varchar(100) DEFAULT '',
  `kecamatan` varchar(100) DEFAULT '',
  `kota_kab` varchar(100) DEFAULT 'Jakarta Pusat',
  `tanggal_masuk` datetime NOT NULL,
  `diagnosa_awal` varchar(255) DEFAULT 'Dalam Pemeriksaan Klinis',
  `kode_icd10` varchar(10) DEFAULT '-',
  `kategori_penyakit` varchar(100) DEFAULT 'Menunggu Diagnosis',
  `hasil_lab` varchar(255) DEFAULT NULL,
  `terapi_farmasi` text,
  `status_kelengkapan` enum('Lengkap','Tidak Lengkap','Duplikat','Anomali') NOT NULL DEFAULT 'Tidak Lengkap',
  `status_validasi` enum('Menunggu','Valid','Ditolak','Konflik') NOT NULL DEFAULT 'Menunggu',
  `catatan_anomali` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `data_masuk`
--

INSERT INTO `data_masuk` (`id`, `no_registrasi`, `unit_sumber`, `no_rm`, `nik`, `nama_pasien`, `jenis_kelamin`, `cara_pembayaran`, `tempat_lahir`, `tanggal_lahir`, `usia`, `agama`, `status_perkawinan`, `alamat`, `kelurahan`, `kecamatan`, `kota_kab`, `tanggal_masuk`, `diagnosa_awal`, `kode_icd10`, `kategori_penyakit`, `hasil_lab`, `terapi_farmasi`, `status_kelengkapan`, `status_validasi`, `catatan_anomali`, `created_at`) VALUES
(1, 'REG-IGD-20261106-4694', 'IGD', 'RM-123', '3313122444899000', 'HARISTA PRATAMA', 'L', 'Pembayaran BPJS', 'Jakarta', '1990-01-01', 28, 'Islam', 'Kawin', 'Jl. Salemba Tengah No. 12', '', '', 'Jakarta Pusat', '2026-11-06 18:57:30', 'Demam Berdarah Dengue (DBD)', 'A91', 'Demam Berdarah Dengue (DBD)', 'NS1 Positif, Trombosit 78.000 /uL', 'Infus RL', 'Lengkap', 'Valid', NULL, '2026-11-06 11:57:30'),
(6, 'REG-IGD-20261106-9380', 'IGD', '00998888', '331244422', 'Lusiana Putri', 'P', 'Pembayaran BPJS', 'Karanganyar', '2004-03-10', 22, 'Islam', 'Kawin', 'Surakarta', '', '', 'Jakarta Pusat', '2026-11-06 22:15:17', 'Dalam Pemeriksaan Klinis', '-', 'Menunggu Diagnosis', '', '', 'Lengkap', 'Valid', NULL, '2026-11-06 15:15:17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_terpadu`
--

CREATE TABLE `data_terpadu` (
  `id` int NOT NULL,
  `data_masuk_id` int DEFAULT NULL,
  `no_rm` varchar(20) NOT NULL,
  `nik` varchar(20) NOT NULL,
  `nama_pasien` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `cara_pembayaran` varchar(50) NOT NULL DEFAULT 'Pembayaran BPJS',
  `tempat_lahir` varchar(100) NOT NULL DEFAULT 'Jakarta',
  `tanggal_lahir` date NOT NULL,
  `usia` int NOT NULL,
  `agama` varchar(50) NOT NULL DEFAULT 'Islam',
  `status_perkawinan` varchar(50) NOT NULL DEFAULT 'Kawin',
  `alamat_lengkap` text NOT NULL,
  `unit_layanan` varchar(100) NOT NULL,
  `tanggal_masuk` datetime NOT NULL,
  `tanggal_keluar` datetime DEFAULT NULL,
  `kondisi_keluar` varchar(50) DEFAULT 'Dalam Perawatan',
  `kode_icd10` varchar(10) NOT NULL,
  `nama_penyakit` varchar(255) NOT NULL,
  `kategori_surveilans` varchar(100) NOT NULL,
  `hasil_lab_terpadu` varchar(255) DEFAULT NULL,
  `status_sirs` enum('Belum Dikirim','Dalam Antrean','Terkirim','Gagal') NOT NULL DEFAULT 'Belum Dikirim',
  `tgl_validasi` datetime DEFAULT NULL,
  `petugas_validasi` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `data_terpadu`
--

INSERT INTO `data_terpadu` (`id`, `data_masuk_id`, `no_rm`, `nik`, `nama_pasien`, `jenis_kelamin`, `cara_pembayaran`, `tempat_lahir`, `tanggal_lahir`, `usia`, `agama`, `status_perkawinan`, `alamat_lengkap`, `unit_layanan`, `tanggal_masuk`, `tanggal_keluar`, `kondisi_keluar`, `kode_icd10`, `nama_penyakit`, `kategori_surveilans`, `hasil_lab_terpadu`, `status_sirs`, `tgl_validasi`, `petugas_validasi`, `created_at`) VALUES
(1, 1, 'RM-123', '3313122444899000', 'HARISTA PRATAMA', 'L', 'Pembayaran BPJS', 'Jakarta', '1990-01-01', 28, 'Islam', 'Kawin', 'Jl. Salemba Tengah No. 12', 'IGD', '2026-11-06 18:57:30', NULL, 'Dalam Perawatan', 'A91', 'Demam Berdarah Dengue (DBD)', 'Demam Berdarah Dengue (DBD)', 'NS1 Positif, Trombosit 78.000 /uL', 'Terkirim', '2026-11-06 19:04:07', 'dr. Hendra Pratama, Sp.A, M.Kes', '2026-11-06 12:04:07'),
(5, 6, '00998888', '331244422', 'Lusiana Putri', 'P', 'Pembayaran BPJS', 'Karanganyar', '2004-03-10', 22, 'Islam', 'Kawin', 'Surakarta', 'IGD', '2026-11-06 22:15:17', NULL, 'Dalam Perawatan', '-', 'Dalam Pemeriksaan Klinis', 'Menunggu Diagnosis', '', 'Belum Dikirim', '2026-11-06 22:15:44', 'dr. Hendra Pratama, Sp.A, M.Kes', '2026-11-06 15:15:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kirim_sirs`
--

CREATE TABLE `kirim_sirs` (
  `id` int NOT NULL,
  `kode_transmisi` varchar(50) NOT NULL,
  `jenis_laporan` varchar(100) NOT NULL,
  `periode_laporan` varchar(50) NOT NULL,
  `jumlah_data` int NOT NULL DEFAULT '0',
  `payload_json` longtext,
  `response_code` varchar(50) DEFAULT '201 Created',
  `response_message` text,
  `nomor_referensi_sirs` varchar(100) DEFAULT NULL,
  `status_pengiriman` enum('Sukses','Gagal','Pending','Antrean') NOT NULL DEFAULT 'Pending',
  `dikirim_oleh` varchar(100) NOT NULL,
  `waktu_pengiriman` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `kirim_sirs`
--

INSERT INTO `kirim_sirs` (`id`, `kode_transmisi`, `jenis_laporan`, `periode_laporan`, `jumlah_data`, `payload_json`, `response_code`, `response_message`, `nomor_referensi_sirs`, `status_pengiriman`, `dikirim_oleh`, `waktu_pengiriman`) VALUES
(1, 'TRX-SIRS-20261106-120424', 'Surveilans Kasus KLB / Penyakit Menular (EWARS)', 'November 2026', 1, '{\n    \"kode_faskes\": \"3171012\",\n    \"nama_faskes\": \"RSUD Mitra Sehat Kemenkes\",\n    \"sistem_pengirim\": \"SurveiLink v2.0 - Hospital Surveillance Bridge\",\n    \"waktu_transmisi\": \"2026-11-06T12:04:24+00:00\",\n    \"jenis_laporan\": \"Surveilans Kasus KLB / Penyakit Menular (EWARS)\",\n    \"periode\": \"November 2026\",\n    \"total_kasus\": 1,\n    \"data_pasien\": [\n        {\n            \"id_surveilink\": 1,\n            \"no_rm\": \"RM-123\",\n            \"nik\": \"3313122444899000\",\n            \"nama_pasien\": \"HARISTA\",\n            \"jenis_kelamin\": \"L\",\n            \"usia\": 28,\n            \"alamat\": \"\",\n            \"icd10\": \"A97\",\n            \"diagnosa\": \"DBD\",\n            \"kategori_surveilans\": \"Demam Berdarah Dengue (DBD)\",\n            \"tanggal_masuk\": \"2026-11-06 18:57:30\",\n            \"hasil_laboratorium\": \"\",\n            \"unit_perawatan\": \"IGD\",\n            \"status_pasien\": \"Dalam Perawatan\"\n        }\n    ]\n}', '201 Created', 'Data surveilans berhasil diverifikasi dan disimpan ke repositori SIRS Kemenkes v2', 'KEMKES-SIRS-RL-2026-956660', 'Sukses', 'dr. Hendra Pratama, Sp.A, M.Kes', '2026-11-06 19:04:24'),
(2, 'TRX-SIRS-20261106-121549', 'Surveilans Kasus KLB / Penyakit Menular (EWARS)', 'November 2026', 1, '{\n    \"kode_faskes\": \"3171012\",\n    \"nama_faskes\": \"RSUD Mitra Sehat Kemenkes\",\n    \"sistem_pengirim\": \"SurveiLink v2.0 - Hospital Surveillance Bridge\",\n    \"waktu_transmisi\": \"2026-11-06T12:15:49+00:00\",\n    \"jenis_laporan\": \"Surveilans Kasus KLB / Penyakit Menular (EWARS)\",\n    \"periode\": \"November 2026\",\n    \"total_kasus\": 1,\n    \"data_pasien\": [\n        {\n            \"id_surveilink\": 2,\n            \"no_rm\": \"009727\",\n            \"nik\": \"3313122444899001\",\n            \"nama_pasien\": \"Nina\",\n            \"jenis_kelamin\": \"L\",\n            \"usia\": 22,\n            \"alamat\": \"jebres surakarta\",\n            \"icd10\": \"Z01\",\n            \"diagnosa\": \"chek up\",\n            \"kategori_surveilans\": \"Demam Berdarah Dengue (DBD)\",\n            \"tanggal_masuk\": \"2026-11-06 19:14:55\",\n            \"hasil_laboratorium\": \"-\",\n            \"unit_perawatan\": \"RAJAL\",\n            \"status_pasien\": \"Dalam Perawatan\"\n        }\n    ]\n}', '201 Created', 'Data surveilans berhasil diverifikasi dan disimpan ke repositori SIRS Kemenkes v2', 'KEMKES-SIRS-RL-2026-535352', 'Sukses', 'dr. Hendra Pratama, Sp.A, M.Kes', '2026-11-06 19:15:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi_alert`
--

CREATE TABLE `notifikasi_alert` (
  `id` int NOT NULL,
  `tipe_alert` enum('EWARS_KLB','INTEGRASI_TERPUTUS','VALIDASI_ANOMALI','SIRS_FEEDBACK','INFO') NOT NULL,
  `judul` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `tingkat_urgensi` enum('Bahaya','Peringatan','Info') NOT NULL DEFAULT 'Info',
  `unit_terkait` varchar(50) DEFAULT NULL,
  `status_baca` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `notifikasi_alert`
--

INSERT INTO `notifikasi_alert` (`id`, `tipe_alert`, `judul`, `pesan`, `tingkat_urgensi`, `unit_terkait`, `status_baca`, `created_at`) VALUES
(1, 'SIRS_FEEDBACK', 'Pengiriman SIRS Kemenkes Berhasil: 1 Data', 'Laporan \'Surveilans Kasus KLB / Penyakit Menular (EWARS)\' berhasil ditransmisikan ke SIRS Online Kemenkes dengan No. Ref: KEMKES-SIRS-RL-2026-956660.', 'Info', 'SIRS Kemenkes', 0, '2026-11-06 12:04:24'),
(2, 'SIRS_FEEDBACK', 'Pengiriman SIRS Kemenkes Berhasil: 1 Data', 'Laporan \'Surveilans Kasus KLB / Penyakit Menular (EWARS)\' berhasil ditransmisikan ke SIRS Online Kemenkes dengan No. Ref: KEMKES-SIRS-RL-2026-535352.', 'Info', 'SIRS Kemenkes', 0, '2026-11-06 12:15:49'),
(3, 'INFO', 'Petugas Baru Terdaftar: dwi mawar', 'Akun pegawai baru dengan username \'dwi mawarni\' terdaftar pada unit Instalasi Gawat Darurat (IGD).', 'Info', 'Instalasi Gawat Darurat (IGD)', 0, '2026-11-06 12:22:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sirs_config`
--

CREATE TABLE `sirs_config` (
  `id` int NOT NULL,
  `kode_rs` varchar(20) NOT NULL DEFAULT '3171012',
  `nama_rs` varchar(150) NOT NULL DEFAULT 'RSUD Mitra Sehat Kemenkes',
  `kelas_rs` varchar(50) NOT NULL DEFAULT 'Kelas B Pendidikan',
  `api_endpoint` varchar(255) NOT NULL DEFAULT 'https://sirs.kemkes.go.id/fo/api/v2/surveilans',
  `api_key` varchar(100) NOT NULL DEFAULT 'KEMKES_SIRS_LIVE_882949',
  `secret_key` varchar(100) NOT NULL DEFAULT 'SECRET_SIRS_KEMKES_2026',
  `status_koneksi` varchar(50) NOT NULL DEFAULT 'Tersambung (LIVE API v2)',
  `last_ping` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `sirs_config`
--

INSERT INTO `sirs_config` (`id`, `kode_rs`, `nama_rs`, `kelas_rs`, `api_endpoint`, `api_key`, `secret_key`, `status_koneksi`, `last_ping`) VALUES
(1, '3171012', 'RSUD Mitra Sehat Kemenkes', 'Kelas B', 'https://sirs.kemkes.go.id/fo/api/v2/surveilans', 'KEMKES_SIRS_LIVE_882949', 'SECRET_SIRS_KEMKES_2026', 'Tersambung (LIVE API v2)', '2026-11-05 20:47:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sumber_data_unit`
--

CREATE TABLE `sumber_data_unit` (
  `id` int NOT NULL,
  `kode_unit` varchar(20) NOT NULL,
  `nama_unit` varchar(100) NOT NULL,
  `tipe_sistem` varchar(50) NOT NULL,
  `lokasi_server` varchar(100) NOT NULL,
  `endpoint_url` varchar(255) NOT NULL,
  `status_koneksi` enum('Connected','Disconnected','Warning','Syncing') NOT NULL DEFAULT 'Connected',
  `latency_ms` int NOT NULL DEFAULT '14',
  `terakhir_sinkron` datetime DEFAULT NULL,
  `total_data_masuk` int NOT NULL DEFAULT '0',
  `total_anomali` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `sumber_data_unit`
--

INSERT INTO `sumber_data_unit` (`id`, `kode_unit`, `nama_unit`, `tipe_sistem`, `lokasi_server`, `endpoint_url`, `status_koneksi`, `latency_ms`, `terakhir_sinkron`, `total_data_masuk`, `total_anomali`, `created_at`) VALUES
(1, 'IGD', 'Instalasi Gawat Darurat', 'SIMRS IGD v4.2', '192.168.10.12:8080/api/v1/patients', 'http://192.168.10.12/simrs-igd/api', 'Connected', 19, '2026-11-06 22:15:17', 3, 0, '2026-11-05 13:47:52'),
(2, 'RAJAL', 'Poliklinik Rawat Jalan', 'SIMRS Poliklinik Terpadu', '192.168.10.14:8080/api/v1/rajal', 'http://192.168.10.14/simrs-rajal/api', 'Connected', 15, '2026-11-06 19:14:55', 0, 0, '2026-11-05 13:47:52'),
(3, 'RANAP', 'Instalasi Rawat Inap & Bangsal', 'SIMRS Inpatient Care', '192.168.10.15:8080/api/v1/ranap', 'http://192.168.10.15/simrs-ranap/api', 'Connected', 18, '2026-11-05 22:21:22', 0, 0, '2026-11-05 13:47:52'),
(4, 'LAB', 'Laboratorium Patologi Klinik', 'LIS MediLab Express', '192.168.20.10:3000/api/results', 'http://192.168.20.10/lis/api', 'Connected', 9, '2026-11-05 22:21:22', 0, 0, '2026-11-05 13:47:52'),
(5, 'FARM', 'Depo Farmasi & Terapi Obat', 'FIS MediPharma Hospital', '192.168.20.25:8000/api/dispense', 'http://192.168.20.25/fis/api', 'Connected', 11, '2026-11-05 22:21:22', 0, 0, '2026-11-05 13:47:52'),
(6, 'ICU', 'Ruang Isolasi & Intensive Care', 'SISR Critical Surveillance', '192.168.30.5:9000/api/icu', 'http://192.168.30.5/sisr/api', 'Connected', 14, '2026-11-05 22:21:22', 0, 0, '2026-11-05 13:47:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin_surveilans','petugas_rekam_medis','admin_unit','supervisor') NOT NULL DEFAULT 'admin_surveilans',
  `unit` varchar(100) NOT NULL DEFAULT 'Instalasi Epidemiologi & Surveilans',
  `foto` varchar(255) DEFAULT 'assets/img/avatar.png',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `email`, `role`, `unit`, `foto`, `created_at`) VALUES
(1, 'admin', '$2y$10$fQ7J1mY6n4uY9F4oT8rG8O7Wk5Rk9w1u6Wn7n6P0p8G3G4q7Y7F7q', 'dr. Hendra Pratama, Sp.A, M.Kes', 'hendra.surveilans@rsudmitra.go.id', 'admin_surveilans', 'Instalasi Epidemiologi & Surveilans RS', 'assets/img/avatar.png', '2026-11-05 13:47:52'),
(2, 'petugas_rm', '$2y$10$fQ7J1mY6n4uY9F4oT8rG8O7Wk5Rk9w1u6Wn7n6P0p8G3G4q7Y7F7q', 'Siti Rahmawati, A.Md.RMIK', 'siti.rm@rsudmitra.go.id', 'petugas_rekam_medis', 'Instalasi Rekam Medis', 'assets/img/avatar.png', '2026-11-05 13:47:52'),
(3, 'unit_igd', '$2y$10$fQ7J1mY6n4uY9F4oT8rG8O7Wk5Rk9w1u6Wn7n6P0p8G3G4q7Y7F7q', 'Ns. Arif Wicaksono, S.Kep', 'arif.igd@rsudmitra.go.id', 'admin_unit', 'Instalasi Gawat Darurat (IGD)', 'assets/img/avatar.png', '2026-11-05 13:47:52'),
(4, 'dr_farhan', '$2y$10$akqTDc4xRbAsyi7STPFr.eK8HBM3XRHHNRe496lRMDiAw/8tu9cua', 'dr. Farhan Malik, Sp.A', 'farhan@rsudmitra.go.id', 'admin_surveilans', 'Instalasi Epidemiologi', 'assets/img/avatar.png', '2026-11-05 15:14:08'),
(5, 'hakimm', '$2y$10$B4IYusu0gbhKgBjoIIs73./Wnp8/211is5lsxL9UKmrc7/17XGrBq', 'Dr.MUH.Hakim', 'hakim@gmail.com', 'admin_surveilans', 'Instalasi Gawat Darurat (IGD)', 'assets/img/avatar.png', '2026-11-05 15:17:29'),
(6, 'dwi mawarni', '$2y$10$H8dGfzZcR.0EwAyDWUd/Mu4LU4SMGO63zS8fWVbGLktsIuYCfehvu', 'dwi mawar', 'mawar123@gmail.com', 'petugas_rekam_medis', 'Instalasi Gawat Darurat (IGD)', 'assets/img/avatar.png', '2026-11-06 12:22:33');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `data_masuk`
--
ALTER TABLE `data_masuk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_registrasi` (`no_registrasi`);

--
-- Indeks untuk tabel `data_terpadu`
--
ALTER TABLE `data_terpadu`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kirim_sirs`
--
ALTER TABLE `kirim_sirs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transmisi` (`kode_transmisi`);

--
-- Indeks untuk tabel `notifikasi_alert`
--
ALTER TABLE `notifikasi_alert`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `sirs_config`
--
ALTER TABLE `sirs_config`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `sumber_data_unit`
--
ALTER TABLE `sumber_data_unit`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_unit` (`kode_unit`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `data_masuk`
--
ALTER TABLE `data_masuk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `data_terpadu`
--
ALTER TABLE `data_terpadu`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `kirim_sirs`
--
ALTER TABLE `kirim_sirs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `notifikasi_alert`
--
ALTER TABLE `notifikasi_alert`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `sumber_data_unit`
--
ALTER TABLE `sumber_data_unit`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
