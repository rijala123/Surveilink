<?php
/**
 * Screen 6: Data Terpadu (Single Source of Truth)
 * Kasus 2: Integrasi & Sinkronisasi Data Surveilans RS - SIRS Kemenkes
 */
$pageTitle = 'Data Terpadu (SSOT) - SurveiLink';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Filter params
$filterPenyakit = $_GET['penyakit'] ?? '';
$filterSirs = $_GET['sirs'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM data_terpadu WHERE 1=1";
$params = [];

if (!empty($filterPenyakit)) {
    $sql .= " AND kategori_surveilans = ?";
    $params[] = $filterPenyakit;
}
if (!empty($filterSirs)) {
    $sql .= " AND status_sirs = ?";
    $params[] = $filterSirs;
}
if (!empty($search)) {
    $sql .= " AND (nama_pasien LIKE ? OR no_rm LIKE ? OR nik LIKE ? OR kode_icd10 LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$terpaduList = $stmt->fetchAll();

$totalSSOT = count($terpaduList);
$readyForSirs = count(array_filter($terpaduList, fn($x) => $x['status_sirs'] === 'Belum Dikirim'));
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
                    <span class="text-slate-800 font-semibold">6. Data Terpadu</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Master Data Terpadu (Single Source of Truth)</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        <?= $totalSSOT ?> Kasus Tervalidasi
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Basis data tunggal terstandarisasi yang mengonsolidasikan rekam medis seluruh unit RS dan siap dilaporkan ke SIRS Kemenkes.</p>
            </div>

            <!-- Top Actions -->
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="exportCSV()" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:border-slate-300 text-slate-700 text-xs font-semibold shadow-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-file-excel text-emerald-600"></i>
                    <span>Ekspor CSV</span>
                </button>
                <button onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:border-slate-300 text-slate-700 text-xs font-semibold shadow-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-print text-slate-600"></i>
                    <span>Cetak Laporan</span>
                </button>
                <?php if ($readyForSirs > 0): ?>
                    <a href="kirim_sirs.php" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim ke SIRS Kemenkes (<?= $readyForSirs ?>)</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
            <form method="GET" action="data_terpadu.php" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari Pasien, RM, NIK, ICD..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <select name="penyakit" onchange="this.form.submit()" class="py-2 px-3 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Semua Kategori Penyakit</option>
                    <option value="Demam Berdarah Dengue (DBD)" <?= $filterPenyakit === 'Demam Berdarah Dengue (DBD)' ? 'selected' : '' ?>>Demam Berdarah Dengue (DBD)</option>
                    <option value="Tuberkulosis (TBC)" <?= $filterPenyakit === 'Tuberkulosis (TBC)' ? 'selected' : '' ?>>Tuberkulosis (TBC)</option>
                    <option value="ISPA Berat / SARI" <?= $filterPenyakit === 'ISPA Berat / SARI' ? 'selected' : '' ?>>ISPA Berat / SARI</option>
                    <option value="Diare Akut & Penyakit Saluran Cerna" <?= $filterPenyakit === 'Diare Akut & Penyakit Saluran Cerna' ? 'selected' : '' ?>>Diare Akut & Kolera</option>
                    <option value="Campak / Rubella" <?= $filterPenyakit === 'Campak / Rubella' ? 'selected' : '' ?>>Campak / Rubella</option>
                </select>

                <select name="sirs" onchange="this.form.submit()" class="py-2 px-3 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Status Pelaporan SIRS</option>
                    <option value="Belum Dikirim" <?= $filterSirs === 'Belum Dikirim' ? 'selected' : '' ?>>Belum Dikirim</option>
                    <option value="Terkirim" <?= $filterSirs === 'Terkirim' ? 'selected' : '' ?>>Sudah Terkirim</option>
                </select>

                <?php if (!empty($search) || !empty($filterPenyakit) || !empty($filterSirs)): ?>
                    <a href="data_terpadu.php" class="text-xs text-rose-600 hover:underline">Reset Filter</a>
                <?php endif; ?>
            </form>

            <div class="text-xs text-slate-500">
                Total Golden Record: <span class="font-bold text-slate-800"><?= $totalSSOT ?></span> Pasien
            </div>
        </div>

        <!-- Master Golden Record Table / Empty State -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            <?php if (empty($terpaduList)): ?>
                <div class="p-12 text-center flex flex-col items-center justify-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-4">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Data Terpadu Masih 0 (Kosong)</h3>
                    <p class="text-xs text-slate-500 max-w-md mt-1 mb-6">
                        Belum ada data pasien yang dipromosikan ke Master Data Terpadu. Lakukan penarikan data dari unit RS dan validasi di menu <b>Validasi Data</b>.
                    </p>
                    <div class="flex items-center gap-3">
                        <button onclick="simulasiTarikData('ALL')" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-2">
                            <i class="fa-solid fa-cloud-arrow-down"></i>
                            <span>Tarik Sampel Data Pasien</span>
                        </button>
                        <a href="validasi_data.php" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold shadow-xs transition flex items-center gap-2">
                            <i class="fa-solid fa-clipboard-check"></i>
                            <span>Buka Workbench Validasi</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="tableDataTerpadu">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-3.5 pl-6">ID & Identitas Pasien</th>
                                <th class="p-3.5">No. RM & NIK</th>
                                <th class="p-3.5">Diagnosa Kemenkes (ICD-10)</th>
                                <th class="p-3.5">Hasil Lab Terpadu</th>
                                <th class="p-3.5">Unit & Status Pasien</th>
                                <th class="p-3.5">Status SIRS</th>
                                <th class="p-3.5 pr-6 text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php foreach ($terpaduList as $row): ?>
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="p-3.5 pl-6">
                                        <div class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($row['nama_pasien']) ?></div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            <?= $row['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>, <?= $row['usia'] ?> th | 
                                            ID: <span class="font-mono text-slate-700 font-bold">#SSOT-<?= sprintf('%04d', $row['id']) ?></span>
                                        </div>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <?php if (($row['cara_pembayaran'] ?? '') === 'Pembayaran BPJS'): ?>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <i class="fa-solid fa-id-card text-[9px]"></i> BPJS
                                                </span>
                                            <?php elseif (($row['cara_pembayaran'] ?? '') === 'Pembayaran Umum'): ?>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                    <i class="fa-solid fa-wallet text-[9px]"></i> Umum
                                                </span>
                                            <?php elseif (($row['cara_pembayaran'] ?? '') === 'Pembayaran Asuransi'): ?>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                                    <i class="fa-solid fa-shield-halved text-[9px]"></i> Asuransi
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                                    <?= htmlspecialchars($row['cara_pembayaran'] ?? 'Pembayaran BPJS') ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="font-mono font-bold text-slate-800">RM: <?= htmlspecialchars($row['no_rm']) ?></div>
                                        <div class="font-mono text-slate-500 text-[11px] mt-0.5"><?= htmlspecialchars($row['nik']) ?></div>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="font-bold text-slate-800"><?= htmlspecialchars($row['nama_penyakit']) ?></div>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                ICD: <?= htmlspecialchars($row['kode_icd10']) ?>
                                            </span>
                                            <span class="text-[10px] text-slate-500"><?= htmlspecialchars($row['kategori_surveilans']) ?></span>
                                        </div>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="text-slate-700 text-xs font-medium">
                                            <?= htmlspecialchars($row['hasil_lab_terpadu'] ?: 'Terkonfirmasi Gejala Klinis') ?>
                                        </div>
                                        <span class="text-[10px] text-emerald-600 block mt-0.5"><i class="fa-solid fa-circle-check"></i> Terverifikasi Lab RS</span>
                                    </td>
                                    <td class="p-3.5">
                                        <span class="font-semibold text-slate-800"><?= htmlspecialchars($row['unit_layanan']) ?></span>
                                        <div class="text-[11px] text-slate-500 mt-0.5"><?= htmlspecialchars($row['kondisi_keluar'] ?: 'Dalam Perawatan') ?></div>
                                    </td>
                                    <td class="p-3.5">
                                        <?php if ($row['status_sirs'] === 'Terkirim'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                <i class="fa-solid fa-cloud-arrow-up"></i> Terkirim SIRS
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                <i class="fa-solid fa-clock"></i> Belum Dikirim
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3.5 pr-6 text-right">
                                        <button onclick='lihatGoldenRecord(<?= json_encode($row) ?>)' class="px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                            Rincian
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<script>
    function lihatGoldenRecord(row) {
        Swal.fire({
            title: 'Golden Record: ' + row.nama_pasien,
            html: `
                <div class="text-left text-xs space-y-2 text-slate-700">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 mb-2">
                        <p><b>Single Source ID:</b> #SSOT-${String(row.id).padStart(4, '0')}</p>
                        <p><b>No. Rekam Medis:</b> ${row.no_rm}</p>
                        <p><b>NIK (Kemenkes):</b> ${row.nik}</p>
                        <p><b>Jenis Kelamin / Usia:</b> ${row.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'}, ${row.usia} tahun</p>
                        <p><b>Tempat, Tgl Lahir:</b> ${row.tempat_lahir || 'Jakarta'}, ${row.tanggal_lahir ? new Date(row.tanggal_lahir).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : '-'}</p>
                        <p><b>Agama / Status:</b> ${row.agama || 'Islam'} / ${row.status_perkawinan || 'Kawin'}</p>
                        <p><b>Cara Pembayaran:</b> <span class="font-semibold text-brand-600">${row.cara_pembayaran || 'Pembayaran BPJS'}</span></p>
                        <p><b>Alamat Lengkap:</b> ${row.alamat_lengkap}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-brand-50 border border-brand-200 mb-2">
                        <p><b>Diagnosa ICD-10:</b> ${row.nama_penyakit} (${row.kode_icd10})</p>
                        <p><b>Kategori Surveilans:</b> ${row.kategori_surveilans}</p>
                        <p><b>Hasil Lab Terpadu:</b> ${row.hasil_lab_terpadu || '-'}</p>
                        <p><b>Unit Pelayanan:</b> ${row.unit_layanan}</p>
                        <p><b>Status Pasien:</b> ${row.kondisi_keluar || 'Dalam Perawatan'}</p>
                    </div>
                    <div class="p-2 text-[11px] text-slate-500">
                        <p><b>Divalidasi Oleh:</b> ${row.petugas_validasi || 'dr. Hendra Pratama, Sp.A'}</p>
                        <p><b>Status Pelaporan SIRS:</b> <span class="font-bold text-brand-700">${row.status_sirs}</span></p>
                    </div>
                </div>
            `,
            confirmButtonColor: '#0284c7',
            confirmButtonText: 'Tutup'
        });
    }

    function exportCSV() {
        const rows = document.querySelectorAll("#tableDataTerpadu tr");
        if (rows.length <= 1) {
            Swal.fire('Info', 'Tidak ada data untuk diekspor!', 'info');
            return;
        }

        let csv = [];
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll("td, th");
            for (let j = 0; j < cols.length - 1; j++) {
                let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/(\s\s+)/gm, " ");
                data = data.replace(/"/g, '""');
                row.push('"' + data + '"');
            }
            csv.push(row.join(","));
        }

        const csvString = csv.join("\n");
        const a = document.createElement("a");
        a.href = "data:text/csv;charset=utf-8," + encodeURIComponent(csvString);
        a.target = "_blank";
        a.download = "SurveiLink_Master_Data_Terpadu_" + new Date().toISOString().slice(0, 10) + ".csv";
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

