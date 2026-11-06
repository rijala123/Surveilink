<?php
/**
 * Screen 8: Riwayat Pengiriman
 * Kasus 2: Integrasi & Sinkronisasi Data Surveilans RS - SIRS Kemenkes
 */
$pageTitle = 'Riwayat Pengiriman SIRS - SurveiLink';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Fetch transmissions log
$logs = $pdo->query("SELECT * FROM kirim_sirs ORDER BY id DESC")->fetchAll();
$totalTransmisi = count($logs);
$totalRecordsSent = array_sum(array_column($logs, 'jumlah_data'));
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
                    <span class="text-slate-800 font-semibold">8. Riwayat Pengiriman</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Log Transmisi & Bukti Lapor SIRS Kemenkes</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">
                        <?= $totalTransmisi ?> Transmisi
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Rekam jejak kepatuhan pelaporan eksternal ke Ditjen Pelayanan Kesehatan Kementerian Kesehatan Republik Indonesia.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="kirim_sirs.php" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Kirim Data Baru</span>
                </a>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status Keberhasilan</span>
                    <h3 class="text-2xl font-black text-slate-800">100% Sukses</h3>
                    <p class="text-[10px] text-emerald-600 font-semibold mt-0.5">HTTP 201 Created</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-users-medical"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pasien Terlapor</span>
                    <h3 class="text-2xl font-black text-slate-800"><?= $totalRecordsSent ?> Pasien</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">Tersimpan di Cloud Kemenkes</p>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sertifikat Token Faskes</span>
                    <h3 class="text-2xl font-black text-slate-800">3171012</h3>
                    <p class="text-[10px] text-indigo-600 font-semibold mt-0.5">RSUD Mitra Sehat</p>
                </div>
            </div>
        </div>

        <!-- History Log Table / Empty State -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            <?php if (empty($logs)): ?>
                <div class="p-12 text-center flex flex-col items-center justify-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-4">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Riwayat Pengiriman Masih 0 (Kosong)</h3>
                    <p class="text-xs text-slate-500 max-w-md mt-1 mb-6">
                        Belum ada transmisi data yang dikirimkan ke server SIRS Kemenkes. Validasi data surveilans dan lakukan pengiriman pada menu <b>Kirim ke SIRS</b>.
                    </p>
                    <a href="kirim_sirs.php" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Buka Menu Kirim ke SIRS</span>
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-3.5 pl-6">Kode Transmisi & Waktu</th>
                                <th class="p-3.5">Modul SIRS & Periode</th>
                                <th class="p-3.5">Jumlah Data</th>
                                <th class="p-3.5">Status & Respon API</th>
                                <th class="p-3.5">Nomor Referensi Kemenkes</th>
                                <th class="p-3.5">Otorisator</th>
                                <th class="p-3.5 pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php foreach ($logs as $log): ?>
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="p-3.5 pl-6">
                                        <div class="font-mono font-bold text-slate-900"><?= htmlspecialchars($log['kode_transmisi']) ?></div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            <?= date('d M Y, H:i', strtotime($log['waktu_pengiriman'])) ?> WIB
                                        </div>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="font-semibold text-slate-800"><?= htmlspecialchars($log['jenis_laporan']) ?></div>
                                        <div class="text-[11px] text-slate-500 mt-0.5"><?= htmlspecialchars($log['periode_laporan']) ?></div>
                                    </td>
                                    <td class="p-3.5">
                                        <span class="font-bold text-slate-800"><?= $log['jumlah_data'] ?> Pasien</span>
                                    </td>
                                    <td class="p-3.5">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($log['response_code']) ?>
                                        </span>
                                        <p class="text-[10px] text-slate-500 mt-0.5 truncate max-w-xs"><?= htmlspecialchars($log['response_message']) ?></p>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="font-mono font-bold text-brand-700 bg-brand-50 px-2.5 py-1 rounded-lg border border-brand-200 inline-block">
                                            <?= htmlspecialchars($log['nomor_referensi_sirs']) ?>
                                        </div>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="text-slate-700"><?= htmlspecialchars($log['dikirim_oleh']) ?></div>
                                    </td>
                                    <td class="p-3.5 pr-6 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button onclick='lihatPayloadLog(<?= json_encode($log) ?>)' class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition flex items-center gap-1">
                                                <i class="fa-solid fa-code text-[10px]"></i>
                                                <span>Payload</span>
                                            </button>
                                            <button onclick='cetakTandaTerima(<?= json_encode($log) ?>)' class="px-2.5 py-1 rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-semibold border border-brand-200 transition flex items-center gap-1">
                                                <i class="fa-solid fa-receipt text-[10px]"></i>
                                                <span>Bukti</span>
                                            </button>
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

<!-- Modal Detail Payload JSON -->
<div id="modalPayload" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-slate-900 text-white rounded-3xl max-w-3xl w-full max-h-[85vh] overflow-hidden flex flex-col shadow-2xl border border-slate-800">
        <div class="p-5 bg-slate-950 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h3 id="modalPayloadTitle" class="font-bold text-sm text-white">Payload Transmisi SIRS</h3>
                <p id="modalPayloadSub" class="text-xs text-slate-400 font-mono"></p>
            </div>
            <button onclick="tutupModalPayload()" class="text-slate-400 hover:text-white p-2">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="p-5 flex-1 overflow-y-auto custom-scrollbar">
            <pre id="modalPayloadContent" class="text-xs font-mono text-emerald-400 leading-relaxed"></pre>
        </div>
        <div class="p-4 bg-slate-950 border-t border-slate-800 flex justify-end">
            <button onclick="tutupModalPayload()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold">Tutup</button>
        </div>
    </div>
</div>

<script>
    function lihatPayloadLog(log) {
        document.getElementById('modalPayloadTitle').textContent = log.jenis_laporan;
        document.getElementById('modalPayloadSub').textContent = log.kode_transmisi + ' | Ref: ' + log.nomor_referensi_sirs;
        
        let jsonStr = log.payload_json;
        try {
            const parsed = JSON.parse(log.payload_json);
            jsonStr = JSON.stringify(parsed, null, 2);
        } catch(e) {}

        document.getElementById('modalPayloadContent').textContent = jsonStr;
        document.getElementById('modalPayload').classList.remove('hidden');
    }

    function tutupModalPayload() {
        document.getElementById('modalPayload').classList.add('hidden');
    }

    function cetakTandaTerima(log) {
        Swal.fire({
            title: 'Tanda Terima Pelaporan SIRS',
            html: `
                <div class="text-left text-xs p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2 text-slate-700">
                    <div class="text-center pb-2 border-b border-slate-200">
                        <h4 class="font-bold text-sm text-slate-900">KEMENTERIAN KESEHATAN RI</h4>
                        <p class="text-[10px] text-slate-500">Direktorat Jenderal Pelayanan Kesehatan - SIRS Online</p>
                    </div>
                    <p><b>Rumah Sakit:</b> RSUD Mitra Sehat (3171012)</p>
                    <p><b>Nomor Referensi:</b> <span class="font-mono font-bold text-emerald-600">${log.nomor_referensi_sirs}</span></p>
                    <p><b>Kode Transmisi:</b> ${log.kode_transmisi}</p>
                    <p><b>Jenis Formulir:</b> ${log.jenis_laporan}</p>
                    <p><b>Periode:</b> ${log.periode_laporan}</p>
                    <p><b>Jumlah Data:</b> ${log.jumlah_data} Kasus Pasien</p>
                    <p><b>Waktu Transmisi:</b> ${log.waktu_pengiriman} WIB</p>
                    <p><b>Status Verifikasi:</b> <span class="text-emerald-600 font-bold">SAH / TERVERIFIKASI (${log.response_code})</span></p>
                </div>
            `,
            confirmButtonColor: '#0284c7',
            confirmButtonText: 'Tutup'
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

