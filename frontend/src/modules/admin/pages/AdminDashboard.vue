<script setup>
import { computed, onMounted, ref } from 'vue';
import adminApi from '../services/adminApi';
import icons from '../icons';
import AdminPageHeader from '../components/AdminPageHeader.vue';
import UserAvatar from '../../../components/UserAvatar.vue';
import { useAuthStore } from '../../auth/stores/auth';
import { timeAgo } from '../../../utils/date';

const auth = useAuthStore();
const loading = ref(true);
const error = ref(null);
const dashboard = ref(null);

const statCards = computed(() => {
    if (!dashboard.value) return [];
    const { totals, this_week: week } = dashboard.value;
    return [
        { label: 'Users', value: totals.users, delta: week.users, icon: icons.users, bubble: 'from-brand-500 to-brand-700 shadow-brand-200' },
        { label: 'Threads', value: totals.threads, delta: week.threads, icon: icons.threads, bubble: 'from-sky-500 to-sky-700 shadow-sky-200' },
        { label: 'Posts', value: totals.posts, delta: week.posts, icon: icons.posts, bubble: 'from-emerald-500 to-emerald-700 shadow-emerald-200' },
        { label: 'Open reports', value: totals.open_reports, alert: totals.open_reports > 0, icon: icons.reports, bubble: 'from-rose-500 to-rose-700 shadow-rose-200' },
    ];
});

const maxCategoryThreads = computed(() =>
    Math.max(1, ...(dashboard.value?.top_categories ?? []).map((c) => c.threads_count)),
);

onMounted(async () => {
    try {
        const { data } = await adminApi.getDashboard();
        dashboard.value = data.data;
    } catch {
        error.value = 'Could not load the dashboard.';
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div>
        <AdminPageHeader
            title="Dashboard"
            :subtitle="`Welcome back, ${auth.user?.name ?? 'admin'} — here's what's happening on the forum.`"
            :icon="icons.dashboard"
        />

        <p v-if="error" class="alert-error mt-4">{{ error }}</p>

        <div v-if="loading" class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div v-for="n in 8" :key="n" class="h-28 animate-pulse rounded-2xl bg-ink-100"></div>
        </div>

        <template v-else-if="dashboard">
            <!-- primary stats -->
            <div class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div
                    v-for="stat in statCards"
                    :key="stat.label"
                    class="card rounded-2xl p-4 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-ink-200/60"
                >
                    <div class="flex items-start justify-between">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-md" :class="stat.bubble">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="stat.icon" />
                            </svg>
                        </span>
                        <span
                            v-if="stat.delta"
                            class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-600"
                        >
                            +{{ stat.delta }} this week
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold tracking-tight" :class="stat.alert ? 'text-rose-600' : 'text-ink-900'">
                        {{ stat.value }}
                    </p>
                    <p class="text-xs font-medium text-ink-500">{{ stat.label }}</p>
                </div>
            </div>

            <!-- secondary stats -->
            <div class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="card flex items-center justify-between rounded-2xl px-4 py-3">
                    <p class="text-xs font-medium text-ink-500">Categories</p>
                    <p class="text-lg font-bold text-ink-900">{{ dashboard.totals.categories }}</p>
                </div>
                <div class="card flex items-center justify-between rounded-2xl px-4 py-3">
                    <p class="text-xs font-medium text-ink-500">Tags</p>
                    <p class="text-lg font-bold text-ink-900">{{ dashboard.totals.tags }}</p>
                </div>
                <div class="card flex items-center justify-between rounded-2xl px-4 py-3">
                    <p class="text-xs font-medium text-ink-500">Banned users</p>
                    <p class="text-lg font-bold" :class="dashboard.totals.banned_users ? 'text-rose-600' : 'text-ink-900'">
                        {{ dashboard.totals.banned_users }}
                    </p>
                </div>
                <router-link
                    :to="{ name: 'admin.reports' }"
                    class="card group flex items-center justify-between rounded-2xl px-4 py-3 transition hover:border-brand-300 hover:bg-brand-50/40"
                >
                    <p class="text-xs font-medium text-brand-600">Review reports</p>
                    <span class="text-brand-500 transition group-hover:translate-x-0.5">→</span>
                </router-link>
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                <!-- latest threads -->
                <div class="card overflow-hidden rounded-2xl">
                    <div class="flex items-center justify-between border-b border-ink-100 bg-ink-50/60 px-4 py-3">
                        <h2 class="text-sm font-semibold text-ink-900">Latest threads</h2>
                        <router-link :to="{ name: 'admin.threads' }" class="text-xs font-medium text-brand-600 hover:underline">View all →</router-link>
                    </div>
                    <div class="divide-y divide-ink-100">
                        <div
                            v-for="thread in dashboard.latest_threads"
                            :key="thread.id"
                            class="flex items-start gap-3 px-4 py-3 transition hover:bg-ink-50/70"
                        >
                            <span
                                class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                                :class="thread.status === 'hidden' ? 'bg-ink-300' : 'bg-emerald-400'"
                                :title="thread.status"
                            ></span>
                            <div class="min-w-0">
                                <router-link
                                    :to="{ name: 'threads.show', params: { slug: thread.slug } }"
                                    class="line-clamp-1 text-sm font-medium text-ink-800 hover:text-brand-600"
                                >
                                    {{ thread.title }}
                                </router-link>
                                <p class="mt-0.5 text-xs text-ink-400">
                                    {{ thread.user?.name }} · {{ thread.category?.name }} · {{ thread.replies_count }} replies · {{ timeAgo(thread.created_at) }}
                                </p>
                            </div>
                        </div>
                        <p v-if="!dashboard.latest_threads.length" class="px-4 py-8 text-center text-sm text-ink-400">No threads yet.</p>
                    </div>
                </div>

                <!-- latest users -->
                <div class="card overflow-hidden rounded-2xl">
                    <div class="flex items-center justify-between border-b border-ink-100 bg-ink-50/60 px-4 py-3">
                        <h2 class="text-sm font-semibold text-ink-900">New members</h2>
                        <router-link :to="{ name: 'admin.users' }" class="text-xs font-medium text-brand-600 hover:underline">View all →</router-link>
                    </div>
                    <div class="divide-y divide-ink-100">
                        <div
                            v-for="user in dashboard.latest_users"
                            :key="user.id"
                            class="flex items-center justify-between gap-3 px-4 py-3 transition hover:bg-ink-50/70"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <UserAvatar :name="user.name" size="md" />
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-ink-800">
                                        {{ user.name }}
                                        <span v-if="user.role !== 'user'" class="ml-1 rounded-full bg-brand-100 px-2 py-0.5 text-[10px] font-semibold text-brand-700 uppercase">{{ user.role }}</span>
                                        <span v-if="user.banned_at" class="ml-1 rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700 uppercase">Banned</span>
                                    </p>
                                    <p class="truncate text-xs text-ink-400">{{ user.email }}</p>
                                </div>
                            </div>
                            <p class="shrink-0 text-xs text-ink-400">{{ timeAgo(user.created_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- top categories -->
            <div class="card mt-4 overflow-hidden rounded-2xl">
                <div class="flex items-center justify-between border-b border-ink-100 bg-ink-50/60 px-4 py-3">
                    <h2 class="text-sm font-semibold text-ink-900">Top categories</h2>
                    <router-link :to="{ name: 'admin.categories' }" class="text-xs font-medium text-brand-600 hover:underline">Manage →</router-link>
                </div>
                <div class="space-y-3 p-4">
                    <div v-for="category in dashboard.top_categories" :key="category.id">
                        <div class="flex items-center justify-between text-sm">
                            <router-link
                                :to="{ name: 'categories.show', params: { slug: category.slug } }"
                                class="font-medium text-ink-700 hover:text-brand-600"
                            >
                                <span v-if="category.icon" class="mr-0.5">{{ category.icon }}</span>
                                {{ category.name }}
                            </router-link>
                            <span class="text-xs text-ink-400">{{ category.threads_count }} threads</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-ink-100">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-brand-400 to-brand-600"
                                :style="{ width: `${Math.round((category.threads_count / maxCategoryThreads) * 100)}%` }"
                            ></div>
                        </div>
                    </div>
                    <p v-if="!dashboard.top_categories.length" class="py-4 text-center text-sm text-ink-400">No categories yet.</p>
                </div>
            </div>
        </template>
    </div>
</template>
