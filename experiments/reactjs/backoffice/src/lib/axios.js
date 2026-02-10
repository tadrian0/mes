import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
});

api.interceptors.request.use((config) => {
    const key = localStorage.getItem('mes_api_key');
    if (key) {
        config.headers['X-API-KEY'] = key;
    }
    return config;
});

export default api;