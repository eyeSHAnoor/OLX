import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { i18nVue } from 'laravel-vue-i18n';
import { createPinia } from 'pinia';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import PermissionsPlugin from './plugins/permissions';
import planPermissionsPlugin from './plugins/planPermissions';
import { configureEcho } from '@laravel/echo-vue';

configureEcho({
    broadcaster: 'reverb',
});

const appName = import.meta.env.VITE_APP_NAME || 'AMO Mercatus';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),

    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin)
            .use(ZiggyVue)
            .use(createPinia())
            .use(PermissionsPlugin)
            .use(planPermissionsPlugin)
            .use(i18nVue, {
                resolve: async (lang: String) => {
                    const langs = import.meta.glob('../../lang/*.json');
                    return await langs[`../../lang/${lang}.json`]();
                },
            });

        // =========================================
        // SERVICE WORKER (PUSH NOTIFICATIONS)
        // =========================================
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', async () => {
                try {
                    const registration = await navigator.serviceWorker.register('/sw.js', {
                        scope: '/',
                    });

                    // console.log('Service Worker registered:', registration.scope);

                    // 🔥 FORCE it to take control immediately
                    if (navigator.serviceWorker.controller === null) {
                        // console.log('⚠️ No controller yet, reloading...');
                        window.location.reload();
                    }
                } catch (err) {
                    console.error('❌ Service Worker error:', err);
                }
            });
        }

        app.mount(el);
    },

    progress: {
        color: '#5ce286',
        includeCSS: true,
        showSpinner: false,
    },
});

// Theme init
initializeTheme();
