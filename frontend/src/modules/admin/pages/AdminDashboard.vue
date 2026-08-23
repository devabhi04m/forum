<script setup>
import { onMounted, ref } from 'vue';
import adminApi from '../services/adminApi';
import { timeAgo } from '../../../utils/date';

const loading = ref(true);
const error = ref(null);
const dashboard = ref(null);

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
        <h1 class="text-2xl font-semibold tracking-tight text-ink-900">Dashboard</h1>
        <p class="mt-1 text-sm text-ink-500">Everything happening on the forum at a glance.</p>

        <p v-if="error" class="alert-error mt-4">{{ error }}</p>

        <div v-if="loading" class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div v-for="n in 8" :key="n" class="h-24 animate-pulse rounded-xl bg-ink-100"></div>
        </div>

        <template v-else-if="dashboard">
            <!-- stat cards -->
            <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="card p-4">
                    <p class="text-2xl font-semibold text-ink-900">{{ dashboard.totals.users }}</p>
                    <p class="text-xs text-ink-500">Users <span v-if="dashboard.this_week.users" class="text-emerald-600">+{{ dashboard.this_week.users }} this week</span></p>
                </div>
                <div class="card p-4">
                    <p class="text-2xl font-semibold text-ink-900">{{ dashboard.totals.threads }}</p>
                    <p class="text-xs text-ink-500">Threads <span v-if="dashboard.this_week.threads" class="text-emerald-600">+{{ dashboard.this_week.threads }}</span></p>
                </div>
                <div class="card p-4">
                    <p class="text-2xl font-semibold text-ink-900">{{ dashboard.totals.posts }}</p>
                    <p class="text-xs text-ink-500">Posts <span v-if="dashboard.this_week.posts" class="text-emerald-600">+{{ dashboard.this_week.posts }}</span></p>
                </div>
                <div class="card p-4">
                    <p class="text-2xl font-semibold" :class="dashboard.totals.open_reports ? 'text-red-600' : 'text-ink-900'">
                        {{ dashboard.totals.open_reports }}
                    </p>
                    <p class="text-xs text-ink-500">Open reports</p>
                </div>
                <div class="card p-4">
                    <p class="text-2xl font-semibold text-ink-900">{{ dashboard.totals.categories }}</p>
                    <p class="text-xs text-ink-500">Categories</p>
                </div>
                <div class="card p-4">
                    <p class="text-2xl font-semibold text-ink-900">{{ dashboard.totals.tags }}</p>
                    <p class="text-xs text-ink-500">Tags</p>
                </div>
                <div class="card p-4">
                    <p class="text-2xl font-semibold" :class="dashboard.totals.banned_users ? 'text-red-600' : 'text-ink-900'">
                        {{ dashboard.totals.banned_users }}
                    </p>
                    <p class="text-xs text-ink-500">Banned users</p>
                </div>
                <router-link :to="{ name: 'admin.reports' }" class="card flex flex-col justify-center p-4 transition hover:border-brand-300">
                    <p class="text-sm font-medium text-brand-600">Review reports →</p>
                    <p class="mt-0.5 text-xs text-ink-400">Jump to the queue</p>
                </router-link>
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                <!-- latest threads -->
                <div class="card">
                    <div class="flex items-center justify-between border-b border-ink-100 px-4 py-3">
                        <h2 class="text-sm font-semibold text-ink-900">Latest threads</h2>
                        <router-link :to="{ name: 'admin.threads' }" class="text-xs font-medium text-brand-600 hover:underline">View all</router-link>
                    </div>
                    <div class="divide-y divide-ink-100">
                        <div v-for="thread in dashboard.latest_threads" :key="thread.id" class="px-4 py-3">
                            <router-link
                                :to="{ name: 'threads.show', params: { slug: thread.slug } }"
                                class="line-clamp-1 text-sm font-medium text-ink-800 hover:text-brand-600"
                            >
                                {{ thread.title }}
                            </router-link>
                            <p class="mt-0.5 text-xs text-ink-400">
                                {{ thread.user?.name }} · {{ thread.category?.name }} · {{ thread.replies_count }} replies · {{ timeAgo(thread.created_at) }}
                                <span v-if="thread.status === 'hidden'" class="ml-1 rounded bg-ink-100 px-1 py-0.5 text-[10px] font-semibold text-ink-500 uppercase">Hidden</span>
                            </p>
                        </div>
                        <p v-if="!dashboard.latest_threads.length" class="px-4 py-6 text-center text-sm text-ink-400">No threads yet.</p>
                    </div>
                </div>

                <!-- latest users -->
                <div class="card">
                    <div class="flex items-center justify-between border-b border-ink-100 px-4 py-3">
                        <h2 class="text-sm font-semibold text-ink-900">New members</h2>
                        <router-link :to="{ name: 'admin.users' }" class="text-xs font-medium text-brand-600 hover:underline">View all</router-link>
                    </div>
                    <div class="divide-y divide-ink-100">
                        <div v-for="user in dashboard.latest_users" :key="user.id" class="flex items-center justify-between px-4 py-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-ink-800">
                                    {{ user.name }}
                                    <span v-if="user.role !== 'user'" class="ml-1 rounded bg-brand-100 px-1.5 py-0.5 text-[10px] font-semibold text-brand-700 uppercase">{{ user.role }}</span>
                                    <span v-if="user.banned_at" class="ml-1 rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-semibold text-red-700 uppercase">Banned</span>
                                </p>
                                <p class="truncate text-xs text-ink-400">{{ user.email }}</p>
                            </div>
                            <p class="shrink-0 text-xs text-ink-400">{{ timeAgo(user.created_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- top categories -->
            <div class="card mt-4">
                <div class="flex items-center justify-between border-b border-ink-100 px-4 py-3">
                    <h2 class="text-sm font-semibold text-ink-900">Top categories</h2>
                    <router-link :to="{ name: 'admin.categories' }" class="text-xs font-medium text-brand-600 hover:underline">Manage</router-link>
                </div>
                <div class="flex flex-wrap gap-2 p-4">
                    <router-link
                        v-for="category in dashboard.top_categories"
                        :key="category.id"
                        :to="{ name: 'categories.show', params: { slug: category.slug } }"
                        class="chip"
                    >
                        {{ category.name }}
                        <span class="text-xs text-ink-400">{{ category.threads_count }}</span>
                    </router-link>
                    <p v-if="!dashboard.top_categories.length" class="text-sm text-ink-400">No categories yet.</p>
                </div>
            </div>
        </template>
    </div>
</template>
