<?php
// Footer Component & Global Native JavaScript Handlers
?>
    <!-- Global Notification & Toast Scripts -->
    <script>
        // 1. Digital Clock in Header
        function updateClock() {
            const el = document.getElementById('liveClock');
            if (!el) return;
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
            el.textContent = timeStr;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // 2. Sidebar Mobile Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mainSidebar = document.getElementById('mainSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (sidebarToggle && mainSidebar && sidebarOverlay) {
            sidebarToggle.addEventListener('click', () => {
                mainSidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            });

            sidebarOverlay.addEventListener('click', () => {
                mainSidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });
        }

        // 3. Dropdown Menu Toggle
        function toggleActionDropdown() {
            const dropdown = document.getElementById('actionDropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            const container = document.getElementById('actionMenuContainer');
            const dropdown = document.getElementById('actionDropdown');
            if (container && dropdown && !container.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // 4. Global Action: Simulasi Tarik Data dari Unit RS
        function simulasiTarikData(unit = 'ALL') {
            Swal.fire({
                title: 'Tarik Data Surveilans Unit?',
                text: 'Sistem akan menarik data pasien surveilans (DBD, TBC, ISPA, Kolera) dari unit ' + (unit === 'ALL' ? 'lintas rumah sakit' : unit) + '.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0284c7',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fa-solid fa-cloud-arrow-down mr-1"></i> Tarik Data Sekarang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghubungkan ke API Unit RS...',
                        html: '<div class="text-xs text-slate-500 mt-2">Mengambil payload rekam medis & hasil laboratorium terdesentralisasi...</div>',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData();
                    formData.append('action', 'simulate_pull');
                    formData.append('unit', unit);

                    fetch('api/actions.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Integrasi Berhasil!',
                                text: data.message,
                                confirmButtonColor: '#0284c7',
                                confirmButtonText: 'Buka Data Masuk'
                            }).then(() => {
                                window.location.href = 'data_masuk.php';
                            });
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Gagal memproses koneksi ke server: ' + err.message, 'error');
                    });
                }
            });
        }

        // 5. Global Action: Reset Data ke 0 (Sesuai Permintaan User)
        function resetDataKeNol() {
            Swal.fire({
                title: 'Konfirmasi Reset ke 0',
                text: 'Semua data operasional (Data Masuk, Data Terpadu, SIRS, Notifikasi, Audit Log) akan direset menjadi 0!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fa-solid fa-trash-can mr-1"></i> Ya, Reset Semua ke 0',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mengosongkan Database...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData();
                    formData.append('action', 'reset_data');

                    fetch('api/actions.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Database Direset!',
                                text: data.message,
                                confirmButtonColor: '#0284c7'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Gagal reset data: ' + err.message, 'error');
                    });
                }
            });
        }
    </script>
</body>
</html>

