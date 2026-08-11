import { chromium } from '@playwright/test';

const BASE = process.env.QA_BASE_URL || 'http://localhost';
// Kredensial admin (nilai bawaan = akun dari DatabaseSeeder/dev). Selalu bisa
// dioverride lewat env untuk lingkungan non-dev.
const ADMIN_EMAIL = process.env.QA_ADMIN_EMAIL || 'admin@disdikpora.karangasemkab.go.id';
const ADMIN_PASSWORD = process.env.QA_ADMIN_PASSWORD || 'Password!2026';
const ADMIN = '/admin';

async function probe(page, url, name) {
  await page.goto(BASE + url, { waitUntil: 'networkidle' });
  await page.waitForTimeout(500);
  const fields = await page.evaluate(() => {
    const out = [];
    document.querySelectorAll('input, textarea, select, [contenteditable="true"], [role="checkbox"]').forEach((el) => {
      const label = el.closest('[class*="fi-field-wrapper"], div');
      let labelText = '';
      if (label) {
        const lbl = label.querySelector('label, legend');
        labelText = lbl ? lbl.textContent.trim() : '';
      }
      out.push({
        tag: el.tagName.toLowerCase(),
        type: el.getAttribute('type') || '',
        model: el.getAttribute('wire:model') || el.getAttribute('name') || '',
        placeholder: el.getAttribute('placeholder') || '',
        label: labelText,
        visible: !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length),
      });
    });
    return out;
  });
  console.log(`\n=== ${name} (${url}) ===`);
  for (const f of fields) {
    console.log(`  ${f.tag}/${f.type} model=${f.model || '-'} label="${f.label || f.placeholder}" visible=${f.visible}`);
  }
}

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  await page.goto(BASE + ADMIN + '/login', { waitUntil: 'networkidle' });
  await page.fill('[id="data.email"]', ADMIN_EMAIL);
  await page.fill('[id="data.password"]', ADMIN_PASSWORD);
  await page.click('button[type=submit]');
  await page.waitForURL('**/admin*', { timeout: 15000 });
  console.log('LOGIN OK ->', page.url());

  await probe(page, '/admin/sliders/create', 'Slider');
  await probe(page, '/admin/news/create', 'News');
  await probe(page, '/admin/announcements/create', 'Announcement');
  await probe(page, '/admin/agendas/create', 'Agenda');
  await probe(page, '/admin/infographics/create', 'Infographic');
  await probe(page, '/admin/videos/create', 'Video');
  await probe(page, '/admin/albums/create', 'Album');
  await browser.close();
})().catch((e) => { console.error('ERR', e.message); process.exit(1); });