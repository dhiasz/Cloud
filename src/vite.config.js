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
        host: '0.0.0.0', // agar bisa diakses dari luar
        port: 5173,      // port default vite
        strictPort: true,
        hmr: {
            host: 'localhost',
        },
    },
});
