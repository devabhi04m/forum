<script setup>
import { onMounted, ref, watch } from 'vue';
import adminApi from '../services/adminApi';
import { timeAgo } from '../../../utils/date';

const loading = ref(false);
const error = ref(null);
const threads = ref([]);
const meta = ref(null);
const search = ref('');
const status = ref('');
const page = ref(1);

const filters = [
    { value: '', label: 'All' },
    { value: 'published', label: 'Published' },
    { value: 'hidden', label: 'Hidden' },
    { value: 'deleted', label: 'Deleted' },
];

async function load() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await adminApi.getThreads({
            q: search.value || undefined,
            status: status.value || undefined,
            page: page.value,
        });
        threads.value = data.data ?? [];
        meta.value = data.meta ?? null;
    } catch {
        error.value = 'Could not load threads.';
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

async function togglePin(thread) {
    const { data } = await adminApi.togglePin(thread.slug);
    thread.is_pinned = data.data.is_pinned;
}

async function toggleLock(thread) {
    const { data } = await adminApi.toggleLock(thread.slug);
    thread.is_locked = data.data.is_locked;
}

async function toggleHide(thread) {
    const { data } = await adminApi.toggleHide(thread.slug);
    thread.status = data.data.status;
}

async function deleteThread(thread) {
    const permanent = !!thread.deleted_at;
    const message = permanent
        ? `Permanently delete "${thread.title}" and all its posts? This cannot be undone.`
        : `Delete "${thread.title}"? You can restore it from the Deleted filter.`;
    if (!confirm(message)) return;
    try {
        await adminApi.deleteThread(thread.slug);
        threads.value = threads.value.filter((t) => t.id !== thread.id);
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not delete the thread.';
    }
}

async function restoreThread(thread) {
    try {
        await adminApi.restoreThread(thread.slug);
        threads.value = threads.value.filter((t) => t.id !== thread.id);
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not restore the thread.';
    }
}

onMounted(load);
</script>

<template>
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink-900">Threads</h1>
        <p class="mt-1 text-sm text-ink-500">Every thread on the forum — pin, lock, hide, delete or restore.</p>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button
                v-for="filter in filters"
                :key="filter.value"
                type="button"
                class="chip"
                :class="status === filter.value && '!border-brand-400 !bg-brand-50 !text-brand-700'"
                @click="status = filter.value"
            >
                {{ filter.label }}
            </button>
            <form class="ml-auto flex gap-2" @submit.prevent="onSearch">
                <input v-model="search" type="search" class="input !w-48 !py-1.5" placeholder="Search titles..." />
                <button type="submit" class="btn-primary !px-3 !py-1.5 text-sm">Search</button>
            </form>
        </div>

        <p v-if="error" class="alert-error mt-4">{{ error }}</p>

        <div v-if="loading" class="mt-4 space-y-3">
            <div v-for="n in 5" :key="n" class="h-20 animate-pulse rounded-xl bg-ink-100"></div>
        </div>

        <div v-else-if="threads.length" class="card mt-4 divide-y divide-ink-100">
            <div v-for="thread in threads" :key="thread.id" class="p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <router-link
                            v-if="!thread.deleted_at"
                            :to="{ name: 'threads.show', params: { slug: thread.slug } }"
                            class="line-clamp-1 text-sm font-medium text-ink-900 hover:text-brand-600"
                        >
                            {{ thread.title }}
                        </router-link>
                        <p v-else class="line-clamp-1 text-sm font-medium text-ink-500 line-through">{{ thread.title }}</p>

                        <p class="mt-1 text-xs text-ink-400">
                            {{ thread.user?.name }} · {{ thread.category?.name }} · {{ thread.replies_count }} replies · {{ thread.views_count }} views · {{ timeAgo(thread.created_at) }}
                            <span v-if="thread.is_pinned" class="ml-1 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 uppercase">Pinned</span>
                            <span v-if="thread.is_locked" class="ml-1 rounded bg-ink-100 px-1.5 py-0.5 text-[10px] font-semibold text-ink-500 uppercase">Locked</span>
                            <span v-if="thread.status === 'hidden'" class="ml-1 rounded bg-ink-100 px-1.5 py-0.5 text-[10px] font-semibold text-ink-500 uppercase">Hidden</span>
                            <span v-if="thread.deleted_at" class="ml-1 rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-semibold text-red-700 uppercase">Deleted</span>
                        </p>
                    </div>

                    <div class="flex shrink-0 flex-wrap gap-1.5">
                        <template v-if="!thread.deleted_at">
                            <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs" @click="togglePin(thread)">
                                {{ thread.is_pinned ? 'Unpin' : 'Pin' }}
                            </button>
                            <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs" @click="toggleLock(thread)">
                                {{ thread.is_locked ? 'Unlock' : 'Lock' }}
                            </button>
                            <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs" @click="toggleHide(thread)">
                                {{ thread.status === 'hidden' ? 'Show' : 'Hide' }}
                            </button>
                            <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs text-red-600" @click="deleteThread(thread)">
                                Delete
                            </button>
                        </template>
                        <template v-else>
                            <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs text-emerald-600" @click="restoreThread(thread)">
                                Restore
                            </button>
                            <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs text-red-600" @click="deleteThread(thread)">
                                Delete forever
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="mt-4 rounded-xl border border-dashed border-ink-300 p-10 text-center">
            <p class="text-sm text-ink-500">No threads found.</p>
        </div>

        <div v-if="meta && meta.last_page > 1" class="mt-4 flex items-center justify-between text-sm text-ink-500">
            <button type="button" class="btn-ghost !px-3 !py-1.5" :disabled="page <= 1" @click="goTo(page - 1)">← Prev</button>
            <span>Page {{ meta.current_page }} of {{ meta.last_page }} · {{ meta.total }} threads</span>
            <button type="button" class="btn-ghost !px-3 !py-1.5" :disabled="page >= meta.last_page" @click="goTo(page + 1)">Next →</button>
        </div>
    </div>
</template>
