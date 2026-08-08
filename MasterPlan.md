	# Master Implementation Roadmap (V2 Final)
## Pembangunan Website Resmi Dinas Pendidikan Kepemudaan dan Olahraga Kabupaten Karangasem

---

## 📋 1. Ringkasan Eksekutif & Prinsip Arsitektur

Dokumen ini merupakan **Rencana Pembangunan Global (High-Level Master Plan)** untuk website resmi **Disdikpora Kabupaten Karangasem**. Platform ini dirancang berfokus pada **Repositori Informasi, Katalog Layanan Publik, Dokumen SOP, dan Keterbukaan Informasi Publik (PPID)** sesuai dengan standar **Sistem Pemerintahan Berbasis Elektronik (SPBE)**, kriteria keamanan **BSSN**, dan **UU KIP No. 14 Tahun 2008**.

### Pilar Arsitektur Utama:
1. **Maintainability untuk Junior Programmer:** Arsitektur *Monolithic MVC* (Laravel 11 + FilamentPHP v3) yang bersih, deklaratif, dan mudah dikembangkan tanpa kerumitan framework JavaScript terpisah (SPA).
2. **Performa Tinggi & Ringan (Lightweight):** Penggunaan *server-side caching* (Redis), kompresi media otomatis (`.webp`), dan *in-browser PDF preview* agar situs dapat diakses cepat (<2 detik) bahkan di area dengan jaringan terbatas.
3. **Kemudahan Akses Publik (Zero Barrier):** Pengunjung dapat langsung mencari, membaca, dan mengunduh berkas tanpa melalui formulir permohonan yang rumit.

---

## 🛠️ 2. Spesifikasi Tech Stack

| Layer | Teknologi | Keterangan & Keunggulan |
| :--- | :--- | :--- |
| **Base Framework** | PHP 8.3 + Laravel 11.x | Stabil, dokumentasi melimpah, dan menjadi standar pengembangan di Indonesia. |
| **Admin Panel Engine** | FilamentPHP v3 | CMS otomatis berbasis PHP deklaratif untuk pembuatan form, tabel, dan filter. |
| **Frontend Rendering** | Blade + Livewire 3 + Tailwind CSS | UI reaktif, ringan, fully-responsive, dan mudah diatur tampilannya. |
| **Database** | MySQL 8.0 / MariaDB | Kompatibel dengan infrastruktur Server Data Center Pemkab Karangasem / Diskominfo. |
| **Cache & Queue** | Redis | Menangani caching query dokumen/layanan, session, dan kompresi berkas background. |
| **Environment** | Docker & Docker Compose | Konsistensi lingkungan dari lokal laptop developer hingga server produksi. |

---

## 🗺️ 3. Struktur Navigasi Utama (Sitemap Publik)

```
[ LOGO DISDIKPORA KARANGASEM ]
├── 1. Beranda (Homepage)
├── 2. Profil Instansi
│   ├── Sambutan Kepala Dinas
│   ├── Visi, Misi & Tupoksi
│   └── Struktur Organisasi
├── 3. Katalog Layanan Publik (Kumpulan Layanan)
├── 4. Dokumen SOP (Kumpulan Dokumen SOP per Bidang)
├── 5. Informasi PPID (Kumpulan Dokumen PPID per Kategori)
│   ├── Informasi Berkala
│   ├── Informasi Serta Merta
│   └── Informasi Setiap Saat
├── 6. Berita & Media
│   ├── Berita & Artikel
│   ├── Pengumuman
│   ├── Agenda Dinas
│   └── Galeri Foto & Video
└── 7. Kontak & Pengaduan (SP4N-LAPOR! & Internal)
```

---

## 🚀 4. Peta Jalan Pengerjaan (Sequential Phase Roadmap)

```
[Fase 1: Foundation] ──► [Fase 2: Auth & CMS] ──► [Fase 3: Content Engine] ──► [Fase 4: Repositori PPID]
                                                                                      │
[Fase 8: Launching] ◄── [Fase 7: Hardening] ◄── [Fase 6: Public UI/UX] ◄── [Fase 5: Layanan & SOP]
```

---

### Fase 1: Inisiasi, Infrastruktur & Environment Setup
> **Fokus:** Menyiapkan fondasi proyek, repository code, dan lingkungan pengembangan lokal berbasis Docker.

- [ ] **1.1 Setup Repository & Version Control**
  - Inisialisasi Git repository (GitHub/GitLab).
  - Penetapan branching strategy (`main`, `staging`, `feature/*`).
  - Pembuatan `.gitignore`, `.env.example`, dan `README.md`.
- [ ] **1.2 Containerization Environment (Docker)**
  - Penyusunan `docker-compose.yml` (App Laravel, MySQL 8.0, Redis, Nginx, Mailpit).
  - Pengujian *zero-configuration setup* agar junior programmer cukup mengeksekusi `docker compose up`.
- [ ] **1.3 Inisialisasi Project Laravel 11**
  - Instalasi Laravel versi 11.
  - Konfigurasi koneksi database, Redis cache, dan queue driver.
- [ ] **1.4 Skema Base Migration Database**
  - Perancangan dan eksekusi migrasi tabel dasar (`users`, `roles`, `permissions`, `settings`, `audit_logs`).

---

### Fase 2: Autentikasi, Hak Akses & Admin CMS Baseline
> **Fokus:** Membangun panel admin yang aman dan terstruktur berbasis peran (RBAC).

- [ ] **2.1 Integrasi Admin Engine (FilamentPHP v3)**
  - Instalasi Filament Core, Form Builder, dan Table Builder.
  - Kustomisasi tema panel admin (Logo Disdikpora & Warna Identitas Kabupaten Karangasem).
- [ ] **2.2 Implementasi Role-Based Access Control (RBAC)**
  - Integrasi `spatie/laravel-permission` ke Filament.
  - Penyiapan Peran Default:
    - `Super Admin` (Akses Penuh Seluruh Sistem).
    - `Admin Redaksi / Berita` (Kelola berita, pengumuman, agenda, galeri).
    - `Admin PPID & SOP` (Kelola repositori dokumen PPID & SOP).
    - `Admin Layanan Publik` (Kelola katalog layanan & formulir).
- [ ] **2.3 Audit Logs & Activity Tracking**
  - Pencatatan otomatis aktivitas admin (*Create, Update, Delete*) untuk keamanan data.
- [x] **2.4 Mekanisme Lupa Password Panel Admin (*Issue #22*)**
  - Alur reset kata sandi via email (Filament `passwordReset()` + Password broker Laravel, tautan sekali pakai kedaluwarsa 60 menit).
  - Anti-spam: rate limit 5 permintaan/menit per alamat IP dan per email pada halaman permintaan, pesan "Terlalu banyak percobaan".
  - Anti-enumeration: pesan sukses seragam untuk email terdaftar maupun tidak.
  - Seluruh antarmuka & email Bahasa Indonesia; prasyarat produksi: SMTP + TLS + `MAIL_FROM_ADDRESS` resmi.

---

### Fase 3: Modul Engine Informasi & Pengelolaan Konten Utama
> **Fokus:** Mengembangkan modul input data informasi harian instansi.

- [ ] **3.1 Manajemen Berita & Artikel**
  - CRUD Berita dengan Rich Text Editor (WYSIWYG).
  - Categorization (Pendidikan, Kepemudaan, Olahraga, Umum).
  - Status Publikasi (`Draft`, `Published`, `Archived`) dan *Scheduled Publishing*.
  - **Auto Image Optimization:** Auto-kompresi gambar yang diunggah ke format `.webp`.
- [ ] **3.2 Manajemen Pengumuman & Infografis**
  - CRUD Pengumuman resmi beserta lampiran berkas PDF.
  - Modul Infografis/Spanduk informasi visual.
- [ ] **3.3 Manajemen Agenda Dinas**
  - Perekaman agenda kerja pimpinan & dinas (Tanggal, Waktu, Lokasi, Penanggung Jawab).
- [ ] **3.4 Manajemen Hero Banner Slider**
  - Pengaturan spanduk gambar utama di halaman depan beserta teks *Call to Action* (CTA).
- [ ] **3.5 Galeri Foto & Video**
  - Manajemen album foto kegiatan dan integrasi link video YouTube resmi.

---

### Fase 4: Modul Repositori Dokumen Informasi PPID
> **Fokus:** Mengelola repositori Keterbukaan Informasi Publik sesuai amanat UU KIP No. 14/2008.

- [ ] **4.1 Pengelompokan Taksonomi PPID**
  - Struktur Kategori Dokumen:
    - *Informasi Berkala* (LAKIP, RENSTRA, RENJA, DPA, Laporan Keuangan).
    - *Informasi Serta Merta* (Pengumuman Darurat, Kondisi Bencana/Keselamatan).
    - *Informasi Setiap Saat* (Daftar Peraturan, Ringkasan Program, Daftar Informasi Publik).
- [ ] **4.2 Digital Document Repository Engine**
  - Upload dan manajemen berkas PDF.
  - Validasi MIME-Type PDF ketat dan pembatasan ukuran berkas.
  - Penambahan Metadata: Nomor Dokumen, Tahun Terbit, Ukuran Berkas, dan Deskripsi Singkat.

---

### Fase 5: Modul Kumpulan Layanan Publik & Kumpulan SOP
> **Fokus:** Mengelola katalog layanan masyarakat dan direktori dokumen Standar Operasional Prosedur.

- [ ] **5.1 Engine Katalog Layanan Publik**
  - Pendataan jenis layanan per Bidang (e.g., Mutasi Siswa, Legalisir Ijazah, Rekomendasi Penelitian, Operasional PAUD).
  - Field Rincian Layanan: Persyaratan, Bagan Alur Prosedur, Estimasi Waktu (SLA), Biaya (Rp0 / Gratis), Kontak Penanggung Jawab, dan File Template Formulir.
- [ ] **5.2 Engine Repositori Dokumen SOP**
  - Pendataan SOP per Bidang/Sub-Bagian (Sekretariat, Pembinaan Pendidikan PAUD & PNF, Pembinaan Pendidikan SD, Pembinaan Pendidikan SMP, Pendidik & Tenaga Kependidikan, Pemuda & Olahraga).
  - Upload Berkas PDF SOP & Metadata (Nomor SOP, Tanggal Pengesahan, Judul).
- [ ] **5.3 Center Pusat Unduhan Berkas (Download Center)**
  - Pengelompokan formulir resmi dan Petunjuk Teknis (Juknis) yang sering dibutuhkan sekolah/masyarakat.

---

### Fase 6: Antarmuka Publik (Public Portal Frontend) & UI/UX Integration
> **Fokus:** Menyajikan tampilan luar yang modern, bersih, cepat, dan responsif di semua ukuran layar.

- [ ] **6.1 Master Layout & Design System**
  - Skema warna identitas portal publik (Biru `#2196F3`, Emas, Putih, Hitam). *Catatan: warna biru khusus tampilan publik; tema admin Filament tetap merah.*
  - Header (Navigasi Utama, Search Bar, Waktu WITA) & Footer Resmi Pemerintah.
- [ ] **6.2 Halaman Utama (Homepage)**
  - Hero Slider Banner & Pengumuman Running Text.
  - Section Pintasan Utama (Katalog Layanan, Dokumen SOP, Dokumen PPID).
  - Section Berita Terbaru, Agenda Dinas, Infografis & Video Galeri.
- [ ] **6.3 Halaman Kumpulan Layanan Publik**
  - Filter kategori bidang & pencarian kata kunci instan.
  - Kartu Layanan (Card View) + Pop-up Modal / Detail Halaman berisi Syarat, Alur, SLA, dan Tombol Download Form.
- [ ] **6.4 Halaman Kumpulan Dokumen SOP**
  - Filter SOP per Bidang/Sub-Bagian.
  - **In-Browser PDF Viewer:** Pratinjau dokumen PDF langsung di layar tanpa wajib unduh.
  - Tombol Unduh Berkas Langsung.
- [ ] **6.5 Halaman Kumpulan Dokumen PPID (3 Kategori)**
  - Tampilan *Tabbed Navigation* (*Informasi Berkala* | *Informasi Serta Merta* | *Informasi Setiap Saat*).
  - Tabel dokumen responsif dilengkapi kolom pencarian dan tombol pratinjau/unduh.
- [ ] **6.6 Halaman Profil Instansi & Kontak/Pengaduan**
  - Sambutan Kadis, Visi Misi, Bagan Struktur Organisasi Interaktif.
  - Halaman Kontak, Peta Google Maps Alamat Dinas, & Tautan Pengaduan SP4N-LAPOR!

---

### Fase 7: Performa, Keamanan & Hardening Sistem (BSSN Compliance)
> **Fokus:** Mengunci keamanan, mengoptimalkan kecepatan akses, dan persiapan *production*.

- [ ] **7.1 Performance & Caching Optimization**
  - Implementasi *Redis Query Caching* untuk menu, data SOP, Layanan, dan PPID.
  - Browser HTTP Caching untuk aset statis (CSS, JS, Gambar).
  - *Lazy loading* gambar di seluruh halaman publik.
- [ ] **7.2 Security Hardening**
  - Sanitasi ketat input WYSIWYG dari potensi serangan XSS (*Cross-Site Scripting*).
  - Proteksi CSRF pada semua formulir.
  - Pembatasan laju lalu lintas (*Rate Limiting*) pada login admin dan form kontak.
  - Konfigurasi Security Headers (`X-Frame-Options`, `X-Content-Type-Options`, `CSP`).
- [ ] **7.3 SEO & Accessibility**
  - OpenGraph Meta Tags untuk pratinjau tautan rapi saat dibagikan ke WhatsApp/Facebook.
  - Peta situs otomatis (`sitemap.xml`) dan `robots.txt`.

---

### Fase 8: QA, UAT, Pelatihan Junior Programmer & Launching
> **Fokus:** Pengujian menyeluruh, alih pengetahuan (handover), dan deployment ke server produksi.

- [ ] **8.1 Quality Assurance (QA)**
  - Pengujian responsivitas layar (Desktop, Tablet, Smartphone) & uji kompatibilitas browser.
- [ ] **8.2 User Acceptance Testing (UAT)**
  - Pengujian fungsi bersama tim internal Disdikpora Karangasem & perbaikan masukan minor.
- [ ] **8.3 Alih Pengetahuan (Handover) & Pelatihan Junior Programmer**
  - Penyusunan **Buku Panduan Pengelolaan Admin (User Manual)**.
  - Pelatihan teknis internal:
    - Cara membuat modul CRUD baru di Filament.
    - Cara melakukan backup & restore database.
    - Cara deploy pembaruan kode via Git.
- [ ] **8.4 Deployment Produksi & Go-Live**
  - Migrasi aplikasi & database ke Server Data Center Diskominfo Karangasem.
  - Pemasangan Sertifikat SSL (HTTPS).
  - Pointing Domain Resmi (`disdikpora.karangasemkab.go.id`).
- [ ] **8.5 Post-Launch Monitoring**
  - Monitoring error log dan performa server minggu pertama.

---

## 📊 5. Matriks Urutan Pengerjaan Fitur (Execution Checklist)

Tabel ini digunakan sebagai acuan *sprint* harian/mingguan untuk memantau progres pengerjaan proyek:

| No | Nama Fitur / Modul | Fase | Bobot | Target Output Utama |
| :---: | :--- | :---: | :---: | :--- |
| 1 | Setup Docker, Git & Laravel Baseline | Fase 1 | 5% | Repository & Environment lokal siap |
| 2 | Migration & Base Database Seeder | Fase 1 | 5% | Skema tabel dasar terbuat |
| 3 | Install FilamentPHP & RBAC Spatie | Fase 2 | 8% | Panel Admin aktif dengan manajemen peran |
| 4 | Audit Log Tracker & Profile Management | Fase 2 | 4% | Histori aktivitas admin tercatat |
| 5 | Modul CMS Berita & Auto WebP Converter | Fase 3 | 10% | Rilis berita & kompresi gambar otomatis |
| 6 | Modul Pengumuman, Agenda & Slider | Fase 3 | 8% | Data pengumuman, agenda, & slider siap |
| 7 | Modul Galeri Foto & Video | Fase 3 | 4% | Album kegiatan & YouTube terintegrasi |
| 8 | Modul Repositori Dokumen PPID (3 Kategori)| Fase 4 | 10% | Manajemen dokumen KIP per kategori |
| 9 | Modul Katalog Layanan Publik & Formulir | Fase 5 | 8% | Manajemen alur, syarat & form layanan |
| 10 | Modul Dokumen SOP per Bidang | Fase 5 | 8% | Manajemen berkas SOP per sub-bagian |
| 11 | Master Layout & Homepage Layout | Fase 6 | 10% | Tampilan depan utama selesai & responsive |
| 12 | Halaman Kumpulan Layanan Publik (Frontend)| Fase 6 | 5% | Publik bisa cari & lihat detail layanan |
| 13 | Halaman Kumpulan Dokumen SOP (Frontend) | Fase 6 | 5% | Pratinjau PDF SOP & unduh langsung |
| 14 | Halaman Kumpulan Dokumen PPID (Frontend)| Fase 6 | 5% | Tabbed PPID (Berkala, Serta Merta, Setiap Saat) |
| 15 | Redis Caching & Security Hardening | Fase 7 | 3% | Kecepatan web <2d & aman standar BSSN |
| 16 | QA, UAT, Training Junior & Go-Live | Fase 8 | 2% | Rilis resmi di server Diskominfo |

---

## 👨‍💻 6. Panduan Pemeliharaan untuk Junior Programmer

Panduan teknis cepat bagi programmer internal instansi untuk mengoperasikan proyek ini:

### 1. Memulai Lingkungan Pengembangan Lokal
```bash
# Clone repository
git clone <repository-url>
cd disdikpora-karangasem

# Copy environment file & jalankan container
cp .env.example .env
docker compose up -d --build

# Generate key & migrasi database
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

### 2. Membuat Modul CMS Baru di Panel Admin (Filament)
Jika ingin menambah tabel CRUD baru (misal: Data Beasiswa/Bantuan), cukup jalankan perintah berikut:
```bash
docker compose exec app php artisan make:filament-resource NamaModul
```

### 3. Membersihkan Cache Server Pasca Update
```bash
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize
```

---

*Disusun oleh: **Web Architect & IT Consultant SPBE***  
*Instansi Target: **Dinas Pendidikan Kepemudaan dan Olahraga Kabupaten Karangasem***
