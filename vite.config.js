import { readFileSync } from 'node:fs';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

// The panel's version has exactly one source of truth: the PHP constant the
// server reports to the browser. Reading it here rather than from package.json
// is what keeps the stale-assets banner honest -- when the two drifted apart in
// 1.1.0 and 1.2.0, every install was told its assets were out of date forever,
// and `saddle:upgrade` republished the same bundle so the warning never cleared.
const saddleVersion = (() => {
    const source = readFileSync(new URL('./src/Saddle.php', import.meta.url), 'utf8');
    const match = source.match(/const\s+VERSION\s*=\s*'([^']+)'/);

    if (! match) {
        throw new Error('Could not read VERSION from src/Saddle.php.');
    }

    return match[1];
})();

export default defineConfig({
    plugins: [vue(), tailwindcss()],
    base: '/vendor/saddle/',
    publicDir: 'resources/static',
    define: {
        __SADDLE_VERSION__: JSON.stringify(saddleVersion),
        __VUE_I18N_LEGACY_API__: JSON.stringify(false),
        __VUE_I18N_FULL_INSTALL__: JSON.stringify(false),
        // Interpret the message AST instead of compiling with new Function,
        // which would trip unsafe-eval under a strict CSP.
        __INTLIFY_JIT_COMPILATION__: JSON.stringify(true),
        __INTLIFY_DROP_MESSAGE_COMPILER__: JSON.stringify(false),
    },
    build: {
        outDir: 'dist',
        manifest: 'manifest.json',
        emptyOutDir: true,
        rollupOptions: { input: 'resources/js/app.js' },
    },
});
