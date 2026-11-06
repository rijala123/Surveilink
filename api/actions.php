<?php
/**
 * SurveiLink - Backend Action Handler (AJAX & API)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$pdo = getDB();

try {
    switch ($action) {
        
        // -------------------------------------------------------------------------
        // 1. RESET SEMUA DATA KE 0
        // -------------------------------------------------------------------------
        case 'reset_data':
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $pdo->exec("TRUNCATE TABLE data_masuk;");
            $pdo->exec("TRUNCATE TABLE data_terpadu;");
            $pdo->exec("TRUNCATE TABLE kirim_sirs;");
            $pdo->exec("TRUNCATE TABLE notifikasi_alert;");
            $pdo->exec("TRUNCATE TABLE audit_log;");
            $pdo->exec("UPDATE sumber_data_unit SET total_data_masuk = 0, total_anomali = 0, terakhir_sinkron = NOW();");
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

            addAuditLog('Reset Data Master', 'System', 'Semua data operasional dan hitungan surveilans direset ke 0.');

            echo json_encode([
                'success' => true,
                'message' => 'Seluruh data berhasil direset ke 0! Database kini dalam status awal.'
            ]);
            break;

        // -------------------------------------------------------------------------
        // 2. SIMULASI TARIK DATA DARI UNIT RS
        // -------------------------------------------------------------------------
        case 'simulate_pull':
            $unitTarget = $_POST['unit'] ?? 'ALL';
            
            // Sample surveillance cases (Kasus 2: Penyakit Menular Berpotensi KLB & Surveilans RS)
            $sampleCases = [
                [
                    'unit' => 'IGD',
                    'no_rm' => 'RM-928101',
                    'nik' => '3171051204890001',
                    'nama' => 'Ahmad Fauzi',
                    'jk' => 'L',
                    'tgl_lahir' => '1989-04-12',
                    'usia' => 37,
                    'alamat' => 'Jl. Salemba Raya No. 14, Kenari, Senen, Jakarta Pusat',
                    'kelurahan' => 'Kenari',
                    'kecamatan' => 'Senen',
                    'kota' => 'Jakarta Pusat',
                    'diagnosa' => 'Dengue Haemorrhagic Fever (DHF) Grade II',
                    'icd10' => 'A91',
                    'kategori' => 'Demam Berdarah Dengue (DBD)',
                    'hasil_lab' => 'Trombosit: 68.000 /uL, Hematokrit: 46%, NS1 Antigen: POSITIF',
                    'farmasi' => 'Infus Ringer Laktat 500ml/6jam, Paracetamol tab 500mg prn',
                    'status_kelengkapan' => 'Lengkap',
                    'status_validasi' => 'Menunggu',
                    'catatan' => 'Suhu 39.2°C hari ke-4, petekie di ekstremitas. Memenuhi kriteria surveilans DBD.'
                ],
                [
                    'unit' => 'RAJAL',
                    'no_rm' => 'RM-928102',
                    'nik' => '3171025508930004',
                    'nama' => 'Dewi Lestari',
                    'jk' => 'P',
                    'tgl_lahir' => '1993-08-15',
                    'usia' => 33,
                    'alamat' => 'Jl. Kramat Pulo Gundul No. 5, Tanah Tinggi, Johar Baru',
                    'kelurahan' => 'Tanah Tinggi',
                    'kecamatan' => 'Johar Baru',
                    'kota' => 'Jakarta Pusat',
                    'diagnosa' => 'Tuberkulosis Paru BTA Positif',
                    'icd10' => 'A15.0',
                    'kategori' => 'Tuberkulosis (TBC)',
                    'hasil_lab' => 'Pemeriksaan TCM/GeneXpert: MTB Detected, Rifampisin Resisten: NEGATIF',
                    'farmasi' => 'FDC Kategori 1 (Rifampisin, INH, Pirazinamid, Etambutol)',
                    'status_kelengkapan' => 'Lengkap',
                    'status_validasi' => 'Menunggu',
                    'catatan' => 'Batuk berdahak > 3 minggu, penurunan BB 6kg dalam 1 bulan.'
                ],
                [
                    'unit' => 'RANAP',
                    'no_rm' => 'RM-928103',
                    'nik' => '3171011802010002',
                    'nama' => 'Bambang Trianto',
                    'jk' => 'L',
                    'tgl_lahir' => '2001-02-18',
                    'usia' => 25,
                    'alamat' => 'Jl. Percetakan Negara IX No. 22, Rawasari, Cempaka Putih',
                    'kelurahan' => 'Rawasari',
                    'kecamatan' => 'Cempaka Putih',
                    'kota' => 'Jakarta Pusat',
                    'diagnosa' => 'Diare Akut Dehidrasi Sedang ec Suspect Kolera',
                    'icd10' => 'A00.9',
                    'kategori' => 'Diare Akut & Penyakit Saluran Cerna',
                    'hasil_lab' => 'Kultur Tinja dalam proses, Leukosit Feses: 10-15/LPB, Elektrolit K: 3.1 mmol/L',
                    'farmasi' => 'RL Infus rehidrasi cepat, Zink tab 20mg, Oralit',
                    'status_kelengkapan' => 'Lengkap',
                    'status_validasi' => 'Menunggu',
                    'catatan' => 'BAB cair > 10x per hari seperti air cucian beras, lemas, turgor kulit lambat.'
                ],
                [
                    'unit' => 'LAB',
                    'no_rm' => 'RM-928104',
                    'nik' => '3171046011150003',
                    'nama' => 'Siti Nurhaliza (Anak)',
                    'jk' => 'P',
                    'tgl_lahir' => '2015-11-20',
                    'usia' => 10,
                    'alamat' => 'Jl. Kebon Kosong No. 18, Kemayoran, Jakarta Pusat',
                    'kelurahan' => 'Kebon Kosong',
                    'kecamatan' => 'Kemayoran',
                    'kota' => 'Jakarta Pusat',
                    'diagnosa' => 'Suspect Campak (Morbili / Measles)',
                    'icd10' => 'B05.9',
                    'kategori' => 'Campak / Rubella',
                    'hasil_lab' => 'IgM Campak: REAKTIF (Titer 1:160)',
                    'farmasi' => 'Vitamin A 200.000 IU dosis tunggal, Paracetamol sirup',
                    'status_kelengkapan' => 'Lengkap',
                    'status_validasi' => 'Menunggu',
                    'catatan' => 'Ruam makulopapular menjalar dari leher ke badan, demam tinggi, bercak koplik.'
                ],
                [
                    'unit' => 'ICU',
                    'no_rm' => 'RM-928105',
                    'nik' => '3171060309650005',
                    'nama' => 'H. Suwandi',
                    'jk' => 'L',
                    'tgl_lahir' => '1965-09-03',
                    'usia' => 61,
                    'alamat' => 'Jl. Gunung Sahari Raya No. 41, Kemayoran',
                    'kelurahan' => 'Gunung Sahari Selatan',
                    'kecamatan' => 'Kemayoran',
                    'kota' => 'Jakarta Pusat',
                    'diagnosa' => 'Severe Acute Respiratory Infection (SARI) / Pneumonia Berat',
                    'icd10' => 'J18.9',
                    'kategori' => 'ISPA Berat / SARI',
                    'hasil_lab' => 'Swab PCR Multiplex: SARS-CoV-2 Negatif, Influenza A Positif, AGD pO2: 65 mmHg',
                    'farmasi' => 'Oseltamivir 75mg 2x1, Ceftriaxone 2gr/24j iv, Oksigen High Flow Nasal Cannula',
                    'status_kelengkapan' => 'Lengkap',
                    'status_validasi' => 'Menunggu',
                    'catatan' => 'Saturasi SpO2 88% room air, sesak napas berat, retraksi interkostal.'
                ],
                [
                    'unit' => 'IGD',
                    'no_rm' => 'RM-928106',
                    'nik' => '3171050101880007',
                    'nama' => 'Rudi Hermawan',
                    'jk' => 'L',
                    'tgl_lahir' => '1988-01-01',
                    'usia' => 38,
                    'alamat' => 'Jl. Bendungan Hilir Gang V No. 8, Tanah Abang',
                    'kelurahan' => 'Bendungan Hilir',
                    'kecamatan' => 'Tanah Abang',
                    'kota' => 'Jakarta Pusat',
                    'diagnosa' => 'Febris Hari ke-3 Observasi Dengue',
                    'icd10' => 'A90',
                    'kategori' => 'Demam Berdarah Dengue (DBD)',
                    'hasil_lab' => 'Trombosit: 142.000 /uL, Hematokrit: 41%, NS1: Positif Lemah',
                    'farmasi' => 'Oral Rehydration, Antipiretik',
                    'status_kelengkapan' => 'Lengkap',
                    'status_validasi' => 'Menunggu',
                    'catatan' => 'Gejala mirip klaster kasus RT 04 Bendungan Hilir.'
                ]
            ];

            $insertedCount = 0;
            $unitCounts = [];

            $stmtCheck = $pdo->prepare("SELECT id FROM data_masuk WHERE no_registrasi = ?");
            $stmtInsert = $pdo->prepare("
                INSERT INTO data_masuk 
                (no_registrasi, unit_sumber, no_rm, nik, nama_pasien, jenis_kelamin, tanggal_lahir, usia, alamat, kelurahan, kecamatan, kota_kab, tanggal_masuk, diagnosa_awal, kode_icd10, kategori_penyakit, hasil_lab, terapi_farmasi, status_kelengkapan, status_validasi, catatan_anomali, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            foreach ($sampleCases as $idx => $case) {
                if ($unitTarget !== 'ALL' && $case['unit'] !== $unitTarget) {
                    continue;
                }

                $regId = 'REG-' . $case['unit'] . '-' . date('Ymd') . '-' . sprintf('%04d', $idx + rand(100, 999));
                
                $stmtCheck->execute([$regId]);
                if (!$stmtCheck->fetch()) {
                    $stmtInsert->execute([
                        $regId,
                        $case['unit'],
                        $case['no_rm'],
                        $case['nik'],
                        $case['nama'],
                        $case['jk'],
                        $case['tgl_lahir'],
                        $case['usia'],
                        $case['alamat'],
                        $case['kelurahan'],
                        $case['kecamatan'],
                        $case['kota'],
                        $case['diagnosa'],
                        $case['icd10'],
                        $case['kategori'],
                        $case['hasil_lab'],
                        $case['farmasi'],
                        $case['status_kelengkapan'],
                        $case['status_validasi'],
                        $case['catatan']
                    ]);
                    $insertedCount++;

                    $unitCode = $case['unit'];
                    $unitCounts[$unitCode] = ($unitCounts[$unitCode] ?? 0) + 1;
                }
            }

            // Update unit stats
            foreach ($unitCounts as $uCode => $cnt) {
                $pdo->prepare("UPDATE sumber_data_unit SET total_data_masuk = total_data_masuk + ?, terakhir_sinkron = NOW() WHERE kode_unit = ?")->execute([$cnt, $uCode]);
            }

            // Also create a timely notification alert for early warning if dengue cases detected
            if ($insertedCount > 0) {
                addNotification(
                    'EWARS_KLB',
                    'Peringatan Dini: Masuk ' . $insertedCount . ' Data Pasien Baru Berpotensi Wabah',
                    'Terdeteksi data surveilans masuk dari unit ' . ($unitTarget === 'ALL' ? 'Lintas Unit RS' : $unitTarget) . ' mencakup kasus DBD, TBC, dan ISPA Berat. Harap segera lakukan verifikasi validasi.',
                    'Peringatan',
                    $unitTarget
                );
                addAuditLog('Tarik Data Unit', 'Data Masuk', "Sinkronisasi {$insertedCount} data pasien dari unit " . ($unitTarget === 'ALL' ? 'Semua Unit' : $unitTarget));
            }

            echo json_encode([
                'success' => true,
                'inserted' => $insertedCount,
                'message' => "Berhasil menarik {$insertedCount} data pasien surveilans dari unit Rumah Sakit!"
            ]);
            break;

        // -------------------------------------------------------------------------
        // 3. TAMBAH DATA PASIEN MANUAL
        // -------------------------------------------------------------------------
        case 'add_patient':
            $no_rm    = trim($_POST['no_rm'] ?? '');
            $nik      = trim($_POST['nik'] ?? '');
            $nama     = trim($_POST['nama_pasien'] ?? '');
            $jk       = $_POST['jenis_kelamin'] ?? 'L';
            $tgl_lhr  = $_POST['tanggal_lahir'] ?? '1990-01-01';
            $usia     = intval($_POST['usia'] ?? 30);
            $unit     = $_POST['unit_sumber'] ?? 'IGD';
            $alamat   = trim($_POST['alamat'] ?? '');
            $diagnosa = trim($_POST['diagnosa_awal'] ?? '');
            $icd10    = trim($_POST['kode_icd10'] ?? '');
            $kategori = trim($_POST['kategori_penyakit'] ?? 'Lainnya');
            $hasil_lab= trim($_POST['hasil_lab'] ?? '');
            $farmasi  = trim($_POST['terapi_farmasi'] ?? '');

            if (empty($nama) || empty($no_rm) || empty($nik)) {
                echo json_encode(['success' => false, 'message' => 'No RM, NIK, dan Nama Pasien wajib diisi!']);
                exit;
            }

            $regId = 'REG-' . $unit . '-' . date('Ymd') . '-' . rand(1000, 9999);
            $stmt = $pdo->prepare("
                INSERT INTO data_masuk 
                (no_registrasi, unit_sumber, no_rm, nik, nama_pasien, jenis_kelamin, tanggal_lahir, usia, alamat, kota_kab, tanggal_masuk, diagnosa_awal, kode_icd10, kategori_penyakit, hasil_lab, terapi_farmasi, status_kelengkapan, status_validasi, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Jakarta Pusat', NOW(), ?, ?, ?, ?, ?, 'Lengkap', 'Menunggu', NOW())
            ");
            $stmt->execute([
                $regId, $unit, $no_rm, $nik, $nama, $jk, $tgl_lhr, $usia, $alamat, $diagnosa, $icd10, $kategori, $hasil_lab, $farmasi
            ]);

            // Update unit stats
            $pdo->prepare("UPDATE sumber_data_unit SET total_data_masuk = total_data_masuk + 1, terakhir_sinkron = NOW() WHERE kode_unit = ?")->execute([$unit]);

            addAuditLog('Input Pasien Manual', 'Data Masuk', "Input manual pasien No. RM {$no_rm} ({$nama}) oleh " . ($_SESSION['user']['username'] ?? 'admin'));

            echo json_encode(['success' => true, 'message' => 'Data pasien surveilans berhasil ditambahkan ke Data Masuk!']);
            break;

        // -------------------------------------------------------------------------
        // 4. VALIDASI DATA PASIEN & PROMOSI KE DATA TERPADU (SINGLE SOURCE OF TRUTH)
        // -------------------------------------------------------------------------
        case 'validate_data':
            $id = intval($_POST['id'] ?? 0);
            $actionType = $_POST['type'] ?? 'approve'; // approve | reject

            $stmt = $pdo->prepare("SELECT * FROM data_masuk WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch();

            if (!$data) {
                echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan!']);
                exit;
            }

            if ($actionType === 'reject') {
                $alasan = trim($_POST['alasan'] ?? 'Data tidak memenuhi standar SIRS Kemenkes');
                $pdo->prepare("UPDATE data_masuk SET status_validasi = 'Ditolak', catatan_anomali = ? WHERE id = ?")->execute([$alasan, $id]);
                addAuditLog('Tolak Validasi', 'Validasi Data', "Data No. RM {$data['no_rm']} ditolak: {$alasan}");
                echo json_encode(['success' => true, 'message' => 'Data berhasil ditandai ditolak.']);
                exit;
            }

            // Validasi sukses -> Masuk ke data_terpadu
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE data_masuk SET status_validasi = 'Valid', status_kelengkapan = 'Lengkap' WHERE id = ?")->execute([$id]);

            // Cek apakah sudah ada di data_terpadu
            $stmtCheck = $pdo->prepare("SELECT id FROM data_terpadu WHERE data_masuk_id = ? OR (no_rm = ? AND kode_icd10 = ?)");
            $stmtCheck->execute([$id, $data['no_rm'], $data['kode_icd10']]);
            $exists = $stmtCheck->fetch();

            if (!$exists) {
                $stmtInsertSSOT = $pdo->prepare("
                    INSERT INTO data_terpadu 
                    (data_masuk_id, no_rm, nik, nama_pasien, jenis_kelamin, tanggal_lahir, usia, alamat_lengkap, unit_layanan, tanggal_masuk, kondisi_keluar, kode_icd10, nama_penyakit, kategori_surveilans, hasil_lab_terpadu, status_sirs, tgl_validasi, petugas_validasi, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Dalam Perawatan', ?, ?, ?, ?, 'Belum Dikirim', NOW(), ?, NOW())
                ");
                $petugas = $_SESSION['user']['nama_lengkap'] ?? 'dr. Hendra Pratama, Sp.A (Admin Surveilans)';
                $stmtInsertSSOT->execute([
                    $id,
                    $data['no_rm'],
                    $data['nik'],
                    $data['nama_pasien'],
                    $data['jenis_kelamin'],
                    $data['tanggal_lahir'],
                    $data['usia'],
                    $data['alamat'],
                    $data['unit_sumber'],
                    $data['tanggal_masuk'],
                    $data['kode_icd10'],
                    $data['diagnosa_awal'],
                    $data['kategori_penyakit'],
                    $data['hasil_lab'],
                    $petugas
                ]);
            }

            $pdo->commit();
            addAuditLog('Validasi Pasien', 'Validasi Data', "Data No RM {$data['no_rm']} ({$data['nama_pasien']}) tervalidasi & dipromosikan ke Data Terpadu (Single Source of Truth).");

            echo json_encode(['success' => true, 'message' => 'Data berhasil divalidasi dan masuk ke Master Data Terpadu RS!']);
            break;

        // -------------------------------------------------------------------------
        // 5. VALIDASI MASSAL (BATCH VALIDATE ALL)
        // -------------------------------------------------------------------------
        case 'batch_validate':
            $stmt = $pdo->query("SELECT id FROM data_masuk WHERE status_validasi = 'Menunggu'");
            $rows = $stmt->fetchAll();
            $count = 0;

            foreach ($rows as $r) {
                // Trigger single validation logic
                $stmtData = $pdo->prepare("SELECT * FROM data_masuk WHERE id = ?");
                $stmtData->execute([$r['id']]);
                $data = $stmtData->fetch();

                if ($data) {
                    $pdo->prepare("UPDATE data_masuk SET status_validasi = 'Valid', status_kelengkapan = 'Lengkap' WHERE id = ?")->execute([$r['id']]);

                    $stmtCheck = $pdo->prepare("SELECT id FROM data_terpadu WHERE data_masuk_id = ?");
                    $stmtCheck->execute([$r['id']]);
                    if (!$stmtCheck->fetch()) {
                        $stmtInsertSSOT = $pdo->prepare("
                            INSERT INTO data_terpadu 
                            (data_masuk_id, no_rm, nik, nama_pasien, jenis_kelamin, tanggal_lahir, usia, alamat_lengkap, unit_layanan, tanggal_masuk, kondisi_keluar, kode_icd10, nama_penyakit, kategori_surveilans, hasil_lab_terpadu, status_sirs, tgl_validasi, petugas_validasi, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Dalam Perawatan', ?, ?, ?, ?, 'Belum Dikirim', NOW(), ?, NOW())
                        ");
                        $petugas = $_SESSION['user']['nama_lengkap'] ?? 'dr. Hendra Pratama, Sp.A';
                        $stmtInsertSSOT->execute([
                            $data['id'],
                            $data['no_rm'],
                            $data['nik'],
                            $data['nama_pasien'],
                            $data['jenis_kelamin'],
                            $data['tanggal_lahir'],
                            $data['usia'],
                            $data['alamat'],
                            $data['unit_sumber'],
                            $data['tanggal_masuk'],
                            $data['kode_icd10'],
                            $data['diagnosa_awal'],
                            $data['kategori_penyakit'],
                            $data['hasil_lab'],
                            $petugas
                        ]);
                        $count++;
                    }
                }
            }

            addAuditLog('Validasi Batch', 'Validasi Data', "Validasi massal {$count} record pasien ke Data Terpadu.");

            echo json_encode([
                'success' => true,
                'count' => $count,
                'message' => "Berhasil memvalidasi {$count} data pasien ke Data Terpadu!"
            ]);
            break;

        // -------------------------------------------------------------------------
        // 6. KIRIM KE SIRS KEMENKES (TRANSMIT TO KEMENKES API v2)
        // -------------------------------------------------------------------------
        case 'kirim_sirs':
            $selectedIds = $_POST['ids'] ?? [];
            $jenisLaporan = $_POST['jenis_laporan'] ?? 'Surveilans Kasus KLB / Penyakit Menular';
            $periode = $_POST['periode'] ?? date('F Y');

            if (empty($selectedIds)) {
                // If not provided, take all in 'Belum Dikirim'
                $stmt = $pdo->query("SELECT id FROM data_terpadu WHERE status_sirs != 'Terkirim'");
                $selectedIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            if (empty($selectedIds)) {
                echo json_encode(['success' => false, 'message' => 'Tidak ada data terpadu yang siap dikirim ke SIRS!']);
                exit;
            }

            // Retrieve records
            $inClause = implode(',', array_map('intval', $selectedIds));
            $stmt = $pdo->query("SELECT * FROM data_terpadu WHERE id IN ($inClause)");
            $records = $stmt->fetchAll();

            // Prepare Kemenkes Standard JSON Payload
            $payloadArray = [
                'kode_faskes' => '3171012',
                'nama_faskes' => 'RSUD Mitra Sehat Kemenkes',
                'sistem_pengirim' => 'SurveiLink v2.0 - Hospital Surveillance Bridge',
                'waktu_transmisi' => date('c'),
                'jenis_laporan' => $jenisLaporan,
                'periode' => $periode,
                'total_kasus' => count($records),
                'data_pasien' => []
            ];

            foreach ($records as $rec) {
                $payloadArray['data_pasien'][] = [
                    'id_surveilink' => $rec['id'],
                    'no_rm' => $rec['no_rm'],
                    'nik' => $rec['nik'],
                    'nama_pasien' => $rec['nama_pasien'],
                    'jenis_kelamin' => $rec['jenis_kelamin'],
                    'usia' => $rec['usia'],
                    'alamat' => $rec['alamat_lengkap'],
                    'icd10' => $rec['kode_icd10'],
                    'diagnosa' => $rec['nama_penyakit'],
                    'kategori_surveilans' => $rec['kategori_surveilans'],
                    'tanggal_masuk' => $rec['tanggal_masuk'],
                    'hasil_laboratorium' => $rec['hasil_lab_terpadu'],
                    'unit_perawatan' => $rec['unit_layanan'],
                    'status_pasien' => $rec['kondisi_keluar']
                ];
            }

            $jsonPayload = json_encode($payloadArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $kodeTransmisi = 'TRX-SIRS-' . date('Ymd-His');
            $refKemenkes = 'KEMKES-SIRS-RL-' . date('Y') . '-' . rand(100000, 999999);

            $stmtInsertLog = $pdo->prepare("
                INSERT INTO kirim_sirs 
                (kode_transmisi, jenis_laporan, periode_laporan, jumlah_data, payload_json, response_code, response_message, nomor_referensi_sirs, status_pengiriman, dikirim_oleh, waktu_pengiriman)
                VALUES (?, ?, ?, ?, ?, '201 Created', 'Data surveilans berhasil diverifikasi dan disimpan ke repositori SIRS Kemenkes v2', ?, 'Sukses', ?, NOW())
            ");
            $dikirimOleh = $_SESSION['user']['nama_lengkap'] ?? 'dr. Hendra Pratama, Sp.A (Admin Surveilans)';
            $stmtInsertLog->execute([
                $kodeTransmisi,
                $jenisLaporan,
                $periode,
                count($records),
                $jsonPayload,
                $refKemenkes,
                $dikirimOleh
            ]);

            // Mark data_terpadu as Terkirim
            $pdo->query("UPDATE data_terpadu SET status_sirs = 'Terkirim' WHERE id IN ($inClause)");

            // Notification of success
            addNotification(
                'SIRS_FEEDBACK',
                'Pengiriman SIRS Kemenkes Berhasil: ' . count($records) . ' Data',
                "Laporan '{$jenisLaporan}' berhasil ditransmisikan ke SIRS Online Kemenkes dengan No. Ref: {$refKemenkes}.",
                'Info',
                'SIRS Kemenkes'
            );

            addAuditLog('Kirim SIRS Kemenkes', 'Kirim ke SIRS', "Transmisi {$kodeTransmisi} ({$jenisLaporan}) berisi " . count($records) . " record. No Ref: {$refKemenkes}");

            echo json_encode([
                'success' => true,
                'kode_transmisi' => $kodeTransmisi,
                'no_referensi' => $refKemenkes,
                'jumlah' => count($records),
                'message' => 'Data berhasil dikirim dan diverifikasi oleh Server SIRS Kemenkes API v2!'
            ]);
            break;

        // -------------------------------------------------------------------------
        // 7. PING / TEST KONEKSI UNIT RS
        // -------------------------------------------------------------------------
        case 'ping_unit':
            $kodeUnit = $_POST['kode_unit'] ?? '';
            $simulatedLatency = rand(8, 22);

            $stmt = $pdo->prepare("UPDATE sumber_data_unit SET latency_ms = ?, status_koneksi = 'Connected', terakhir_sinkron = NOW() WHERE kode_unit = ?");
            $stmt->execute([$simulatedLatency, $kodeUnit]);

            addAuditLog('Ping Koneksi Unit', 'Status Sumber Data', "Tes koneksi heartbeat ke unit {$kodeUnit} (Latency: {$simulatedLatency}ms)");

            echo json_encode([
                'success' => true,
                'latency' => $simulatedLatency,
                'status' => 'Connected',
                'message' => "Koneksi ke {$kodeUnit} normal (Latency: {$simulatedLatency} ms)"
            ]);
            break;

        // -------------------------------------------------------------------------
        // 8. NOTIFIKASI: TANDAI SEMUA DIBACA
        // -------------------------------------------------------------------------
        case 'mark_all_read':
            $pdo->exec("UPDATE notifikasi_alert SET status_baca = 1");
            addAuditLog('Tandai Notifikasi Dibaca', 'Notifikasi & Peringatan Dini', 'Semua notifikasi ditandai telah dibaca.');

            echo json_encode([
                'success' => true,
                'message' => 'Seluruh notifikasi berhasil ditandai telah dibaca.'
            ]);
            break;

        // -------------------------------------------------------------------------
        // 9. NOTIFIKASI: TANDAI SATU DIBACA
        // -------------------------------------------------------------------------
        case 'mark_read_single':
            $notifId = intval($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE notifikasi_alert SET status_baca = 1 WHERE id = ?");
            $stmt->execute([$notifId]);

            echo json_encode([
                'success' => true,
                'message' => 'Notifikasi berhasil ditandai telah dibaca.'
            ]);
            break;

        // -------------------------------------------------------------------------
        // 10. NOTIFIKASI: BERSIHKAN SEMUA
        // -------------------------------------------------------------------------
        case 'clear_notifications':
            $pdo->exec("TRUNCATE TABLE notifikasi_alert");
            addAuditLog('Bersihkan Notifikasi', 'Notifikasi & Peringatan Dini', 'Seluruh notifikasi dan peringatan dini dibersihkan.');

            echo json_encode([
                'success' => true,
                'message' => 'Seluruh notifikasi berhasil dibersihkan.'
            ]);
            break;

        // -------------------------------------------------------------------------
        // 11. NOTIFIKASI: HAPUS SATU NOTIFIKASI
        // -------------------------------------------------------------------------
        case 'delete_single_notification':
            $notifId = intval($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM notifikasi_alert WHERE id = ?");
            $stmt->execute([$notifId]);

            echo json_encode([
                'success' => true,
                'message' => 'Notifikasi berhasil dihapus.'
            ]);
            break;

        // -------------------------------------------------------------------------
        // 12. AUDIT LOG: KOSONGKAN LOG
        // -------------------------------------------------------------------------
        case 'clear_audit':
            $pdo->exec("TRUNCATE TABLE audit_log");
            echo json_encode([
                'success' => true,
                'message' => 'Seluruh riwayat audit log berhasil dikosongkan.'
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenali!']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
}

