<?php
/**
 * Screen 10: Audit Log
 * Kasus 2: Integrasi & Sinkronisasi Data Surveilans RS - SIRS Kemenkes
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/database.php';
$pdo = getDB();

// Handle clear logs BEFORE header outputs HTML
if (isset($_GET['clear_all'])) {
    $pdo->exec("TRUNCATE TABLE audit_log");
    header('Location: audit_log.php');
    exit;
}

$pageTitle = 'Audit Log - SurveiLink';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Filter params
$filterModul = $_GET['modul'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM audit_log WHERE 1=1";
$params = [];

if (!empty($filterModul)) {
    $sql .= " AND modul = ?";
    $params[] = $filterModul;
}
if (!empty($search)) {
    $sql .= " AND (username LIKE ? OR aktivitas LIKE ? OR detail LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$auditLogs = $stmt->fetchAll();

$totalAudit = count($auditLogs);
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
                    <span class="text-slate-800 font-semibold">10. Audit Log</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Audit Trail & Kepatuhan Keamanan Sistem</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                        <?= $totalAudit ?> Log Tercatat
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Pencatatan menyeluruh seluruh aktivitas pengguna, integrasi data medis, dan transmisi SIRS sesuai regulasi Permenkes.</p>
            </div>

            <!-- Top Actions -->
            <div class="flex items-center gap-2">
                <button onclick="exportAuditCSV()" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:border-slate-300 text-slate-700 text-xs font-semibold shadow-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-file-excel text-emerald-600"></i>
                    <span>Ekspor CSV</span>
                </button>
                <?php if ($totalAudit > 0): ?>
                    <button type="button" onclick="kosongkanAuditLog()" class="px-3.5 py-2 rounded-xl bg-white border border-rose-200 hover:bg-rose-50 text-rose-600 text-xs font-semibold transition flex items-center gap-1.5">
                        <i class="fa-solid fa-trash-can"></i>
                        <span>Kosongkan Log</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
            <form method="GET" action="audit_log.php" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari aktivitas, user, detail..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <select name="modul" onchange="this.form.submit()" class="py-2 px-3 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Semua Modul</option>
                    <option value="Autentikasi" <?= $filterModul === 'Autentikasi' ? 'selected' : '' ?>>Autentikasi & Login</option>
                    <option value="Data Masuk" <?= $filterModul === 'Data Masuk' ? 'selected' : '' ?>>Data Masuk / Ingest</option>
                    <option value="Validasi Data" <?= $filterModul === 'Validasi Data' ? 'selected' : '' ?>>Validasi Data</option>
                    <option value="Kirim ke SIRS" <?= $filterModul === 'Kirim ke SIRS' ? 'selected' : '' ?>>Kirim ke SIRS</option>
                    <option value="Status Sumber Data" <?= $filterModul === 'Status Sumber Data' ? 'selected' : '' ?>>Status Sumber Data</option>
                    <option value="Notifikasi & Peringatan Dini" <?= $filterModul === 'Notifikasi & Peringatan Dini' ? 'selected' : '' ?>>Notifikasi & Peringatan Dini</option>
                    <option value="System" <?= $filterModul === 'System' ? 'selected' : '' ?>>System / Reset</option>
                </select>

                <?php if (!empty($search) || !empty($filterModul)): ?>
                    <a href="audit_log.php" class="text-xs text-rose-600 hover:underline">Reset Filter</a>
                <?php endif; ?>
            </form>

            <div class="text-xs text-slate-500">
                Menampilkan <span class="font-bold text-slate-800"><?= $totalAudit ?></span> aktivitas
            </div>
        </div>

        <!-- Audit Table / Empty State -->
        <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            <?php if (empty($auditLogs)): ?>
                <div class="p-12 text-center flex flex-col items-center justify-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-4">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Audit Log Masih 0 (Kosong)</h3>
                    <p class="text-xs text-slate-500 max-w-md mt-1 mb-6">
                        Belum ada aktivitas yang tercatat setelah reset data awal. Aktivitas login, tarik data, validasi, dan transmisi SIRS akan otomatis tercatat di sini.
                    </p>
                    <button onclick="simulasiTarikData('ALL')" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-2">
                        <i class="fa-solid fa-play"></i>
                        <span>Mulai Aktivitas Sistem</span>
                    </button>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="tableAuditLog">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-3.5 pl-6">ID & Waktu</th>
                                <th class="p-3.5">Pengguna / Peran</th>
                                <th class="p-3.5">Modul Sistem</th>
                                <th class="p-3.5">Aktivitas</th>
                                <th class="p-3.5">Detail Aktivitas</th>
                                <th class="p-3.5 pr-6">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php foreach ($auditLogs as $log): ?>
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="p-3.5 pl-6">
                                        <div class="font-mono text-[11px] font-bold text-slate-700">#LOG-<?= sprintf('%04d', $log['id']) ?></div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            <?= date('d M Y, H:i:s', strtotime($log['waktu'])) ?>
                                        </div>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="font-bold text-slate-800"><?= htmlspecialchars($log['username']) ?></div>
                                        <span class="text-[10px] text-slate-500"><?= htmlspecialchars($log['role']) ?></span>
                                    </td>
                                    <td class="p-3.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            <?= htmlspecialchars($log['modul']) ?>
                                        </span>
                                    </td>
                                    <td class="p-3.5">
                                        <span class="font-semibold text-slate-900"><?= htmlspecialchars($log['aktivitas']) ?></span>
                                    </td>
                                    <td class="p-3.5">
                                        <p class="text-slate-600 text-xs max-w-md"><?= htmlspecialchars($log['detail']) ?></p>
                                    </td>
                                    <td class="p-3.5 pr-6 font-mono text-[11px] text-slate-500">
                                        <?= htmlspecialchars($log['ip_address']) ?>
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
    function kosongkanAuditLog() {
        Swal.fire({
            title: 'Kosongkan Riwayat Audit Log?',
            text: 'Semua catatan aktivitas pengguna akan dihapus permanen dari sistem.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Kosongkan',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (res.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'clear_audit');
                fetch('api/actions.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Audit Log Dikosongkan',
                            text: data.message,
                            timer: 1200,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    });
            }
        });
    }

    function exportAuditCSV() {
        const rows = document.querySelectorAll("#tableAuditLog tr");
        if (rows.length <= 1) {
            Swal.fire('Info', 'Tidak ada data untuk diekspor!', 'info');
            return;
        }

        let csv = [];
        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll("td, th");
            for (let j = 0; j < cols.length; j++) {
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
        a.download = "SurveiLink_Audit_Log_" + new Date().toISOString().slice(0, 10) + ".csv";
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
