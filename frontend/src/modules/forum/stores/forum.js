import { defineStore } from 'pinia';
import forumApi from '../services/forumApi';

export const useForumStore = defineStore('forum', {
    state: () => ({
        categories: [],
        tags: [],
        threads: [],
        threadsMeta: null,
        currentCategory: null,
        currentThread: null,
        posts: [],
        postsMeta: null,
        lastThreadParams: {},
        loading: false,
        error: null,
    }),

    actions: {
        async fetchCategories() {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await forumApi.getCategories();
                this.categories = data.data ?? data;
            } catch (err) {
                this.error = 'Could not load categories.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async fetchCategory(slug) {
            const { data } = await forumApi.getCategory(slug);
            this.currentCategory = data.data ?? data;
        },

        async fetchThreads(params = {}) {
            this.loading = true;
            this.error = null;
            this.lastThreadParams = params;
            try {
                const { data } = await forumApi.getThreads(params);
                this.threads = data.data ?? data;
                this.threadsMeta = data.meta ?? null;
            } catch (err) {
                this.error = 'Could not load threads.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async loadMoreThreads() {
            if (!this.threadsMeta || this.threadsMeta.current_page >= this.threadsMeta.last_page) return;

            const { data } = await forumApi.getThreads({
                ...this.lastThreadParams,
                page: this.threadsMeta.current_page + 1,
            });
            this.threads.push(...(data.data ?? []));
            this.threadsMeta = data.meta ?? this.threadsMeta;
        },

        async fetchThread(slug) {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await forumApi.getThread(slug);
                this.currentThread = data.data ?? data;
            } catch (err) {
                this.error = 'Thread not found.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async fetchPosts(threadSlug) {
            const { data } = await forumApi.getPosts(threadSlug);
            this.posts = data.data ?? data;
            this.postsMeta = data.meta ?? null;
        },

        async loadMorePosts() {
            if (!this.currentThread || !this.postsMeta || this.postsMeta.current_page >= this.postsMeta.last_page) return;

            const { data } = await forumApi.getPosts(this.currentThread.slug, {
                page: this.postsMeta.current_page + 1,
            });
            this.posts.push(...(data.data ?? []));
            this.postsMeta = data.meta ?? this.postsMeta;
        },

        async createThread(payload) {
            const { data } = await forumApi.createThread(payload);
            return data.data ?? data;
        },

        async updateThread(id, payload) {
            const { data } = await forumApi.updateThread(id, payload);
            const thread = data.data ?? data;
            if (this.currentThread?.id === id) {
                this.currentThread = thread;
            }
            return thread;
        },

        async deleteThread(id) {
            await forumApi.deleteThread(id);
            if (this.currentThread?.id === id) {
                this.currentThread = null;
            }
        },

        async createPost(threadSlug, payload) {
            const { data } = await forumApi.createPost(threadSlug, payload);
            this.posts.push(data.data ?? data);
            if (this.currentThread) {
                this.currentThread.replies_count = (this.currentThread.replies_count ?? 0) + 1;
            }
            return data.data ?? data;
        },

        async fetchTags() {
            if (this.tags.length) return;
            const { data } = await forumApi.getTags();
            this.tags = data.data ?? data;
        },

        async voteThread(slug, vote) {
            const { data } = await forumApi.voteThread(slug, vote);
            const result = data.data ?? data;
            if (this.currentThread?.slug === slug) {
                this.currentThread.likes_count = result.score;
                this.currentThread.my_vote = result.my_vote;
            }
            return result;
        },

        async votePost(postId, vote) {
            const { data } = await forumApi.votePost(postId, vote);
            const result = data.data ?? data;
            const post = this.posts.find((p) => p.id === postId);
            if (post) {
                post.likes_count = result.score;
                post.my_vote = result.my_vote;
            }
            return result;
        },

        async toggleBookmark(slug) {
            const { data } = await forumApi.toggleBookmark(slug);
            const result = data.data ?? data;
            if (this.currentThread?.slug === slug) {
                this.currentThread.is_bookmarked = result.bookmarked;
            }
            return result;
        },

        async toggleFollow(slug) {
            const { data } = await forumApi.toggleFollow(slug);
            const result = data.data ?? data;
            if (this.currentThread?.slug === slug) {
                this.currentThread.is_following = result.following;
            }
            return result;
        },

        async deletePost(postId) {
            await forumApi.deletePost(postId);
            this.posts = this.posts.filter((p) => p.id !== postId);
            if (this.currentThread) {
                this.currentThread.replies_count = Math.max(0, (this.currentThread.replies_count ?? 1) - 1);
            }
        },
    },
});
