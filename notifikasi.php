<?php
/**
 * Screen 9: Notifikasi & Peringatan Dini
 * Kasus 2: Integrasi & Sinkronisasi Data Surveilans RS - SIRS Kemenkes
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/database.php';
$pdo = getDB();

// Handle URL actions BEFORE header outputs any HTML to avoid "headers already sent"
if (isset($_GET['mark_read'])) {
    $pdo->exec("UPDATE notifikasi_alert SET status_baca = 1");
    addAuditLog('Tandai Notifikasi Dibaca', 'Notifikasi & Peringatan Dini', 'Semua notifikasi ditandai telah dibaca.');
    header('Location: notifikasi.php');
    exit;
}

if (isset($_GET['clear_all'])) {
    $pdo->exec("TRUNCATE TABLE notifikasi_alert");
    addAuditLog('Bersihkan Notifikasi', 'Notifikasi & Peringatan Dini', 'Seluruh notifikasi dan peringatan dini dibersihkan.');
    header('Location: notifikasi.php');
    exit;
}

$pageTitle = 'Notifikasi & Peringatan Dini - SurveiLink';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Filter params
$filterUrgensi = $_GET['urgensi'] ?? '';
$sql = "SELECT * FROM notifikasi_alert WHERE 1=1";
$params = [];

if (!empty($filterUrgensi)) {
    $sql .= " AND tingkat_urgensi = ?";
    $params[] = $filterUrgensi;
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$notifList = $stmt->fetchAll();

$totalNotif = count($notifList);
$countBelumDibaca = count(array_filter($notifList, fn($x) => $x['status_baca'] == 0));
$countBahaya = count(array_filter($notifList, fn($x) => $x['tingkat_urgensi'] === 'Bahaya'));
$countPeringatan = count(array_filter($notifList, fn($x) => $x['tingkat_urgensi'] === 'Peringatan'));
$countInfo = count(array_filter($notifList, fn($x) => $x['tingkat_urgensi'] === 'Info'));
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
                    <span class="text-slate-800 font-semibold">9. Notifikasi & Peringatan Dini</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Sistem Deteksi Dini & Peringatan Surveilans (EWARS)</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $countBelumDibaca > 0 ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700' ?>">
                        <?= $countBelumDibaca ?> Alert Baru
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Peringatan otomatis lonjakan kasus berpotensi Kejadian Luar Biasa (KLB) dan monitoring hambatan sinkronisasi data unit RS.</p>
            </div>

            <!-- Top Actions -->
            <div class="flex flex-wrap items-center gap-2">
                <?php if ($totalNotif > 0): ?>
                    <button type="button" onclick="tandaiSemuaDibaca()" class="px-3 py-2 rounded-xl bg-white border border-slate-200 hover:border-brand-500 text-slate-700 text-xs font-semibold shadow-xs transition flex items-center gap-1.5 hover:bg-brand-50 hover:text-brand-700">
                        <i class="fa-solid fa-envelope-open-text text-brand-600"></i>
                        <span>Tandai Semua Dibaca</span>
                    </button>
                    <button type="button" onclick="bersihkanNotifikasi()" class="px-3 py-2 rounded-xl bg-white border border-rose-200 hover:border-rose-400 text-rose-600 text-xs font-semibold shadow-xs transition flex items-center gap-1.5 hover:bg-rose-50">
                        <i class="fa-solid fa-trash-can text-rose-500"></i>
                        <span>Bersihkan</span>
                    </button>
                <?php endif; ?>
                <button type="button" onclick="buatSimulasiAlert()" class="px-3.5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Simulasi Peringatan Dini</span>
                </button>
            </div>
        </div>

        <!-- 3 Filter Urgency Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="notifikasi.php?urgensi=Bahaya" class="p-4 rounded-2xl bg-white border <?= $filterUrgensi === 'Bahaya' ? 'border-rose-500 ring-2 ring-rose-100' : 'border-slate-200' ?> shadow-xs hover:border-rose-300 transition flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Potensi KLB / Bahaya</span>
                    <h3 class="text-2xl font-black text-rose-600"><?= $countBahaya ?></h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">Ambang Kasus Terlampaui</p>
                </div>
            </a>

            <a href="notifikasi.php?urgensi=Peringatan" class="p-4 rounded-2xl bg-white border <?= $filterUrgensi === 'Peringatan' ? 'border-amber-500 ring-2 ring-amber-100' : 'border-slate-200' ?> shadow-xs hover:border-amber-300 transition flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Peringatan Sinkronisasi</span>
                    <h3 class="text-2xl font-black text-amber-600"><?= $countPeringatan ?></h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">Keterlambatan Data Unit</p>
                </div>
            </a>

            <a href="notifikasi.php?urgensi=Info" class="p-4 rounded-2xl bg-white border <?= $filterUrgensi === 'Info' ? 'border-blue-500 ring-2 ring-blue-100' : 'border-slate-200' ?> shadow-xs hover:border-blue-300 transition flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Informasi & SIRS</span>
                    <h3 class="text-2xl font-black text-blue-600"><?= $countInfo ?></h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">Status Transmisi & Sistem</p>
                </div>
            </a>
        </div>

        <?php if (!empty($filterUrgensi)): ?>
            <div class="flex items-center justify-between text-xs text-slate-600 bg-slate-100 px-4 py-2.5 rounded-xl">
                <span>Menampilkan filter urgensi: <b class="text-slate-800"><?= htmlspecialchars($filterUrgensi) ?></b></span>
                <a href="notifikasi.php" class="text-rose-600 font-semibold hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-xmark"></i> Hapus Filter
                </a>
            </div>
        <?php endif; ?>

        <!-- Notification List / Empty State -->
        <div class="space-y-3" id="notifContainer">
            <?php if (empty($notifList)): ?>
                <div class="p-12 text-center rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col items-center justify-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-4">
                        <i class="fa-solid fa-bell-slash"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Tidak Ada Peringatan Aktif (0)</h3>
                    <p class="text-xs text-slate-500 max-w-md mt-1 mb-6">
                        Sistem surveilans RS dalam kondisi normal dan tidak ada ancaman KLB yang terdeteksi saat ini.
                    </p>
                    <button type="button" onclick="buatSimulasiAlert()" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-xs transition flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>Buat Simulasi Deteksi Dini EWARS</span>
                    </button>
                </div>
            <?php else: ?>
                <?php foreach ($notifList as $notif): ?>
                    <?php
                        $isUnread = ($notif['status_baca'] == 0);
                        $colorClass = $isUnread ? 'border-amber-200/80 bg-amber-50/20' : 'border-slate-200 bg-white opacity-85';
                        $iconClass = 'fa-solid fa-info text-blue-600 bg-blue-50';
                        $badgeClass = 'bg-blue-100 text-blue-800';

                        if ($notif['tingkat_urgensi'] === 'Bahaya') {
                            $colorClass = $isUnread ? 'border-rose-300 bg-rose-50/40' : 'border-slate-200 bg-white opacity-85';
                            $iconClass = 'fa-solid fa-triangle-exclamation text-rose-600 bg-rose-100';
                            $badgeClass = 'bg-rose-100 text-rose-800';
                        } elseif ($notif['tingkat_urgensi'] === 'Peringatan') {
                            $colorClass = $isUnread ? 'border-amber-300 bg-amber-50/40' : 'border-slate-200 bg-white opacity-85';
                            $iconClass = 'fa-solid fa-circle-exclamation text-amber-600 bg-amber-100';
                            $badgeClass = 'bg-amber-100 text-amber-800';
                        }
                    ?>
                    <div id="notif-card-<?= $notif['id'] ?>" class="p-5 rounded-2xl border <?= $colorClass ?> shadow-xs flex items-start gap-4 transition hover:shadow-md relative group">
                        <div class="w-10 h-10 rounded-xl <?= $iconClass ?> flex items-center justify-center text-base shrink-0 mt-0.5"></div>
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-sm text-slate-900"><?= htmlspecialchars($notif['judul']) ?></h3>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold <?= $badgeClass ?>">
                                        <?= htmlspecialchars($notif['tingkat_urgensi']) ?>
                                    </span>
                                    <?php if (!empty($notif['unit_terkait'])): ?>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">
                                            Unit: <?= htmlspecialchars($notif['unit_terkait']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($isUnread): ?>
                                        <span id="unread-tag-<?= $notif['id'] ?>" class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-rose-500 text-white animate-pulse">
                                            BARU
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-slate-100 text-slate-500">
                                            <i class="fa-solid fa-check"></i> Dibaca
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <span class="text-[11px] text-slate-400">
                                    <i class="fa-regular fa-clock mr-1"></i><?= date('d M Y, H:i', strtotime($notif['created_at'])) ?> WIB
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed mt-1">
                                <?= htmlspecialchars($notif['pesan']) ?>
                            </p>
                            
                            <!-- Card Actions -->
                            <div class="mt-3 flex items-center gap-2">
                                <?php if ($isUnread): ?>
                                    <button type="button" onclick="tandaiSatuDibaca(<?= $notif['id'] ?>)" class="text-[11px] font-semibold text-brand-600 hover:text-brand-800 bg-white border border-slate-200 px-2.5 py-1 rounded-lg transition hover:bg-slate-50 flex items-center gap-1">
                                        <i class="fa-solid fa-check"></i> Tandai Dibaca
                                    </button>
                                <?php endif; ?>
                                <button type="button" onclick="hapusSatuNotifikasi(<?= $notif['id'] ?>)" class="text-[11px] font-semibold text-slate-400 hover:text-rose-600 px-2 py-1 rounded-lg transition hover:bg-rose-50 flex items-center gap-1">
                                    <i class="fa-solid fa-trash-can text-[10px]"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>
</div>

<script>
    // Tandai Semua Notifikasi Dibaca via AJAX
    function tandaiSemuaDibaca() {
        Swal.fire({
            title: 'Tandai Semua Dibaca?',
            text: 'Semua notifikasi dan peringatan dini akan ditandai telah dibaca.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            confirmButtonText: '<i class="fa-solid fa-check-double mr-1"></i> Ya, Tandai Semua',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (res.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const formData = new FormData();
                formData.append('action', 'mark_all_read');

                fetch('api/actions.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                                timer: 1200,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Gagal memproses: ' + err.message, 'error');
                    });
            }
        });
    }

    // Bersihkan Semua Notifikasi via AJAX
    function bersihkanNotifikasi() {
        Swal.fire({
            title: 'Bersihkan Semua Notifikasi?',
            text: 'Seluruh notifikasi dan peringatan dini akan dikosongkan dari database.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: '<i class="fa-solid fa-trash-can mr-1"></i> Ya, Bersihkan Semua',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (res.isConfirmed) {
                Swal.fire({
                    title: 'Membersihkan notifikasi...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const formData = new FormData();
                formData.append('action', 'clear_notifications');

                fetch('api/actions.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Notifikasi Bersih',
                                text: data.message,
                                timer: 1200,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Gagal membersihkan: ' + err.message, 'error');
                    });
            }
        });
    }

    // Tandai Satu Notifikasi Dibaca
    function tandaiSatuDibaca(id) {
        const formData = new FormData();
        formData.append('action', 'mark_read_single');
        formData.append('id', id);

        fetch('api/actions.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Notifikasi ditandai dibaca',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                }
            });
    }

    // Hapus Satu Notifikasi
    function hapusSatuNotifikasi(id) {
        const formData = new FormData();
        formData.append('action', 'delete_single_notification');
        formData.append('id', id);

        fetch('api/actions.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const card = document.getElementById('notif-card-' + id);
                    if (card) {
                        card.style.opacity = '0';
                        setTimeout(() => {
                            card.remove();
                            window.location.reload();
                        }, 200);
                    }
                }
            });
    }

    // Buat Simulasi Alert
    function buatSimulasiAlert() {
        Swal.fire({
            title: 'Simulasi Alert EWARS',
            text: 'Sistem akan mensimulasikan peringatan dini lonjakan klaster kasus Demam Berdarah (DBD) di IGD.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            confirmButtonText: 'Terbitkan Peringatan Dini'
        }).then(res => {
            if (res.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'simulate_pull');
                formData.append('unit', 'IGD');

                fetch('api/actions.php', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(() => {
                        window.location.reload();
                    });
            }
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
