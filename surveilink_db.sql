-- ========================================================
-- Database Schema: SurveiLink Patient Registration & Surveillance System
-- Kasus 2: Integrasi & Sinkronisasi Data Surveilans Rumah Sakit
-- Single Source of Truth & Sinkronisasi SIRS Kemenkes
-- ========================================================

CREATE DATABASE IF NOT EXISTS `surveilink_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `surveilink_db`;

-- Drop existing tables to ensure clean state
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_log`;
DROP TABLE IF EXISTS `notifikasi_alert`;
DROP TABLE IF EXISTS `kirim_sirs`;
DROP TABLE IF EXISTS `data_terpadu`;
DROP TABLE IF EXISTS `data_masuk`;
DROP TABLE IF EXISTS `sumber_data_unit`;
DROP TABLE IF EXISTS `sirs_config`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Table: users (Petugas & Administrator RS)
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `role` ENUM('admin_surveilans', 'petugas_rekam_medis', 'admin_unit', 'supervisor') NOT NULL DEFAULT 'admin_surveilans',
    `unit` VARCHAR(100) NOT NULL DEFAULT 'Instalasi Epidemiologi & Surveilans',
    `foto` VARCHAR(255) DEFAULT 'assets/img/avatar.png',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default user (admin / admin123)
-- Password hash for 'admin123'
INSERT INTO `users` (`username`, `password`, `nama_lengkap`, `email`, `role`, `unit`) VALUES
('admin', '$2y$10$fQ7J1mY6n4uY9F4oT8rG8O7Wk5Rk9w1u6Wn7n6P0p8G3G4q7Y7F7q', 'dr. Hendra Pratama, Sp.A, M.Kes', 'hendra.surveilans@rsudmitra.go.id', 'admin_surveilans', 'Instalasi Epidemiologi & Surveilans RS'),
('petugas_rm', '$2y$10$fQ7J1mY6n4uY9F4oT8rG8O7Wk5Rk9w1u6Wn7n6P0p8G3G4q7Y7F7q', 'Siti Rahmawati, A.Md.RMIK', 'siti.rm@rsudmitra.go.id', 'petugas_rekam_medis', 'Instalasi Rekam Medis'),
('unit_igd', '$2y$10$fQ7J1mY6n4uY9F4oT8rG8O7Wk5Rk9w1u6Wn7n6P0p8G3G4q7Y7F7q', 'Ns. Arif Wicaksono, S.Kep', 'arif.igd@rsudmitra.go.id', 'admin_unit', 'Instalasi Gawat Darurat (IGD)');

-- 2. Table: sirs_config (Pengaturan Akun SIRS Kemenkes)
CREATE TABLE `sirs_config` (
    `id` INT PRIMARY KEY,
    `kode_rs` VARCHAR(20) NOT NULL DEFAULT '3171012',
    `nama_rs` VARCHAR(150) NOT NULL DEFAULT 'RSUD Mitra Sehat Kemenkes',
    `kelas_rs` VARCHAR(50) NOT NULL DEFAULT 'Kelas B Pendidikan',
    `api_endpoint` VARCHAR(255) NOT NULL DEFAULT 'https://sirs.kemkes.go.id/fo/api/v2/surveilans',
    `api_key` VARCHAR(100) NOT NULL DEFAULT 'KEMKES_SIRS_LIVE_882949',
    `secret_key` VARCHAR(100) NOT NULL DEFAULT 'SECRET_SIRS_KEMKES_2026',
    `status_koneksi` VARCHAR(50) NOT NULL DEFAULT 'Tersambung (LIVE API v2)',
    `last_ping` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sirs_config` (`id`, `kode_rs`, `nama_rs`, `kelas_rs`, `api_endpoint`, `api_key`, `secret_key`, `status_koneksi`, `last_ping`)
VALUES (1, '3171012', 'RSUD Mitra Sehat Kemenkes', 'Kelas B', 'https://sirs.kemkes.go.id/fo/api/v2/surveilans', 'KEMKES_SIRS_LIVE_882949', 'SECRET_SIRS_KEMKES_2026', 'Tersambung (LIVE API v2)', NOW());

-- 3. Table: sumber_data_unit (Status Integrasi 6 Unit RS)
CREATE TABLE `sumber_data_unit` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kode_unit` VARCHAR(20) NOT NULL UNIQUE,
    `nama_unit` VARCHAR(100) NOT NULL,
    `tipe_sistem` VARCHAR(50) NOT NULL,
    `lokasi_server` VARCHAR(100) NOT NULL,
    `endpoint_url` VARCHAR(255) NOT NULL,
    `status_koneksi` ENUM('Connected', 'Disconnected', 'Warning', 'Syncing') NOT NULL DEFAULT 'Connected',
    `latency_ms` INT NOT NULL DEFAULT 14,
    `terakhir_sinkron` DATETIME NULL,
    `total_data_masuk` INT NOT NULL DEFAULT 0,
    `total_anomali` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed unit sumber data dengan total data = 0
INSERT INTO `sumber_data_unit` (`kode_unit`, `nama_unit`, `tipe_sistem`, `lokasi_server`, `endpoint_url`, `status_koneksi`, `latency_ms`, `terakhir_sinkron`, `total_data_masuk`, `total_anomali`) VALUES
('IGD', 'Instalasi Gawat Darurat', 'SIMRS IGD v4.2', '192.168.10.12:8080/api/v1/patients', 'http://192.168.10.12/simrs-igd/api', 'Connected', 12, NOW(), 0, 0),
('RAJAL', 'Poliklinik Rawat Jalan', 'SIMRS Poliklinik Terpadu', '192.168.10.14:8080/api/v1/rajal', 'http://192.168.10.14/simrs-rajal/api', 'Connected', 15, NOW(), 0, 0),
('RANAP', 'Instalasi Rawat Inap & Bangsal', 'SIMRS Inpatient Care', '192.168.10.15:8080/api/v1/ranap', 'http://192.168.10.15/simrs-ranap/api', 'Connected', 18, NOW(), 0, 0),
('LAB', 'Laboratorium Patologi Klinik', 'LIS MediLab Express', '192.168.20.10:3000/api/results', 'http://192.168.20.10/lis/api', 'Connected', 9, NOW(), 0, 0),
('FARM', 'Depo Farmasi & Terapi Obat', 'FIS MediPharma Hospital', '192.168.20.25:8000/api/dispense', 'http://192.168.20.25/fis/api', 'Connected', 11, NOW(), 0, 0),
('ICU', 'Ruang Isolasi & Intensive Care', 'SISR Critical Surveillance', '192.168.30.5:9000/api/icu', 'http://192.168.30.5/sisr/api', 'Connected', 14, NOW(), 0, 0);

-- 4. Table: data_masuk (Staging Data Mentah Masuk dari Unit RS)
-- Dimulai dengan 0 data (kosong)
CREATE TABLE `data_masuk` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `no_registrasi` VARCHAR(50) NOT NULL UNIQUE,
    `unit_sumber` VARCHAR(50) NOT NULL,
    `no_rm` VARCHAR(20) NOT NULL,
    `nik` VARCHAR(20) NOT NULL,
    `nama_pasien` VARCHAR(100) NOT NULL,
    `jenis_kelamin` ENUM('L', 'P') NOT NULL DEFAULT 'L',
    `cara_pembayaran` VARCHAR(50) NOT NULL DEFAULT 'Pembayaran BPJS',
    `tempat_lahir` VARCHAR(100) NOT NULL DEFAULT 'Jakarta',
    `tanggal_lahir` DATE NOT NULL,
    `usia` INT NOT NULL,
    `agama` VARCHAR(50) NOT NULL DEFAULT 'Islam',
    `status_perkawinan` VARCHAR(50) NOT NULL DEFAULT 'Kawin',
    `alamat` TEXT NOT NULL,
    `kelurahan` VARCHAR(100) DEFAULT '',
    `kecamatan` VARCHAR(100) DEFAULT '',
    `kota_kab` VARCHAR(100) DEFAULT 'Jakarta Pusat',
    `tanggal_masuk` DATETIME NOT NULL,
    `diagnosa_awal` VARCHAR(255) DEFAULT 'Dalam Pemeriksaan Klinis',
    `kode_icd10` VARCHAR(10) DEFAULT '-',
    `kategori_penyakit` VARCHAR(100) DEFAULT 'Menunggu Diagnosis',
    `hasil_lab` VARCHAR(255) DEFAULT NULL,
    `terapi_farmasi` TEXT DEFAULT NULL,
    `status_kelengkapan` ENUM('Lengkap', 'Tidak Lengkap', 'Duplikat', 'Anomali') NOT NULL DEFAULT 'Tidak Lengkap',
    `status_validasi` ENUM('Menunggu', 'Valid', 'Ditolak', 'Konflik') NOT NULL DEFAULT 'Menunggu',
    `catatan_anomali` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Table: data_terpadu (Golden Record - Single Source of Truth)
-- Dimulai dengan 0 data (kosong)
CREATE TABLE `data_terpadu` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `data_masuk_id` INT NULL,
    `no_rm` VARCHAR(20) NOT NULL,
    `nik` VARCHAR(20) NOT NULL,
    `nama_pasien` VARCHAR(100) NOT NULL,
    `jenis_kelamin` ENUM('L', 'P') NOT NULL,
    `cara_pembayaran` VARCHAR(50) NOT NULL DEFAULT 'Pembayaran BPJS',
    `tempat_lahir` VARCHAR(100) NOT NULL DEFAULT 'Jakarta',
    `tanggal_lahir` DATE NOT NULL,
    `usia` INT NOT NULL,
    `agama` VARCHAR(50) NOT NULL DEFAULT 'Islam',
    `status_perkawinan` VARCHAR(50) NOT NULL DEFAULT 'Kawin',
    `alamat_lengkap` TEXT NOT NULL,
    `unit_layanan` VARCHAR(100) NOT NULL,
    `tanggal_masuk` DATETIME NOT NULL,
    `tanggal_keluar` DATETIME NULL,
    `kondisi_keluar` VARCHAR(50) DEFAULT 'Dalam Perawatan',
    `kode_icd10` VARCHAR(10) NOT NULL,
    `nama_penyakit` VARCHAR(255) NOT NULL,
    `kategori_surveilans` VARCHAR(100) NOT NULL,
    `hasil_lab_terpadu` VARCHAR(255) DEFAULT NULL,
    `status_sirs` ENUM('Belum Dikirim', 'Dalam Antrean', 'Terkirim', 'Gagal') NOT NULL DEFAULT 'Belum Dikirim',
    `tgl_validasi` DATETIME NULL,
    `petugas_validasi` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Table: kirim_sirs (Log Transmisi Pelaporan ke SIRS Kemenkes)
-- Dimulai dengan 0 data (kosong)
CREATE TABLE `kirim_sirs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kode_transmisi` VARCHAR(50) NOT NULL UNIQUE,
    `jenis_laporan` VARCHAR(100) NOT NULL,
    `periode_laporan` VARCHAR(50) NOT NULL,
    `jumlah_data` INT NOT NULL DEFAULT 0,
    `payload_json` LONGTEXT NULL,
    `response_code` VARCHAR(50) NULL,
    `response_message` TEXT NULL,
    `nomor_referensi_sirs` VARCHAR(100) NULL,
    `status_pengiriman` ENUM('Sukses', 'Gagal', 'Pending', 'Antrean') NOT NULL DEFAULT 'Pending',
    `dikirim_oleh` VARCHAR(100) NOT NULL,
    `waktu_pengiriman` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Table: notifikasi_alert (Sistem Peringatan Dini EWARS & Anomali)
-- Dimulai dengan 0 data (kosong)
CREATE TABLE `notifikasi_alert` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tipe_alert` ENUM('EWARS_KLB', 'INTEGRASI_TERPUTUS', 'VALIDASI_ANOMALI', 'SIRS_FEEDBACK', 'INFO') NOT NULL,
    `judul` VARCHAR(255) NOT NULL,
    `pesan` TEXT NOT NULL,
    `tingkat_urgensi` ENUM('Bahaya', 'Peringatan', 'Info') NOT NULL DEFAULT 'Info',
    `unit_terkait` VARCHAR(50) DEFAULT NULL,
    `status_baca` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Table: audit_log (Audit Trail Keamanan & Kepatuhan)
-- Dimulai dengan 0 data (kosong)
CREATE TABLE `audit_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `username` VARCHAR(50) NOT NULL,
    `role` VARCHAR(50) NOT NULL,
    `aktivitas` VARCHAR(100) NOT NULL,
    `modul` VARCHAR(50) NOT NULL,
    `detail` TEXT NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `waktu` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
