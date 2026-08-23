<script setup>
import { onMounted, ref, watch } from 'vue';
import forumApi from '../services/forumApi';
import ThreadCard from '../components/ThreadCard.vue';
import UserAvatar from '../../../components/UserAvatar.vue';
import { useAuthStore } from '../../auth/stores/auth';
import { timeAgo } from '../../../utils/date';

const auth = useAuthStore();

const TABS = [
    { key: 'threads', label: 'My threads' },
    { key: 'replies', label: 'My replies' },
    { key: 'bookmarks', label: 'Bookmarks' },
];

const tab = ref('threads');
// null means not fetched yet, so each tab only loads once
const lists = ref({ threads: null, replies: null, bookmarks: null });
const loading = ref(false);
const error = ref(null);

const loaders = {
    threads: forumApi.getMyThreads,
    replies: forumApi.getMyPosts,
    bookmarks: forumApi.getMyBookmarks,
};

async function load(which) {
    if (lists.value[which]) return;

    loading.value = true;
    error.value = null;
    try {
        const { data } = await loaders[which]();
        lists.value[which] = data.data ?? data;
    } catch {
        error.value = 'Could not load this list.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => load(tab.value));
watch(tab, load);
</script>

<template>
    <div class="mx-auto max-w-4xl px-4 py-8">
        <div class="flex items-center gap-3">
            <UserAvatar :name="auth.user?.name" size="lg" />
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-ink-900">{{ auth.user?.name }}</h1>
                <p class="text-sm text-ink-500">{{ auth.user?.email }}</p>
            </div>
        </div>

        <div class="mt-6 flex gap-2">
            <button
                v-for="t in TABS"
                :key="t.key"
                type="button"
                class="chip"
                :class="tab === t.key && '!border-brand-400 !bg-brand-50 !text-brand-700'"
                @click="tab = t.key"
            >
                {{ t.label }}
            </button>
        </div>

        <p v-if="error" class="alert-error mt-4">{{ error }}</p>

        <div v-if="loading" class="mt-6 space-y-3">
            <div v-for="n in 3" :key="n" class="h-24 animate-pulse rounded-xl bg-ink-100"></div>
        </div>

        <template v-else-if="tab === 'threads'">
            <div v-if="lists.threads?.length" class="mt-6 space-y-3">
                <ThreadCard v-for="thread in lists.threads" :key="thread.id" :thread="thread" />
            </div>
            <div v-else-if="lists.threads" class="mt-6 rounded-xl border border-dashed border-ink-300 p-10 text-center">
                <p class="text-sm text-ink-500">You haven't started any threads yet.</p>
                <router-link :to="{ name: 'threads.create' }" class="mt-3 inline-block text-sm font-medium text-brand-600 hover:underline">
                    Start one →
                </router-link>
            </div>
        </template>

        <template v-else-if="tab === 'replies'">
            <div v-if="lists.replies?.length" class="mt-6 space-y-3">
                <div v-for="post in lists.replies" :key="post.id" class="card p-4">
                    <router-link
                        v-if="post.thread"
                        :to="{ name: 'threads.show', params: { slug: post.thread.slug } }"
                        class="font-medium text-ink-900 transition hover:text-brand-700"
                    >
                        {{ post.thread.title }}
                    </router-link>
                    <span v-else class="text-sm text-ink-400">(thread no longer exists)</span>
                    <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-ink-500">{{ post.content }}</p>
                    <span class="mt-2 block text-xs text-ink-400">{{ timeAgo(post.created_at) }}</span>
                </div>
            </div>
            <div v-else-if="lists.replies" class="mt-6 rounded-xl border border-dashed border-ink-300 p-10 text-center">
                <p class="text-sm text-ink-500">No replies yet.</p>
            </div>
        </template>

        <template v-else>
            <div v-if="lists.bookmarks?.length" class="mt-6 space-y-3">
                <ThreadCard v-for="thread in lists.bookmarks" :key="thread.id" :thread="thread" />
            </div>
            <div v-else-if="lists.bookmarks" class="mt-6 rounded-xl border border-dashed border-ink-300 p-10 text-center">
                <p class="text-sm text-ink-500">No bookmarks yet. Hit "Bookmark" on any thread to save it here.</p>
            </div>
        </template>
    </div>
</template>
