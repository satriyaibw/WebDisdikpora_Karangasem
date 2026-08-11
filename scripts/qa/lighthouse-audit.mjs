import { launch } from 'chrome-launcher';
import { chromium as pwChromium } from '@playwright/test';
import lh from 'lighthouse';
import { existsSync, mkdirSync, writeFileSync } from 'fs';

const BASE = process.env.QA_BASE_URL || 'http://localhost';
const OUT = 'results/lighthouse';
mkdirSync(OUT, { recursive: true });

// chrome-launcher butuh executable Chrome/Chromium. Bila CHROME_PATH tidak
// diset, pakai Chromium yang sudah di-install bersama Playwright.
if (!process.env.CHROME_PATH) {
  const pwPath = pwChromium.executablePath();
  if (existsSync(pwPath)) {
    process.env.CHROME_PATH = pwPath;
  }
}

const PAGES = [
  ['home', '/'],
  ['profil', '/profil'],
  ['layanan', '/layanan'],
  ['sop', '/sop'],
  ['ppid', '/ppid'],
  ['berita', '/berita'],
  ['unduhan', '/unduhan'],
  ['kontak', '/kontak'],
];

(async () => {
  const chrome = await launch({ chromeFlags: ['--headless=new', '--no-sandbox', '--disable-gpu'] });
  const rows = [];
  for (const [name, path] of PAGES) {
    const url = BASE + path;
    try {
      const r = await lh(url, { port: chrome.port, output: 'json', onlyCategories: ['performance', 'accessibility', 'best-practices', 'seo'], logLevel: 'error' });
      const c = r.lhr.categories;
      rows.push({
        page: name,
        performance: Math.round(c.performance.score * 100),
        accessibility: Math.round(c.accessibility.score * 100),
        'best-practices': Math.round(c['best-practices'].score * 100),
        seo: Math.round(c.seo.score * 100),
      });
      writeFileSync(`${OUT}/${name}.json`, JSON.stringify(r.lhr, null, 2));
      const line = rows[rows.length - 1];
      console.log(`${name.padEnd(12)} Perf ${line.performance}  Akses ${line.accessibility}  BP ${line['best-practices']}  SEO ${line.seo}`);
    } catch (e) {
      console.log(`${name.padEnd(12)} ERR ${e.message.slice(0, 120)}`);
    }
  }
  writeFileSync(`${OUT}/summary.json`, JSON.stringify(rows, null, 2));
  await chrome.kill();
})();