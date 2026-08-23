<script setup>
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useForumStore } from '../stores/forum';
import ThreadCard from '../components/ThreadCard.vue';

const route = useRoute();
const router = useRouter();
const forum = useForumStore();

const term = ref(route.query.q ?? '');
const searched = ref(false);

async function load() {
    term.value = route.query.q ?? '';

    if (!route.query.q && !route.query.tag) {
        forum.threads = [];
        searched.value = false;
        return;
    }

    const params = {};
    if (route.query.q) params.q = route.query.q;
    if (route.query.tag) params.tag = route.query.tag;

    await forum.fetchThreads(params);
    searched.value = true;
}

function onSubmit() {
    const q = term.value.trim();
    if (!q) return;
    router.push({ name: 'search', query: { q } });
}

onMounted(load);
watch(() => route.query, load);
</script>

<template>
    <div class="mx-auto max-w-4xl px-4 py-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink-900">
            <template v-if="route.query.tag">Threads tagged "{{ route.query.tag }}"</template>
            <template v-else>Search</template>
        </h1>

        <form v-if="!route.query.tag" class="mt-4 flex gap-2" @submit.prevent="onSubmit">
            <input
                v-model="term"
                type="search"
                class="input"
                placeholder="Search threads..."
                aria-label="Search threads"
            />
            <button type="submit" class="btn-primary shrink-0">Search</button>
        </form>

        <p v-if="forum.error" class="alert-error mt-4">{{ forum.error }}</p>

        <div v-if="forum.loading" class="mt-6 space-y-3">
            <div v-for="n in 3" :key="n" class="h-28 animate-pulse rounded-xl bg-ink-100"></div>
        </div>

        <div v-else-if="searched && !forum.threads.length" class="mt-6 rounded-xl border border-dashed border-ink-300 p-10 text-center">
            <p class="text-sm text-ink-500">Nothing matched. Try a different keyword.</p>
        </div>

        <div v-else-if="forum.threads.length" class="mt-6 space-y-3">
            <p class="text-sm text-ink-500">
                {{ forum.threadsMeta?.total ?? forum.threads.length }} {{ (forum.threadsMeta?.total ?? forum.threads.length) === 1 ? 'result' : 'results' }}
            </p>
            <ThreadCard v-for="thread in forum.threads" :key="thread.id" :thread="thread" />

            <div v-if="forum.threadsMeta && forum.threadsMeta.current_page < forum.threadsMeta.last_page" class="pt-1 text-center">
                <button type="button" class="btn-ghost text-sm" @click="forum.loadMoreThreads()">
                    Load more results
                </button>
            </div>
        </div>
    </div>
</template>
