import { createInertiaApp } from '@inertiajs/vue3';
import { configureEcho } from '@laravel/echo-vue';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import money from 'v-money3';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import '../css/app.css';
import Toast from './components/ui/toast/Toast.vue';
import api from './lib/axios';

configureEcho({
    broadcaster: 'reverb',
});

const appName = import.meta.env.VITE_APP_NAME || 'FamiliaMogi';
const pinia = createPinia();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(pinia);
        app.use(ZiggyVue);

        app.use(money);
        app.component('Toast', Toast);

        app.config.globalProperties.$axios = api;
        app.provide('axios', api);

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
