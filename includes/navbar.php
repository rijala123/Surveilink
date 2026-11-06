<?php
// Top Navigation Bar
?>
<header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
    <div class="px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <!-- Left: Logo & Hospital Identity -->
        <div class="flex items-center space-x-4">
            <button id="sidebarToggle" class="lg:hidden text-slate-500 hover:text-slate-700 p-2 rounded-lg hover:bg-slate-100 transition">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
            <a href="dashboard.php" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-700 to-brand-500 flex items-center justify-center text-white shadow-md shadow-brand-500/20 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-heart-pulse text-lg"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xl font-bold tracking-tight text-slate-900">Survei<span class="text-brand-600">Link</span></span>
                        <span class="px-2 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-800 rounded-full border border-emerald-200">KEMENKES v2</span>
                    </div>
                    <p class="text-[11px] text-slate-500 hidden sm:block">Sistem Integrasi Surveilans RSUD Mitra Sehat</p>
                </div>
            </a>
        </div>

        <!-- Center: SIRS Bridge Live Indicator -->
        <div class="hidden md:flex items-center space-x-3 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200/80 text-xs">
            <span class="flex h-2 w-2 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="font-medium text-slate-700">SIRS Online Kemenkes:</span>
            <span class="text-emerald-700 font-semibold flex items-center gap-1">
                <i class="fa-solid fa-cloud-arrow-up text-[11px]"></i> Terhubung (Live)
            </span>
            <span class="text-slate-300">|</span>
            <span id="liveClock" class="font-mono text-slate-600 font-medium"></span>
        </div>

        <!-- Right: Actions, Simulation Controls, User Profile -->
        <div class="flex items-center space-x-3">
            <!-- Data Simulation Action Dropdown -->
            <div class="relative inline-block text-left" id="actionMenuContainer">
                <button type="button" onclick="toggleActionDropdown()" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg bg-brand-50 text-brand-700 border border-brand-200 hover:bg-brand-100 transition shadow-xs">
                    <i class="fa-solid fa-wand-magic-sparkles text-brand-600"></i>
                    <span class="hidden sm:inline">Uji Coba Data</span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>
                <div id="actionDropdown" class="hidden absolute right-0 mt-2 w-64 rounded-xl bg-white shadow-xl ring-1 ring-black/5 divide-y divide-slate-100 z-50 animate-in fade-in zoom-in-95 duration-100">
                    <div class="p-2">
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-2">Simulasi Alur Kasus 2</span>
                        <button onclick="simulasiTarikData('ALL')" class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs text-slate-700 rounded-lg hover:bg-slate-50 hover:text-brand-600 transition">
                            <i class="fa-solid fa-cloud-arrow-down text-brand-500"></i>
                            <div>
                                <p class="font-semibold">Tarik Data Pasien Unit RS</p>
                                <p class="text-[10px] text-slate-400">Tarik kasus DBD, TBC, ISPA dari IGD & Lab</p>
                            </div>
                        </button>
                    </div>
                    <div class="p-2">
                        <span class="text-[11px] font-semibold text-rose-500 uppercase tracking-wider px-2">Reset Database</span>
                        <button onclick="resetDataKeNol()" class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs text-rose-600 rounded-lg hover:bg-rose-50 transition">
                            <i class="fa-solid fa-rotate-left text-rose-500"></i>
                            <div>
                                <p class="font-semibold">Reset Semua Data ke 0</p>
                                <p class="text-[10px] text-rose-400">Kosongkan data untuk evaluasi awal</p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Notification Bell -->
            <a href="notifikasi.php" class="relative p-2 text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition" title="Notifikasi & Peringatan Dini">
                <i class="fa-regular fa-bell text-lg"></i>
                <?php if ($totalNotifBelumDibaca > 0): ?>
                    <span class="absolute top-1.5 right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-xs">
                        <?= $totalNotifBelumDibaca ?>
                    </span>
                <?php endif; ?>
            </a>

            <!-- User Profile & Quick Link to Login -->
            <div class="flex items-center space-x-2 pl-2 border-l border-slate-200">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-slate-700 to-slate-900 text-white flex items-center justify-center font-bold text-xs ring-2 ring-slate-100">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
                <div class="hidden xl:block text-left">
                    <p class="text-xs font-semibold text-slate-800 leading-none"><?= htmlspecialchars($_SESSION['user']['nama_lengkap'] ?? 'Admin RS') ?></p>
                    <span class="text-[10px] text-slate-500"><?= htmlspecialchars($_SESSION['user']['unit'] ?? 'Surveilans RS') ?></span>
                </div>
                <a href="login.php?logout=1" class="text-xs text-slate-400 hover:text-rose-600 p-1.5 rounded-md hover:bg-slate-100 transition" title="Keluar / Ganti Akun">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </div>
</header>

