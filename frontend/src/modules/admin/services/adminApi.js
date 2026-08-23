import api from '../../../bootstrap';

export default {
    getDashboard() {
        return api.get('/forum/admin/dashboard');
    },

    // users (shared with the moderation endpoints)
    getUsers(params = {}) {
        return api.get('/forum/moderation/users', { params });
    },
    toggleBan(userId) {
        return api.post(`/forum/moderation/users/${userId}/ban`);
    },
    setRole(userId, role) {
        return api.patch(`/forum/moderation/users/${userId}/role`, { role });
    },

    // reports
    getReports(params = {}) {
        return api.get('/forum/moderation/reports', { params });
    },
    reviewReport(id, status) {
        return api.patch(`/forum/moderation/reports/${id}`, { status });
    },

    // categories
    getCategories() {
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

    // threads
    getThreads(params = {}) {
        return api.get('/forum/admin/threads', { params });
    },
    deleteThread(slug) {
        return api.delete(`/forum/admin/threads/${slug}`);
    },
    restoreThread(slug) {
        return api.post(`/forum/admin/threads/${slug}/restore`);
    },
    togglePin(slug) {
        return api.post(`/forum/moderation/threads/${slug}/pin`);
    },
    toggleLock(slug) {
        return api.post(`/forum/moderation/threads/${slug}/lock`);
    },
    toggleHide(slug) {
        return api.post(`/forum/moderation/threads/${slug}/hide`);
    },

    // posts
    getPosts(params = {}) {
        return api.get('/forum/admin/posts', { params });
    },
    deletePost(id) {
        return api.delete(`/forum/admin/posts/${id}`);
    },
    restorePost(id) {
        return api.post(`/forum/admin/posts/${id}/restore`);
    },

    // roles & permissions
    getRoles() {
        return api.get('/forum/admin/roles');
    },
    createRole(payload) {
        return api.post('/forum/admin/roles', payload);
    },
    updateRole(id, payload) {
        return api.put(`/forum/admin/roles/${id}`, payload);
    },
    deleteRole(id) {
        return api.delete(`/forum/admin/roles/${id}`);
    },
    getPermissions() {
        return api.get('/forum/admin/permissions');
    },
    createPermission(payload) {
        return api.post('/forum/admin/permissions', payload);
    },
    deletePermission(id) {
        return api.delete(`/forum/admin/permissions/${id}`);
    },

    // dummy data
    getDummyStatus() {
        return api.get('/forum/admin/dummy-data');
    },
    importDummyData(payload) {
        return api.post('/forum/admin/dummy-data', payload);
    },
    deleteDummyData() {
        return api.delete('/forum/admin/dummy-data');
    },

    // tags
    getTags() {
        return api.get('/forum/admin/tags');
    },
    createTag(payload) {
        return api.post('/forum/admin/tags', payload);
    },
    updateTag(slug, payload) {
        return api.put(`/forum/admin/tags/${slug}`, payload);
    },
    deleteTag(slug) {
        return api.delete(`/forum/admin/tags/${slug}`);
    },
};
