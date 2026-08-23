<script setup>
import { onMounted, ref, watch } from 'vue';
import adminApi from '../services/adminApi';
import icons from '../icons';
import AdminPageHeader from '../components/AdminPageHeader.vue';
import { timeAgo } from '../../../utils/date';

const loading = ref(false);
const error = ref(null);
const posts = ref([]);
const meta = ref(null);
const search = ref('');
const status = ref('');
const page = ref(1);

async function load() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await adminApi.getPosts({
            q: search.value || undefined,
            status: status.value || undefined,
            page: page.value,
        });
        posts.value = data.data ?? [];
        meta.value = data.meta ?? null;
    } catch {
        error.value = 'Could not load posts.';
    } finally {
        loading.value = false;
    }
}

function onSearch() {
    page.value = 1;
    load();
}

function goTo(newPage) {
    page.value = newPage;
    load();
}

watch(status, () => {
    page.value = 1;
    load();
});

async function deletePost(post) {
    const permanent = !!post.deleted_at;
    const message = permanent
        ? 'Permanently delete this post? This cannot be undone.'
        : 'Delete this post? You can restore it from the Deleted filter.';
    if (!confirm(message)) return;
    try {
        await adminApi.deletePost(post.id);
        posts.value = posts.value.filter((p) => p.id !== post.id);
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not delete the post.';
    }
}

async function restorePost(post) {
    try {
        await adminApi.restorePost(post.id);
        posts.value = posts.value.filter((p) => p.id !== post.id);
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not restore the post.';
    }
}

onMounted(load);
</script>

<template>
    <div>
        <AdminPageHeader
            title="Posts"
            subtitle="Every reply on the forum — search, delete and restore."
            :icon="icons.posts"
            :count="meta?.total ?? null"
            count-label="posts"
        />

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="button" class="chip" :class="status === '' && '!border-brand-400 !bg-brand-50 !text-brand-700'" @click="status = ''">
                All
            </button>
            <button type="button" class="chip" :class="status === 'deleted' && '!border-brand-400 !bg-brand-50 !text-brand-700'" @click="status = 'deleted'">
                Deleted
            </button>
            <form class="ml-auto flex gap-2" @submit.prevent="onSearch">
                <input v-model="search" type="search" class="input !w-48 !py-1.5" placeholder="Search content..." />
                <button type="submit" class="btn-primary !px-3 !py-1.5 text-sm">Search</button>
            </form>
        </div>

        <p v-if="error" class="alert-error mt-4">{{ error }}</p>

        <div v-if="loading" class="mt-4 space-y-3">
            <div v-for="n in 5" :key="n" class="h-20 animate-pulse rounded-xl bg-ink-100"></div>
        </div>

        <div v-else-if="posts.length" class="card mt-4 divide-y divide-ink-100 overflow-hidden rounded-2xl">
            <div v-for="post in posts" :key="post.id" class="p-4 transition hover:bg-ink-50/70">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm text-ink-700" :class="post.deleted_at && 'text-ink-400 line-through'">{{ post.excerpt }}</p>
                        <p class="mt-1 text-xs text-ink-400">
                            {{ post.user?.name }} · {{ post.likes_count }} likes · {{ timeAgo(post.created_at) }}
                            <span v-if="post.deleted_at" class="ml-1 rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700 uppercase">Deleted</span>
                        </p>
                        <router-link
                            v-if="post.thread"
                            :to="{ name: 'threads.show', params: { slug: post.thread.slug } }"
                            class="mt-0.5 inline-block text-xs font-medium text-brand-600 hover:underline"
                        >
                            in: {{ post.thread.title }} →
                        </router-link>
                    </div>

                    <div class="flex shrink-0 gap-1.5">
                        <template v-if="!post.deleted_at">
                            <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs text-red-600" @click="deletePost(post)">Delete</button>
                        </template>
                        <template v-else>
                            <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs text-emerald-600" @click="restorePost(post)">Restore</button>
                            <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs text-red-600" @click="deletePost(post)">Delete forever</button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="mt-4 rounded-xl border border-dashed border-ink-300 p-10 text-center">
            <p class="text-sm text-ink-500">No posts found.</p>
        </div>

        <div v-if="meta && meta.last_page > 1" class="mt-4 flex items-center justify-between text-sm text-ink-500">
            <button type="button" class="btn-ghost !px-3 !py-1.5" :disabled="page <= 1" @click="goTo(page - 1)">← Prev</button>
            <span>Page {{ meta.current_page }} of {{ meta.last_page }} · {{ meta.total }} posts</span>
            <button type="button" class="btn-ghost !px-3 !py-1.5" :disabled="page >= meta.last_page" @click="goTo(page + 1)">Next →</button>
        </div>
    </div>
</template>
