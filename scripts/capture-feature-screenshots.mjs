import { spawn } from 'node:child_process';
import { mkdir, rm, writeFile } from 'node:fs/promises';
import path from 'node:path';
import os from 'node:os';

const baseUrl = process.env.APP_SCREENSHOT_BASE_URL || 'http://127.0.0.1:8001';
const chromePath = process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const remotePort = Number(process.env.CHROME_REMOTE_PORT || 9223);
const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
const outRoot = path.join('screenshots', `fitur-${timestamp}`);

const viewports = {
  web: { width: 1366, height: 768, deviceScaleFactor: 1, mobile: false },
};

const credentials = {
  admin: { email: 'admin@kos.com', password: 'password' },
  penyedia: { email: 'penyedia@kos.com', password: 'password' },
  penyewa_siti: { email: 'siti@kos.com', password: 'penyewa123' },
  penyewa_nadia: { email: 'nadia@kos.com', password: 'penyewa123' },
};

class CdpPage {
  constructor(wsUrl) {
    this.ws = new WebSocket(wsUrl);
    this.nextId = 1;
    this.pending = new Map();
    this.events = new EventTarget();
    this.ws.addEventListener('message', (event) => {
      const payload = JSON.parse(event.data);
      if (payload.id && this.pending.has(payload.id)) {
        const { resolve, reject } = this.pending.get(payload.id);
        this.pending.delete(payload.id);
        if (payload.error) reject(new Error(payload.error.message));
        else resolve(payload.result || {});
        return;
      }
      if (payload.method) {
        this.events.dispatchEvent(new CustomEvent(payload.method, { detail: payload.params || {} }));
      }
    });
  }

  async ready() {
    if (this.ws.readyState === WebSocket.OPEN) return;
    await new Promise((resolve, reject) => {
      this.ws.addEventListener('open', resolve, { once: true });
      this.ws.addEventListener('error', reject, { once: true });
    });
  }

  async send(method, params = {}) {
    await this.ready();
    const id = this.nextId++;
    const request = { id, method, params };
    const response = new Promise((resolve, reject) => {
      this.pending.set(id, { resolve, reject });
      setTimeout(() => {
        if (this.pending.delete(id)) reject(new Error(`CDP timeout: ${method}`));
      }, 30000);
    });
    this.ws.send(JSON.stringify(request));
    return response;
  }

  waitFor(method, predicate = () => true, timeout = 30000) {
    return new Promise((resolve, reject) => {
      const timer = setTimeout(() => {
        this.events.removeEventListener(method, listener);
        reject(new Error(`Timed out waiting for ${method}`));
      }, timeout);
      const listener = (event) => {
        if (!predicate(event.detail)) return;
        clearTimeout(timer);
        this.events.removeEventListener(method, listener);
        resolve(event.detail);
      };
      this.events.addEventListener(method, listener);
    });
  }

  async close() {
    if (this.ws.readyState === WebSocket.OPEN) this.ws.close();
  }
}

async function wait(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

async function waitForChrome() {
  const versionUrl = `http://127.0.0.1:${remotePort}/json/version`;
  for (let i = 0; i < 80; i += 1) {
    try {
      const response = await fetch(versionUrl);
      if (response.ok) return response.json();
    } catch {
      await wait(250);
    }
  }
  throw new Error('Chrome remote debugging endpoint did not become ready.');
}

async function newPage() {
  const response = await fetch(`http://127.0.0.1:${remotePort}/json/new?${encodeURIComponent('about:blank')}`, {
    method: 'PUT',
  });
  if (!response.ok) throw new Error(`Could not create Chrome page: ${response.status}`);
  const target = await response.json();
  const page = new CdpPage(target.webSocketDebuggerUrl);
  await page.ready();
  await page.send('Page.enable');
  await page.send('Runtime.enable');
  await page.send('Network.enable');
  return page;
}

async function navigate(page, url) {
  const load = page.waitFor('Page.loadEventFired').catch(() => null);
  await page.send('Page.navigate', { url });
  await load;
  await wait(800);
}

async function evaluate(page, expression) {
  const result = await page.send('Runtime.evaluate', {
    expression,
    awaitPromise: true,
    returnByValue: true,
  });
  if (result.exceptionDetails) {
    throw new Error(result.exceptionDetails.text || 'Runtime evaluation failed');
  }
  return result.result?.value;
}

async function setViewport(page, viewportName) {
  const viewport = viewports[viewportName];
  await page.send('Emulation.setDeviceMetricsOverride', viewport);
  await page.send('Emulation.setTouchEmulationEnabled', { enabled: viewport.mobile });
}

async function login(page, credential) {
  await page.send('Network.clearBrowserCookies');
  await page.send('Network.clearBrowserCache');
  await navigate(page, `${baseUrl}/login`);
  const ok = await evaluate(
    page,
    `(() => {
      const email = document.querySelector('input[name="email"]');
      const password = document.querySelector('input[name="password"]');
      const form = email && email.form;
      if (!email || !password || !form) return false;
      email.value = ${JSON.stringify(credential.email)};
      email.dispatchEvent(new Event('input', { bubbles: true }));
      password.value = ${JSON.stringify(credential.password)};
      password.dispatchEvent(new Event('input', { bubbles: true }));
      form.submit();
      return true;
    })()`
  );
  if (!ok) throw new Error(`Login form not found for ${credential.email}`);
  await page.waitFor('Page.loadEventFired').catch(() => null);
  await wait(1000);
}

async function screenshot(page, file) {
  await evaluate(page, 'window.scrollTo(0, 0)');
  await wait(200);
  const result = await page.send('Page.captureScreenshot', {
    format: 'png',
    captureBeyondViewport: false,
    fromSurface: true,
  });
  await writeFile(file, Buffer.from(result.data, 'base64'));
}

async function loadTargets() {
  return [
    { viewport: 'web', role: 'public', name: 'public-home', url: '/' },
    { viewport: 'web', role: 'public', name: 'public-daftar-kamar', url: '/kamar' },
    { viewport: 'web', role: 'public', name: 'public-detail-kamar', url: '/kamar/1' },
    { viewport: 'web', role: 'public', name: 'public-peta-lokasi', url: '/maps' },
    { viewport: 'web', role: 'public', name: 'auth-login', url: '/login' },
    { viewport: 'web', role: 'admin', name: 'admin-dashboard', url: '/admin/dashboard' },
    { viewport: 'web', role: 'admin', name: 'admin-kamar', url: '/admin/kamar' },
    { viewport: 'web', role: 'admin', name: 'admin-fasilitas', url: '/admin/fasilitas' },
    { viewport: 'web', role: 'admin', name: 'admin-penyewa', url: '/admin/penyewa' },
    { viewport: 'web', role: 'admin', name: 'admin-pemesanan', url: '/admin/pemesanan' },
    { viewport: 'web', role: 'admin', name: 'admin-pembayaran-awal', url: '/admin/pembayaran-awal' },
    { viewport: 'web', role: 'admin', name: 'admin-penghuni', url: '/admin/penghuni' },
    { viewport: 'web', role: 'admin', name: 'admin-tagihan-bulanan', url: '/admin/tagihan-bulanan' },
    { viewport: 'web', role: 'admin', name: 'admin-pembayaran-bulanan', url: '/admin/pembayaran-bulanan' },
    { viewport: 'web', role: 'admin', name: 'admin-keluhan', url: '/admin/keluhan' },
    { viewport: 'web', role: 'admin', name: 'admin-laporan', url: '/admin/laporan' },
    { viewport: 'web', role: 'penyedia', name: 'penyedia-dashboard', url: '/penyedia/dashboard' },
    { viewport: 'web', role: 'penyedia', name: 'penyedia-kamar', url: '/penyedia/kamar' },
    { viewport: 'web', role: 'penyedia', name: 'penyedia-tambah-kamar', url: '/penyedia/kamar/create' },
    { viewport: 'web', role: 'penyewa_siti', name: 'penyewa-dashboard', url: '/penyewa/dashboard' },
    { viewport: 'web', role: 'penyewa_siti', name: 'penyewa-daftar-kamar', url: '/penyewa/kamar' },
    { viewport: 'web', role: 'penyewa_siti', name: 'penyewa-detail-kamar', url: '/penyewa/kamar/1' },
    { viewport: 'web', role: 'penyewa_siti', name: 'penyewa-favorit', url: '/penyewa/favorit' },
    { viewport: 'web', role: 'penyewa_siti', name: 'penyewa-pemesanan', url: '/penyewa/pemesanan' },
    { viewport: 'web', role: 'penyewa_siti', name: 'penyewa-pembayaran-awal', url: '/penyewa/pembayaran-awal' },
    { viewport: 'web', role: 'penyewa_siti', name: 'penyewa-tagihan', url: '/penyewa/tagihan' },
    { viewport: 'web', role: 'penyewa_siti', name: 'penyewa-riwayat-pembayaran', url: '/penyewa/riwayat-pembayaran' },
    { viewport: 'web', role: 'penyewa_siti', name: 'penyewa-keluhan', url: '/penyewa/keluhan' },
    { viewport: 'web', role: 'penyewa_siti', name: 'penyewa-profil', url: '/penyewa/profil' },
  ];
}

async function main() {
  await mkdir(outRoot, { recursive: true });
  await mkdir(path.join(outRoot, 'web'), { recursive: true });

  const userDataDir = path.join(os.tmpdir(), `chrome-kos-screens-${Date.now()}`);
  const chrome = spawn(chromePath, [
    `--remote-debugging-port=${remotePort}`,
    `--user-data-dir=${userDataDir}`,
    '--headless=new',
    '--disable-gpu',
    '--hide-scrollbars',
    '--no-first-run',
    '--no-default-browser-check',
  ], { stdio: 'ignore' });

  const results = [];

  try {
    await waitForChrome();
    const targets = await loadTargets();
    const grouped = Map.groupBy(targets, (target) => `${target.viewport}:${target.role}`);

    for (const [group, pages] of grouped) {
      const [viewportName, role] = group.split(':');
      const page = await newPage();
      await setViewport(page, viewportName);

      if (credentials[role]) {
        await login(page, credentials[role]);
      }

      for (const target of pages) {
        const file = path.join(outRoot, target.viewport, `${target.role}__${target.name}.png`);
        const item = {
          viewport: target.viewport,
          role: target.role,
          name: target.name,
          url: target.url,
          status: null,
          finalUrl: null,
          file,
          ok: false,
          error: null,
        };

        try {
          await navigate(page, `${baseUrl}${target.url}`);
          item.finalUrl = await evaluate(page, 'location.href');
          item.status = await evaluate(page, 'document.title || document.querySelector("h1")?.innerText || ""');
          const pageText = await evaluate(page, 'document.body?.innerText || ""');
          const isLoginRedirect = target.role !== 'public' && item.finalUrl.includes('/login');
          const hasServerError = /Server Error|Internal Server Error|Illuminate\\|Exception|SQLSTATE/i.test(pageText);
          if (isLoginRedirect || hasServerError) {
            throw new Error(isLoginRedirect ? 'Redirected to login' : 'Server error text detected');
          }
          await screenshot(page, file);
          item.ok = true;
          process.stdout.write(`OK ${target.viewport}/${target.role}/${target.name}\n`);
        } catch (error) {
          item.error = error.message;
          process.stdout.write(`FAIL ${target.viewport}/${target.role}/${target.name}: ${error.message}\n`);
        }

        results.push(item);
      }

      await page.close();
    }
  } finally {
    chrome.kill();
    await rm(userDataDir, { recursive: true, force: true }).catch(() => {});
  }

  const manifest = {
    baseUrl,
    createdAt: new Date().toISOString(),
    outRoot,
    total: results.length,
    ok: results.filter((result) => result.ok).length,
    failed: results.filter((result) => !result.ok).length,
    results,
  };
  await writeFile(path.join(outRoot, 'manifest.json'), JSON.stringify(manifest, null, 2));

  if (manifest.failed > 0) {
    process.exitCode = 1;
  }
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});

