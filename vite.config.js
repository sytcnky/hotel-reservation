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
                'resources/css/filament/admin/theme.css',
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
    css: {
        preprocessorOptions: {
            scss: { quietDeps: true },
        },
    },
    server: {
        port: 5173,
        strictPort: true,
    },
})
