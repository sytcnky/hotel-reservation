import path from 'node:path'
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/site.scss',
                'resources/js/site/site.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(process.cwd(), 'resources'),
            '@flaticon': path.resolve(process.cwd(), 'node_modules/@flaticon'),
        },
    },
    css: { preprocessorOptions: { scss: { quietDeps: true } } },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,
        origin: 'http://192.168.1.15:5173',
        hmr: { host: '192.168.1.15', port: 5173, protocol: 'ws' },
    },
})
