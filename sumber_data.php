<?php
/**
 * Screen 3: Status Sumber Data
 * Kasus 2: Integrasi & Sinkronisasi Data Surveilans RS - SIRS Kemenkes
 */
$pageTitle = 'Status Sumber Data - SurveiLink';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Fetch all source units
$units = $pdo->query("SELECT * FROM sumber_data_unit ORDER BY id ASC")->fetchAll();

// Total stats across units
$totalRecordsIngested = array_sum(array_column($units, 'total_data_masuk'));
$totalAnomalies = array_sum(array_column($units, 'total_anomali'));
?>

<div class="flex-1 flex overflow-hidden">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto custom-scrollbar p-4 sm:p-6 lg:p-8 space-y-6">
        
        <!-- Breadcrumbs & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="dashboard.php" class="hover:text-brand-600">SurveiLink</a>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">3. Status Sumber Data</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Konektivitas & Integrasi 6 Unit RS</h1>
                <p class="text-xs text-slate-500 mt-0.5">Memantau koneksi bridge API, status sinkronisasi, dan penarikan data medis terdesentralisasi.</p>
            </div>

            <div class="flex items-center gap-2">
                <button onclick="pingSemuaUnit()" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:border-brand-500 text-slate-700 text-xs font-semibold shadow-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-heart-pulse text-emerald-500"></i>
                    <span>Ping Semua Unit</span>
                </button>
                <button onclick="simulasiTarikData('ALL')" class="px-3.5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    <span>Tarik Data Semua Unit</span>
                </button>
            </div>
        </div>

        <!-- Summary Metric Banners -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-server"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Unit Terkoneksi</span>
                    <h3 class="text-2xl font-black text-slate-800">6 / 6 Unit RS</h3>
                    <p class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1 mt-0.5">
                        <i class="fa-solid fa-circle-check"></i> 100% Online & Ready
                    </p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-file-import"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Data Ditarik</span>
                    <h3 class="text-2xl font-black text-slate-800"><?= $totalRecordsIngested ?> Record</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">Status Awal: 0 Data (Ready Ingest)</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata Latensi Jaringan</span>
                    <h3 class="text-2xl font-black text-slate-800">~13 ms</h3>
                    <p class="text-[10px] text-indigo-600 font-semibold mt-0.5">Jaringan Intranet RS Stabil</p>
                </div>
            </div>
        </div>

        <!-- 6 Units Interactive Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($units as $u): ?>
                <div class="rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-brand-300 transition overflow-hidden flex flex-col justify-between">
                    <!-- Unit Card Top -->
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="px-2.5 py-1 rounded-lg bg-brand-50 border border-brand-200 text-brand-700 font-extrabold text-xs">
                                    <?= htmlspecialchars($u['kode_unit']) ?>
                                </span>
                                <span class="text-[11px] font-semibold text-slate-400">
                                    <?= htmlspecialchars($u['tipe_sistem']) ?>
                                </span>
                            </div>
                            <span id="badge-<?= $u['kode_unit'] ?>" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span id="status-text-<?= $u['kode_unit'] ?>">Tersambung</span>
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-slate-900 mb-1"><?= htmlspecialchars($u['nama_unit']) ?></h3>
                        <p class="text-xs font-mono text-slate-500 bg-slate-50 p-2 rounded-lg border border-slate-100 break-all mb-4">
                            <i class="fa-solid fa-link text-slate-400 mr-1"></i><?= htmlspecialchars($u['lokasi_server']) ?>
                        </p>

                        <!-- Unit Stats -->
                        <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-slate-100">
                            <div>
                                <span class="text-slate-400 text-[10px] block">Data Masuk:</span>
                                <span class="font-bold text-slate-800 text-sm"><?= $u['total_data_masuk'] ?> Pasien</span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] block">Latency:</span>
                                <span id="latency-<?= $u['kode_unit'] ?>" class="font-bold text-emerald-600 text-sm"><?= $u['latency_ms'] ?> ms</span>
                            </div>
                            <div class="col-span-2 mt-1">
                                <span class="text-slate-400 text-[10px] block">Terakhir Sinkron:</span>
                                <span class="text-slate-600 text-[11px]"><?= $u['terakhir_sinkron'] ? date('d M Y H:i:s', strtotime($u['terakhir_sinkron'])) : 'Belum Pernah' ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Unit Card Bottom Actions -->
                    <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2">
                        <button onclick="pingUnit('<?= $u['kode_unit'] ?>')" class="flex-1 py-1.5 px-3 rounded-lg bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200 transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-signal text-emerald-500 text-[11px]"></i>
                            <span>Tes Ping</span>
                        </button>
                        <button onclick="simulasiTarikData('<?= $u['kode_unit'] ?>')" class="flex-1 py-1.5 px-3 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-download text-[11px]"></i>
                            <span>Tarik Data</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Technical Standardization Card (Kasus 2 Architecture) -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <h2 class="text-base font-bold text-slate-900 mb-2">Standardisasi & Arsitektur Protokol Antar-Unit (Kasus 2)</h2>
            <p class="text-xs text-slate-600 leading-relaxed mb-4">
                SurveiLink mengintegrasikan sistem rumah sakit yang berbeda arsitektur database. Melalui modul adapter, data mentah dari SIMRS IGD, Rawat Inap, Rawat Jalan, LIS Lab, dan FIS Farmasi dipetakan ke format standar Kemenkes (ICD-10, LOINC, dan SatuSehat HL7 FHIR Specification).
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="font-bold text-slate-800 flex items-center gap-2 mb-1">
                        <i class="fa-solid fa-code text-brand-600"></i> Format Data Masuk
                    </span>
                    <p class="text-slate-500 text-[11px]">JSON Payload REST API & HL7 FHIR Bundle dari sistem LIS/SIMRS lokal.</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="font-bold text-slate-800 flex items-center gap-2 mb-1">
                        <i class="fa-solid fa-arrows-split-up-and-left text-amber-600"></i> Normalisasi ICD-10
                    </span>
                    <p class="text-slate-500 text-[11px]">Pencocokan diagnosa klinis dokter ke kode standar surveilans Kemenkes.</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="font-bold text-slate-800 flex items-center gap-2 mb-1">
                        <i class="fa-solid fa-shield-halved text-emerald-600"></i> Keamanan & Enkripsi
                    </span>
                    <p class="text-slate-500 text-[11px]">Autentikasi Bearer Token + AES-256 untuk perlindungan data privasi pasien.</p>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
    // Ping single unit via AJAX
    function pingUnit(kodeUnit) {
        const latencyEl = document.getElementById('latency-' + kodeUnit);
        if (latencyEl) {
            latencyEl.textContent = 'Ping...';
        }

        const formData = new FormData();
        formData.append('action', 'ping_unit');
        formData.append('kode_unit', kodeUnit);

        fetch('api/actions.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (latencyEl) latencyEl.textContent = data.latency + ' ms';
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2500
                });
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Gagal ping unit: ' + err.message, 'error');
        });
    }

    // Ping all units
    function pingSemuaUnit() {
        const units = ['IGD', 'RAJAL', 'RANAP', 'LAB', 'FARM', 'ICU'];
        Swal.fire({
            title: 'Memeriksa Seluruh Unit...',
            didOpen: () => { Swal.showLoading(); }
        });

        let completed = 0;
        units.forEach(u => {
            const formData = new FormData();
            formData.append('action', 'ping_unit');
            formData.append('kode_unit', u);
            fetch('api/actions.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(d => {
                    completed++;
                    const latEl = document.getElementById('latency-' + u);
                    if (latEl && d.latency) latEl.textContent = d.latency + ' ms';
                    if (completed === units.length) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Semua Unit Normal!',
                            text: '6 unit RS merespons heartbeat dengan latensi stabil.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                });
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

