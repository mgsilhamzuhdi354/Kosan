import { test, expect } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';

const baseURL = process.env.BLACKBOX_BASE_URL || 'http://localhost/Kosan/kos2-putri-betung/public';
const outputDir = process.env.BLACKBOX_OUTPUT_DIR || path.join('screenshots', `blackbox-${new Date().toISOString().replace(/[:.]/g, '-')}`);

const results = [];

function ensureOutputDir() {
  fs.mkdirSync(outputDir, { recursive: true });
}

function screenshotPath(fileName) {
  return path.join(outputDir, fileName);
}

async function assertHealthyPage(page) {
  const body = await page.locator('body').innerText();
  expect(body).not.toMatch(/server error|fatal error|exception|stack trace/i);
  expect(body.trim().length).toBeGreaterThan(20);
}

async function saveScreenshot(page, fileName) {
  await page.screenshot({ path: screenshotPath(fileName), fullPage: true });
}

async function runCase(browser, id, title, steps) {
  const context = await browser.newContext({
    viewport: { width: 1366, height: 768 },
  });
  const page = await context.newPage();

  try {
    await steps(page);
    await assertHealthyPage(page);
    await saveScreenshot(page, `${id}-pass.png`);
    results.push({ id, title, status: 'PASS', screenshot: `${id}-pass.png`, note: 'Halaman berhasil dimuat dan validasi UI terpenuhi.' });
  } catch (error) {
    await saveScreenshot(page, `${id}-fail.png`).catch(() => {});
    results.push({ id, title, status: 'FAIL', screenshot: `${id}-fail.png`, note: error.message.replace(/\s+/g, ' ').trim() });
  } finally {
    await context.close();
  }
}

async function loginAs(page, email, password) {
  await page.goto(`${baseURL}/login`, { waitUntil: 'domcontentloaded' });
  await page.fill('#email', email);
  await page.fill('#password', password);
  await Promise.all([
    page.waitForLoadState('networkidle'),
    page.getByRole('button', { name: /log in/i }).click(),
  ]);
}

function writeReport() {
  const lines = [
    '# Black-Box Testing',
    '',
    `Tanggal uji: ${new Date().toLocaleString('id-ID')}`,
    `URL uji: ${baseURL}`,
    '',
    '| ID | Skenario | Status | Screenshot | Catatan |',
    '| --- | --- | --- | --- | --- |',
    ...results.map((result) => `| ${result.id} | ${result.title} | ${result.status} | ${result.screenshot} | ${result.note.replace(/\|/g, '/')} |`),
    '',
    'Akun uji:',
    '',
    '- Admin: admin@kos.com / password',
    '- Penyewa: siti@kos.com / penyewa123',
    '- Penyedia: penyedia@kos.com / password',
    '',
    'Catatan: pengujian dilakukan dari sisi pengguna melalui browser, tanpa memanggil controller atau model secara langsung.',
    '',
  ];

  fs.writeFileSync(path.join(outputDir, 'blackbox-report.md'), lines.join('\n'), 'utf8');
}

test.describe.configure({ timeout: 120_000 });

test('black-box UI smoke flow', async ({ browser }) => {
  ensureOutputDir();

  await runCase(browser, '01-public-home', 'Beranda publik dapat dibuka', async (page) => {
    const response = await page.goto(baseURL, { waitUntil: 'networkidle' });
    expect(response?.ok()).toBeTruthy();
    await expect(page.locator('body')).toContainText(/kos|kamar/i);
  });

  await runCase(browser, '02-public-kamar', 'Daftar kamar publik dapat dibuka', async (page) => {
    const response = await page.goto(`${baseURL}/kamar`, { waitUntil: 'networkidle' });
    expect(response?.ok()).toBeTruthy();
    await expect(page.locator('body')).toContainText(/kamar/i);
  });

  await runCase(browser, '03-login-invalid', 'Login dengan kredensial salah ditolak', async (page) => {
    await page.goto(`${baseURL}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('#email', 'salah@kos.com');
    await page.fill('#password', 'password-salah');
    await Promise.all([
      page.waitForLoadState('networkidle'),
      page.getByRole('button', { name: /log in/i }).click(),
    ]);
    expect(page.url()).toContain('/login');
    await expect(page.locator('body')).toContainText(/email|password|credentials|login/i);
  });

  await runCase(browser, '04-admin-dashboard', 'Admin berhasil login dan masuk dashboard', async (page) => {
    await loginAs(page, 'admin@kos.com', 'password');
    await expect(page).toHaveURL(/\/admin\/dashboard/);
    await expect(page.locator('body')).toContainText(/Dashboard Admin|Executive Overview|Total Kamar/i);
  });

  await runCase(browser, '05-penyewa-dashboard', 'Penyewa berhasil login dan masuk dashboard', async (page) => {
    await loginAs(page, 'siti@kos.com', 'penyewa123');
    await expect(page).toHaveURL(/\/penyewa\/dashboard/);
    await expect(page.locator('body')).toContainText(/Dashboard Penyewa|Area Penyewa|Status Hunian/i);
  });

  await runCase(browser, '06-penyedia-dashboard', 'Penyedia berhasil login dan masuk dashboard', async (page) => {
    await loginAs(page, 'penyedia@kos.com', 'password');
    await expect(page).toHaveURL(/\/penyedia\/dashboard/);
    await expect(page.locator('body')).toContainText(/Dashboard Penyedia|Panel Penyedia Kos|Total Kos/i);
  });

  writeReport();

  const failures = results.filter((result) => result.status === 'FAIL');
  expect(failures, failures.map((failure) => `${failure.id}: ${failure.note}`).join('\n')).toHaveLength(0);
});
