<?php
/**
 * Register Page - SurveiLink Hospital Surveillance System
 * Pendaftaran Akun Petugas & Administrator Surveilans RS
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/database.php';

$pdo = getDB();
$errorMsg = '';
$successMsg = '';

// Handle Registration POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaLengkap = trim($_POST['nama_lengkap'] ?? '');
    $username    = trim($_POST['username'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $role        = $_POST['role'] ?? 'admin_unit';
    $unit        = trim($_POST['unit'] ?? 'Instalasi Gawat Darurat (IGD)');
    $password    = $_POST['password'] ?? '';
    $konfirmasi  = $_POST['konfirmasi_password'] ?? '';

    // Validations
    if (empty($namaLengkap) || empty($username) || empty($email) || empty($password)) {
        $errorMsg = 'Seluruh bidang formulir wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Format email tidak valid!';
    } elseif (strlen($password) < 6) {
        $errorMsg = 'Kata sandi minimal harus 6 karakter!';
    } elseif ($password !== $konfirmasi) {
        $errorMsg = 'Konfirmasi kata sandi tidak cocok dengan kata sandi!';
    } else {
        // Cek duplikasi username atau email
        $stmtCek = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmtCek->execute([$username, $email]);
        if ($stmtCek->fetch()) {
            $errorMsg = 'Username atau Email sudah terdaftar dalam sistem. Gunakan yang lain!';
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmtInsert = $pdo->prepare("
                INSERT INTO users (username, password, nama_lengkap, email, role, unit, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmtInsert->execute([$username, $hashedPassword, $namaLengkap, $email, $role, $unit]);
            $newUserId = $pdo->lastInsertId();

            // Catat ke Audit Log
            addAuditLog('Registrasi Petugas', 'Autentikasi', "Pendaftaran akun baru {$username} ({$namaLengkap}) sebagai {$role} di unit {$unit}.", $newUserId, $username, $role);

            // Tambahkan notifikasi sistem
            addNotification(
                'INFO',
                'Petugas Baru Terdaftar: ' . $namaLengkap,
                "Akun pegawai baru dengan username '{$username}' terdaftar pada unit {$unit}.",
                'Info',
                $unit
            );

            // Redirect ke login dengan pesan sukses
            header('Location: login.php?registered=1&u=' . urlencode($username));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Petugas Baru - SurveiLink</title>
    
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
<body class="bg-slate-900 text-slate-800 min-h-screen flex items-center justify-center p-4 relative overflow-x-hidden py-10">

    <!-- Decorative Medical Waves Background -->
    <div class="absolute inset-0 z-0 opacity-20 pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-brand-500 rounded-full filter blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-500 rounded-full filter blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:24px_24px] opacity-25"></div>
    </div>

    <!-- Register Container -->
    <div class="relative z-10 w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-12 border border-slate-100 my-auto">
        
        <!-- Left Side: Branding & Hospital Context -->
        <div class="lg:col-span-5 bg-gradient-to-br from-slate-900 via-brand-950 to-brand-900 text-white p-8 lg:p-10 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 text-white/5 text-9xl">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            
            <div class="relative z-10">
                <!-- App Logo -->
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-brand-400 flex items-center justify-center text-white shadow-lg shadow-brand-500/30">
                        <i class="fa-solid fa-heart-pulse text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-white">Survei<span class="text-brand-400">Link</span></h1>
                        <span class="text-[11px] font-medium text-slate-300">Pendaftaran Akun Petugas RS</span>
                    </div>
                </div>

                <!-- Registration Guidelines -->
                <div class="p-4 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 mb-6 space-y-2">
                    <div class="flex items-center gap-2 text-emerald-400 text-xs font-semibold">
                        <i class="fa-solid fa-id-badge"></i>
                        <span>Otorisasi Pegawai Rumah Sakit</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Akun ini digunakan untuk pencatatan registrasi pasien surveilans, verifikasi rekam medis, dan otorisasi pelaporan ke SIRS Kemenkes.
                    </p>
                </div>

                <div class="space-y-3 text-xs text-slate-300">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-emerald-400"></i>
                        <span>Akses terenkripsi standar Permenkes</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-brand-400"></i>
                        <span>Koneksi terpadu 6 unit layanan RS</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-check text-amber-400"></i>
                        <span>Audit trail otomatis untuk setiap aktivitas</span>
                    </div>
                </div>
            </div>

            <!-- Footer Badge -->
            <div class="relative z-10 pt-6 mt-6 border-t border-white/10 flex items-center justify-between text-[11px] text-slate-400">
                <a href="login.php" class="text-brand-300 hover:text-white flex items-center gap-1 font-medium transition">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali ke Halaman Login</span>
                </a>
                <span class="text-emerald-400 font-medium"><i class="fa-solid fa-shield-halved mr-1"></i>SIRS v2</span>
            </div>
        </div>

        <!-- Right Side: Register Form -->
        <div class="lg:col-span-7 p-8 lg:p-10 flex flex-col justify-center bg-white">
            <div class="mb-5">
                <span class="text-xs font-semibold uppercase tracking-wider text-brand-600 bg-brand-50 px-2.5 py-1 rounded-md">Registrasi Pegawai</span>
                <h2 class="text-2xl font-bold text-slate-900 mt-2">Daftar Akun Petugas Baru</h2>
                <p class="text-xs text-slate-500 mt-1">Lengkapi data diri kedinasan untuk mendapatkan hak akses SurveiLink.</p>
            </div>

            <?php if (!empty($errorMsg)): ?>
                <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-500 text-sm"></i>
                    <span><?= htmlspecialchars($errorMsg) ?></span>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="space-y-3.5">
                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1" for="nama_lengkap">Nama Lengkap & Gelar*</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-regular fa-id-card"></i>
                        </span>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" required
                            value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>"
                            class="w-full pl-10 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                            placeholder="Contoh: dr. Dian Permata, Sp.PD / Ns. Andi, S.Kep">
                    </div>
                </div>

                <!-- Grid: Username & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="username">Username / NIP*</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-regular fa-user"></i>
                            </span>
                            <input type="text" id="username" name="username" required
                                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                class="w-full pl-10 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                                placeholder="dian_surveilans">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="email">Email Kedinasan*</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <input type="email" id="email" name="email" required
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                class="w-full pl-10 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                                placeholder="dian@rsudmitra.go.id">
                        </div>
                    </div>
                </div>

                <!-- Grid: Peran & Unit -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="role">Peran / Otoritas*</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-solid fa-user-shield"></i>
                            </span>
                            <select id="role" name="role" class="w-full pl-10 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50 hover:bg-white">
                                <option value="admin_surveilans">Admin Epidemiologi RS</option>
                                <option value="petugas_rekam_medis">Petugas Rekam Medis (RMIK)</option>
                                <option value="admin_unit" selected>Admin Unit RS</option>
                                <option value="supervisor">Supervisor / Direksi</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="unit">Unit Penempatan*</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-solid fa-hospital"></i>
                            </span>
                            <select id="unit" name="unit" class="w-full pl-10 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50 hover:bg-white">
                                <option value="Instalasi Gawat Darurat (IGD)">Instalasi Gawat Darurat (IGD)</option>
                                <option value="Poliklinik Rawat Jalan">Poliklinik Rawat Jalan</option>
                                <option value="Instalasi Rawat Inap & Bangsal">Instalasi Rawat Inap & Bangsal</option>
                                <option value="Laboratorium Patologi Klinik">Laboratorium Patologi Klinik</option>
                                <option value="Depo Farmasi & Terapi Obat">Depo Farmasi & Terapi Obat</option>
                                <option value="Ruang Isolasi & ICU">Ruang Isolasi & ICU</option>
                                <option value="Instalasi Epidemiologi & Surveilans">Instalasi Epidemiologi & Surveilans</option>
                                <option value="Instalasi Rekam Medis">Instalasi Rekam Medis</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Grid: Password & Konfirmasi Password -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="password">Kata Sandi*</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" id="password" name="password" required
                                class="w-full pl-10 pr-8 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                                placeholder="Min. 6 karakter">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="konfirmasi_password">Ulangi Kata Sandi*</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <i class="fa-solid fa-shield-check"></i>
                            </span>
                            <input type="password" id="konfirmasi_password" name="konfirmasi_password" required
                                class="w-full pl-10 pr-8 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition placeholder:text-slate-400 bg-slate-50/50 hover:bg-white"
                                placeholder="Ketik ulang kata sandi">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white font-semibold text-xs shadow-md shadow-brand-500/25 transition flex items-center justify-center gap-2 mt-4">
                    <i class="fa-solid fa-user-check"></i>
                    <span>Daftarkan Akun Petugas</span>
                </button>
            </form>

            <!-- Link to Login -->
            <div class="mt-5 pt-4 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    Sudah memiliki akun terdaftar? 
                    <a href="login.php" class="font-bold text-brand-600 hover:text-brand-700 hover:underline">
                        Masuk di Sini &rarr;
                    </a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>

