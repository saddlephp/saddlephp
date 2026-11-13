// Captures the README screenshots from the workbench demo panel.
//
//   SADDLE_DEMO=1 DB_CONNECTION=sqlite DB_DATABASE=$PWD/workbench/demo.sqlite \
//     vendor/bin/testbench migrate:fresh --seeder='Workbench\Database\Seeders\DemoSeeder'
//   SADDLE_DEMO=1 ... vendor/bin/testbench serve --port=8765
//   npx playwright install chromium   # once
//   node .github/screenshots/capture.mjs
//
// Each page is captured in light and dark at 2x, then framed in a browser window
// on a warm glow, and written to .github/screenshots/{name}-{mode}.png.

import { chromium } from 'playwright';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const BASE = process.env.SADDLE_URL ?? 'http://127.0.0.1:8765';
const OUT = dirname(fileURLToPath(import.meta.url));

const FRAME = {
    light: {
        page: '#f7f3ee',
        glow: ['rgba(217,80,31,0.22)', 'rgba(234,179,8,0.14)', 'rgba(217,80,31,0.12)', 'rgba(120,53,15,0.12)'],
        chrome: '#ffffff',
        border: 'rgba(28,25,23,0.10)',
        dot: '#e7e1da',
        pill: '#f4f0eb',
        pillText: '#78716c',
        shadow: '0 30px 70px -20px rgba(41,24,12,0.30), 0 12px 24px -12px rgba(41,24,12,0.14)',
    },
    dark: {
        page: '#0c0a09',
        glow: ['rgba(217,80,31,0.28)', 'rgba(234,179,8,0.10)', 'rgba(217,80,31,0.16)', 'rgba(146,64,14,0.20)'],
        chrome: '#161311',
        border: 'rgba(255,255,255,0.09)',
        dot: '#332d29',
        pill: '#1f1b18',
        pillText: '#8f8780',
        shadow: '0 30px 80px -20px rgba(0,0,0,0.75), 0 0 0 1px rgba(255,255,255,0.04)',
    },
};

function frameHtml(png, mode, url) {
    const c = FRAME[mode];
    const [g1, g2, g3, g4] = c.glow;
    return `<!doctype html><html><head><style>
        html, body { margin: 0; }
        body { font-family: -apple-system, 'Segoe UI', Roboto, sans-serif; }
        #stage {
            display: inline-block; padding: 72px 88px 88px;
            background:
                radial-gradient(60% 55% at 12% 8%, ${g1}, transparent 70%),
                radial-gradient(50% 50% at 38% 100%, ${g2}, transparent 70%),
                radial-gradient(55% 55% at 88% 12%, ${g3}, transparent 70%),
                radial-gradient(50% 60% at 100% 95%, ${g4}, transparent 70%),
                ${c.page};
        }
        .window { width: 1440px; border-radius: 14px; overflow: hidden; background: ${c.chrome};
            border: 1px solid ${c.border}; box-shadow: ${c.shadow}; }
        .bar { height: 44px; display: flex; align-items: center; gap: 8px; padding: 0 16px; border-bottom: 1px solid ${c.border}; }
        .dot { width: 12px; height: 12px; border-radius: 50%; background: ${c.dot}; }
        .pill { margin: 0 auto; transform: translateX(-30px); height: 26px; min-width: 380px; padding: 0 14px; border-radius: 7px;
            background: ${c.pill}; color: ${c.pillText}; font-size: 13px; display: flex; align-items: center; justify-content: center; }
        img { display: block; width: 1440px; }
    </style></head><body><div id="stage"><div class="window">
        <div class="bar"><span class="dot"></span><span class="dot"></span><span class="dot"></span><span class="pill">${url}</span></div>
        <img src="data:image/png;base64,${png.toString('base64')}">
    </div></div></body></html>`;
}

const shots = [
    { name: 'dashboard', path: '/admin' },
    {
        name: 'table',
        path: '/admin/resources/horses',
        // Select two rows so the bulk-action bar shows.
        prepare: async (page) => {
            const boxes = page.locator('tbody input[type="checkbox"]');
            await boxes.nth(1).check();
            await boxes.nth(3).check();
        },
    },
    { name: 'form', path: '/admin/resources/horses/1/edit' },
];

const browser = await chromium.launch();

for (const mode of ['light', 'dark']) {
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 2 });
    await context.addInitScript((m) => localStorage.setItem('saddle-theme', m), mode);
    const page = await context.newPage();
    await page.goto(`${BASE}/demo-login`);

    for (const shot of shots) {
        await page.goto(BASE + shot.path);
        await page.waitForLoadState('networkidle');
        if (shot.prepare) await shot.prepare(page);
        await page.evaluate(() => document.fonts.ready);
        const options = { animations: 'disabled', caret: 'hide' };
        const png = await page.screenshot(options);

        const framer = await context.newPage();
        await framer.setViewportSize({ width: 1800, height: 1200 });
        await framer.setContent(frameHtml(png, mode, `your-app.com${shot.path}`));
        await framer.locator('#stage img').evaluate((img) => img.decode());
        await framer.locator('#stage').screenshot({ path: join(OUT, `${shot.name}-${mode}.png`), ...options });
        await framer.close();
        console.log(`${shot.name}-${mode}`);
    }

    await context.close();
}

await browser.close();
