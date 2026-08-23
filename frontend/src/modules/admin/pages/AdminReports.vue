<script setup>
import { onMounted, ref } from 'vue';
import adminApi from '../services/adminApi';
import { timeAgo } from '../../../utils/date';

const loading = ref(true);
const error = ref(null);
const reports = ref([]);

async function load() {
    try {
        const { data } = await adminApi.getReports();
        reports.value = data.data ?? [];
    } catch {
        error.value = 'Could not load reports.';
    } finally {
        loading.value = false;
    }
}

async function review(report, status) {
    try {
        await adminApi.reviewReport(report.id, status);
        reports.value = reports.value.filter((r) => r.id !== report.id);
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not update the report.';
    }
}

onMounted(load);
</script>

<template>
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink-900">Reports</h1>
        <p class="mt-1 text-sm text-ink-500">Open reports from the community, oldest first.</p>

        <p v-if="error" class="alert-error mt-4">{{ error }}</p>

        <div v-if="loading" class="mt-6 space-y-3">
            <div v-for="n in 3" :key="n" class="h-20 animate-pulse rounded-xl bg-ink-100"></div>
        </div>

        <div v-else-if="reports.length" class="mt-6 space-y-3">
            <div v-for="report in reports" :key="report.id" class="card p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm text-ink-700">
                            <span class="font-medium">{{ report.reporter?.name }}</span>
                            reported a {{ report.post ? 'reply' : 'thread' }}
                            <span class="text-ink-400">· {{ timeAgo(report.created_at) }}</span>
                        </p>
                        <p class="mt-1 text-sm text-ink-500">"{{ report.reason }}"</p>
                        <router-link
                            v-if="report.thread || report.post?.thread"
                            :to="{ name: 'threads.show', params: { slug: report.thread?.slug ?? report.post.thread.slug } }"
                            class="mt-1 inline-block text-sm font-medium text-brand-600 hover:underline"
                        >
                            {{ report.thread?.title ?? report.post.thread.title }} →
                        </router-link>
                        <p v-if="report.post" class="mt-1 truncate text-xs text-ink-400">{{ report.post.excerpt }}</p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <button type="button" class="btn-primary !px-3 !py-1.5 text-sm" @click="review(report, 'resolved')">Resolve</button>
                        <button type="button" class="btn-ghost !px-3 !py-1.5 text-sm" @click="review(report, 'dismissed')">Dismiss</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="mt-6 rounded-xl border border-dashed border-ink-300 p-10 text-center">
            <p class="text-sm text-ink-500">No open reports. All quiet.</p>
        </div>
    </div>
</template>
