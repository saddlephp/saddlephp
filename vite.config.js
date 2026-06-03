import { readFileSync } from 'node:fs';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

const pkg = JSON.parse(readFileSync(new URL('./package.json', import.meta.url), 'utf8'));

export default defineConfig({
    plugins: [vue(), tailwindcss()],
    base: '/vendor/saddle/',
    define: {
        __SADDLE_VERSION__: JSON.stringify(pkg.version),
    },
    build: {
        outDir: 'dist',
        manifest: 'manifest.json',
        emptyOutDir: true,
        rollupOptions: { input: 'resources/js/app.js' },
    },
});
