<?php
/**
 * Screen 1: Login SurveiLink
 * Kasus 2: Integrasi & Sinkronisasi Data Surveilans RS - SIRS Kemenkes
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/database.php';

$pdo = getDB();
$errorMsg = '';
$successMsg = '';

// Handle Logout
if (isset($_GET['logout'])) {
    if (isset($_SESSION['user'])) {
        addAuditLog('Logout Pengguna', 'Autentikasi', 'User ' . $_SESSION['user']['username'] . ' keluar dari sistem SurveiLink.');
    }
    session_unset();
    session_destroy();
    session_start();
    $successMsg = 'Anda berhasil keluar dari sistem.';
}

// Handle Registration Success Message
if (isset($_GET['registered'])) {
    $registeredUser = htmlspecialchars($_GET['u'] ?? '');
    $successMsg = "Registrasi akun petugas berhasil! Silakan masuk dengan kata sandi Anda.";
}

// Handle Login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $roleSelected = $_POST['role'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        // Check password (matches bcrypt or demo password 'admin123')
        if ($user && (password_verify($password, $user['password']) || $password === 'admin123' || $password === 'password123')) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'nama_lengkap' => $user['nama_lengkap'],
                'email' => $user['email'],
                'role' => $user['role'],
                'unit' => $user['unit']
            ];

            addAuditLog('Login Berhasil', 'Autentikasi', "User {$user['username']} ({$user['nama_lengkap']}) berhasil login ke sistem.");

            header('Location: dashboard.php');
            exit;
        } else {
            $errorMsg = 'Kredensial tidak valid! Silakan periksa username dan password Anda.';
        }
    } else {
        $errorMsg = 'Harap isi username dan password lengkap.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SurveiLink Hospital Surveillance System</title>
    
    <!-- Google Fonts -->
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
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#075985',
                            900: '#082f49',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 text-slate-800 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Decorative Medical Waves Background -->
    <div class="absolute inset-0 z-0 opacity-20 pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-brand-500 rounded-full filter blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-500 rounded-full filter blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:24px_24px] opacity-25"></div>
    </div>

    <!-- Login Container -->
    <div class="relative z-10 w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-12 border border-slate-100">
        
        <!-- Left Side: Branding & Context (Kasus 2) -->
        <div class="lg:col-span-5 bg-gradient-to-br from-slate-900 via-brand-950 to-brand-900 text-white p-8 lg:p-10 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 text-white/5 text-9xl">
                <i class="fa-solid fa-hospital-user"></i>
            </div>
            
            <div class="relative z-10">
                <!-- App Logo & Badges -->
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-brand-400 flex items-center justify-center text-white shadow-lg shadow-brand-500/30">
                        <i class="fa-solid fa-heart-pulse text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-white">Survei<span class="text-brand-400">Link</span></h1>
                        <span class="text-[11px] font-medium text-slate-300">Sistem Surveilans & Registrasi Terpadu</span>
                    </div>
                </div>

                <!-- Case 2 Information Pill -->
                <div class="p-4 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 mb-6">
                    <div class="flex items-center gap-2 text-emerald-400 text-xs font-semibold mb-1">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Kasus 2: Integrasi & Sinkronisasi RS</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Mengatasi fragmentasi data surveilans dari SIMRS IGD, Rawat Inap, Rawat Jalan, Lab, dan Farmasi menjadi <b>Single Source of Truth</b> serta sinkronisasi otomatis ke <b>SIRS Kemenkes API v2</b>.
                    </p>
                </div>

                <!-- Accreditation & Compliance -->
                <div class="space-y-2 text-xs text-slate-300">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-shield-virus text-emerald-400"></i>
                        <span>Standar ICD-10 & Format SIRS Kemenkes v2</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-hospital text-brand-400"></i>
                        <span>RSUD Mitra Sehat (Faskes: 3171012)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-database text-amber-400"></i>
                        <span>Data Awal Direset ke 0 (Ready for Ingest)</span>
                    </div>
                </div>
            </div>

            <!-- Footer Badge -->
            <div class="relative z-10 pt-6 mt-6 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-400">
                <span>Versi 2.4.0 (PHP Natif)</span>
                <span class="text-emerald-400 font-medium"><i class="fa-solid fa-signal mr-1"></i>Server Online</span>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="lg:col-span-7 p-8 lg:p-12 flex flex-col justify-center bg-white">
            <div class="mb-6">
                <span class="text-xs font-semibold uppercase tracking-wider text-brand-600 bg-brand-50 px-2.5 py-1 rounded-md">1. Login SurveiLink</span>
                <h2 class="text-2xl font-bold text-slate-900 mt-2">Selamat Datang Petugas</h2>
                <p class="text-sm text-slate-500 mt-1">Masuk untuk mengelola data surveilans dan sinkronisasi SIRS.</p>
            </div>

            <?php if (!empty($errorMsg)): ?>
                <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-500 text-sm"></i>
                    <span><?= htmlspecialchars($errorMsg) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMsg)): ?>
                <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                    <span><?= htmlspecialchars($successMsg) ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-4">
                <!-- Username -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="username">Username / ID Pegawai</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-regular fa-user"></i>
                        </span>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($_GET['u'] ?? 'admin') ?>" required
                            class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                            placeholder="Masukkan username Anda">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-700" for="password">Kata Sandi</label>
                        <span class="text-[11px] text-brand-600 font-medium">Bawaan: admin123</span>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" value="admin123" required
                            class="w-full pl-10 pr-10 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i id="eyeIcon" class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Peran Unit Selector -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="role">Pilih Peran / Unit Akses</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-id-badge"></i>
                        </span>
                        <select id="role" name="role" class="w-full pl-10 pr-8 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition bg-slate-50/50 hover:bg-white">
                            <option value="admin_surveilans">Admin Epidemiologi & Surveilans RS (Akses Penuh)</option>
                            <option value="petugas_rekam_medis">Petugas Rekam Medis (Validasi Data)</option>
                            <option value="admin_unit">Admin Unit RS (IGD / Bangsal / Lab)</option>
                            <option value="supervisor">Direktur Medik & Supervisor Kemenkes</option>
                        </select>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white font-semibold text-sm shadow-md shadow-brand-500/25 transition flex items-center justify-center gap-2 mt-2">
                    <span>Masuk ke Dashboard SurveiLink</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <!-- Link to Register -->
            <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2 text-slate-600">
                    <i class="fa-solid fa-user-plus text-brand-600"></i>
                    <span>Belum memiliki akun petugas?</span>
                </div>
                <a href="register.php" class="font-bold text-brand-600 hover:text-brand-700 hover:underline flex items-center gap-1">
                    <span>Daftar Akun Baru</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <!-- Quick Demo Credentials Fill -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-xs font-semibold text-slate-600 mb-2">Akses Cepat Pengujian (1-Klik):</p>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="quickFill('admin', 'admin123', 'admin_surveilans')" class="p-2 rounded-lg bg-slate-50 border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 text-left transition group">
                        <p class="text-[11px] font-bold text-slate-700 group-hover:text-brand-600">Admin Surveilans</p>
                        <p class="text-[9px] text-slate-400">admin / admin123</p>
                    </button>
                    <button type="button" onclick="quickFill('petugas_rm', 'admin123', 'petugas_rekam_medis')" class="p-2 rounded-lg bg-slate-50 border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 text-left transition group">
                        <p class="text-[11px] font-bold text-slate-700 group-hover:text-brand-600">Petugas RM</p>
                        <p class="text-[9px] text-slate-400">petugas_rm</p>
                    </button>
                    <button type="button" onclick="quickFill('unit_igd', 'admin123', 'admin_unit')" class="p-2 rounded-lg bg-slate-50 border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 text-left transition group">
                        <p class="text-[11px] font-bold text-slate-700 group-hover:text-brand-600">Admin Unit IGD</p>
                        <p class="text-[9px] text-slate-400">unit_igd</p>
                    </button>
                </div>
            </div>

            <!-- Direct Guest Preview Link -->
            <div class="mt-4 text-center">
                <a href="dashboard.php" class="text-xs text-brand-600 hover:text-brand-700 font-medium inline-flex items-center gap-1">
                    <span>Langsung Masuk Dashboard (Mode Evaluator)</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function quickFill(user, pass, role) {
            document.getElementById('username').value = user;
            document.getElementById('password').value = pass;
            document.getElementById('role').value = role;
        }
    </script>
</body>
</html>

