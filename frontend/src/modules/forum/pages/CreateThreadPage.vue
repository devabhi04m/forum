<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useForumStore } from '../stores/forum';

const route = useRoute();
const router = useRouter();
const forum = useForumStore();

const categoryId = ref('');
const title = ref('');
const content = ref('');
const selectedTags = ref([]);
const submitting = ref(false);
const error = ref(null);

onMounted(async () => {
    forum.fetchTags();

    if (!forum.categories.length) {
        await forum.fetchCategories();
    }

    if (route.query.category) {
        const flat = forum.categories.flatMap((c) => [c, ...(c.children ?? [])]);
        const match = flat.find((c) => c.slug === route.query.category);
        if (match) categoryId.value = match.id;
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
        const thread = await forum.createThread({
            category_id: categoryId.value,
            title: title.value,
            content: content.value,
            tags: selectedTags.value,
        });
        router.push({ name: 'threads.show', params: { slug: thread.slug } });
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not create thread.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="mx-auto max-w-2xl px-4 py-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink-900">New thread</h1>
        <p class="mt-1 text-sm text-ink-500">Start a new discussion.</p>

        <form class="card mt-6 space-y-4 p-6" @submit.prevent="onSubmit">
            <div>
                <label class="field-label" for="thread-category">Category</label>
                <select id="thread-category" v-model="categoryId" required class="input">
                    <option value="" disabled>Select a category</option>
                    <template v-for="category in forum.categories" :key="category.id">
                        <option :value="category.id">{{ category.name }}</option>
                        <option v-for="child in category.children" :key="child.id" :value="child.id">
                            &nbsp;&nbsp;- {{ child.name }}
                        </option>
                    </template>
                </select>
            </div>

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
                    {{ submitting ? 'Creating...' : 'Create thread' }}
                </button>
            </div>
        </form>
    </div>
</template>
