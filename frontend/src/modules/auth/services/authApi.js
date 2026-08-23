import axios from 'axios';
import api from '../../../bootstrap';

const oauth = axios.create({
    baseURL: import.meta.env.VITE_OAUTH_BASE_URL,
});

export default {
    register(payload) {
        return api.post('/register', payload);
    },
    login({ email, password }) {
        return oauth.post('/token', {
            grant_type: 'password',
            client_id: import.meta.env.VITE_OAUTH_CLIENT_ID,
            client_secret: import.meta.env.VITE_OAUTH_CLIENT_SECRET,
            username: email,
            password,
            scope: '',
        });
    },
    me() {
        return api.get('/user');
    },
};
