<script setup>
import { onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useForumStore } from '../stores/forum';
import ThreadCard from '../components/ThreadCard.vue';

const route = useRoute();
const forum = useForumStore();

async function load() {
    forum.currentCategory = null;
    forum.threads = [];
    await Promise.all([
        forum.fetchCategory(route.params.slug),
        forum.fetchThreads({ category: route.params.slug }),
    ]);
}

onMounted(load);
watch(() => route.params.slug, load);
</script>

<template>
    <div class="mx-auto max-w-4xl px-4 py-8">
        <router-link :to="{ name: 'forum.home' }" class="text-sm text-ink-500 transition hover:text-brand-600">
            ← All categories
        </router-link>

        <div class="mt-3 mb-6 flex items-start justify-between gap-4">
            <div v-if="forum.currentCategory" class="flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-50 to-brand-100 text-lg font-semibold text-brand-700">
                    {{ forum.currentCategory.name[0] }}
                </span>
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-ink-900">{{ forum.currentCategory.name }}</h1>
                    <p v-if="forum.currentCategory.description" class="mt-0.5 text-sm text-ink-500">{{ forum.currentCategory.description }}</p>
                </div>
            </div>
            <div v-else class="h-11 w-64 animate-pulse rounded-lg bg-ink-100"></div>

            <router-link
                :to="{ name: 'threads.create', query: { category: route.params.slug } }"
                class="btn-primary shrink-0 !px-3 !py-1.5"
            >
                New thread
            </router-link>
        </div>

        <div v-if="forum.currentCategory?.children?.length" class="mb-6 flex flex-wrap gap-2">
            <router-link
                v-for="child in forum.currentCategory.children"
                :key="child.id"
                :to="{ name: 'categories.show', params: { slug: child.slug } }"
                class="chip"
            >
                {{ child.name }}
                <span class="text-xs text-ink-400">{{ child.threads_count ?? 0 }}</span>
            </router-link>
        </div>

        <p v-if="forum.error" class="alert-error mb-4">{{ forum.error }}</p>

        <div v-if="forum.loading" class="space-y-3">
            <div v-for="n in 4" :key="n" class="h-28 animate-pulse rounded-xl bg-ink-100"></div>
        </div>

        <div v-else-if="!forum.threads.length" class="rounded-xl border border-dashed border-ink-300 p-10 text-center">
            <p class="text-sm text-ink-500">No threads yet in this category.</p>
            <router-link
                :to="{ name: 'threads.create', query: { category: route.params.slug } }"
                class="mt-3 inline-block text-sm font-medium text-brand-600 hover:underline"
            >
                Start the first discussion →
            </router-link>
        </div>

        <div v-else class="space-y-3">
            <ThreadCard v-for="thread in forum.threads" :key="thread.id" :thread="thread" />
        </div>

        <div v-if="forum.threadsMeta && forum.threadsMeta.current_page < forum.threadsMeta.last_page" class="mt-4 text-center">
            <button type="button" class="btn-ghost text-sm" @click="forum.loadMoreThreads()">
                Load more threads
            </button>
        </div>
    </div>
</template>
