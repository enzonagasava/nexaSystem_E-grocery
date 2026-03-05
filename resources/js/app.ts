<<<<<<< HEAD
import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
=======
import { createInertiaApp } from '@inertiajs/vue3';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
import { configureEcho } from '@laravel/echo-vue';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import money from 'v-money3';
<<<<<<< HEAD
=======
import { createApp, h } from 'vue';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
import { ZiggyVue } from 'ziggy-js';
import '../css/app.css';
// FullCalendar styles: removed direct imports because some FullCalendar packages do not
// expose CSS via package exports in this environment. If you want styles, either
// install the correct FullCalendar packages that include CSS or add the CSS via CDN
// in your Blade layout (example below).
<<<<<<< HEAD

/* Example CDN include (add to Blade head for dev):
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/common@6/main.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6/main.min.css" rel="stylesheet" />
*/
import Toast from './components/ui/toast/Toast.vue';
import FlashHandler from './components/FlashHandler.vue' 
import api from './lib/axios';
import { useLoadingStore } from './stores/loading';

configureEcho({
    broadcaster: 'reverb',
});

const appName = import.meta.env.VITE_APP_NAME || 'NexaSystem_Cliente';
const pinia = createPinia();

// Configurar eventos de navegação Inertia para loading
router.on('start', () => {
    const loadingStore = useLoadingStore(pinia);
    loadingStore.show();
});

router.on('finish', () => {
    const loadingStore = useLoadingStore(pinia);
    loadingStore.hide();
});


createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue') as Record<string, () => Promise<any>>),
=======

/* Example CDN include (add to Blade head for dev):
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/common@6/main.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6/main.min.css" rel="stylesheet" />
*/
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
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
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
