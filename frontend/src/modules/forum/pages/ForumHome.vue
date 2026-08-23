<script setup>
import { onMounted } from 'vue';
import { useForumStore } from '../stores/forum';
import ThreadCard from '../components/ThreadCard.vue';

const forum = useForumStore();

onMounted(() => {
    forum.fetchCategories();
    forum.fetchThreads();
});
</script>

<template>
    <div class="mx-auto max-w-4xl px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold tracking-tight text-ink-900">Forum</h1>
            <p class="mt-1 text-sm text-ink-500">Pick a category and jump in.</p>
        </div>

        <p v-if="forum.error" class="alert-error mb-4">{{ forum.error }}</p>

        <section aria-label="Categories">
            <div v-if="forum.loading && !forum.categories.length" class="space-y-3">
                <div v-for="n in 3" :key="n" class="h-24 animate-pulse rounded-xl bg-ink-100"></div>
            </div>

            <div v-else class="space-y-3">
                <div v-for="category in forum.categories" :key="category.id" class="card p-4 transition hover:border-brand-300">
                    <router-link
                        :to="{ name: 'categories.show', params: { slug: category.slug } }"
                        class="group flex items-center gap-3"
                    >
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-brand-50 to-brand-100 text-base font-semibold text-brand-700">
                            {{ category.name[0] }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-medium text-ink-900 group-hover:text-brand-700">{{ category.name }}</span>
                            <span v-if="category.description" class="mt-0.5 block truncate text-sm text-ink-500">{{ category.description }}</span>
                        </span>
                        <span class="shrink-0 rounded-full bg-ink-50 px-2.5 py-1 text-xs font-medium text-ink-500">
                            {{ category.threads_count ?? 0 }} {{ (category.threads_count ?? 0) === 1 ? 'thread' : 'threads' }}
                        </span>
                    </router-link>

                    <div v-if="category.children?.length" class="mt-3 flex flex-wrap gap-2 border-t border-ink-100 pt-3">
                        <router-link
                            v-for="child in category.children"
                            :key="child.id"
                            :to="{ name: 'categories.show', params: { slug: child.slug } }"
                            class="chip"
                        >
                            {{ child.name }}
                            <span class="text-xs text-ink-400">{{ child.threads_count ?? 0 }}</span>
                        </router-link>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="forum.threads.length" aria-label="Recent discussions" class="mt-10">
            <h2 class="mb-3 text-sm font-semibold tracking-wide text-ink-500 uppercase">Recent discussions</h2>
            <div class="space-y-3">
                <ThreadCard v-for="thread in forum.threads.slice(0, 5)" :key="thread.id" :thread="thread" />
            </div>
        </section>
    </div>
</template>
