import { readFileSync } from 'node:fs';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

const pkg = JSON.parse(readFileSync(new URL('./package.json', import.meta.url), 'utf8'));

export default defineConfig({
    plugins: [vue(), tailwindcss()],
    base: '/vendor/saddle/',
    publicDir: 'resources/static',
    define: {
        __SADDLE_VERSION__: JSON.stringify(pkg.version),
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
