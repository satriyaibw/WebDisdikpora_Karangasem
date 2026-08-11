# Laporan QA — Fase 8.1 (Pengujian Otomatis & Audit)

Disusun 2026-08-10. Skrip pengujian tersedia di `scripts/qa/` (Playwright + Lighthouse).
Suite otomatis PHP: `docker compose exec app php artisan test`.

## Hasil

| Area | Hasil |
|---|---|
| Suite PHP (Unit + Feature) | **285 passed** (1298 assertions) |
| Pint (gaya kode) | PASS (236 berkas) |
| Responsivitas — 15 halaman × 3 viewport | **45/45 PASS** (0 overflow) |
| Kompatibilitas browser — 15 halaman × Chromium/Firefox/WebKit | **45/45 PASS** |
| Lighthouse — 8 halaman utama | Aksesibilitas **100**, Best Practices **100**, SEO **100**, Performa 76–78 |

## Pemetaan ke MasterPlan

- **Fase 2.1** (SEO & Aksesibilitas): akses 100 pada seluruh halaman publik, heading berurutan, kontras WCAG AA, target sentuh rutin.
- **Fase 5.1** (Kinerja): CLS 0.406 → 0; LCP tertunda 6.3 s karena `x-cloak` hero dihilangkan; skor performa stabil 76–78 (sisa gap: bundle Livewire bawaan).
- **Fase 6.4** (In-Browser PDF Viewer): pratinjau PDF SOP di iframe — sempat diblokir CSP (`frame-src`), sudah dibuka `'self'`; terverifikasi di 3 engine browser.
- **Fase 7.2** (CSP): `frame-src 'self'` dibuka untuk pratinjau PDF SOP (iframe same-origin `/storage/…`) tanpa melonggarkan origin pihak ketiga; `object-src` tetap `'none'` (tidak ada `<object>`/`<embed>`), dikuatkan test `SecurityHeadersTest` (assert `frame-src 'self'` + `object-src 'none'`).
- **Fase 8.1** (Pengujian): script QA + laporan ini.

## Perbaikan Utama dari Audit

- Overflow horizontal 111px (smartphone) di 15 halaman — email footer kini `break-all`.
- CLS 0.406 di beranda — hero tidak lagi disembunyikan `x-cloak` sampai Alpine inisialisasi.
- Kontras: `brand-500 #2196F3` → `#1475C4`, `brand-600 #0D7BD2` → `#0B74C6` (rasio ≥ 4.5:1).
- Heading order: `h2` semantik (sr-only) untuk Pintasan Utama, Katalog Layanan/SOP, Dokumen PPID, Daftar Berita.
- Dots slider 24px (target sentuh), `aria-label` logo disinkronkan dengan teks terlihat.

## Catatan Terbuka

- `livewire.js` (~389 KB) tidak diminifkan oleh Livewire 3; dominan pada TTI & skor performa — dianggap bawaan, tidak dimodifikasi.
- `bf-cache` nonaktif pada halaman Livewire (default ekosistem Alpine/Livewire).
- Biarkan `node_modules/` dan `results/` di bawah `scripts/qa/` tidak di-commit (`.gitignore`).
- Skrip QA dapat dioverride lewat env: `QA_BASE_URL`, `QA_ADMIN_EMAIL`, `QA_ADMIN_PASSWORD`, `QA_ASSETS_DIR` (lihat README → QA browser & audit).

Detail audit lengkap: `scripts/qa/results/` (JSON Lighthouse + laporan rinci).