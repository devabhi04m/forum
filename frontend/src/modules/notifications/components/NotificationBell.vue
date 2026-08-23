<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import notificationApi from '../services/notificationApi';
import { timeAgo } from '../../../utils/date';

const router = useRouter();

const open = ref(false);
const items = ref([]);
const unread = ref(0);
let timer = null;

const LABELS = {
    reply: 'replied to your thread',
    followed: 'posted in a thread you follow',
    mention: 'mentioned you',
    report: 'reviewed your report',
};

async function refresh() {
    try {
        const { data } = await notificationApi.list();
        items.value = data.data ?? [];
        unread.value = data.meta?.unread ?? 0;
    } catch {
        // header widget, fail quietly
    }
}

function describe(n) {
    if (n.data.kind === 'report') {
        return `Your report was ${n.data.status}`;
    }
    return `${n.data.actor} ${LABELS[n.data.kind] ?? 'did something'}`;
}

async function openItem(n) {
    open.value = false;
    if (!n.read_at) {
        notificationApi.markRead(n.id);
        n.read_at = new Date().toISOString();
        unread.value = Math.max(0, unread.value - 1);
    }
    if (n.data.thread_slug) {
        router.push({ name: 'threads.show', params: { slug: n.data.thread_slug } });
    }
}

async function markAllRead() {
    await notificationApi.markAllRead();
    items.value.forEach((n) => (n.read_at = n.read_at || new Date().toISOString()));
    unread.value = 0;
}

function toggle() {
    open.value = !open.value;
    if (open.value) refresh();
}

onMounted(() => {
    refresh();
    timer = setInterval(refresh, 60000);
});

onBeforeUnmount(() => clearInterval(timer));
</script>

<template>
    <div class="relative">
        <button
            type="button"
            class="relative flex h-9 w-9 cursor-pointer items-center justify-center rounded-full text-ink-500 transition hover:bg-ink-100 hover:text-ink-700"
            aria-label="Notifications"
            @click="toggle"
        >
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path d="M10 2.5a5 5 0 0 0-5 5v3l-1.4 2.6a.6.6 0 0 0 .53.9h11.74a.6.6 0 0 0 .53-.9L15 10.5v-3a5 5 0 0 0-5-5z" />
                <path d="M8.5 15.5a1.5 1.5 0 0 0 3 0" />
            </svg>
            <span
                v-if="unread"
                class="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white"
            >
                {{ unread > 9 ? '9+' : unread }}
            </span>
        </button>

        <div v-if="open" class="fixed inset-0 z-10" @click="open = false"></div>

        <div v-if="open" class="card absolute right-0 z-20 mt-2 w-80 overflow-hidden shadow-lg">
            <div class="flex items-center justify-between border-b border-ink-100 px-4 py-2.5">
                <span class="text-sm font-semibold text-ink-800">Notifications</span>
                <button
                    v-if="unread"
                    type="button"
                    class="cursor-pointer text-xs font-medium text-brand-600 hover:underline"
                    @click="markAllRead"
                >
                    Mark all read
                </button>
            </div>

            <p v-if="!items.length" class="px-4 py-6 text-center text-sm text-ink-400">Nothing yet.</p>

            <div v-else class="max-h-96 overflow-y-auto">
                <button
                    v-for="n in items"
                    :key="n.id"
                    type="button"
                    class="block w-full cursor-pointer border-b border-ink-100 px-4 py-3 text-left transition last:border-b-0 hover:bg-ink-50"
                    :class="!n.read_at && 'bg-brand-50/40'"
                    @click="openItem(n)"
                >
                    <span class="block text-sm text-ink-700">
                        {{ describe(n) }}
                        <span v-if="n.data.thread_title" class="font-medium">"{{ n.data.thread_title }}"</span>
                    </span>
                    <span v-if="n.data.excerpt" class="mt-0.5 block truncate text-xs text-ink-400">{{ n.data.excerpt }}</span>
                    <span class="mt-1 block text-xs text-ink-400">{{ timeAgo(n.created_at) }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
