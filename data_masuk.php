<?php
/**
 * Screen 4: Data Masuk
 * Kasus 2: Integrasi & Sinkronisasi Data Surveilans RS - SIRS Kemenkes
 */
$pageTitle = 'Data Masuk - SurveiLink';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Filter params
$filterUnit = $_GET['unit'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM data_masuk WHERE 1=1";
$params = [];

if (!empty($filterUnit)) {
    $sql .= " AND unit_sumber = ?";
    $params[] = $filterUnit;
}
if (!empty($filterStatus)) {
    $sql .= " AND status_validasi = ?";
    $params[] = $filterStatus;
}
if (!empty($search)) {
    $sql .= " AND (nama_pasien LIKE ? OR no_rm LIKE ? OR nik LIKE ? OR diagnosa_awal LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dataMasukList = $stmt->fetchAll();

$totalCount = count($dataMasukList);
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
                    <span class="text-slate-800 font-semibold">4. Data Masuk (Staging)</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Staging Data Masuk Unit RS</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                        <?= $totalCount ?> Data Pasien
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Repositori transit data mentah surveilans yang ditarik dari SIMRS, LIS, dan FIS sebelum divalidasi.</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="bukaModalTambah()" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:border-brand-500 text-slate-700 text-xs font-semibold shadow-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-brand-600"></i>
                    <span>Input Pasien Manual</span>
                </button>
                <button onclick="simulasiTarikData('ALL')" class="px-3.5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    <span>Tarik Data Unit RS</span>
                </button>
                <?php if ($totalCount > 0): ?>
                    <a href="validasi_data.php" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold shadow-xs transition flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-check"></i>
                        <span>Proses Validasi &rarr;</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
            <form method="GET" action="data_masuk.php" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <!-- Search -->
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari No. RM, NIK, Nama..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Unit Filter -->
                <select name="unit" onchange="this.form.submit()" class="py-2 px-3 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Semua Unit RS</option>
                    <option value="IGD" <?= $filterUnit === 'IGD' ? 'selected' : '' ?>>IGD (Gawat Darurat)</option>
                    <option value="RAJAL" <?= $filterUnit === 'RAJAL' ? 'selected' : '' ?>>Rawat Jalan (Poliklinik)</option>
                    <option value="RANAP" <?= $filterUnit === 'RANAP' ? 'selected' : '' ?>>Rawat Inap (Bangsal)</option>
                    <option value="LAB" <?= $filterUnit === 'LAB' ? 'selected' : '' ?>>Laboratorium (LIS)</option>
                    <option value="ICU" <?= $filterUnit === 'ICU' ? 'selected' : '' ?>>Ruang Isolasi / ICU</option>
                </select>

                <!-- Status Filter -->
                <select name="status" onchange="this.form.submit()" class="py-2 px-3 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Semua Status Validasi</option>
                    <option value="Menunggu" <?= $filterStatus === 'Menunggu' ? 'selected' : '' ?>>Menunggu Validasi</option>
                    <option value="Valid" <?= $filterStatus === 'Valid' ? 'selected' : '' ?>>Valid (Terkonsolidasi)</option>
                    <option value="Ditolak" <?= $filterStatus === 'Ditolak' ? 'selected' : '' ?>>Ditolak / Anomali</option>
                </select>

                <?php if (!empty($search) || !empty($filterUnit) || !empty($filterStatus)): ?>
                    <a href="data_masuk.php" class="text-xs text-rose-600 hover:underline">Reset Filter</a>
                <?php endif; ?>
            </form>

            <div class="text-xs text-slate-500">
                Status: <span class="font-semibold text-slate-800"><?= $totalCount ?></span> data ditemukan
            </div>
        </div>

        <!-- Data Table / Empty State -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            <?php if (empty($dataMasukList)): ?>
                <!-- Clean Zero State (Data Reset to 0) -->
                <div class="p-12 text-center flex flex-col items-center justify-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-4">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Data Masuk Masih 0 (Kosong)</h3>
                    <p class="text-xs text-slate-500 max-w-md mt-1 mb-6">
                        Belum ada data pasien surveilans yang masuk ke sistem. Anda dapat menarik data uji coba dari unit RS atau menambahkan data pasien manual.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <button onclick="simulasiTarikData('ALL')" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-2">
                            <i class="fa-solid fa-cloud-arrow-down"></i>
                            <span>Tarik Sampel Data Pasien Unit RS</span>
                        </button>
                        <button onclick="bukaModalTambah()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition flex items-center gap-2">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>Input Data Manual</span>
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <!-- Table View -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-3.5 pl-6">No. Reg / Unit</th>
                                <th class="p-3.5">Pasien & No. RM</th>
                                <th class="p-3.5">NIK & Alamat</th>
                                <th class="p-3.5">Diagnosa Klinis & ICD-10</th>
                                <th class="p-3.5">Hasil Lab & Terapi</th>
                                <th class="p-3.5">Status Validasi</th>
                                <th class="p-3.5 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php foreach ($dataMasukList as $row): ?>
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="p-3.5 pl-6">
                                        <div class="font-mono font-bold text-slate-800 text-[11px]"><?= htmlspecialchars($row['no_registrasi']) ?></div>
                                        <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold rounded-md bg-brand-50 text-brand-700 border border-brand-200">
                                            <?= htmlspecialchars($row['unit_sumber']) ?>
                                        </span>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($row['nama_pasien']) ?></div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            RM: <span class="font-mono font-semibold text-slate-700"><?= htmlspecialchars($row['no_rm']) ?></span> | 
                                            <?= $row['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?>, <?= $row['usia'] ?> th
                                        </div>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="font-mono text-slate-700 font-medium"><?= htmlspecialchars($row['nik']) ?></div>
                                        <div class="text-[11px] text-slate-400 truncate max-w-xs" title="<?= htmlspecialchars($row['alamat']) ?>">
                                            <?= htmlspecialchars($row['kelurahan']) ?>, <?= htmlspecialchars($row['kota_kab']) ?>
                                        </div>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="font-semibold text-slate-800"><?= htmlspecialchars($row['diagnosa_awal']) ?></div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                ICD: <?= htmlspecialchars($row['kode_icd10']) ?>
                                            </span>
                                            <span class="text-[10px] text-slate-500"><?= htmlspecialchars($row['kategori_penyakit']) ?></span>
                                        </div>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="text-[11px] text-slate-700 line-clamp-1" title="<?= htmlspecialchars($row['hasil_lab'] ?? '-') ?>">
                                            <b>Lab:</b> <?= htmlspecialchars($row['hasil_lab'] ?: 'Belum ada hasil') ?>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5 line-clamp-1" title="<?= htmlspecialchars($row['terapi_farmasi'] ?? '-') ?>">
                                            <b>Rx:</b> <?= htmlspecialchars($row['terapi_farmasi'] ?: 'Belum ada terapi') ?>
                                        </div>
                                    </td>
                                    <td class="p-3.5">
                                        <?php if ($row['status_validasi'] === 'Valid'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                <i class="fa-solid fa-circle-check"></i> Valid (SSOT)
                                            </span>
                                        <?php elseif ($row['status_validasi'] === 'Ditolak'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                <i class="fa-solid fa-circle-xmark"></i> Ditolak
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                <i class="fa-solid fa-clock"></i> Menunggu
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3.5 pr-6 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button onclick='lihatDetail(<?= json_encode($row) ?>)' class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-slate-100 transition" title="Lihat Detail Pasien">
                                                <i class="fa-solid fa-eye text-xs"></i>
                                            </button>
                                            <?php if ($row['status_validasi'] === 'Menunggu'): ?>
                                                <button onclick="validasiCepat(<?= $row['id'] ?>)" class="px-2 py-1 rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-700 text-[11px] font-semibold border border-brand-200 transition">
                                                    Validasi &rarr;
                                                </button>
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

    </main>
</div>

<!-- Modal Tambah Data Pasien Manual -->
<div id="modalTambah" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto custom-scrollbar shadow-2xl border border-slate-200">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Input Data Pasien Surveilans Manual</h3>
                <p class="text-xs text-slate-500">Mendaftarkan kasus baru untuk standarisasi integrasi surveilans RS.</p>
            </div>
            <button onclick="tutupModalTambah()" class="text-slate-400 hover:text-slate-600 p-2">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formTambahPasien" onsubmit="simpanPasienManual(event)" class="p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Rekam Medis (RM)*</label>
                    <input type="text" name="no_rm" required placeholder="Contoh: RM-928108" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">NIK (16 Digit)*</label>
                    <input type="text" name="nik" required maxlength="16" placeholder="31710XXXXXXXXXXX" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pasien Lengkap*</label>
                    <input type="text" name="nama_pasien" required placeholder="Nama lengkap pasien" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Sumber RS*</label>
                    <select name="unit_sumber" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="IGD">Instalasi Gawat Darurat (IGD)</option>
                        <option value="RAJAL">Poliklinik Rawat Jalan</option>
                        <option value="RANAP">Rawat Inap & Bangsal</option>
                        <option value="LAB">Laboratorium Patologi</option>
                        <option value="ICU">Ruang Isolasi / ICU</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Usia (Tahun)</label>
                    <input type="number" name="usia" value="28" min="0" max="120" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-brand-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Diagnosa Klinis Awal*</label>
                    <input type="text" name="diagnosa_awal" required placeholder="Contoh: Demam Berdarah Dengue (DBD) Grade I" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode ICD-10*</label>
                    <input type="text" name="kode_icd10" required placeholder="Contoh: A91" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Surveilans</label>
                    <select name="kategori_penyakit" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="Demam Berdarah Dengue (DBD)">Demam Berdarah Dengue (DBD)</option>
                        <option value="Tuberkulosis (TBC)">Tuberkulosis (TBC)</option>
                        <option value="ISPA Berat / SARI">ISPA Berat / SARI</option>
                        <option value="Diare Akut & Kolera">Diare Akut & Kolera</option>
                        <option value="Campak / Rubella">Campak / Rubella</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2" placeholder="Jl. Salemba Tengah No. 12, Senen, Jakarta Pusat" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-brand-500"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Hasil Uji Laboratorium</label>
                    <input type="text" name="hasil_lab" placeholder="Contoh: NS1 Positif, Trombosit 74.000 /uL" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="tutupModalTambah()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs">Simpan ke Data Masuk</button>
            </div>
        </form>
    </div>
</div>

<script>
    function bukaModalTambah() {
        document.getElementById('modalTambah').classList.remove('hidden');
    }
    function tutupModalTambah() {
        document.getElementById('modalTambah').classList.add('hidden');
    }

    function simpanPasienManual(e) {
        e.preventDefault();
        const form = document.getElementById('formTambahPasien');
        const formData = new FormData(form);
        formData.append('action', 'add_patient');

        fetch('api/actions.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Berhasil', data.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal', data.message, 'error');
                }
            });
    }

    function validasiCepat(id) {
        Swal.fire({
            title: 'Validasi Pasien Ini?',
            text: 'Data akan divalidasi dan dimasukkan ke Single Source of Truth (Data Terpadu).',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            confirmButtonText: 'Ya, Validasi'
        }).then(res => {
            if (res.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'validate_data');
                formData.append('id', id);
                formData.append('type', 'approve');

                fetch('api/actions.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Validasi Sukses', data.message, 'success').then(() => {
                                window.location.reload();
                            });
                        }
                    });
            }
        });
    }

    function lihatDetail(row) {
        Swal.fire({
            title: row.nama_pasien,
            html: `
                <div class="text-left text-xs space-y-2 text-slate-700">
                    <p><b>No. Registrasi:</b> ${row.no_registrasi}</p>
                    <p><b>Unit Sumber:</b> ${row.unit_sumber}</p>
                    <p><b>No. RM:</b> ${row.no_rm} | <b>NIK:</b> ${row.nik}</p>
                    <p><b>Alamat:</b> ${row.alamat || '-'}</p>
                    <p><b>Diagnosa:</b> ${row.diagnosa_awal} (${row.kode_icd10})</p>
                    <p><b>Kategori:</b> ${row.kategori_penyakit}</p>
                    <p><b>Hasil Lab:</b> ${row.hasil_lab || '-'}</p>
                    <p><b>Terapi Farmasi:</b> ${row.terapi_farmasi || '-'}</p>
                    <p><b>Catatan / Anomali:</b> ${row.catatan_anomali || 'Tidak ada anomali'}</p>
                </div>
            `,
            confirmButtonColor: '#0284c7',
            confirmButtonText: 'Tutup'
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

