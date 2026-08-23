import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
});

const token = localStorage.getItem('auth_token');
if (token) {
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

export function setAuthToken(token) {
    if (token) {
        localStorage.setItem('auth_token', token);
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    } else {
        localStorage.removeItem('auth_token');
        delete api.defaults.headers.common['Authorization'];
    }
}

// token expired or got revoked, log them out and start over
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401 && localStorage.getItem('auth_token')) {
            setAuthToken(null);
            localStorage.removeItem('auth_user');
            window.location.assign('/login');
        }
        return Promise.reject(error);
    },
);

export default api;
