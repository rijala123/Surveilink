<?php
/**
 * Screen 7: Kirim ke SIRS
 * Kasus 2: Integrasi & Sinkronisasi Data Surveilans RS - SIRS Kemenkes
 */
$pageTitle = 'Kirim ke SIRS - SurveiLink';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Fetch validated data ready for SIRS
$readyRecords = $pdo->query("SELECT * FROM data_terpadu WHERE status_sirs != 'Terkirim' ORDER BY id DESC")->fetchAll();
$sirsConfig = $pdo->query("SELECT * FROM sirs_config WHERE id = 1")->fetch();

$totalReady = count($readyRecords);
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
                    <span class="text-slate-800 font-semibold">7. Kirim ke SIRS</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Transmisi Data Surveilans ke SIRS Kemenkes</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-100 text-brand-800">
                        SIRS Online API v2
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Otomatisasi pengiriman laporan berkala dan kasus berpotensi KLB dari Single Source of Truth ke Ditjen Yankes Kemenkes RI.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="riwayat_pengiriman.php" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:border-slate-300 text-slate-700 text-xs font-semibold shadow-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Riwayat Transmisi &rarr;</span>
                </a>
            </div>
        </div>

        <!-- SIRS Kemenkes Handshake Credentials Status -->
        <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-brand-950 to-brand-900 text-white shadow-md relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-emerald-400 text-2xl border border-white/10 shrink-0">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-sm tracking-tight text-white"><?= htmlspecialchars($sirsConfig['nama_rs']) ?></h3>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                                Kode Faskes: <?= htmlspecialchars($sirsConfig['kode_rs']) ?>
                            </span>
                        </div>
                        <p class="text-xs text-slate-300 font-mono mt-0.5">
                            Endpoint: <span class="text-brand-300"><?= htmlspecialchars($sirsConfig['api_endpoint']) ?></span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <span class="text-[10px] text-slate-400 block">Status Koneksi API:</span>
                        <span class="text-xs font-bold text-emerald-400 flex items-center gap-1.5 justify-end">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            <?= htmlspecialchars($sirsConfig['status_koneksi']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Form: Module Selector & Period -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4">Pengaturan Modul Pelaporan SIRS</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Modul Formulir SIRS*</label>
                    <select id="pilihJenisLaporan" onchange="updatePayloadPreview()" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-brand-500">
                        <option value="Surveilans Kasus KLB / Penyakit Menular (EWARS)">Surveilans Kasus KLB & Penyakit Menular (EWARS)</option>
                        <option value="RL 5.3: Daftar 10 Besar Penyakit Rawat Inap">RL 5.3: 10 Besar Penyakit Rawat Inap</option>
                        <option value="RL 5.4: Daftar 10 Besar Penyakit Rawat Jalan">RL 5.4: 10 Besar Penyakit Rawat Jalan</option>
                        <option value="RL 5.1: Pengunjung Rumah Sakit">RL 5.1: Pengunjung Rumah Sakit</option>
                        <option value="RL 5.2: Kunjungan Rawat Jalan">RL 5.2: Kunjungan Rawat Jalan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Periode Pelaporan*</label>
                    <input type="text" id="pilihPeriode" value="<?= date('F Y') ?>" onchange="updatePayloadPreview()" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Petugas Otorisasi Transmisi</label>
                    <input type="text" readonly value="<?= htmlspecialchars($_SESSION['user']['nama_lengkap'] ?? 'dr. Hendra Pratama, Sp.A') ?>" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-500">
                </div>
            </div>
        </div>

        <!-- Grid: Ready Records Selection & Live JSON Preview -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left: Ready Records Checklist -->
            <div class="lg:col-span-7 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col justify-between overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Pilih Data Terpadu untuk Ditransmisikan</h3>
                        <p class="text-[11px] text-slate-500">Centang pasien yang akan dimasukkan ke payload laporan SIRS.</p>
                    </div>
                    <?php if ($totalReady > 0): ?>
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-slate-600 flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" id="checkSemua" checked onchange="toggleSelectAll(this)" class="rounded text-brand-600 focus:ring-brand-500">
                                <span>Pilih Semua</span>
                            </label>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="p-4 flex-1 overflow-y-auto max-h-96 custom-scrollbar">
                    <?php if ($totalReady === 0): ?>
                        <div class="p-8 text-center text-xs text-slate-500">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center text-lg mx-auto mb-2">
                                <i class="fa-solid fa-clipboard-check"></i>
                            </div>
                            <h4 class="font-bold text-slate-700">Tidak Ada Data Baru yang Siap Kirim (0)</h4>
                            <p class="mt-1">Seluruh data terpadu telah terkirim atau belum ada data yang divalidasi.</p>
                            <a href="data_masuk.php" class="mt-3 inline-block px-3.5 py-1.5 rounded-lg bg-brand-50 text-brand-700 font-semibold text-xs border border-brand-200">
                                Buka Data Masuk &rarr;
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach ($readyRecords as $rec): ?>
                                <label class="p-3 rounded-xl border border-slate-200 hover:border-brand-300 hover:bg-slate-50/70 transition flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="selected_records[]" value="<?= $rec['id'] ?>" checked onchange="updatePayloadPreview()" class="patient-checkbox mt-1 rounded text-brand-600 focus:ring-brand-500">
                                    <div class="flex-1 text-xs">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-slate-900"><?= htmlspecialchars($rec['nama_pasien']) ?></span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                ICD: <?= htmlspecialchars($rec['kode_icd10']) ?>
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            RM: <?= htmlspecialchars($rec['no_rm']) ?> | NIK: <?= htmlspecialchars($rec['nik']) ?> | Unit: <?= htmlspecialchars($rec['unit_layanan']) ?>
                                        </div>
                                        <p class="text-[11px] text-slate-700 mt-1 font-medium"><?= htmlspecialchars($rec['nama_penyakit']) ?></p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-500">Terpilih: <b id="countTerpilih" class="text-slate-800"><?= $totalReady ?></b> record</span>
                    <button id="btnKirimSirs" onclick="eksekusiKirimSirs()" <?= $totalReady === 0 ? 'disabled' : '' ?> class="px-4 py-2 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 disabled:opacity-50 text-white text-xs font-semibold shadow-md shadow-brand-500/20 transition flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim ke SIRS Kemenkes Sekarang</span>
                    </button>
                </div>
            </div>

            <!-- Right: Real-time Payload JSON Preview -->
            <div class="lg:col-span-5 rounded-2xl bg-slate-900 border border-slate-800 text-white shadow-xl flex flex-col justify-between overflow-hidden">
                <div class="p-4 bg-slate-950 border-b border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="font-mono text-slate-400 ml-2 font-bold">KEMENKES_PAYLOAD.json</span>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded bg-white/10 text-slate-300 font-mono">REST v2</span>
                </div>

                <div class="p-4 flex-1">
                    <pre id="payloadPreview" class="text-[11px] font-mono text-emerald-400 overflow-x-auto max-h-96 custom-scrollbar leading-relaxed"></pre>
                </div>

                <div class="p-3 bg-slate-950 border-t border-slate-800 text-center text-[11px] text-slate-400">
                    Format payload tervalidasi sesuai spesifikasi Juknis SIRS Online Kemenkes RI.
                </div>
            </div>
        </div>

    </main>
</div>

<script>
    const readyData = <?= json_encode($readyRecords) ?>;

    function toggleSelectAll(master) {
        document.querySelectorAll('.patient-checkbox').forEach(cb => {
            cb.checked = master.checked;
        });
        updatePayloadPreview();
    }

    function getSelectedIds() {
        const checked = document.querySelectorAll('.patient-checkbox:checked');
        const ids = [];
        checked.forEach(cb => ids.push(parseInt(cb.value)));
        return ids;
    }

    function updatePayloadPreview() {
        const ids = getSelectedIds();
        const countEl = document.getElementById('countTerpilih');
        const btnKirim = document.getElementById('btnKirimSirs');
        if (countEl) countEl.textContent = ids.length;
        if (btnKirim) btnKirim.disabled = (ids.length === 0);

        const jenisLaporan = document.getElementById('pilihJenisLaporan').value;
        const periode = document.getElementById('pilihPeriode').value;

        const filtered = readyData.filter(d => ids.includes(parseInt(d.id)));

        const payload = {
            "kode_faskes": "3171012",
            "nama_faskes": "RSUD Mitra Sehat Kemenkes",
            "sistem_pengirim": "SurveiLink v2.0 - Hospital Bridge",
            "waktu_transmisi": new Date().toISOString(),
            "jenis_laporan": jenisLaporan,
            "periode": periode,
            "total_kasus": filtered.length,
            "data_pasien": filtered.map(f => ({
                "id_surveilink": f.id,
                "no_rm": f.no_rm,
                "nik": f.nik,
                "nama_pasien": f.nama_pasien,
                "jenis_kelamin": f.jenis_kelamin,
                "cara_pembayaran": f.cara_pembayaran || "Pembayaran BPJS",
                "tempat_lahir": f.tempat_lahir || "Jakarta",
                "tanggal_lahir": f.tanggal_lahir,
                "usia": f.usia,
                "agama": f.agama || "Islam",
                "status_perkawinan": f.status_perkawinan || "Kawin",
                "icd10": f.kode_icd10,
                "diagnosa": f.nama_penyakit,
                "kategori_surveilans": f.kategori_surveilans,
                "hasil_laboratorium": f.hasil_lab_terpadu,
                "unit_perawatan": f.unit_layanan
            }))
        };

        document.getElementById('payloadPreview').textContent = JSON.stringify(payload, null, 2);
    }

    function eksekusiKirimSirs() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            Swal.fire('Info', 'Pilih minimal satu data pasien untuk dikirim!', 'info');
            return;
        }

        const jenisLaporan = document.getElementById('pilihJenisLaporan').value;
        const periode = document.getElementById('pilihPeriode').value;

        Swal.fire({
            title: 'Kirim ke Server SIRS Kemenkes?',
            text: `Menransmisikan ${ids.length} data kasus surveilans untuk modul "${jenisLaporan}".`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            confirmButtonText: '<i class="fa-solid fa-paper-plane mr-1"></i> Transmisikan Sekarang',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (res.isConfirmed) {
                Swal.fire({
                    title: 'Menghubungkan ke SIRS Kemenkes API v2...',
                    html: '<div class="text-xs text-slate-500 mt-2">Enkripsi payload JSON, validasi token faskes 3171012, dan handshake server Kemenkes...</div>',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const formData = new FormData();
                formData.append('action', 'kirim_sirs');
                formData.append('jenis_laporan', jenisLaporan);
                formData.append('periode', periode);
                ids.forEach(id => formData.append('ids[]', id));

                fetch('api/actions.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Transmisi Sukses (HTTP 201)!',
                                html: `
                                    <div class="text-left text-xs space-y-1.5 p-3 rounded-xl bg-slate-50 border border-slate-200 mt-2">
                                        <p><b>Kode Transmisi:</b> ${data.kode_transmisi}</p>
                                        <p><b>No. Referensi Kemenkes:</b> <span class="font-mono text-emerald-600 font-bold">${data.no_referensi}</span></p>
                                        <p><b>Jumlah Data:</b> ${data.jumlah} record</p>
                                        <p class="text-slate-500">Data telah diterima dan diverifikasi di server pelaporan Kemenkes.</p>
                                    </div>
                                `,
                                confirmButtonColor: '#0284c7',
                                confirmButtonText: 'Buka Riwayat Pengiriman'
                            }).then(() => {
                                window.location.href = 'riwayat_pengiriman.php';
                            });
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Gagal transmisi: ' + err.message, 'error');
                    });
            }
        });
    }

    // Initialize preview on page load
    updatePayloadPreview();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

