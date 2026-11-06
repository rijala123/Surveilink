<?php
/**
 * Screen 5: Validasi Data
 * Kasus 2: Integrasi & Sinkronisasi Data Surveilans RS - SIRS Kemenkes
 */
$pageTitle = 'Validasi Data - SurveiLink';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Fetch records for validation
$pendingList = $pdo->query("SELECT * FROM data_masuk WHERE status_validasi = 'Menunggu' ORDER BY id DESC")->fetchAll();
$validatedList = $pdo->query("SELECT * FROM data_masuk WHERE status_validasi = 'Valid' ORDER BY id DESC LIMIT 10")->fetchAll();
$rejectedList = $pdo->query("SELECT * FROM data_masuk WHERE status_validasi = 'Ditolak' ORDER BY id DESC LIMIT 10")->fetchAll();

$pendingCount = count($pendingList);
$validatedCount = count($validatedList);
$rejectedCount = count($rejectedList);
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
                    <span class="text-slate-800 font-semibold">5. Validasi Data</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Workbench Validasi & Standardisasi Data</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        <?= $pendingCount ?> Menunggu
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Deduplikasi NIK/RM, verifikasi kode ICD-10, dan resolusi fragmentasi data antar-unit sebelum masuk ke Master Data Terpadu.</p>
            </div>

            <div class="flex items-center gap-2">
                <?php if ($pendingCount > 0): ?>
                    <button onclick="validasiSemua()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-2">
                        <i class="fa-solid fa-check-double"></i>
                        <span>Validasi Semua Data (<?= $pendingCount ?>)</span>
                    </button>
                <?php endif; ?>
                <button onclick="simulasiTarikData('ALL')" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    <span>Tarik Data Baru</span>
                </button>
            </div>
        </div>

        <!-- 4 Validation Integrity Rules Banner -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Aturan Validasi Kemenkes (Kasus 2: Single Source of Truth)</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div class="flex items-center gap-2 font-bold text-slate-800 mb-1">
                        <i class="fa-solid fa-id-card text-brand-600"></i> NIK 16 Digit
                    </div>
                    <p class="text-[11px] text-slate-500">Pencocokan NIK dengan identitas tunggal Dukcapil & SatuSehat.</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div class="flex items-center gap-2 font-bold text-slate-800 mb-1">
                        <i class="fa-solid fa-book-medical text-emerald-600"></i> Standardisasi ICD-10
                    </div>
                    <p class="text-[11px] text-slate-500">Kesesuaian kode penyakit menular wajib lapor (A90-A91, A15, J18, dll).</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div class="flex items-center gap-2 font-bold text-slate-800 mb-1">
                        <i class="fa-solid fa-code-merge text-amber-600"></i> Konsolidasi Lintas Unit
                    </div>
                    <p class="text-[11px] text-slate-500">Penggabungan rekam medis klinis IGD + konfirmasi uji laboratorium.</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div class="flex items-center gap-2 font-bold text-slate-800 mb-1">
                        <i class="fa-solid fa-file-shield text-indigo-600"></i> Kelengkapan SIRS
                    </div>
                    <p class="text-[11px] text-slate-500">Memastikan data siap kirim tanpa penolakan validasi server Kemenkes.</p>
                </div>
            </div>
        </div>

        <!-- Validation Workspace Tabs -->
        <div class="space-y-4">
            <!-- Tab Headers -->
            <div class="flex items-center gap-2 border-b border-slate-200 pb-2 text-xs font-semibold">
                <button onclick="pilihTab('tabMenunggu')" id="btnTabMenunggu" class="px-4 py-2 rounded-xl bg-brand-50 text-brand-700 border border-brand-200 transition">
                    Antrean Validasi (<?= $pendingCount ?>)
                </button>
                <button onclick="pilihTab('tabValid')" id="btnTabValid" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 transition">
                    Tervalidasi / Terkonsolidasi (<?= $validatedCount ?>)
                </button>
                <button onclick="pilihTab('tabDitolak')" id="btnTabDitolak" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 transition">
                    Anomali / Ditolak (<?= $rejectedCount ?>)
                </button>
            </div>

            <!-- Tab 1: Menunggu Validasi -->
            <div id="tabMenunggu" class="space-y-4">
                <?php if ($pendingCount === 0): ?>
                    <div class="p-12 text-center rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col items-center justify-center">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mb-3">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">Tidak Ada Antrean Validasi (0)</h3>
                        <p class="text-xs text-slate-500 max-w-sm mt-1 mb-4">
                            Semua data masuk telah tervalidasi atau database saat ini dalam kondisi awal 0. Tarik data baru dari unit RS untuk memulai evaluasi.
                        </p>
                        <button onclick="simulasiTarikData('ALL')" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition">
                            <i class="fa-solid fa-cloud-arrow-down mr-1"></i> Tarik Sampel Data Pasien
                        </button>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 gap-4">
                        <?php foreach ($pendingList as $row): ?>
                            <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-brand-300 transition">
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm shrink-0">
                                            <i class="fa-solid fa-user-injured"></i>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-base font-bold text-slate-900"><?= htmlspecialchars($row['nama_pasien']) ?></h3>
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-brand-50 text-brand-700 border border-brand-200">
                                                    <?= htmlspecialchars($row['unit_sumber']) ?>
                                                </span>
                                            </div>
                                            <div class="text-xs text-slate-500 mt-0.5">
                                                No. RM: <b class="text-slate-800"><?= htmlspecialchars($row['no_rm']) ?></b> | 
                                                NIK: <span class="font-mono text-slate-700"><?= htmlspecialchars($row['nik']) ?></span> | 
                                                <?= $row['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>, <?= $row['usia'] ?> tahun
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Quick Validation Actions -->
                                    <div class="flex items-center gap-2">
                                        <button onclick="tolakData(<?= $row['id'] ?>)" class="px-3 py-1.5 rounded-lg border border-rose-200 hover:bg-rose-50 text-rose-600 text-xs font-semibold transition">
                                            <i class="fa-solid fa-xmark mr-1"></i> Tolak / Anomali
                                        </button>
                                        <button onclick="setujuiValidasi(<?= $row['id'] ?>)" class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                                            <i class="fa-solid fa-check"></i>
                                            <span>Setujui ke SSOT</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Clinical & Lab Cross-Check Data -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-4 text-xs">
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Diagnosa Klinis & ICD-10</span>
                                        <p class="font-bold text-slate-800"><?= htmlspecialchars($row['diagnosa_awal']) ?></p>
                                        <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-800">
                                            ICD-10: <?= htmlspecialchars($row['kode_icd10']) ?> (<?= htmlspecialchars($row['kategori_penyakit']) ?>)
                                        </span>
                                    </div>

                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Hasil Laboratorium (LIS)</span>
                                        <p class="text-slate-700 text-xs"><?= htmlspecialchars($row['hasil_lab'] ?: 'Belum ada hasil lab') ?></p>
                                        <span class="inline-block mt-1 text-[10px] text-emerald-600 font-semibold">
                                            <i class="fa-solid fa-vial"></i> Konfirmasi Patologi
                                        </span>
                                    </div>

                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Alamat Domisili & Wilayah</span>
                                        <p class="text-slate-700 text-xs truncate"><?= htmlspecialchars($row['alamat']) ?></p>
                                        <span class="inline-block mt-1 text-[10px] text-slate-500">
                                            Kec: <?= htmlspecialchars($row['kecamatan'] ?: 'Senen') ?>, <?= htmlspecialchars($row['kota_kab'] ?: 'Jakarta Pusat') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab 2: Tervalidasi / Terkonsolidasi -->
            <div id="tabValid" class="hidden space-y-4">
                <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700">Daftar Data Masuk yang Berhasil Tervalidasi</span>
                        <a href="data_terpadu.php" class="text-xs text-brand-600 font-semibold hover:underline">Buka Data Terpadu (SSOT) &rarr;</a>
                    </div>
                    <?php if (empty($validatedList)): ?>
                        <div class="p-8 text-center text-xs text-slate-500">Belum ada data yang divalidasi.</div>
                    <?php else: ?>
                        <div class="divide-y divide-slate-100 text-xs">
                            <?php foreach ($validatedList as $v): ?>
                                <div class="p-3.5 flex items-center justify-between hover:bg-slate-50">
                                    <div>
                                        <span class="font-bold text-slate-800"><?= htmlspecialchars($v['nama_pasien']) ?></span>
                                        <span class="text-slate-400 text-[11px] ml-2">RM: <?= htmlspecialchars($v['no_rm']) ?> | ICD: <?= htmlspecialchars($v['kode_icd10']) ?></span>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check"></i> Siap Kirim SIRS
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab 3: Ditolak / Anomali -->
            <div id="tabDitolak" class="hidden space-y-4">
                <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
                    <div class="p-4 bg-slate-50 border-b border-slate-200">
                        <span class="text-xs font-bold text-slate-700">Daftar Kasus Anomali / Ditolak</span>
                    </div>
                    <?php if (empty($rejectedList)): ?>
                        <div class="p-8 text-center text-xs text-slate-500">Tidak ada kasus anomali / ditolak.</div>
                    <?php else: ?>
                        <div class="divide-y divide-slate-100 text-xs">
                            <?php foreach ($rejectedList as $r): ?>
                                <div class="p-3.5 flex items-center justify-between hover:bg-slate-50">
                                    <div>
                                        <span class="font-bold text-slate-800"><?= htmlspecialchars($r['nama_pasien']) ?></span>
                                        <p class="text-rose-600 text-[11px] mt-0.5">Alasan: <?= htmlspecialchars($r['catatan_anomali'] ?? 'Data tidak lengkap') ?></p>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        Ditolak
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
    function pilihTab(tabId) {
        document.getElementById('tabMenunggu').classList.add('hidden');
        document.getElementById('tabValid').classList.add('hidden');
        document.getElementById('tabDitolak').classList.add('hidden');
        document.getElementById(tabId).classList.remove('hidden');

        // Toggle active button style
        ['btnTabMenunggu', 'btnTabValid', 'btnTabDitolak'].forEach(b => {
            document.getElementById(b).className = 'px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 transition';
        });

        if (tabId === 'tabMenunggu') {
            document.getElementById('btnTabMenunggu').className = 'px-4 py-2 rounded-xl bg-brand-50 text-brand-700 border border-brand-200 transition';
        } else if (tabId === 'tabValid') {
            document.getElementById('btnTabValid').className = 'px-4 py-2 rounded-xl bg-brand-50 text-brand-700 border border-brand-200 transition';
        } else {
            document.getElementById('btnTabDitolak').className = 'px-4 py-2 rounded-xl bg-brand-50 text-brand-700 border border-brand-200 transition';
        }
    }

    function setujuiValidasi(id) {
        const formData = new FormData();
        formData.append('action', 'validate_data');
        formData.append('id', id);
        formData.append('type', 'approve');

        fetch('api/actions.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Data Tervalidasi',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            });
    }

    function tolakData(id) {
        Swal.fire({
            title: 'Tolak / Laporkan Anomali',
            input: 'text',
            inputLabel: 'Masukkan alasan penolakan data:',
            inputPlaceholder: 'Misal: NIK tidak cocok dengan identitas pasien',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Tolak Data'
        }).then(result => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'validate_data');
                formData.append('id', id);
                formData.append('type', 'reject');
                formData.append('alasan', result.value || 'Data tidak memenuhi standar SIRS');

                fetch('api/actions.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        Swal.fire('Ditolak', data.message, 'info').then(() => {
                            window.location.reload();
                        });
                    });
            }
        });
    }

    function validasiSemua() {
        Swal.fire({
            title: 'Validasi Massal?',
            text: 'Semua data di antrean akan dipromosikan ke Master Data Terpadu (Single Source of Truth).',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            confirmButtonText: 'Ya, Validasi Semua'
        }).then(res => {
            if (res.isConfirmed) {
                Swal.fire({ title: 'Memvalidasi data...', didOpen: () => { Swal.showLoading(); } });
                const formData = new FormData();
                formData.append('action', 'batch_validate');

                fetch('api/actions.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        Swal.fire('Selesai', data.message, 'success').then(() => {
                            window.location.href = 'data_terpadu.php';
                        });
                    });
            }
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

