import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: false,
        // Use HTTP for Vite dev server (recommended for Herd)
        // If Herd is using HTTPS, access site via HTTP (http://noteds.test) to avoid mixed content
        hmr: {
            host: 'noteds.test',
            protocol: 'ws', // Use ws (not wss) for HTTP Vite server
        },
    },
});
