# Web Resmi Disdikpora Kabupaten Karangasem

Website resmi **Dinas Pendidikan, Kepemudaan dan Olahraga Kabupaten Karangasem** — platform repositori informasi, katalog layanan publik, dokumen SOP, dan keterbukaan informasi publik (PPID) sesuai standar SPBE.

## Tech Stack

| Layer | Teknologi |
| :--- | :--- |
| Base Framework | PHP 8.3 (Docker) / 8.2+ (native — distro terbaru seperti Fedora 44 membawa 8.5, berfungsi) + Laravel 11.x |
| Admin Panel Engine | FilamentPHP v3 *(Fase 2)* |
| Frontend | Blade + Livewire 3 + Tailwind CSS |
| Database | MySQL 8.4 LTS (Docker) / MySQL 8.x atau MariaDB (native, kompatibel) |
| Cache & Queue | Redis 7 (Docker, dengan password) / Redis atau Valkey (native) |
| Mail | Mailpit (Docker) / `MAIL_MAILER=log` (native) |
| Environment | Docker & Docker Compose — atau instalasi native langsung (lihat bagian Setup Lokal) |

## Prasyarat

### Docker

- Docker Engine + Docker Compose plugin
- Sudo akses untuk menjalankan service Docker (pada sebagian mesin)
- Composer 2 di host (folder `vendor/` di-mount ke container, jadi `composer install` dijalankan di host)

### Native (tanpa Docker)

- PHP ≥ 8.2 CLI dengan ekstensi: `pdo_mysql`/`mysqlnd`, `xml`, `mbstring`, `curl`, `gd`, `zip`, `intl`, `bcmath`, `opcache`, `redis` (untuk session/queue; opsional bila memakai `CACHE_STORE=file` dan driver session/queue alternatif)
- Composer 2 (disarankan installer resmi https://getcomposer.org — versi dari apt/dnf sering tertinggal)
- MySQL 8.x atau MariaDB
- Redis atau Valkey (untuk session/queue; opsional bila memakai driver lain)
- Node.js ≥ 18 + npm

Contoh instalasi paket:

```bash
# Fedora / RHEL / Rocky Linux (dnf)
sudo dnf install php php-cli php-fpm php-mysqlnd php-xml php-mbstring php-curl php-gd php-zip php-intl php-bcmath php-redis composer mariadb-server redis nodejs npm

# Debian / Ubuntu (apt)
sudo apt update
sudo apt install php-cli php-fpm php-mysql php-xml php-mbstring php-curl php-gd php-zip php-intl php-bcmath php-redis composer mariadb-server redis-server nodejs npm
```

## Setup Docker (Zero-Configuration)

```bash
# 1. Clone repository
git clone https://github.com/satriyaibw/WebDisdikpora_Karangasem.git
cd WebDisdikpora_Karangasem

# 2. Copy environment file
cp .env.example .env

# 3. Install dependency (host — folder vendor/ di-mount ke container).
#    `composer install` otomatis membuat symlink public/storage → ../storage/app/public
#    via script post-autoload-dump (lihat composer.json).
composer install

# 4. Jalankan seluruh container (App, Nginx, MySQL, Redis, Mailpit, Queue Worker)
docker compose up -d --build

# 5. Generate key aplikasi
docker compose exec app php artisan key:generate

# 6. Migrasi database + seeder awal
docker compose exec app php artisan migrate --seed

# 7. (Cadangan bila langkah 3 tidak menjalankannya) Publikasikan symlink
#    storage/app/public ke public/storage — WAJIB agar unduhan & pratinjau
#    berkas (SOP, PPID, unduhan, galeri, berita) tidak 404.
docker compose exec app php artisan storage:link
```

Setelah selesai, akses:

- **Website:** http://localhost
- **Mailpit UI:** http://localhost:8025

## Setup Lokal (Native / Tanpa Docker)

```bash
# 1. Prasyarat (lihat bagian Prasyarat → Native) lalu buat database & user:
#    mysql -u root -p
#    CREATE DATABASE disdikpora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
#    CREATE USER 'disdikpora'@'localhost' IDENTIFIED BY 'disdikpora';
#    GRANT ALL PRIVILEGES ON disdikpora.* TO 'disdikpora'@'localhost';
#    FLUSH PRIVILEGES;

# 2. Clone repository
git clone https://github.com/satriyaibw/WebDisdikpora_Karangasem.git
cd WebDisdikpora_Karangasem

# 3. Copy environment file
cp .env.example .env

# 4. Sesuaikan variabel wajib di .env (nilai default .env.example untuk Docker):
#    APP_URL=http://127.0.0.1:8000     # bukan http://localhost (artisan serve = port 8000)
#    DB_HOST=127.0.0.1                 # bukan "db" (nama service Docker)
#    REDIS_HOST=127.0.0.1              # bukan "redis"
#    REDIS_PASSWORD=null               # bila Redis/Valkey lokal tanpa password
#    MAIL_MAILER=log                   # tanpa Mailpit — email ditulis ke log
#    CACHE_STORE=file                  # HANYA bila phpredis 6.x memicu error
#                                      # "Cannot use bool as array" (lihat Troubleshooting)

# 5. Install dependency + build aset frontend
composer install          # otomatis membuat symlink public/storage (post-autoload-dump)
npm install && npm run build

# 6. Generate key & siapkan storage
php artisan key:generate
php artisan storage:link  # publikasi storage/app/public ke public/storage

# 7. Migrasi database + seeder awal
php artisan migrate --seed

# 8. Jalankan web server (development)
php artisan serve         # buka http://127.0.0.1:8000
```

### Queue worker & scheduler native

Fungsi yang dijalankan service `queue-worker` di Docker perlu dijalankan manual di terminal terpisah (atau via supervisor/systemd):

```bash
php artisan queue:work redis --sleep=3 --tries=3   # proses antrian
php artisan schedule:work                          # scheduler (cron pengganti)
```

## Pemecahan Masalah (Troubleshooting)

| Gejala | Penyebab | Solusi |
| :--- | :--- | :--- |
| Unduh/pratinjau berkas 404 (SOP, PPID, unduhan, galeri, berita) | Symlink `public/storage` belum ada — `Storage::url()` menghasilkan `{APP_URL}/storage/...` yang tidak dilayani web server | `php artisan storage:link`, lalu bersihkan cache (`php artisan optimize:clear`). Otomatis dibuat saat `composer install` via `post-autoload-dump` |
| URL situs/berkas mengarah ke port 80 saat memakai `php artisan serve` | `APP_URL=http://localhost` (benar hanya untuk nginx Docker di port 80) | Set `APP_URL=http://127.0.0.1:8000` di `.env` lalu `php artisan optimize:clear` |
| `ErrorException: Cannot use bool as array` di `RedisTagSet` saat cache Redis | phpredis 6.x tidak kompatibel dengan operasi cache bertag Laravel 11 (mis. `PublicCache` flush saat seeding) | Set `CACHE_STORE=file` di `.env`. Session/queue via Redis tetap aman — hanya cache bertag yang bermasalah |
| Koneksi database gagal di native | `DB_HOST=db` (nama service Docker) tidak dikenal di host | Set `DB_HOST=127.0.0.1` (sesuaikan user/password dengan database yang dibuat) |
| Email tidak terkirim / Mailpit tidak ada di native | `.env` masih `MAIL_MAILER=smtp` + `MAIL_HOST=mailpit` | Set `MAIL_MAILER=log` (atau konfigurasi SMTP sendiri) |
| `public/storage` terlanjur ada sebagai folder (bukan symlink) | Dibuat manual tanpa `storage:link` | Hapus folder kosong tersebut (JANGAN hapus `storage/app/public`), lalu `php artisan storage:link` |
| Halaman beranda blank/cache basi setelah update kode | Cache aplikasi lama | `php artisan optimize:clear` (+ `optimize` bila produksi) |

## Perintah Utama (dijalankan di dalam container)

```bash
# Melihat status service
docker compose ps

# Akses terminal PHP di dalam container
docker compose exec app sh

# Membersihkan cache server pasca update
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize

# Audit keamanan dependency Composer
docker compose exec app composer audit
```

## Struktur Direktori

```
├── app/                 # Logic aplikasi (Models, Controllers, Services)
├── bootstrap/           # Bootstrap framework & cache
├── config/              # Konfigurasi aplikasi
├── database/            # Migration, seeder, factory
├── docker/              # Konfigurasi container (nginx, php)
├── public/              # Entry point publik (index.php, assets, symlink storage/)
├── resources/           # Blade views, assets, bahasa
├── routes/              # Definisi route
├── storage/             # Cache, log, upload lokal (storage/app/public di-publish via storage:link)
├── tests/               # Unit & feature tests
├── vendor/              # Dependency Composer (tidak dikomit)
├── Dockerfile           # Image PHP 8.3-fpm untuk service app
└── docker-compose.yml   # Orkestrasi environment development
```

## Branching Strategy

| Branch | Keterangan |
| :--- | :--- |
| `main` | Produksi — hanya terima merge dari `staging` setelah QA |
| `staging` | Uji integrasi antar developer |
| `feature/*` | Kerja harian, dibuat dari `staging` |

Commit convention: `type: deskripsi singkat` (contoh: `feat: ...`, `fix: ...`, `docs: ...`).

## User Awal (Seeder)

- Email: `admin@disdikpora.karangasemkab.go.id`
- Password: nilai `ADMIN_INITIAL_PASSWORD` pada `.env` (ubah sebelum deployment produksi)
- Jika `ADMIN_INITIAL_PASSWORD` tidak diset, seeder membuat password acak dan mencetaknya ke output terminal saat user admin baru pertama kali dibuat.

## Catatan Implementasi

- Seluruh environment diset timezone **WITA** (`Asia/Makassar`).
- Database tersimpan di volume Docker `dbdata` — tidak hilang saat container di-restart.
- Versi Laravel mengikuti keputusan issue #1: **Laravel 11.x** (framework ini telah EOL; pastikan roadmap peningkatan versi keamanan direncanakan pada fase hardening).
- Konfigurasi rahasia HANYA disimpan di `.env` lokal, tidak pernah dikomit.
- Port `3306` (MySQL) dan `6379` (Redis) hanya dipublikasikan ke `127.0.0.1` (loopback) untuk keperluan tooling lokal.
- `php artisan storage:link` otomatis dieksekusi saat `composer install`/`composer update` (script `post-autoload-dump` di `composer.json`, idempotent — tidak menimpa symlink/folder yang sudah ada). `public/storage` dan `storage/app/public` di-gitignore sehingga aman di semua environment.
- Seeder membuat PDF contoh valid (bukan string PDF kosong), sehingga pratinjau data hasil seeding tampil normal.

## Catatan Keamanan

- **`composer.json` menonaktifkan blokir security advisory** (`policy.advisories.block: false`). Ini disengaja karena Laravel 11 sudah EOL dan memiliki CVE publik sehingga `composer install` akan gagal jika diaktifkan. **WAJIB dihapus** setelah upgrade ke Laravel 12.61.1+ sebelum go-live, dan pantau kerentanan secara rutin dengan `composer audit`.
- MySQL 8.0 telah EOL sejak April 2026 — image telah di-pin ke `mysql:8.4` (LTS). Perhatikan versi image Docker lain yang di-pin agar selalu mendapat update keamanan.

## Referensi

- `MasterPlan.md` — Rencana Implementasi Global (roadmap 8 fase)
- Issue & backlog — https://github.com/satriyaibw/WebDisdikpora_Karangasem/issues
