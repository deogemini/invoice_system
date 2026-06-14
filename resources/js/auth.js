import { reactive } from 'vue';
import axios from 'axios';

export const auth = reactive({
    user: null,
    loading: true,
});

export const isAuthenticated = () => Boolean(auth.user);
export const isAdministrator = () => auth.user?.role === 'administrator';
export const isClient = () => auth.user?.role === 'client';

const readCookie = (name) => document.cookie
    .split('; ')
    .find((cookie) => cookie.startsWith(`${name}=`))
    ?.split('=')
    .slice(1)
    .join('=');

const refreshCsrfCookie = async () => {
    await axios.get('/sanctum/csrf-cookie');

    const token = readCookie('XSRF-TOKEN');

    if (token) {
        axios.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(token);
    }
};

export const fetchCurrentUser = async () => {
    try {
        const response = await axios.get('/api/auth/me');
        auth.user = response.data.user;
    } catch {
        auth.user = null;
    } finally {
        auth.loading = false;
    }
};

export const login = async (credentials) => {
    await refreshCsrfCookie();

    const response = await axios.post('/api/auth/login', credentials);
    auth.user = response.data.user;
    return response;
};

export const logout = async () => {
    await refreshCsrfCookie();

    await axios.post('/api/auth/logout');
    auth.user = null;
};

export const verifyPasswordResetEmail = async (email) => {
    await refreshCsrfCookie();

    return axios.post('/api/auth/forgot-password', { email });
};

export const resetPassword = async (payload) => {
    await refreshCsrfCookie();

    return axios.post('/api/auth/reset-password', payload);
};
