import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import i18n from 'laravel-vue-i18n/vite';
import path from 'path';
import AutoImport from 'unplugin-auto-import/vite';
import Components from 'unplugin-vue-components/vite';
import DefineOptions from 'unplugin-vue-define-options/vite';
import { defineConfig } from 'vite';
import run from 'vite-plugin-run';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: [],
        }),
        tailwindcss(),

        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        i18n(),
        Components({
            dirs: ['./resources/js/components/**'],
            dts: true,
            resolvers: [
                (componentName) => {
                    if (['Head', 'Link'].includes(componentName)) {
                        return { name: componentName, from: '@inertiajs/vue3' };
                    }

                    if (['Icon'].includes(componentName)) {
                        return { name: componentName, from: '@iconify/vue' };
                    }
                },
            ],
        }),
        AutoImport({
            defaultExportByFilename: true,
            dts: true,

            include: [
                /\.vue$/,
                /\.vue\?vue/, // .vue
                // /\.[tj]sx?$/, // .ts, .tsx, .js, .jsx
                // /\.md$/, // .md
            ],

            // global imports to register
            imports: [
                'vue',
                {
                    '@inertiajs/vue3': ['usePage', 'router', 'useForm'],
                    '@vueuse/core': [
                        'useStorage', // import { useMouse } from '@vueuse/core',
                    ],
                },
            ],

            // Auto import for module exports under directories
            dirs: ['./resources/js/composables/**', './resources/js/stores/**'],
        }),
        DefineOptions(),

        run([
            {
                name: 'Generate TypeScript Types',
                run: ['php', 'artisan', 'typescript:transform'],
                pattern: ['app/**/*Data.php', 'app/**/Enums/**/*.php'],
                delay: 1000, // Optional: Add delay to avoid race conditions
                silent: true,
            },
        ]),
    ],

    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },

    server: {
        host: true,
        port: 5173,
        watch: {
            ignored: ['**/*.php'], // Ignore PHP files completely
        },
        hmr: {
            host: 'localhost',
            port: 5173,
        },
    },
});
