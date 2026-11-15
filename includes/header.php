<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// Auto-seed session if not set for seamless demo navigation
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'id' => 1,
        'username' => 'admin',
        'nama_lengkap' => 'dr. Hendra Pratama, Sp.A, M.Kes',
        'role' => 'admin_surveilans',
        'role_label' => 'Administrator Epidemiologi & Surveilans',
        'unit' => 'Instalasi Epidemiologi RS'
    ];
}

$pdo = getDB();

// Get real-time counts for badges
$totalDataMasuk = $pdo->query("SELECT COUNT(*) FROM data_masuk")->fetchColumn();
$totalMenungguValidasi = $pdo->query("SELECT COUNT(*) FROM data_masuk WHERE status_validasi = 'Menunggu'")->fetchColumn();
$totalDataTerpadu = $pdo->query("SELECT COUNT(*) FROM data_terpadu")->fetchColumn();
$totalKirimSirs = $pdo->query("SELECT COUNT(*) FROM kirim_sirs")->fetchColumn();
$totalNotifBelumDibaca = $pdo->query("SELECT COUNT(*) FROM notifikasi_alert WHERE status_baca = 0")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Active page helper
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'SurveiLink - Integrasi Surveilans RS & SIRS Kemenkes' ?></title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#075985',
                            800: '#0c4a6e',
                            900: '#082f49',
                        },
                        kemenkes: {
                            teal: '#009688',
                            dark: '#004d40',
                            gold: '#f59e0b'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- SweetAlert2 CDN for modern notifications -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .badge-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .75; transform: scale(1.05); }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased flex flex-col min-h-screen">

