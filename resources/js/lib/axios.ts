import axios from 'axios';
import { useLoadingStore } from '@/stores/loading';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
    },
    withCredentials: true,
});

// Ensure CSRF token is sent for web (non-API) AJAX requests
try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) api.defaults.headers.common['X-CSRF-TOKEN'] = token;
} catch (e) {
    // ignore in non-browser environments
}
// Interceptor para mostrar loading em requisições API
api.interceptors.request.use(
    (config) => {
        // Não mostrar loading para requisições silenciosas
        if (!config.headers?.['X-Silent-Request']) {
            try {
                const loadingStore = useLoadingStore();
                loadingStore.show();
            } catch {
                // Store pode não estar disponível ainda
            }
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    },
);

api.interceptors.response.use(
    (response) => {
        if (!response.config.headers?.['X-Silent-Request']) {
            try {
                const loadingStore = useLoadingStore();
                loadingStore.hide();
            } catch {
                // Store pode não estar disponível ainda
            }
        }
        return response;
    },
    (error) => {
        try {
            const loadingStore = useLoadingStore();
            loadingStore.hide();
        } catch {
            // Store pode não estar disponível ainda
        }
        return Promise.reject(error);
    },
);

export default api;
