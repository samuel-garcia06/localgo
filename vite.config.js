import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const hmrHost = env.VITE_HMR_HOST || env.VITE_DEV_SERVER_HOST;
    const hmrPort = Number(env.VITE_HMR_PORT || 5173);

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/css/filament/admin/theme.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
        server: {
            host: '0.0.0.0',
            port: 5173,
            strictPort: true,
            origin: env.VITE_DEV_SERVER_URL || undefined,
            hmr: hmrHost
                ? {
                    host: hmrHost,
                    port: hmrPort,
                    protocol: 'ws',
                }
                : undefined,
            watch: {
                ignored: ['**/storage/framework/views/**', '**/vendor/**'],
            },
        },
    };
});
