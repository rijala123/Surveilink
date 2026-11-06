<?php
// Sidebar Navigation Component
$navItems = [
    [
        'category' => 'Autentikasi & Ikhtisar',
        'items' => [
            [
                'number' => '1',
                'title' => 'Login SurveiLink',
                'url' => 'login.php',
                'icon' => 'fa-solid fa-right-to-bracket',
                'badge' => null
            ],
            [
                'number' => '2',
                'title' => 'Dashboard Utama',
                'url' => 'dashboard.php',
                'icon' => 'fa-solid fa-chart-pie',
                'badge' => null
            ]
        ]
    ],
    [
        'category' => 'Integrasi & Ingestion Unit',
        'items' => [
            [
                'number' => '3',
                'title' => 'Status Sumber Data',
                'url' => 'sumber_data.php',
                'icon' => 'fa-solid fa-network-wired',
                'badge' => '6 Unit'
            ],
            [
                'number' => '4',
                'title' => 'Data Masuk (Staging)',
                'url' => 'data_masuk.php',
                'icon' => 'fa-solid fa-inbox',
                'badge' => $totalDataMasuk > 0 ? $totalDataMasuk : '0'
            ]
        ]
    ],
    [
        'category' => 'Standardisasi & Single Source of Truth',
        'items' => [
            [
                'number' => '5',
                'title' => 'Validasi Data',
                'url' => 'validasi_data.php',
                'icon' => 'fa-solid fa-clipboard-check',
                'badge' => $totalMenungguValidasi > 0 ? $totalMenungguValidasi : '0',
                'badgeColor' => $totalMenungguValidasi > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600'
            ],
            [
                'number' => '6',
                'title' => 'Data Terpadu (SSOT)',
                'url' => 'data_terpadu.php',
                'icon' => 'fa-solid fa-database',
                'badge' => $totalDataTerpadu > 0 ? $totalDataTerpadu : '0'
            ]
        ]
    ],
    [
        'category' => 'Sinkronisasi Eksternal Kemenkes',
        'items' => [
            [
                'number' => '7',
                'title' => 'Kirim ke SIRS',
                'url' => 'kirim_sirs.php',
                'icon' => 'fa-solid fa-paper-plane',
                'badge' => 'API v2'
            ],
            [
                'number' => '8',
                'title' => 'Riwayat Pengiriman',
                'url' => 'riwayat_pengiriman.php',
                'icon' => 'fa-solid fa-clock-rotate-left',
                'badge' => $totalKirimSirs > 0 ? $totalKirimSirs : '0'
            ]
        ]
    ],
    [
        'category' => 'Peringatan & Keamanan',
        'items' => [
            [
                'number' => '9',
                'title' => 'Notifikasi & Peringatan Dini',
                'url' => 'notifikasi.php',
                'icon' => 'fa-solid fa-bell',
                'badge' => $totalNotifBelumDibaca > 0 ? $totalNotifBelumDibaca : '0',
                'badgeColor' => $totalNotifBelumDibaca > 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600'
            ],
            [
                'number' => '10',
                'title' => 'Audit Log',
                'url' => 'audit_log.php',
                'icon' => 'fa-solid fa-shield-halved',
                'badge' => null
            ]
        ]
    ]
];
?>
<!-- Sidebar Overlay for mobile -->
<div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden lg:hidden backdrop-blur-xs transition-opacity"></div>

<!-- Sidebar Component -->
<aside id="mainSidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-72 bg-white border-r border-slate-200 flex flex-col justify-between transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out shrink-0">
    <div class="h-full overflow-y-auto custom-scrollbar p-4 space-y-6">
        
        <!-- Hospital Branding Banner in Sidebar -->
        <div class="p-3 rounded-xl bg-gradient-to-br from-slate-900 to-brand-950 text-white relative overflow-hidden shadow-sm">
            <div class="absolute -right-4 -bottom-4 text-white/5 text-6xl">
                <i class="fa-solid fa-hospital"></i>
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-300">Surveilans Terpadu</span>
                </div>
                <h3 class="font-bold text-sm tracking-tight">Kasus 2: Integrasi RS</h3>
                <p class="text-[11px] text-slate-300 mt-1 line-clamp-2">Mengatasi fragmentasi data antar-unit RS & Sinkronisasi SIRS Kemenkes.</p>
            </div>
        </div>

        <!-- Navigation Sections -->
        <nav class="space-y-6">
            <?php foreach ($navItems as $group): ?>
                <div>
                    <h4 class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                        <?= htmlspecialchars($group['category']) ?>
                    </h4>
                    <div class="space-y-1">
                        <?php foreach ($group['items'] as $item): ?>
                            <?php 
                                $isActive = ($currentPage === $item['url']) || ($item['url'] === 'dashboard.php' && $currentPage === '');
                                $activeClasses = $isActive 
                                    ? 'bg-brand-50 text-brand-700 font-semibold border-l-4 border-brand-600 shadow-xs' 
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 font-medium';
                            ?>
                            <a href="<?= $item['url'] ?>" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs transition group <?= $activeClasses ?>">
                                <div class="flex items-center gap-3">
                                    <span class="w-5 text-center text-slate-400 font-mono text-[11px] group-hover:text-brand-600"><?= $item['number'] ?>.</span>
                                    <i class="<?= $item['icon'] ?> text-sm <?= $isActive ? 'text-brand-600' : 'text-slate-400 group-hover:text-slate-700' ?>"></i>
                                    <span><?= htmlspecialchars($item['title']) ?></span>
                                </div>
                                <?php if ($item['badge'] !== null): ?>
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full <?= $item['badgeColor'] ?? 'bg-slate-100 text-slate-700' ?>">
                                        <?= $item['badge'] ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- Sidebar Bottom Status Footer -->
    <div class="p-4 border-t border-slate-200 bg-slate-50">
        <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
            <span>Server Database:</span>
            <span class="font-semibold text-emerald-600 flex items-center gap-1">
                <i class="fa-solid fa-circle text-[8px]"></i> Laragon MySQL
            </span>
        </div>
        <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
            <div class="bg-brand-500 h-full rounded-full w-full"></div>
        </div>
        <div class="flex justify-between items-center text-[10px] text-slate-400 mt-2">
            <span>DB: <b class="text-slate-600">surveilink_db</b></span>
            <span>v2.4.0 (Natif PHP/JS)</span>
        </div>
    </div>
</aside>

