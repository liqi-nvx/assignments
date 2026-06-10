import { createInertiaApp } from '@inertiajs/vue3';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

import axios from 'axios';

// 注入全局 Axios 拦截器，每次发请求自动抓取本地存储的最新凭证
axios.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    const tenantId = localStorage.getItem('tenant_id');

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    
    if (tenantId) {
        config.headers['X-Tenant'] = tenantId;
    }

    return config;
}, (error) => {
    return Promise.reject(error);
});

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    progress: {
        color: '#4B5563',
    },
});
