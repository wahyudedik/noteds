import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            // Explicitly set dev server URL to use HTTP
            detectServe: false,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: false,
        // Always use HTTP for Vite dev server (even when app is HTTPS)
        // This prevents SSL certificate issues
        https: false,
        // HMR configuration
        hmr: {
            host: 'noteds.test',
            protocol: 'ws', // Use WebSocket (not secure WebSocket) for HTTP
        },
    },
});
