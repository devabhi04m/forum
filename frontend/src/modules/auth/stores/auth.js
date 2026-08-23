import { defineStore } from 'pinia';
import authApi from '../services/authApi';
import { setAuthToken } from '../../../bootstrap';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
        token: localStorage.getItem('auth_token') || null,
        error: null,
        loading: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        isModerator: (state) => ['moderator', 'admin'].includes(state.user?.role),
        isAdmin: (state) => state.user?.role === 'admin',
    },

    actions: {
        async login(credentials) {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await authApi.login(credentials);
                this.token = data.access_token;
                setAuthToken(this.token);

                const { data: userResponse } = await authApi.me();
                this.user = userResponse;
                localStorage.setItem('auth_user', JSON.stringify(this.user));
            } catch (err) {
                this.error = err.response?.data?.message || 'Invalid email or password.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async register(payload) {
            this.loading = true;
            this.error = null;
            try {
                await authApi.register(payload);
                await this.login({ email: payload.email, password: payload.password });
            } catch (err) {
                this.error = err.response?.data?.message || 'Could not create account.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        logout() {
            this.user = null;
            this.token = null;
            setAuthToken(null);
            localStorage.removeItem('auth_user');
        },
    },
});
