<?php
/**
 * SurveiLink - Database Configuration
 * Koneksi MySQL via PDO (Laragon / phpMyAdmin)
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'surveilink_db');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Koneksi Database Gagal: " . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

// Helper untuk catat Audit Log
function addAuditLog($aktivitas, $modul, $detail, $user_id = null, $username = null, $role = null) {
    try {
        $pdo = getDB();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId   = $user_id ?? ($_SESSION['user']['id'] ?? 1);
        $userName = $username ?? ($_SESSION['user']['username'] ?? 'admin');
        $userRole = $role ?? ($_SESSION['user']['role'] ?? 'admin_surveilans');
        $ip       = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, username, role, aktivitas, modul, detail, ip_address, waktu) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $userName, $userRole, $aktivitas, $modul, $detail, $ip]);
    } catch (Exception $e) {
        // Silent fail for logging to not break flow
    }
}

// Helper untuk tambah Notifikasi Alert
function addNotification($tipe, $judul, $pesan, $urgensi = 'Info', $unit = null) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO notifikasi_alert (tipe_alert, judul, pesan, tingkat_urgensi, unit_terkait, status_baca, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
        $stmt->execute([$tipe, $judul, $pesan, $urgensi, $unit]);
    } catch (Exception $e) {
        // Silent fail
    }
}

