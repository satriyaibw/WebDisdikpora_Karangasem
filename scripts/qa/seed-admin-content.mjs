import { chromium } from '@playwright/test';
import { existsSync } from 'fs';

const BASE = process.env.QA_BASE_URL || 'http://localhost';
// Direktori aset demo (slider.jpg, news1.jpg, ..., album2.jpg) untuk upload.
const ASSETS = process.env.QA_ASSETS_DIR || '/tmp/opencode/assets';
// Kredensial admin (nilai bawaan = akun dari DatabaseSeeder/dev). Selalu bisa
// dioverride lewat env untuk lingkungan non-dev.
const ADMIN = process.env.QA_ADMIN_EMAIL || 'admin@disdikpora.karangasemkab.go.id';
const PASS = process.env.QA_ADMIN_PASSWORD || 'Password!2026';

const ASSET_FILES = ['slider.jpg', 'news1.jpg', 'news2.jpg', 'lampiran.pdf', 'infografis.jpg', 'album1.jpg', 'album2.jpg'];

const loc = (m) => `[id="${m}"]`;
const SUBMIT = 'button.fi-btn[type="submit"]';

async function waitSubmit(page) {
  await page.waitForSelector(SUBMIT, { timeout: 20000 });
}

async function pickSearchable(page, model, label) {
  const sel = page.locator(loc(model));
  const wrapper = sel.locator('xpath=ancestor::div[contains(concat(" ", normalize-space(@class), " "), " choices ")][1]');
  await wrapper.locator('.choices__inner').click();
  await page.waitForTimeout(400);
  const opt = wrapper.locator('.choices__list--dropdown .choices__item').filter({ hasText: new RegExp(`^\\s*${label}\\s*$`) }).first();
  await opt.click();
}

async function uploadFile(page, path, nth = 0) {
  await page.locator('input[type=file]').nth(nth).setInputFiles(path);
  await page.waitForTimeout(4000);
}

async function setField(page, spec) {
  const { model, type, value } = spec;
  if (type === 'text' || type === 'date' || type === 'datetime' || type === 'time' || type === 'url' || type === 'number') {
    const el = page.locator(loc(model));
    await el.fill(value);
    if (type === 'text') { await el.press('Tab'); await page.waitForTimeout(250); }
  } else if (type === 'textarea') {
    await page.locator(loc(model)).fill(value);
  } else if (type === 'select') {
    await page.locator(loc(model)).selectOption({ label: value });
    await page.waitForTimeout(200);
  } else if (type === 'searchable') {
    await pickSearchable(page, model, value);
  } else if (type === 'richtext') {
    const inputId = `trix-value-${model}`;
    await page.evaluate(({ inputId, value }) => {
      const te = document.querySelector('trix-editor');
      if (te) te.editor.loadHTML(`<div>${value}</div>`);
      else document.querySelector(`#${inputId}`).value = value;
    }, { inputId, value });
    await page.waitForTimeout(300);
  } else if (type === 'file') {
    await uploadFile(page, value);
  } else if (type === 'toggle') {
    if (value === true) {
      const sw = page.locator('.fi-fo-toggle');
      const checked = await sw.getAttribute('aria-checked');
      if (checked !== 'true') await sw.click();
    }
  }
}

async function createResource(page, url, fields, label) {
  await page.goto(BASE + url, { waitUntil: 'domcontentloaded' });
  await waitSubmit(page);
  await page.waitForTimeout(500);
  for (const f of fields) await setField(page, f);
  await page.click(SUBMIT);
  await page.waitForURL('**/admin*/**/edit', { timeout: 20000 });
  await page.waitForTimeout(800);
  const toast = await page.locator('[role="status"], .fi-fo-notification, [x-data*="notification"]').first().textContent().catch(() => '');
  console.log(`OK ${label} -> ${page.url().replace(BASE, '')} | toast: ${(toast || 'none').trim().slice(0, 60)}`);
}

(async () => {
  const missing = ASSET_FILES.filter((f) => !existsSync(`${ASSETS}/${f}`));
  if (missing.length > 0) {
    console.error(`Aset demo tidak ditemukan di ${ASSETS}: ${missing.join(', ')}`);
    console.error('Atur env QA_ASSETS_DIR ke direktori berisi file tersebut, lalu jalankan ulang.');
    process.exit(1);
  }

  const browser = await chromium.launch();
  const page = await browser.newPage();
  await page.goto(BASE + '/admin/login');
  await page.fill('[id="data.email"]', ADMIN);
  await page.fill('[id="data.password"]', PASS);
  await page.click('button[type=submit]');
  await page.waitForURL('**/admin*', { timeout: 15000 });
  console.log('LOGIN OK');

  const now = new Date();
  const pad = (n) => String(n).padStart(2, '0');
  const localDate = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  const localDateTime = (d) => `${localDate(d)}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
  const addDay = (n) => { const d = new Date(now.getTime() + n * 86400000); return localDate(d); };
  const stamp = `${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;

  await createResource(page, '/admin/sliders/create', [
    { model: 'data.image', type: 'file', value: ASSETS + '/slider.jpg' },
    { model: 'data.title', type: 'text', value: 'Selamat Datang di Situs Resmi Disdikpora Karangasem' },
    { model: 'data.description', type: 'textarea', value: 'Melayani dengan Hati — Pendidikan Bermutu, Pemuda Berprestasi, Olahraga Berjaya.' },
    { model: 'data.link', type: 'url', value: 'https://disdikpora.karangasemkab.go.id' },
    { model: 'data.sort_order', type: 'number', value: '1' },
    { model: 'data.is_active', type: 'toggle', value: true },
  ], 'SLIDER');

  await createResource(page, '/admin/news/create', [
    { model: 'data.title', type: 'text', value: 'Disdikpora Karangasem Gelar Sosialisasi SPMB 2026 (' + stamp + ')' },
    { model: 'data.category_id', type: 'searchable', value: 'Pendidikan' },
    { model: 'data.excerpt', type: 'textarea', value: 'Sosialisasi penerimaan murid baru tahun ajaran 2026 untuk jenjang SD dan SMP se-Kabupaten Karangasem.' },
    { model: 'data.content', type: 'richtext', value: 'Karangasem, 10 Agustus 2026 — Dinas Pendidikan Kepemudaan dan Olahraga Kabupaten Karangasem menyelenggarakan sosialisasi Sistem Penerimaan Murid Baru (SPMB) tahun ajaran 2026-2027.' },
    { model: 'data.cover_image', type: 'file', value: ASSETS + '/news1.jpg' },
    { model: 'data.status', type: 'select', value: 'Terbit' },
    { model: 'data.published_at', type: 'datetime', value: localDateTime(now) },
  ], 'NEWS 1');

  await createResource(page, '/admin/news/create', [
    { model: 'data.title', type: 'text', value: 'Turnamen Olahraga Pelajar Karangasem Resmi Dibuka (' + stamp + ')' },
    { model: 'data.category_id', type: 'searchable', value: 'Olahraga' },
    { model: 'data.excerpt', type: 'textarea', value: 'Ribuan pelajar dari berbagai kecamatan berlaga dalam kejuaraan antar sekolah.' },
    { model: 'data.content', type: 'richtext', value: 'Karangasem — Turnamen Olahraga Pelajar se-Kabupaten Karangasem resmi dibuka oleh Kepala Dinas Pendidikan Kepemudaan dan Olahraga.' },
    { model: 'data.cover_image', type: 'file', value: ASSETS + '/news2.jpg' },
    { model: 'data.status', type: 'select', value: 'Terbit' },
    { model: 'data.published_at', type: 'datetime', value: localDateTime(now) },
  ], 'NEWS 2');

  await createResource(page, '/admin/announcements/create', [
    { model: 'data.title', type: 'text', value: 'Pengumuman Penerimaan Peserta Didik Baru Tahun 2026 (' + stamp + ')' },
    { model: 'data.content', type: 'richtext', value: 'Diumumkan kepada seluruh masyarakat bahwa penerimaan peserta didik baru jenjang SD dan SMP dibuka mulai tanggal 1 Juli 2026.' },
    { model: 'data.announcement_number', type: 'text', value: '800/421/DISDIKPORA' },
    { model: 'data.announcement_date', type: 'date', value: localDate(now) },
    { model: 'data.attachment_path', type: 'file', value: ASSETS + '/lampiran.pdf' },
    { model: 'data.is_important', type: 'toggle', value: true },
    { model: 'data.status', type: 'select', value: 'Terbit' },
  ], 'ANNOUNCEMENT 1');

  await createResource(page, '/admin/announcements/create', [
    { model: 'data.title', type: 'text', value: 'Jadwal Pembagian Rapor Semester Genap (' + stamp + ')' },
    { model: 'data.content', type: 'richtext', value: 'Pembagian rapor semester genap dilaksanakan serentak di seluruh satuan pendidikan.' },
    { model: 'data.announcement_number', type: 'text', value: '800/422/DISDIKPORA' },
    { model: 'data.announcement_date', type: 'date', value: localDate(now) },
    { model: 'data.status', type: 'select', value: 'Terbit' },
  ], 'ANNOUNCEMENT 2');

  await createResource(page, '/admin/agendas/create', [
    { model: 'data.title', type: 'text', value: 'Rapat Koordinasi Kepala Sekolah se-Karangasem' },
    { model: 'data.date', type: 'date', value: addDay(5) },
    { model: 'data.start_time', type: 'time', value: '08:30' },
    { model: 'data.end_time', type: 'time', value: '12:00' },
    { model: 'data.location', type: 'text', value: 'Aula Dinas Pendidikan Karangasem' },
    { model: 'data.pic', type: 'text', value: 'Sekretariat Dinas' },
    { model: 'data.description', type: 'textarea', value: 'Koordinasi persiapan pelaksanaan SPMB 2026.' },
  ], 'AGENDA 1');

  await createResource(page, '/admin/agendas/create', [
    { model: 'data.title', type: 'text', value: 'Final Lomba Poster Anti Narkoba' },
    { model: 'data.date', type: 'date', value: addDay(3) },
    { model: 'data.start_time', type: 'time', value: '09:00' },
    { model: 'data.location', type: 'text', value: 'Gedung Kesenian Karangasem' },
    { model: 'data.pic', type: 'text', value: 'Bidang Pemuda' },
  ], 'AGENDA 2');

  await createResource(page, '/admin/agendas/create', [
    { model: 'data.title', type: 'text', value: 'Senam Pagi Bersama ASN Disdikpora' },
    { model: 'data.date', type: 'date', value: addDay(7) },
    { model: 'data.start_time', type: 'time', value: '06:30' },
    { model: 'data.location', type: 'text', value: 'Halaman Kantor Disdikpora' },
  ], 'AGENDA 3');

  await createResource(page, '/admin/infographics/create', [
    { model: 'data.image', type: 'file', value: ASSETS + '/infografis.jpg' },
    { model: 'data.title', type: 'text', value: 'Infografis SPMB 2026' },
    { model: 'data.link', type: 'url', value: 'https://disdikpora.karangasemkab.go.id' },
    { model: 'data.is_active', type: 'toggle', value: true },
  ], 'INFOGRAPHIC');

  await createResource(page, '/admin/videos/create', [
    { model: 'data.title', type: 'text', value: 'Profil Disdikpora Karangasem' },
    { model: 'data.youtube_url', type: 'url', value: 'https://www.youtube.com/watch?v=jNQXAC9IVRw' },
    { model: 'data.description', type: 'textarea', value: 'Video profil singkat Dinas Pendidikan.' },
    { model: 'data.status', type: 'select', value: 'Terbit' },
  ], 'VIDEO');

  await page.goto(BASE + '/admin/albums/create', { waitUntil: 'domcontentloaded' });
  await waitSubmit(page);
  await page.locator(loc('data.title')).fill('Galeri Kegiatan SPMB 2026 (' + stamp + ')');
  await page.locator(loc('data.description')).fill('Dokumentasi sosialisasi penerimaan murid baru.');
  const addPhotoBtn = page.getByRole('button', { name: /Tambah Foto|Add item/i }).first();
  await addPhotoBtn.click();
  await page.waitForTimeout(400);
  await addPhotoBtn.click();
  await page.waitForTimeout(400);
  await uploadFile(page, ASSETS + '/album1.jpg', 0);
  await uploadFile(page, ASSETS + '/album2.jpg', 1);
  await page.click(SUBMIT);
  await page.waitForURL('**/albums*/**/edit', { timeout: 20000 });
  console.log('OK ALBUM ->', page.url().replace(BASE, ''));

  await browser.close();
  console.log('SELESAI — semua konten demo dibuat');
})().catch((e) => { console.error('ERR', e.message); process.exit(1); });
