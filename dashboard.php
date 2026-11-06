<?php
/**
 * Screen 2: Dashboard Utama
 * Kasus 2: Integrasi & Sinkronisasi Data Surveilans RS - SIRS Kemenkes
 */
$pageTitle = 'Dashboard Utama - SurveiLink Hospital Surveillance System';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Fetch metrics from database
$totalDataMasuk = $pdo->query("SELECT COUNT(*) FROM data_masuk")->fetchColumn();
$totalMenungguValidasi = $pdo->query("SELECT COUNT(*) FROM data_masuk WHERE status_validasi = 'Menunggu'")->fetchColumn();
$totalDataTerpadu = $pdo->query("SELECT COUNT(*) FROM data_terpadu")->fetchColumn();
$totalKirimSirs = $pdo->query("SELECT COUNT(*) FROM kirim_sirs WHERE status_pengiriman = 'Sukses'")->fetchColumn();
$totalNotifikasi = $pdo->query("SELECT COUNT(*) FROM notifikasi_alert WHERE status_baca = 0")->fetchColumn();

// Fetch units status
$units = $pdo->query("SELECT * FROM sumber_data_unit ORDER BY id ASC")->fetchAll();

// Fetch disease distribution from data_masuk or data_terpadu
$diseaseStats = $pdo->query("
    SELECT kategori_penyakit, COUNT(*) as jumlah 
    FROM data_masuk 
    GROUP BY kategori_penyakit 
    ORDER BY jumlah DESC 
    LIMIT 5
")->fetchAll();

// Fetch recent activity
$recentAudits = $pdo->query("SELECT * FROM audit_log ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-6 lg:p-8 space-y-6">
        
        <!-- Welcome Hero Banner -->
        <div class="rounded-3xl bg-gradient-to-r from-brand-900 via-brand-800 to-slate-900 text-white p-6 sm:p-8 relative overflow-hidden shadow-lg">
            <div class="absolute -right-10 -bottom-10 text-white/5 text-9xl pointer-events-none">
                <i class="fa-solid fa-hospital-wide"></i>
            </div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-brand-500/30 text-brand-200 border border-brand-400/30 rounded-full">
                            2. Dashboard Utama
                        </span>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 rounded-full flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            SIRS Kemenkes Live Sync
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Integrasi & Sinkronisasi Surveilans RS
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl leading-relaxed">
                        Mengatasi fragmentasi data antar-unit (IGD, Rawat Inap, Rawat Jalan, Lab, Farmasi), membentuk <b>Single Source of Truth</b>, dan otomatisasi pelaporan berkala ke <b>SIRS Online Kemenkes</b>.
                    </p>
                </div>

                <!-- Fast Simulation Action Bar -->
                <div class="flex flex-wrap gap-2.5 shrink-0">
                    <button onclick="simulasiTarikData('ALL')" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-semibold shadow-md shadow-emerald-900/30 transition flex items-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-down text-sm"></i>
                        <span>Tarik Data Unit RS</span>
                    </button>
                    <button onclick="resetDataKeNol()" class="px-3.5 py-2.5 rounded-xl bg-white/10 hover:bg-rose-500/20 text-rose-300 hover:text-rose-200 border border-white/10 hover:border-rose-400/40 text-xs font-semibold transition flex items-center gap-2">
                        <i class="fa-solid fa-rotate-left text-sm"></i>
                        <span>Reset Data (0)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 5 Key KPI Metric Cards (Initial 0 State) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            
            <!-- Card 1: Data Masuk -->
            <a href="data_masuk.php" class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-md hover:border-brand-300 transition group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Data Masuk</span>
                    <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center group-hover:bg-sky-600 group-hover:text-white transition">
                        <i class="fa-solid fa-inbox text-sm"></i>
                    </div>
                </div>
                <div class="flex items-baseline justify-between">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight"><?= $totalDataMasuk ?></h3>
                    <span class="text-[11px] text-slate-400">Pasien Staging</span>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Dari 6 Unit RS</span>
                    <span class="text-brand-600 font-semibold group-hover:translate-x-0.5 transition-transform"><i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>

            <!-- Card 2: Menunggu Validasi -->
            <a href="validasi_data.php" class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-md hover:border-amber-300 transition group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Menunggu Validasi</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition">
                        <i class="fa-solid fa-clipboard-question text-sm"></i>
                    </div>
                </div>
                <div class="flex items-baseline justify-between">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight"><?= $totalMenungguValidasi ?></h3>
                    <span class="text-[11px] text-amber-600 font-semibold">Perlu Verifikasi</span>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Deduplikasi NIK/RM</span>
                    <span class="text-amber-600 font-semibold group-hover:translate-x-0.5 transition-transform"><i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>

            <!-- Card 3: Data Terpadu (Single Source of Truth) -->
            <a href="data_terpadu.php" class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-md hover:border-emerald-300 transition group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Data Terpadu (SSOT)</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition">
                        <i class="fa-solid fa-database text-sm"></i>
                    </div>
                </div>
                <div class="flex items-baseline justify-between">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight"><?= $totalDataTerpadu ?></h3>
                    <span class="text-[11px] text-emerald-600 font-semibold">Valid & Konsolidasi</span>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Siap Kirim SIRS</span>
                    <span class="text-emerald-600 font-semibold group-hover:translate-x-0.5 transition-transform"><i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>

            <!-- Card 4: Terkirim SIRS -->
            <a href="riwayat_pengiriman.php" class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-md hover:border-indigo-300 transition group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Terkirim ke SIRS</span>
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition">
                        <i class="fa-solid fa-paper-plane text-sm"></i>
                    </div>
                </div>
                <div class="flex items-baseline justify-between">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight"><?= $totalKirimSirs ?></h3>
                    <span class="text-[11px] text-indigo-600 font-semibold">Batch Transmisi</span>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Kemenkes API v2</span>
                    <span class="text-indigo-600 font-semibold group-hover:translate-x-0.5 transition-transform"><i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>

            <!-- Card 5: Peringatan Dini EWARS -->
            <a href="notifikasi.php" class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-md hover:border-rose-300 transition group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Peringatan Dini (EWARS)</span>
                    <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition">
                        <i class="fa-solid fa-bell text-sm"></i>
                    </div>
                </div>
                <div class="flex items-baseline justify-between">
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight"><?= $totalNotifikasi ?></h3>
                    <span class="text-[11px] text-rose-600 font-semibold">Alert Aktif</span>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Ambang KLB / Anomali</span>
                    <span class="text-rose-600 font-semibold group-hover:translate-x-0.5 transition-transform"><i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>
        </div>

        <!-- Integration Pipeline Workflow (Kasus 2: Architectural Visualization) -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Arsitektur Pipeline Integrasi Surveilans RS</h2>
                    <p class="text-xs text-slate-500">Alur standardisasi data medis dari unit terdesentralisasi menuju pelaporan terpusat SIRS Kemenkes.</p>
                </div>
                <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                    <i class="fa-solid fa-diagram-project"></i> 4 Tahap Sinkronisasi
                </span>
            </div>

            <!-- Pipeline Steps -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 relative">
                <!-- Step 1 -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 relative">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-7 h-7 rounded-lg bg-brand-600 text-white font-bold text-xs flex items-center justify-center">1</span>
                        <h3 class="font-bold text-xs text-slate-800">Unit RS (Terfragmentasi)</h3>
                    </div>
                    <p class="text-[11px] text-slate-500">Data terpisah di SIMRS IGD, Rawat Inap, Poliklinik, LIS Lab, & FIS Farmasi.</p>
                    <div class="mt-3 flex items-center gap-1 text-[10px] text-emerald-600 font-semibold">
                        <i class="fa-solid fa-circle-check"></i> 6 Unit Tersambung
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 relative">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-7 h-7 rounded-lg bg-sky-600 text-white font-bold text-xs flex items-center justify-center">2</span>
                        <h3 class="font-bold text-xs text-slate-800">Ingestion (Data Masuk)</h3>
                    </div>
                    <p class="text-[11px] text-slate-500">Penarikan otomatis berkala & staging data mentah surveilans pasien.</p>
                    <div class="mt-3 flex items-center gap-1 text-[10px] text-slate-500">
                        <span class="font-bold text-slate-700"><?= $totalDataMasuk ?></span> record dalam antrean
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 relative">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-7 h-7 rounded-lg bg-amber-600 text-white font-bold text-xs flex items-center justify-center">3</span>
                        <h3 class="font-bold text-xs text-slate-800">Standardisasi & Validasi</h3>
                    </div>
                    <p class="text-[11px] text-slate-500">Deduplikasi NIK/RM, validasi kode ICD-10, dan pembersihan anomali data.</p>
                    <div class="mt-3 flex items-center gap-1 text-[10px] text-amber-600 font-semibold">
                        <i class="fa-solid fa-spell-check"></i> Validasi 1-Klik Aktif
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 relative">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-bold text-xs flex items-center justify-center">4</span>
                        <h3 class="font-bold text-xs text-slate-800">Single Source & SIRS</h3>
                    </div>
                    <p class="text-[11px] text-slate-500">Golden Record tersimpan & otomatis ditransmisikan ke SIRS Kemenkes v2.</p>
                    <div class="mt-3 flex items-center gap-1 text-[10px] text-emerald-600 font-semibold">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Format Kemenkes Ready
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts & Surveillance Insights -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Main Chart: Tren Surveilans Penyakit Berpotensi KLB -->
            <div class="lg:col-span-8 p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Tren Kasus Surveilans Berpotensi KLB</h2>
                        <p class="text-xs text-slate-500">Monitoring penyakit menular: Demam Berdarah (DBD), TBC, ISPA Akut, Campak.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600">Periode: November 2026</span>
                    </div>
                </div>

                <!-- Chart Canvas Container -->
                <div class="relative h-72 w-full">
                    <canvas id="surveillanceTrendChart"></canvas>
                    <?php if ($totalDataMasuk == 0): ?>
                        <div id="chartEmptyOverlay" class="absolute inset-0 bg-white/90 backdrop-blur-xs flex flex-col items-center justify-center text-center p-6 rounded-xl border border-dashed border-slate-200">
                            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                                <i class="fa-solid fa-chart-line text-xl"></i>
                            </div>
                            <h4 class="font-bold text-sm text-slate-700">Data Surveilans Masih 0</h4>
                            <p class="text-xs text-slate-500 max-w-sm mt-1">Belum ada kasus surveilans yang masuk. Klik tombol di bawah untuk menarik sampel kasus uji coba dari unit RS.</p>
                            <button onclick="simulasiTarikData('ALL')" class="mt-3 px-3.5 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition">
                                <i class="fa-solid fa-cloud-arrow-down mr-1"></i> Tarik Data Uji Coba Sekarang
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Side Widget: Status 6 Unit Sumber Data RS -->
            <div class="lg:col-span-4 p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Status 6 Unit RS</h2>
                            <p class="text-xs text-slate-500">Konektivitas sistem terdesentralisasi</p>
                        </div>
                        <a href="sumber_data.php" class="text-xs text-brand-600 font-semibold hover:underline">Kelola &rarr;</a>
                    </div>

                    <div class="space-y-3">
                        <?php foreach ($units as $u): ?>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between hover:bg-slate-100/70 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-xs font-bold text-slate-700">
                                        <?= htmlspecialchars($u['kode_unit']) ?>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-slate-800 leading-tight"><?= htmlspecialchars($u['nama_unit']) ?></p>
                                        <span class="text-[10px] text-slate-500"><?= htmlspecialchars($u['tipe_sistem']) ?></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600">
                                        <i class="fa-solid fa-circle text-[7px]"></i> <?= $u['latency_ms'] ?>ms
                                    </span>
                                    <p class="text-[10px] text-slate-400"><?= $u['total_data_masuk'] ?> data</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100">
                    <button onclick="simulasiTarikData('ALL')" class="w-full py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-arrows-rotate text-xs"></i>
                        <span>Sinkronkan Seluruh Unit RS</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Bottom Grid: Quick Links to All 10 Screens -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Akses Cepat 10 Halaman SurveiLink</h2>
                    <p class="text-xs text-slate-500">Navigasi langsung ke setiap modul sistem surveilans</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <a href="login.php" class="p-3 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 transition group">
                    <div class="text-xs font-bold text-slate-700 group-hover:text-brand-600 flex items-center gap-2">
                        <span class="text-[10px] bg-slate-100 group-hover:bg-brand-100 px-1.5 py-0.5 rounded">1</span>
                        <span>Login SurveiLink</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Portal autentikasi RS</p>
                </a>
                <a href="dashboard.php" class="p-3 rounded-xl border border-brand-300 bg-brand-50/50 transition">
                    <div class="text-xs font-bold text-brand-700 flex items-center gap-2">
                        <span class="text-[10px] bg-brand-200 text-brand-800 px-1.5 py-0.5 rounded">2</span>
                        <span>Dashboard Utama</span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">Ringkasan KPI & grafik</p>
                </a>
                <a href="sumber_data.php" class="p-3 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 transition group">
                    <div class="text-xs font-bold text-slate-700 group-hover:text-brand-600 flex items-center gap-2">
                        <span class="text-[10px] bg-slate-100 group-hover:bg-brand-100 px-1.5 py-0.5 rounded">3</span>
                        <span>Status Sumber Data</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Status 6 unit RS & ping</p>
                </a>
                <a href="data_masuk.php" class="p-3 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 transition group">
                    <div class="text-xs font-bold text-slate-700 group-hover:text-brand-600 flex items-center gap-2">
                        <span class="text-[10px] bg-slate-100 group-hover:bg-brand-100 px-1.5 py-0.5 rounded">4</span>
                        <span>Data Masuk</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Staging data mentah</p>
                </a>
                <a href="validasi_data.php" class="p-3 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 transition group">
                    <div class="text-xs font-bold text-slate-700 group-hover:text-brand-600 flex items-center gap-2">
                        <span class="text-[10px] bg-slate-100 group-hover:bg-brand-100 px-1.5 py-0.5 rounded">5</span>
                        <span>Validasi Data</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Deduplikasi & ICD-10</p>
                </a>
                <a href="data_terpadu.php" class="p-3 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 transition group">
                    <div class="text-xs font-bold text-slate-700 group-hover:text-brand-600 flex items-center gap-2">
                        <span class="text-[10px] bg-slate-100 group-hover:bg-brand-100 px-1.5 py-0.5 rounded">6</span>
                        <span>Data Terpadu</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Single Source of Truth</p>
                </a>
                <a href="kirim_sirs.php" class="p-3 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 transition group">
                    <div class="text-xs font-bold text-slate-700 group-hover:text-brand-600 flex items-center gap-2">
                        <span class="text-[10px] bg-slate-100 group-hover:bg-brand-100 px-1.5 py-0.5 rounded">7</span>
                        <span>Kirim ke SIRS</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Transmisi API Kemenkes</p>
                </a>
                <a href="riwayat_pengiriman.php" class="p-3 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 transition group">
                    <div class="text-xs font-bold text-slate-700 group-hover:text-brand-600 flex items-center gap-2">
                        <span class="text-[10px] bg-slate-100 group-hover:bg-brand-100 px-1.5 py-0.5 rounded">8</span>
                        <span>Riwayat Pengiriman</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Log & nomor tiket SIRS</p>
                </a>
                <a href="notifikasi.php" class="p-3 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 transition group">
                    <div class="text-xs font-bold text-slate-700 group-hover:text-brand-600 flex items-center gap-2">
                        <span class="text-[10px] bg-slate-100 group-hover:bg-brand-100 px-1.5 py-0.5 rounded">9</span>
                        <span>Notifikasi & Alert</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Deteksi dini EWARS</p>
                </a>
                <a href="audit_log.php" class="p-3 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 transition group">
                    <div class="text-xs font-bold text-slate-700 group-hover:text-brand-600 flex items-center gap-2">
                        <span class="text-[10px] bg-slate-100 group-hover:bg-brand-100 px-1.5 py-0.5 rounded">10</span>
                        <span>Audit Log</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Audit trail kepatuhan</p>
                </a>
            </div>
        </div>

    </main>
</div>

<!-- Chart Script -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('surveillanceTrendChart').getContext('2d');
        
        // Initial values or data based on DB
        const totalCount = <?= $totalDataMasuk ?>;
        const labels = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        const dataDBD = totalCount > 0 ? [3, 5, 4, 8, 7, 9, 12] : [0, 0, 0, 0, 0, 0, 0];
        const dataISPA = totalCount > 0 ? [8, 12, 10, 15, 14, 18, 20] : [0, 0, 0, 0, 0, 0, 0];
        const dataTBC = totalCount > 0 ? [2, 1, 3, 2, 4, 3, 5] : [0, 0, 0, 0, 0, 0, 0];

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'DBD (Demam Berdarah)',
                        data: dataDBD,
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.1)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2
                    },
                    {
                        label: 'ISPA / SARI Berat',
                        data: dataISPA,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2
                    },
                    {
                        label: 'Tuberkulosis (TBC)',
                        data: dataTBC,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 } }
                    }
                }
            }
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

