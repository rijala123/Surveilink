<?php
/**
 * Halaman Tersendiri: Kelola & Atur Akun Seluruh User
 * Diakses melalui tautan khusus pada halaman login.
 * Kasus 2: Integrasi & Sinkronisasi Data Surveilans RS - SIRS Kemenkes
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/database.php';
$pdo = getDB();

// Parameters filter & pencarian
$search     = trim($_GET['search'] ?? '');
$filterRole = trim($_GET['role'] ?? '');
$filterUnit = trim($_GET['unit'] ?? '');

// Query daftar seluruh pengguna
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (nama_lengkap LIKE ? OR username LIKE ? OR email LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if (!empty($filterRole)) {
    $sql .= " AND role = ?";
    $params[] = $filterRole;
}

if (!empty($filterUnit)) {
    $sql .= " AND unit LIKE ?";
    $params[] = "%$filterUnit%";
}

$sql .= " ORDER BY id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Statistik ringkas per kategori peran
$statTotal      = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$statAdminSurv  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin_surveilans'")->fetchColumn();
$statPetugasRM  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'petugas_rekam_medis'")->fetchColumn();
$statAdminUnit  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin_unit'")->fetchColumn();
$statSupervisor = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'supervisor'")->fetchColumn();

// Daftar unit kerja standar di RS
$unitOptions = [
    'Instalasi Epidemiologi & Surveilans RS',
    'Instalasi Rekam Medis',
    'Instalasi Gawat Darurat (IGD)',
    'Bangsal Rawat Inap',
    'Instalasi Rawat Jalan',
    'Laboratorium Patologi Klinik',
    'Instalasi Farmasi',
    'Direksi & Manajemen Medis'
];

// Helper visual badge peran
function getRoleBadge($role) {
    switch ($role) {
        case 'admin_surveilans':
            return [
                'label' => 'Admin Surveilans (Akses Penuh)',
                'badge' => 'bg-purple-100 text-purple-800 border-purple-200',
                'dot'   => 'bg-purple-500',
                'icon'  => 'fa-shield-halved'
            ];
        case 'petugas_rekam_medis':
            return [
                'label' => 'Petugas Rekam Medis',
                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'dot'   => 'bg-emerald-500',
                'icon'  => 'fa-clipboard-check'
            ];
        case 'admin_unit':
            return [
                'label' => 'Admin Unit RS (IGD/Lab/Bangsal)',
                'badge' => 'bg-sky-100 text-sky-800 border-sky-200',
                'dot'   => 'bg-sky-500',
                'icon'  => 'fa-hospital-user'
            ];
        case 'supervisor':
            return [
                'label' => 'Supervisor / Direktur Medik',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-200',
                'dot'   => 'bg-amber-500',
                'icon'  => 'fa-user-tie'
            ];
        default:
            return [
                'label' => ucfirst(str_replace('_', ' ', $role)),
                'badge' => 'bg-slate-100 text-slate-700 border-slate-200',
                'dot'   => 'bg-slate-400',
                'icon'  => 'fa-user'
            ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun Seluruh User - SurveiLink</title>
    
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
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 CDN -->
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
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Standalone Header Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand & Page Identity -->
            <div class="flex items-center space-x-3">
                <a href="login.php" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-700 to-brand-500 flex items-center justify-center text-white shadow-md shadow-brand-500/20 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-heart-pulse text-lg"></i>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-lg font-bold tracking-tight text-slate-900">Survei<span class="text-brand-600">Link</span></span>
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-purple-100 text-purple-800 rounded-full border border-purple-200">
                                <i class="fa-solid fa-users-gear mr-1"></i>ADMIN AKUN
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 hidden sm:block">Pengaturan Akun Petugas & Hak Akses Unit RS</p>
                    </div>
                </a>
            </div>

            <!-- Center Info: Hospital Identity -->
            <div class="hidden md:flex items-center space-x-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200 text-xs text-slate-600">
                <i class="fa-solid fa-hospital text-brand-600"></i>
                <span class="font-medium">RSUD Mitra Sehat</span>
                <span class="text-slate-300">|</span>
                <span class="text-[11px] text-slate-500">Faskes: 3171012</span>
            </div>

            <!-- Right Actions: Back to Login & Dashboard -->
            <div class="flex items-center space-x-2">
                <a href="login.php" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                    <span>Kembali ke Halaman Login</span>
                </a>
                <a href="dashboard.php" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-xl bg-brand-50 text-brand-700 hover:bg-brand-100 border border-brand-200 transition">
                    <i class="fa-solid fa-chart-pie text-[11px]"></i>
                    <span class="hidden sm:inline">Buka Dashboard</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container (Full Standalone Width) -->
    <main class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-6 flex-1">
        
        <!-- Breadcrumbs & Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="login.php" class="hover:text-brand-600 transition flex items-center gap-1">
                        <i class="fa-solid fa-right-to-bracket text-[10px]"></i> Halaman Login
                    </a>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">Pengaturan Akun Pengguna</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pengaturan & Kelola Seluruh Akun User</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                        <?= count($users) ?> Akun Terdaftar
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Ubah nama lengkap, peran wewenang (role), unit penugasan, email, dan kata sandi akun petugas seluruh sistem.</p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button onclick="bukaModalTambah()" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm shadow-brand-500/20 transition inline-flex items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Tambah Akun Baru</span>
                </button>
            </div>
        </div>

        <!-- Banner Info Standalone Access -->
        <div class="p-4 rounded-2xl bg-gradient-to-r from-brand-50 via-sky-50 to-indigo-50 border border-brand-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div class="flex items-start sm:items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-brand-500/25">
                    <i class="fa-solid fa-shield-halved text-sm"></i>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800">Halaman Mandiri Manajemen Akun & Hak Akses Pengguna</h4>
                    <p class="text-slate-600 mt-0.5">Halaman ini berjalan mandiri (tanpa sidebar) dan diakses dari link kecil di formulir login untuk kemudahan konfigurasi akun dan simulasi peran.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3 py-1 rounded-lg bg-white border border-brand-200 font-mono text-[11px] text-brand-800 shadow-xs">
                    <i class="fa-solid fa-key mr-1 text-brand-600"></i>Sandi Standar: admin123
                </span>
            </div>
        </div>

        <!-- Ringkasan Statistik Kartu Pengguna -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
            <!-- Total User -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-base">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-slate-500">Total Akun</p>
                    <p class="text-xl font-bold text-slate-900"><?= $statTotal ?></p>
                </div>
            </div>

            <!-- Admin Surveilans -->
            <div class="p-4 rounded-2xl bg-white border border-purple-100 shadow-xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-purple-600">Admin Surveilans</p>
                    <p class="text-xl font-bold text-purple-950"><?= $statAdminSurv ?></p>
                </div>
            </div>

            <!-- Petugas Rekam Medis -->
            <div class="p-4 rounded-2xl bg-white border border-emerald-100 shadow-xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-emerald-600">Petugas RM</p>
                    <p class="text-xl font-bold text-emerald-950"><?= $statPetugasRM ?></p>
                </div>
            </div>

            <!-- Admin Unit RS -->
            <div class="p-4 rounded-2xl bg-white border border-sky-100 shadow-xs flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-hospital-user"></i>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-sky-600">Admin Unit RS</p>
                    <p class="text-xl font-bold text-sky-950"><?= $statAdminUnit ?></p>
                </div>
            </div>

            <!-- Supervisor -->
            <div class="p-4 rounded-2xl bg-white border border-amber-100 shadow-xs flex items-center gap-3 col-span-2 sm:col-span-1">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-amber-600">Supervisor / Direksi</p>
                    <p class="text-xl font-bold text-amber-950"><?= $statSupervisor ?></p>
                </div>
            </div>
        </div>

        <!-- Filter & Pencarian Box -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="kelola_user.php" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                <!-- Search Input -->
                <div class="sm:col-span-5 relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 placeholder:text-slate-400 bg-slate-50/50 hover:bg-white transition"
                        placeholder="Cari nama lengkap, username, atau email...">
                </div>

                <!-- Filter Peran / Role -->
                <div class="sm:col-span-3">
                    <select name="role" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-slate-50/50 hover:bg-white transition">
                        <option value="">Semua Peran / Role</option>
                        <option value="admin_surveilans" <?= $filterRole === 'admin_surveilans' ? 'selected' : '' ?>>Admin Surveilans RS (Akses Penuh)</option>
                        <option value="petugas_rekam_medis" <?= $filterRole === 'petugas_rekam_medis' ? 'selected' : '' ?>>Petugas Rekam Medis (Validasi)</option>
                        <option value="admin_unit" <?= $filterRole === 'admin_unit' ? 'selected' : '' ?>>Admin Unit RS (IGD/Lab/Bangsal)</option>
                        <option value="supervisor" <?= $filterRole === 'supervisor' ? 'selected' : '' ?>>Supervisor / Direktur Medik</option>
                    </select>
                </div>

                <!-- Filter Unit -->
                <div class="sm:col-span-3">
                    <select name="unit" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-slate-50/50 hover:bg-white transition">
                        <option value="">Semua Unit Penugasan</option>
                        <?php foreach ($unitOptions as $uOpt): ?>
                            <option value="<?= htmlspecialchars($uOpt) ?>" <?= $filterUnit === $uOpt ? 'selected' : '' ?>>
                                <?= htmlspecialchars($uOpt) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="sm:col-span-1 flex items-center gap-1.5">
                    <button type="submit" class="w-full py-2 px-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold transition flex items-center justify-center gap-1 shadow-xs" title="Terapkan Filter">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    <?php if (!empty($search) || !empty($filterRole) || !empty($filterUnit)): ?>
                        <a href="kelola_user.php" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs transition" title="Reset Filter">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Seluruh Pengguna -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-address-book text-brand-600"></i>
                    <h3 class="font-bold text-slate-900 text-sm">Daftar Akun Petugas Rumah Sakit</h3>
                    <span class="text-xs text-slate-400">• Total <?= count($users) ?> Akun</span>
                </div>
                <div class="text-xs text-slate-500">
                    Klik tombol <span class="font-semibold text-amber-600"><i class="fa-solid fa-pen-to-square"></i> Edit</span> untuk mengubah Nama, Peran (Role), Unit, atau Sandi.
                </div>
            </div>

            <?php if (empty($users)): ?>
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-user-slash"></i>
                    </div>
                    <h4 class="font-bold text-slate-700 text-sm">Tidak Ada Akun Ditemukan</h4>
                    <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau reset filter yang dipilih.</p>
                    <a href="kelola_user.php" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Reset Semua Filter</span>
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50/70 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5">ID & Pengguna</th>
                                <th class="px-6 py-3.5">Nama Lengkap & Email</th>
                                <th class="px-6 py-3.5">Peran / Role Akses</th>
                                <th class="px-6 py-3.5">Unit Penugasan</th>
                                <th class="px-6 py-3.5">Terdaftar Sejak</th>
                                <th class="px-6 py-3.5 text-center">Aksi Pengelolaan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($users as $u): ?>
                                <?php 
                                    $roleInfo = getRoleBadge($u['role']);
                                    $isPrimaryAdmin = ($u['id'] == 1);
                                    
                                    // Ambil inisial nama
                                    $words = explode(' ', trim($u['nama_lengkap']));
                                    $initials = '';
                                    foreach (array_slice($words, 0, 2) as $w) {
                                        $initials .= strtoupper(substr($w, 0, 1));
                                    }
                                    if (empty($initials)) $initials = 'US';
                                ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <!-- ID & Avatar & Username -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-slate-700 to-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                                <?= $initials ?>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-slate-900"><?= htmlspecialchars($u['username']) ?></span>
                                                    <span class="text-[10px] text-slate-400 font-mono">#<?= $u['id'] ?></span>
                                                    <?php if ($isPrimaryAdmin): ?>
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700" title="Administrator Utama Sistem">
                                                            Master
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="text-[10px] text-slate-400">Pegawai RSUD Mitra Sehat</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Nama Lengkap & Email -->
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-slate-900"><?= htmlspecialchars($u['nama_lengkap']) ?></p>
                                        <div class="flex items-center gap-1.5 text-slate-500 mt-0.5 text-[11px]">
                                            <i class="fa-regular fa-envelope text-[10px] text-slate-400"></i>
                                            <span><?= htmlspecialchars($u['email']) ?></span>
                                        </div>
                                    </td>

                                    <!-- Peran / Role -->
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border <?= $roleInfo['badge'] ?>">
                                            <span class="w-1.5 h-1.5 rounded-full <?= $roleInfo['dot'] ?>"></span>
                                            <i class="fa-solid <?= $roleInfo['icon'] ?> text-[10px]"></i>
                                            <span><?= htmlspecialchars($roleInfo['label']) ?></span>
                                        </span>
                                    </td>

                                    <!-- Unit Penugasan -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2 text-slate-700">
                                            <i class="fa-regular fa-hospital text-brand-500 text-xs"></i>
                                            <span class="font-medium"><?= htmlspecialchars($u['unit']) ?></span>
                                        </div>
                                    </td>

                                    <!-- Tanggal Pendaftaran -->
                                    <td class="px-6 py-4 text-slate-500 text-[11px]">
                                        <div class="flex items-center gap-1.5">
                                            <i class="fa-regular fa-calendar text-slate-400"></i>
                                            <span><?= !empty($u['created_at']) ? date('d M Y, H:i', strtotime($u['created_at'])) : '-' ?></span>
                                        </div>
                                    </td>

                                    <!-- Aksi Pengelolaan -->
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <!-- Tombol Edit Akun (Nama, Role, Unit, dll.) -->
                                            <button onclick='bukaModalEdit(<?= json_encode($u) ?>)'
                                                class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-semibold transition flex items-center gap-1.5 shadow-xs"
                                                title="Edit Akun (Ubah Nama, Peran/Role, Unit, dll.)">
                                                <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                                <span>Edit</span>
                                            </button>

                                            <!-- Tombol Ganti Sandi Cepat -->
                                            <button onclick="bukaModalGantiPassword(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['username'])) ?>', '<?= htmlspecialchars(addslashes($u['nama_lengkap'])) ?>')"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 border border-slate-200 transition"
                                                title="Ganti / Reset Kata Sandi">
                                                <i class="fa-solid fa-key text-xs"></i>
                                            </button>

                                            <!-- Tombol Hapus Akun -->
                                            <?php if (!$isPrimaryAdmin): ?>
                                                <button onclick="hapusUser(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['nama_lengkap'])) ?>', '<?= htmlspecialchars(addslashes($u['username'])) ?>')"
                                                    class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition"
                                                    title="Hapus Akun Pengguna">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            <?php else: ?>
                                                <span class="p-1.5 text-slate-300 cursor-not-allowed" title="Admin master (ID 1) dilindungi">
                                                    <i class="fa-solid fa-lock text-xs"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Panduan Hak Akses Peran SurveiLink -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center gap-2 text-slate-900 font-bold text-sm">
                <i class="fa-solid fa-circle-info text-brand-600"></i>
                <span>Wewenang Peran (Role) Petugas Surveilans Rumah Sakit:</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div class="p-3.5 rounded-xl bg-purple-50/70 border border-purple-100 space-y-1.5">
                    <div class="flex items-center gap-2 font-bold text-purple-900">
                        <i class="fa-solid fa-shield-halved text-purple-600"></i>
                        <span>Admin Surveilans RS</span>
                    </div>
                    <p class="text-slate-600 leading-relaxed">Wewenang penuh master: validasi epidemiologi, sinkronisasi transmisi SIRS Kemenkes, audit log, dan integrasi unit.</p>
                </div>
                <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-100 space-y-1.5">
                    <div class="flex items-center gap-2 font-bold text-emerald-900">
                        <i class="fa-solid fa-clipboard-check text-emerald-600"></i>
                        <span>Petugas Rekam Medis</span>
                    </div>
                    <p class="text-slate-600 leading-relaxed">Wewenang validasi nomor RM, NIK Dukcapil, koding ICD-10, dan verifikasi kelengkapan identitas pasien surveilans.</p>
                </div>
                <div class="p-3.5 rounded-xl bg-sky-50/70 border border-sky-100 space-y-1.5">
                    <div class="flex items-center gap-2 font-bold text-sky-900">
                        <i class="fa-solid fa-hospital-user text-sky-600"></i>
                        <span>Admin Unit RS (IGD/Lab)</span>
                    </div>
                    <p class="text-slate-600 leading-relaxed">Wewenang input pasien dari unit terkait (IGD, Bangsal, Lab, Rajal, Farmasi) dan pemantauan staging unit masing-masing.</p>
                </div>
                <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-100 space-y-1.5">
                    <div class="flex items-center gap-2 font-bold text-amber-900">
                        <i class="fa-solid fa-user-tie text-amber-600"></i>
                        <span>Supervisor & Direksi</span>
                    </div>
                    <p class="text-slate-600 leading-relaxed">Wewenang supervisi eksekutif: pemantauan tren epidemiologi KLB, grafik agregat penyakit, dan riwayat transmisi SIRS.</p>
                </div>
            </div>
        </div>

    </main>

    <!-- Standalone Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-800">SurveiLink</span>
                <span>• Sistem Integrasi Surveilans & Registrasi Pasien RS</span>
            </div>
            <div class="flex items-center gap-4 text-[11px]">
                <a href="login.php" class="text-brand-600 hover:text-brand-700 hover:underline">Halaman Login</a>
                <span>•</span>
                <a href="dashboard.php" class="text-brand-600 hover:text-brand-700 hover:underline">Dashboard Utama</a>
                <span>•</span>
                <span>RSUD Mitra Sehat &copy; <?= date('Y') ?></span>
            </div>
        </div>
    </footer>

    <!-- ========================================================================= -->
    <!-- MODAL 1: EDIT AKUN PENGGUNA (UBAH NAMA, ROLE, UNIT, EMAIL, SANDI) -->
    <!-- ========================================================================= -->
    <div id="modalEditUser" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-xl w-full max-h-[90vh] overflow-y-auto custom-scrollbar shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white/95 backdrop-blur-xs z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shadow-xs">
                        <i class="fa-solid fa-user-pen"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Ubah Data Akun Pengguna</h3>
                        <p class="text-xs text-slate-500">Perbarui nama lengkap, peran wewenang (role), unit, atau kata sandi.</p>
                    </div>
                </div>
                <button type="button" onclick="tutupModalEdit()" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-100 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formEditUser" onsubmit="simpanEditUser(event)" class="p-6 space-y-4">
                <input type="hidden" id="edit_id" name="id">

                <!-- ID Akun Info -->
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-slate-500">ID Pengguna:</span>
                        <span id="display_edit_id" class="font-mono font-bold text-slate-800 ml-1">#1</span>
                    </div>
                    <div>
                        <span class="text-slate-500">Status Akun:</span>
                        <span class="inline-flex items-center gap-1 font-semibold text-emerald-600 ml-1">
                            <i class="fa-solid fa-circle-check text-[10px]"></i> Aktif
                        </span>
                    </div>
                </div>

                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="edit_nama_lengkap">
                        Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-regular fa-user"></i>
                        </span>
                        <input type="text" id="edit_nama_lengkap" name="nama_lengkap" required
                            class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                            placeholder="Contoh: dr. Hendra Pratama, Sp.A, M.Kes">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Username -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="edit_username">
                            Username Login <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-solid fa-at"></i>
                            </span>
                            <input type="text" id="edit_username" name="username" required
                                class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                                placeholder="username_petugas">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="edit_email">
                            Alamat Email Resmi <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <input type="email" id="edit_email" name="email" required
                                class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                                placeholder="nama@rsudmitra.go.id">
                        </div>
                    </div>
                </div>

                <!-- Peran / Role Akses -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="edit_role">
                        Peran / Role Akses Sistem <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-id-badge"></i>
                        </span>
                        <select id="edit_role" name="role" required
                            class="w-full pl-10 pr-8 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition bg-slate-50/50 hover:bg-white font-medium text-slate-800">
                            <option value="admin_surveilans">Admin Epidemiologi & Surveilans RS (Akses Penuh Master)</option>
                            <option value="petugas_rekam_medis">Petugas Rekam Medis (Validasi Data & Koding ICD-10)</option>
                            <option value="admin_unit">Admin Unit RS (IGD / Bangsal / Lab / Rajal / Farmasi)</option>
                            <option value="supervisor">Supervisor / Direktur Medik & Eksekutif Kemenkes</option>
                        </select>
                    </div>
                </div>

                <!-- Unit Penugasan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="edit_unit">
                        Unit / Departemen Penugasan <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-regular fa-hospital"></i>
                        </span>
                        <select id="edit_unit" name="unit" required
                            class="w-full pl-10 pr-8 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition bg-slate-50/50 hover:bg-white font-medium text-slate-800">
                            <?php foreach ($unitOptions as $uOpt): ?>
                                <option value="<?= htmlspecialchars($uOpt) ?>"><?= htmlspecialchars($uOpt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Ubah Password (Opsional) -->
                <div class="pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-700" for="edit_password">
                            Ubah Kata Sandi Baru (Opsional)
                        </label>
                        <span class="text-[10px] text-slate-400">Kosongkan jika tidak ingin mengubah sandi</span>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="edit_password" name="password" minlength="6"
                            class="w-full pl-10 pr-10 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                            placeholder="Ketik kata sandi baru (minimal 6 karakter)">
                        <button type="button" onclick="togglePasswordVisibility('edit_password', 'eyeIconEdit')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i id="eyeIconEdit" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" onclick="tutupModalEdit()" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-md shadow-brand-500/20 transition flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Perubahan Akun</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: TAMBAH AKUN PENGGUNA BARU -->
    <!-- ========================================================================= -->
    <div id="modalTambahUser" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-xl w-full max-h-[90vh] overflow-y-auto custom-scrollbar shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white/95 backdrop-blur-xs z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center text-lg shadow-xs">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Tambah Akun Petugas Baru</h3>
                        <p class="text-xs text-slate-500">Daftarkan akun pegawai baru dengan peran dan unit yang sesuai.</p>
                    </div>
                </div>
                <button type="button" onclick="tutupModalTambah()" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-100 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formTambahUser" onsubmit="simpanTambahUser(event)" class="p-6 space-y-4">
                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="tambah_nama_lengkap">
                        Nama Lengkap & Gelar Pegawai <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-regular fa-user"></i>
                        </span>
                        <input type="text" id="tambah_nama_lengkap" name="nama_lengkap" required
                            class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                            placeholder="Contoh: dr. Anita Kusuma, Sp.PK">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Username -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="tambah_username">
                            Username Login <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-solid fa-at"></i>
                            </span>
                            <input type="text" id="tambah_username" name="username" required
                                class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                                placeholder="anita_lab">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="tambah_email">
                            Alamat Email Resmi <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <input type="email" id="tambah_email" name="email" required
                                class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                                placeholder="anita@rsudmitra.go.id">
                        </div>
                    </div>
                </div>

                <!-- Peran / Role Akses -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="tambah_role">
                        Peran / Role Akses Sistem <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-id-badge"></i>
                        </span>
                        <select id="tambah_role" name="role" required
                            class="w-full pl-10 pr-8 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition bg-slate-50/50 hover:bg-white font-medium text-slate-800">
                            <option value="admin_unit" selected>Admin Unit RS (IGD / Bangsal / Lab / Rajal / Farmasi)</option>
                            <option value="petugas_rekam_medis">Petugas Rekam Medis (Validasi & ICD-10)</option>
                            <option value="admin_surveilans">Admin Epidemiologi & Surveilans RS (Akses Penuh)</option>
                            <option value="supervisor">Supervisor / Direktur Medik & Evaluator Kemenkes</option>
                        </select>
                    </div>
                </div>

                <!-- Unit Penugasan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="tambah_unit">
                        Unit / Departemen Penugasan <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-regular fa-hospital"></i>
                        </span>
                        <select id="tambah_unit" name="unit" required
                            class="w-full pl-10 pr-8 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition bg-slate-50/50 hover:bg-white font-medium text-slate-800">
                            <?php foreach ($unitOptions as $uOpt): ?>
                                <option value="<?= htmlspecialchars($uOpt) ?>"><?= htmlspecialchars($uOpt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-700" for="tambah_password">
                            Kata Sandi Awal <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] text-slate-400">Bawaan: admin123 (min. 6 karakter)</span>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="tambah_password" name="password" value="admin123" required minlength="6"
                            class="w-full pl-10 pr-10 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility('tambah_password', 'eyeIconTambah')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i id="eyeIconTambah" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" onclick="tutupModalTambah()" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-md shadow-brand-500/20 transition flex items-center gap-2">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Daftarkan Akun Baru</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: GANTI KATA SANDI CEPAT -->
    <!-- ========================================================================= -->
    <div id="modalGantiPassword" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg shadow-xs">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Reset Kata Sandi</h3>
                        <p class="text-xs text-slate-500" id="pwd_modal_subtitle">Atur kata sandi baru untuk pengguna.</p>
                    </div>
                </div>
                <button type="button" onclick="tutupModalGantiPassword()" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-100 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formGantiPassword" onsubmit="simpanPasswordBaru(event)" class="p-6 space-y-4">
                <input type="hidden" id="pwd_user_id">

                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-xs">
                    <p class="text-slate-500">Akun Pengguna:</p>
                    <p class="font-bold text-slate-800 text-sm mt-0.5" id="pwd_user_display">-</p>
                </div>

                <!-- Password Baru -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="pwd_baru">
                        Kata Sandi Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="pwd_baru" required minlength="6"
                            class="w-full pl-10 pr-10 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                            placeholder="Minimal 6 karakter">
                        <button type="button" onclick="togglePasswordVisibility('pwd_baru', 'eyeIconPwd1')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i id="eyeIconPwd1" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Konfirmasi Password Baru -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="pwd_konfirmasi">
                        Ulangi Kata Sandi Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-shield-halved"></i>
                        </span>
                        <input type="password" id="pwd_konfirmasi" required minlength="6"
                            class="w-full pl-10 pr-10 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                            placeholder="Ulangi kata sandi">
                        <button type="button" onclick="togglePasswordVisibility('pwd_konfirmasi', 'eyeIconPwd2')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i id="eyeIconPwd2" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" onclick="tutupModalGantiPassword()" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-md shadow-brand-500/20 transition flex items-center gap-2">
                        <i class="fa-solid fa-check"></i>
                        <span>Terapkan Sandi Baru</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript Handlers -->
    <script>
        // 1. Toggle Password Visibility
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input || !icon) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // 2. Modal Edit User Handlers
        function bukaModalEdit(user) {
            document.getElementById('edit_id').value = user.id;
            document.getElementById('display_edit_id').textContent = '#' + user.id;
            document.getElementById('edit_nama_lengkap').value = user.nama_lengkap || '';
            document.getElementById('edit_username').value = user.username || '';
            document.getElementById('edit_email').value = user.email || '';
            document.getElementById('edit_role').value = user.role || 'admin_unit';
            document.getElementById('edit_unit').value = user.unit || 'Instalasi Gawat Darurat (IGD)';
            document.getElementById('edit_password').value = '';

            document.getElementById('modalEditUser').classList.remove('hidden');
        }

        function tutupModalEdit() {
            document.getElementById('modalEditUser').classList.add('hidden');
        }

        function simpanEditUser(e) {
            e.preventDefault();
            const form = document.getElementById('formEditUser');
            const formData = new FormData(form);
            formData.append('action', 'edit_user');

            Swal.fire({
                title: 'Menyimpan Perubahan...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch('api/actions.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            timer: 1600,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Gagal Menyimpan', data.message, 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error', 'Gagal memproses ke server: ' + err.message, 'error');
                });
        }

        // 3. Modal Tambah User Handlers
        function bukaModalTambah() {
            document.getElementById('formTambahUser').reset();
            document.getElementById('tambah_password').value = 'admin123';
            document.getElementById('modalTambahUser').classList.remove('hidden');
        }

        function tutupModalTambah() {
            document.getElementById('modalTambahUser').classList.add('hidden');
        }

        function simpanTambahUser(e) {
            e.preventDefault();
            const form = document.getElementById('formTambahUser');
            const formData = new FormData(form);
            formData.append('action', 'add_user');

            Swal.fire({
                title: 'Mendaftarkan Akun Baru...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch('api/actions.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pendaftaran Berhasil!',
                            text: data.message,
                            timer: 1800,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Gagal Mendaftar', data.message, 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error', 'Gagal memproses ke server: ' + err.message, 'error');
                });
        }

        // 4. Modal Ganti Password Cepat
        function bukaModalGantiPassword(id, username, nama) {
            document.getElementById('pwd_user_id').value = id;
            document.getElementById('pwd_user_display').textContent = nama + ' (' + username + ')';
            document.getElementById('pwd_baru').value = '';
            document.getElementById('pwd_konfirmasi').value = '';
            document.getElementById('modalGantiPassword').classList.remove('hidden');
        }

        function tutupModalGantiPassword() {
            document.getElementById('modalGantiPassword').classList.add('hidden');
        }

        function simpanPasswordBaru(e) {
            e.preventDefault();
            const id = document.getElementById('pwd_user_id').value;
            const pwd = document.getElementById('pwd_baru').value;
            const konfirmasi = document.getElementById('pwd_konfirmasi').value;

            if (pwd.length < 6) {
                Swal.fire('Perhatian', 'Kata sandi minimal harus 6 karakter!', 'warning');
                return;
            }

            if (pwd !== konfirmasi) {
                Swal.fire('Perhatian', 'Konfirmasi kata sandi tidak cocok dengan kata sandi baru!', 'warning');
                return;
            }

            // Ambil data user yang ada
            const formData = new FormData();
            formData.append('action', 'get_user');
            formData.append('id', id);

            Swal.fire({
                title: 'Memproses Kata Sandi...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch('api/actions.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        throw new Error(res.message);
                    }
                    const user = res.data;
                    const updateData = new FormData();
                    updateData.append('action', 'edit_user');
                    updateData.append('id', user.id);
                    updateData.append('nama_lengkap', user.nama_lengkap);
                    updateData.append('username', user.username);
                    updateData.append('email', user.email);
                    updateData.append('role', user.role);
                    updateData.append('unit', user.unit);
                    updateData.append('password', pwd);

                    return fetch('api/actions.php', { method: 'POST', body: updateData });
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Kata Sandi Diperbarui!',
                            text: 'Kata sandi baru berhasil disimpan untuk akun tersebut.',
                            timer: 1600,
                            showConfirmButton: false
                        }).then(() => {
                            tutupModalGantiPassword();
                        });
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error', 'Gagal memperbarui kata sandi: ' + err.message, 'error');
                });
        }

        // 5. Hapus User
        function hapusUser(id, nama, username) {
            Swal.fire({
                title: 'Hapus Akun Pengguna?',
                html: `Apakah Anda yakin ingin menghapus akun <b>${nama}</b> (<code>${username}</code>)?<br><span class="text-xs text-rose-500 font-semibold">Tindakan ini tidak dapat dibatalkan.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fa-solid fa-trash-can mr-1"></i> Ya, Hapus Akun',
                cancelButtonText: 'Batal'
            }).then(res => {
                if (res.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus Akun...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    const formData = new FormData();
                    formData.append('action', 'delete_user');
                    formData.append('id', id);

                    fetch('api/actions.php', { method: 'POST', body: formData })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Akun Dihapus!',
                                    text: data.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Gagal Menghapus', data.message, 'error');
                            }
                        })
                        .catch(err => {
                            Swal.fire('Error', 'Gagal menghapus akun: ' + err.message, 'error');
                        });
                }
            });
        }
    </script>
</body>
</html>
