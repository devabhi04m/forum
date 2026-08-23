import api from '../../../bootstrap';

export default {
    list(params = {}) {
        return api.get('/notifications', { params });
    },
    markRead(id) {
        return api.post(`/notifications/${id}/read`);
    },
    markAllRead() {
        return api.post('/notifications/read-all');
    },
};
