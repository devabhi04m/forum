import api from '../../../bootstrap';

export default {
    getCategories() {
        return api.get('/forum/categories');
    },
    getCategory(slug) {
        return api.get(`/forum/categories/${slug}`);
    },
    getThreads(params = {}) {
        return api.get('/forum/threads', { params });
    },
    getThread(slug) {
        return api.get(`/forum/threads/${slug}`);
    },
    createThread(payload) {
        return api.post('/forum/threads', payload);
    },
    updateThread(id, payload) {
        return api.put(`/forum/threads/${id}`, payload);
    },
    deleteThread(id) {
        return api.delete(`/forum/threads/${id}`);
    },
    getPosts(threadSlug, params = {}) {
        return api.get(`/forum/threads/${threadSlug}/posts`, { params });
    },
    deletePost(postId) {
        return api.delete(`/forum/posts/${postId}`);
    },
    reportThread(threadSlug, reason) {
        return api.post(`/forum/threads/${threadSlug}/report`, { reason });
    },
    reportPost(postId, reason) {
        return api.post(`/forum/posts/${postId}/report`, { reason });
    },
    createPost(threadSlug, payload) {
        return api.post(`/forum/threads/${threadSlug}/posts`, payload);
    },
    getTags() {
        return api.get('/forum/tags');
    },
    voteThread(threadSlug, vote) {
        return api.post(`/forum/threads/${threadSlug}/vote`, { vote });
    },
    votePost(postId, vote) {
        return api.post(`/forum/posts/${postId}/vote`, { vote });
    },
    toggleBookmark(threadSlug) {
        return api.post(`/forum/threads/${threadSlug}/bookmark`);
    },
    toggleFollow(threadSlug) {
        return api.post(`/forum/threads/${threadSlug}/follow`);
    },
    getMyThreads(params = {}) {
        return api.get('/forum/me/threads', { params });
    },
    getMyPosts(params = {}) {
        return api.get('/forum/me/posts', { params });
    },
    getMyBookmarks(params = {}) {
        return api.get('/forum/me/bookmarks', { params });
    },
};
