import { chromium, firefox, webkit } from '@playwright/test';
import { mkdirSync, writeFileSync } from 'fs';

const BASE = process.env.QA_BASE_URL || 'http://localhost';
const OUT = 'results';
const VIEWPORT = { width: 1366, height: 768 };
const BROWSERS = [['chromium', chromium], ['firefox', firefox], ['webkit', webkit]];
// Satu-satunya error konsol yang sengaja diabaikan: font CDN pihak ketiga
// (fonts.bunny.net) yang bisa tidak terjangkau saat offline. pageerror TIDAK pernah difilter.
const IGNORED_CONSOLE = [/fonts\.bunny\.net/];
const PAGES = [
  ['home', '/'],
  ['profil', '/profil'],
  ['layanan', '/layanan'],
  ['sop', '/sop'],
  ['ppid', '/ppid'],
  ['berita', '/berita'],
  ['pengumuman', '/pengumuman'],
  ['agenda', '/agenda'],
  ['galeri', '/galeri'],
  ['unduhan', '/unduhan'],
  ['kontak', '/kontak'],
  ['layanan-detail', '/layanan/legalisir-ijazah'],
  ['sop-detail', '/sop/sop-legalisir-ijazah'],
  ['berita-detail', '/berita/disdikpora-karangasem-gelar-sosialisasi-spmb-2026-08-10'],
  ['galeri-detail', '/galeri/1'],
];

mkdirSync(OUT, { recursive: true });

(async () => {
  const results = { summary: { total: 0, pass: 0, fail: 0 }, rows: [] };
  for (const [bname, btype] of BROWSERS) {
    const browser = await btype.launch();
    for (const [name, path] of PAGES) {
      const page = await browser.newPage({ viewport: VIEWPORT });
      const row = { browser: bname, page: name, status: 'FAIL', issues: [] };
      try {
        const errors = [];
        page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text().slice(0, 160)); });
        page.on('pageerror', (e) => errors.push('pageerror: ' + e.message.slice(0, 160)));
        const resp = await page.goto(BASE + path, { waitUntil: 'domcontentloaded', timeout: 30000 });
        await page.waitForLoadState('load');
        await page.waitForSelector('footer', { timeout: 15000 });
        await page.waitForTimeout(500);
        if (resp && resp.status() === 200) { row.status = 'OK'; } else { row.issues.push(`HTTP ${resp?.status()}`); row.status = 'FAIL'; }
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
        if (overflow > 0) row.issues.push(`overflow ${overflow}px`);
        const missing = await page.evaluate(() => {
          const bad = [];
          if (!document.querySelector('header')) bad.push('header');
          if (!document.querySelector('footer')) bad.push('footer');
          return bad;
        });
        row.issues.push(...missing.map((m) => 'missing ' + m));
        const realErrors = errors.filter((e) => !IGNORED_CONSOLE.some((re) => re.test(e)));
        row.issues.push(...realErrors.slice(0, 3).map((e) => 'js: ' + e));
        row.status = row.issues.length === 0 ? 'PASS' : 'FAIL';
        if (row.status === 'FAIL') {
          const d = `${OUT}/screenshots-browser/${name}`;
          mkdirSync(d, { recursive: true });
          await page.screenshot({ path: `${d}/${bname}.png`, fullPage: true });
        }
      } catch (e) {
        row.issues.push(e.message.split('\n')[0].slice(0, 140));
      }
      results.summary.total++;
      row.status === 'PASS' ? results.summary.pass++ : results.summary.fail++;
      results.rows.push(row);
      console.log(`${bname.padEnd(9)} ${row.status.padEnd(4)} ${name.padEnd(18)} ${row.issues.join('; ')}`);
      await page.close();
    }
    await browser.close();
  }
  writeFileSync(`${OUT}/browser-compat.json`, JSON.stringify(results, null, 2));
  console.log(`\nKOMPATIBILITAS: ${results.summary.pass}/${results.summary.total} PASS`);
})().catch((e) => { console.error('ERR', e.message); process.exit(1); });