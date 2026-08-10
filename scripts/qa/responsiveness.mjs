import { chromium } from '@playwright/test';
import { mkdirSync, writeFileSync } from 'fs';

const BASE = 'http://localhost';
const OUT = 'results';
const VIEWPORTS = [
  { name: 'desktop', width: 1366, height: 768 },
  { name: 'tablet', width: 1024, height: 768 },
  { name: 'smartphone', width: 390, height: 844 },
];
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
  ['galeri-detail', '/galeri/1'],
  ['profil-struktur', '/profil/struktur'],
];

mkdirSync(OUT, { recursive: true });

(async () => {
  const browser = await chromium.launch();
  const results = { summary: { total: 0, pass: 0, fail: 0 }, rows: [] };

  for (const [name, path] of PAGES) {
    for (const vp of VIEWPORTS) {
      const page = await browser.newPage({ viewport: { width: vp.width, height: vp.height } });
      const row = { page: name, viewport: vp.name, status: 'FAIL', issues: [] };
      try {
        const resp = await page.goto(BASE + path, { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(600);
        row.status = resp && resp.status() === 200 ? 'OK' : 'FAIL';
        if (row.status !== 'OK') row.issues.push(`HTTP ${resp?.status()}`);

        const overflow = await page.evaluate(() => {
          const d = document.documentElement;
          return d.scrollWidth - d.clientWidth;
        });
        if (overflow > 0) row.issues.push(`horizontal overflow ${overflow}px`);

        const must = ['header', 'footer'];
        for (const el of must) {
          const count = await page.locator(el).count();
          if (count === 0) row.issues.push(`missing <${el}>`);
        }

        const shotDir = `${OUT}/screenshots/${name}`;
        mkdirSync(shotDir, { recursive: true });
        await page.screenshot({ path: `${shotDir}/${vp.name}.png`, fullPage: true });
      } catch (e) {
        row.status = 'FAIL';
        row.issues.push(e.message.split('\n')[0].slice(0, 120));
      }
      if (row.status === 'OK' && row.issues.length === 0) row.status = 'PASS';
      results.summary.total++;
      row.status === 'PASS' ? results.summary.pass++ : results.summary.fail++;
      results.rows.push(row);
      console.log(`${row.status.padEnd(4)} ${name.padEnd(18)} ${vp.name.padEnd(11)} ${row.issues.join('; ')}`);
      await page.close();
    }
  }

  writeFileSync(`${OUT}/responsiveness.json`, JSON.stringify(results, null, 2));
  await browser.close();
  console.log(`\nRESPONSIVITAS: ${results.summary.pass}/${results.summary.total} PASS`);
})().catch((e) => { console.error('ERR', e.message); process.exit(1); });