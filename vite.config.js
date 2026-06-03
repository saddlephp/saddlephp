import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [vue(), tailwindcss()],
    base: '/vendor/rodeo/',
    build: {
        outDir: 'dist',
        manifest: 'manifest.json',
        emptyOutDir: true,
        rollupOptions: { input: 'resources/js/app.js' },
    },
});
