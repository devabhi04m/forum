<script setup>
import { onMounted, ref } from 'vue';
import adminApi from '../services/adminApi';
import icons from '../icons';
import AdminPageHeader from '../components/AdminPageHeader.vue';
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
        <AdminPageHeader
            title="Reports"
            subtitle="Open reports from the community, oldest first."
            :icon="icons.reports"
            :count="loading ? null : reports.length"
            count-label="open"
        />

        <p v-if="error" class="alert-error mt-4">{{ error }}</p>

        <div v-if="loading" class="mt-6 space-y-3">
            <div v-for="n in 3" :key="n" class="h-20 animate-pulse rounded-xl bg-ink-100"></div>
        </div>

        <div v-else-if="reports.length" class="mt-6 space-y-3">
            <div v-for="report in reports" :key="report.id" class="card rounded-2xl p-4 transition hover:border-brand-200 hover:shadow-md hover:shadow-ink-200/50">
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

        <div v-else class="mt-6 rounded-2xl border border-dashed border-ink-300 p-10 text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-500">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <p class="mt-3 text-sm font-medium text-ink-700">No open reports</p>
            <p class="mt-0.5 text-xs text-ink-400">All quiet — nothing needs your attention.</p>
        </div>
    </div>
</template>
