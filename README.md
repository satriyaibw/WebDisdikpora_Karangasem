# Web Resmi Disdikpora Kabupaten Karangasem

Website resmi **Dinas Pendidikan, Kepemudaan dan Olahraga Kabupaten Karangasem** — platform repositori informasi, katalog layanan publik, dokumen SOP, dan keterbukaan informasi publik (PPID) sesuai standar SPBE.

## Tech Stack

| Layer | Teknologi |
| :--- | :--- |
| Base Framework | PHP 8.3 + Laravel 11.x |
| Admin Panel Engine | FilamentPHP v3 *(Fase 2)* |
| Frontend | Blade + Livewire 3 + Tailwind CSS |
| Database | MySQL 8.0 |
| Cache & Queue | Redis |
| Environment | Docker & Docker Compose |

## Prasyarat

- Docker Engine + Docker Compose plugin
- Sudo akses untuk menjalankan service Docker (pada sebagian mesin)

## Setup Lokal (Zero-Configuration)

```bash
# 1. Clone repository
git clone <repository-url>
cd WebDisdikpora_Karangasem

# 2. Copy environment file
cp .env.example .env

# 3. Jalankan seluruh container (App, Nginx, MySQL, Redis, Mailpit)
docker compose up -d --build

# 4. Generate key aplikasi
docker compose exec app php artisan key:generate

# 5. Migrasi database + seeder awal
docker compose exec app php artisan migrate --seed
```

Setelah selesai, akses:

- **Website:** http://localhost
- **Mailpit UI:** http://localhost:8025

## Perintah Utama (dijalankan di dalam container)

```bash
# Melihat status service
docker compose ps

# Akses terminal PHP di dalam container
docker compose exec app sh

# Membersihkan cache server pasca update
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize
```

## Struktur Direktori

```
├── app/                 # Logic aplikasi (Models, Controllers, Services)
├── bootstrap/           # Bootstrap framework & cache
├── config/              # Konfigurasi aplikasi
├── database/            # Migration, seeder, factory
├── docker/              # Konfigurasi container (nginx, php)
├── public/              # Entry point publik (index.php, assets)
├── resources/           # Blade views, assets, bahasa
├── routes/              # Definisi route
├── storage/             # Cache, log, upload lokal
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

## Catatan Implementasi

- Seluruh environment diset timezone **WITA** (`Asia/Makassar`).
- Database tersimpan di volume Docker `dbdata` — tidak hilang saat container di-restart.
- Versi Laravel mengikuti keputusan issue #1: **Laravel 11.x** (framework ini telah EOL; pastikan roadmap peningkatan versi keamanan direncanakan pada fase hardening).
- Konfigurasi rahasia HANYA disimpan di `.env` lokal, tidak pernah dikomit.

## Referensi

- `MasterPlan.md` — Rencana Implementasi Global (roadmap 8 fase)
- `issue.md` — Issue per fase
