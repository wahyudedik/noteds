import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
            valetTls: false,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host: true,
        port: 5176,
        strictPort: true,
        cors: true,
        hmr: {
            protocol: 'ws',
            host: 'localhost',
            port: 5176,
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: undefined, // Disable manual chunking to reduce number of files
            },
        },
        chunkSizeWarningLimit: 2000, // Increase chunk size warning limit
    },
});
