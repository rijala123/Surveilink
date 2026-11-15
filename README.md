# SurveiLink - Sistem Integrasi & Sinkronisasi Data Surveilans Rumah Sakit

**SurveiLink** adalah sistem dashboard dan jembatan integrasi data surveilans rumah sakit yang dirancang untuk **Kasus 2: Integrasi & Sinkronisasi Data Surveilans Rumah Sakit**. Aplikasi ini berfokus pada mengatasi fragmentasi data medis antar-unit rumah sakit, pembentukan *Single Source of Truth* (SSOT), standardisasi kode surveilans internal (ICD-10/LOINC), serta sinkronisasi otomatis ke sistem pelaporan eksternal Kementerian Kesehatan RI (**SIRS Online Kemenkes API v2**).

Dibangun menggunakan **PHP Native, Vanilla JavaScript, Tailwind CSS (CDN), dan MySQL di Laragon / phpMyAdmin**.

---

## 🏥 Akses Aplikasi & phpMyAdmin di Laragon

- **URL Aplikasi**: [http://localhost/SurveiLink/](http://localhost/SurveiLink/)
- **Halaman Login**: [http://localhost/SurveiLink/login.php](http://localhost/SurveiLink/login.php)
- **Halaman Registrasi**: [http://localhost/SurveiLink/register.php](http://localhost/SurveiLink/register.php)
- **Dashboard Utama**: [http://localhost/SurveiLink/dashboard.php](http://localhost/SurveiLink/dashboard.php)
- **Database phpMyAdmin**: `surveilink_db` di [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)
- **File Skema Database**: [`surveilink_db.sql`](surveilink_db.sql)
- **Dokumentasi PRD Lengkap**: [`doc/prd.md`](doc/prd.md) (Product Requirements Document)

### 🔑 Kredensial Login Bawaan:
- **Admin Epidemiologi & Surveilans**: `admin` / `admin123` (Akses Penuh)
- **Petugas Rekam Medis**: `petugas_rm` / `admin123`
- **Admin Unit IGD**: `unit_igd` / `admin123`

---

## 📋 10 Halaman (Frame) SurveiLink & Navigasi Antar Halaman

Seluruh 10 halaman telah terhubung dan dapat diklik secara interaktif melalui sidebar navigasi dan tautan cepat:

1. **[Login SurveiLink](login.php)**  
   Halaman autentikasi petugas rumah sakit dengan seleksi peran/unit (Admin Surveilans, Petugas Rekam Medis, Admin IGD), validasi akun, dan fitur tombol 1-klik *Quick Demo Fill*.

2. **[Dashboard Utama](dashboard.php)**  
   Pusat kendali eksekutif dengan 5 kartu indikator KPI (Total Data Masuk: 0, Menunggu Validasi: 0, Data Terpadu: 0, Terkirim SIRS: 0, Peringatan Dini: 0), diagram visual tren surveilans KLB (DBD, ISPA, TBC), status konektivitas 6 unit RS, dan diagram alur pipeline integrasi 4-tahap.

3. **[Status Sumber Data](sumber_data.php)**  
   Monitoring status konektivitas, latensi (ping), dan endpoint 6 unit sumber RS (SIMRS IGD, SIMRS Rajal, SIMRS Ranap, LIS Laboratorium, FIS Farmasi, SISR ICU) dilengkapi tombol *Tes Ping* dan *Tarik Data Unit*.

4. **[Data Masuk](data_masuk.php)**  
   *Staging area* untuk data mentah pasien surveilans yang ditarik dari unit-unit rumah sakit sebelum diverifikasi. Dilengkapi fitur filter unit, pencarian No. RM/NIK, modal input pasien manual, dan penarikan data batch.

5. **[Validasi Data](validasi_data.php)**  
   *Workbench* penyelesaian fragmentasi dan deduplikasi data: verifikasi NIK 16 digit, standardisasi kode ICD-10 Kemenkes, resolusi anomali diagnosa klinis vs hasil laboratorium, serta tombol *Validasi 1-Klik* dan *Validasi Massal ke Single Source of Truth*.

6. **[Data Terpadu (Single Source of Truth)](data_terpadu.php)**  
   Master data surveilans rumah sakit yang terkonsolidasi dan terstandar (*Golden Record*), riwayat perawatan lintas unit, fitur ekspor CSV/Excel, serta pencetakan rekapitulasi laporan surveilans.

7. **[Kirim ke SIRS](kirim_sirs.php)**  
   Modul pengiriman data ke Ditjen Yankes Kemenkes RI. Mendukung formulir RL 5.1, RL 5.2, RL 5.3, RL 5.4, dan Kasus KLB EWARS, seleksi checklist data, *live preview payload JSON* standar Kemenkes v2, dan transmisi API interaktif dengan respons HTTP 201 Created.

8. **[Riwayat Pengiriman](riwayat_pengiriman.php)**  
   Log rekam jejak pengiriman ke SIRS Kemenkes, mencatat kode transmisi, nomor referensi pelaporan Kemenkes, jumlah data, payload JSON terkirim, serta fitur cetak bukti tanda terima pelaporan.

9. **[Notifikasi & Peringatan Dini](notifikasi.php)**  
   Sistem deteksi dini *Early Warning Alert and Response System* (EWARS) untuk lonjakan kasus berpotensi KLB (DBD/ISPA/Kolera), deteksi keterlambatan sinkronisasi data dari unit RS, dan feedback transmisi SIRS.

10. **[Audit Log](audit_log.php)**  
    Audit trail kepatuhan keamanan rekam medis dan privasi data pasien (Permenkes), mencatat aktivitas setiap pengguna (waktu, peran, modul, detail aktivitas, dan IP address).

---

## 🔄 Status Data Awal (Reset ke 0) & Fitur Pengujian Alur

Sesuai instruksi:
- **Status Data Awal**: Seluruh data operasional (`data_masuk`, `data_terpadu`, `kirim_sirs`, `notifikasi_alert`, `audit_log`) telah di-reset dan dimulai dari **angka 0** (tampilan *empty-state* elegan).
- **Fitur Uji Coba Data (Header Navbar)**:
  - Tombol **"Uji Coba Data" -> "Tarik Data Pasien Unit RS"**: Menarik data surveilans realistis (kasus DBD, TBC, ISPA berat, Campak) dari IGD & Lab sehingga Anda dapat menguji seluruh alur integrasi dari Data Masuk &rarr; Validasi &rarr; Data Terpadu &rarr; Kirim ke SIRS.
  - Tombol **"Reset Semua Data ke 0"**: Mengosongkan kembali seluruh data ke angka 0 kapan saja untuk evaluasi kondisi awal.

---

## 📚 Dokumentasi Teknis & Spesifikasi Sistem

Dokumentasi lengkap sistem SurveiLink tersedia di direktori `doc/`:
- **[Product Requirements Document (PRD)](doc/prd.md)**: Dokumen spesifikasi kebutuhan produk, latar belakang klinis kasus 2, kamus data 8 tabel, spesifikasi 10 modul fungsional, dan skema transmisi SIRS Online Kemenkes API v2.
- **[Panduan Pembuatan Aplikasi Full AI](doc/cara_pembuatan_full_ai.md)**: Panduan metodologi dan alur kerja pengembangan aplikasi secara 100% berbasis kecerdasan buatan, memanfaatkan ekosistem **Google Stitch** (Desain UI), **Antigravity IDE** (AI Coding Assistant), dan **Laragon v6** (Local Development Stack).
- **[Spesifikasi Arsitektur Sistem](doc/architecture.md)**: Dokumen teknis arsitektur berlapis (*Tiered Architecture*), topologi jaringan intranet 6 unit RS, diagram sequence transmisi SIRS, dan arsitektur keamanan data.


