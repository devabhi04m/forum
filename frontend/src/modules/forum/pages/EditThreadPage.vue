<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useForumStore } from '../stores/forum';

const route = useRoute();
const router = useRouter();
const forum = useForumStore();

const title = ref('');
const content = ref('');
const selectedTags = ref([]);
const submitting = ref(false);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    forum.fetchTags();

    try {
        await forum.fetchThread(route.params.slug);
        title.value = forum.currentThread?.title ?? '';
        content.value = forum.currentThread?.content ?? '';
        selectedTags.value = (forum.currentThread?.tags ?? []).map((t) => t.id);
    } finally {
        loading.value = false;
    }
});

function toggleTag(id) {
    if (selectedTags.value.includes(id)) {
        selectedTags.value = selectedTags.value.filter((t) => t !== id);
    } else {
        selectedTags.value.push(id);
    }
}

async function onSubmit() {
    error.value = null;
    submitting.value = true;
    try {
        const thread = await forum.updateThread(forum.currentThread.id, {
            title: title.value,
            content: content.value,
            tags: selectedTags.value,
        });
        router.push({ name: 'threads.show', params: { slug: thread.slug } });
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not update thread.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="mx-auto max-w-2xl px-4 py-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink-900">Edit thread</h1>
        <p class="mt-1 text-sm text-ink-500">Update your title or content.</p>

        <div v-if="loading" class="mt-6 space-y-3">
            <div class="h-10 animate-pulse rounded-lg bg-ink-100"></div>
            <div class="h-32 animate-pulse rounded-lg bg-ink-100"></div>
        </div>

        <form v-else class="card mt-6 space-y-4 p-6" @submit.prevent="onSubmit">
            <div>
                <label class="field-label" for="thread-title">Title</label>
                <input
                    id="thread-title"
                    v-model="title"
                    type="text"
                    required
                    maxlength="255"
                    class="input"
                    placeholder="What's your question or topic?"
                />
            </div>

            <div>
                <label class="field-label" for="thread-content">Content</label>
                <textarea
                    id="thread-content"
                    v-model="content"
                    required
                    minlength="10"
                    rows="7"
                    class="input resize-none"
                    placeholder="Write your post here..."
                ></textarea>
            </div>

            <div v-if="forum.tags.length">
                <span class="field-label">Tags <span class="font-normal text-ink-400">(optional)</span></span>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="tag in forum.tags"
                        :key="tag.id"
                        type="button"
                        class="chip"
                        :class="selectedTags.includes(tag.id) && '!border-brand-400 !bg-brand-50 !text-brand-700'"
                        @click="toggleTag(tag.id)"
                    >
                        {{ tag.name }}
                    </button>
                </div>
            </div>

            <p v-if="error" class="alert-error">{{ error }}</p>

            <div class="flex justify-end gap-2 border-t border-ink-100 pt-4">
                <button type="button" class="btn-ghost" @click="router.back()">Cancel</button>
                <button type="submit" :disabled="submitting" class="btn-primary">
                    {{ submitting ? 'Saving...' : 'Save changes' }}
                </button>
            </div>
        </form>
    </div>
</template>
