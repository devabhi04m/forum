import api from '../../../bootstrap';

export default {
    getReports(params = {}) {
        return api.get('/forum/moderation/reports', { params });
    },
    reviewReport(id, status) {
        return api.patch(`/forum/moderation/reports/${id}`, { status });
    },
    togglePin(threadSlug) {
        return api.post(`/forum/moderation/threads/${threadSlug}/pin`);
    },
    toggleLock(threadSlug) {
        return api.post(`/forum/moderation/threads/${threadSlug}/lock`);
    },
    toggleHide(threadSlug) {
        return api.post(`/forum/moderation/threads/${threadSlug}/hide`);
    },
    getStats() {
        return api.get('/forum/moderation/stats');
    },
    getUsers(params = {}) {
        return api.get('/forum/moderation/users', { params });
    },
    toggleBan(userId) {
        return api.post(`/forum/moderation/users/${userId}/ban`);
    },
    setRole(userId, role) {
        return api.patch(`/forum/moderation/users/${userId}/role`, { role });
    },
    getAllCategories() {
        return api.get('/forum/moderation/categories');
    },
    createCategory(payload) {
        return api.post('/forum/moderation/categories', payload);
    },
    updateCategory(slug, payload) {
        return api.put(`/forum/moderation/categories/${slug}`, payload);
    },
    deleteCategory(slug) {
        return api.delete(`/forum/moderation/categories/${slug}`);
    },
};
