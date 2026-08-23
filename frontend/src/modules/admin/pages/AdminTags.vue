<script setup>
import { onMounted, ref } from 'vue';
import adminApi from '../services/adminApi';

const loading = ref(true);
const error = ref(null);
const tags = ref([]);

const newTag = ref('');
const creating = ref(false);

const editingSlug = ref(null);
const editName = ref('');
const saving = ref(false);

async function load() {
    try {
        const { data } = await adminApi.getTags();
        tags.value = data.data ?? [];
    } catch {
        error.value = 'Could not load tags.';
    } finally {
        loading.value = false;
    }
}

async function create() {
    creating.value = true;
    error.value = null;
    try {
        await adminApi.createTag({ name: newTag.value });
        newTag.value = '';
        await load();
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not create the tag.';
    } finally {
        creating.value = false;
    }
}

function startEdit(tag) {
    editingSlug.value = tag.slug;
    editName.value = tag.name;
}

async function saveEdit() {
    saving.value = true;
    error.value = null;
    try {
        await adminApi.updateTag(editingSlug.value, { name: editName.value });
        editingSlug.value = null;
        await load();
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not rename the tag.';
    } finally {
        saving.value = false;
    }
}

async function remove(tag) {
    if (!confirm(`Delete the tag "${tag.name}"? It will be removed from ${tag.threads_count} thread(s).`)) return;
    error.value = null;
    try {
        await adminApi.deleteTag(tag.slug);
        await load();
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not delete the tag.';
    }
}

onMounted(load);
</script>

<template>
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink-900">Tags</h1>
        <p class="mt-1 text-sm text-ink-500">Create, rename and delete the tags used on threads.</p>

        <p v-if="error" class="alert-error mt-4">{{ error }}</p>

        <form class="card mt-4 flex flex-wrap items-end gap-3 p-4" @submit.prevent="create">
            <div class="min-w-48 flex-1">
                <label class="field-label" for="tag-name">Name</label>
                <input id="tag-name" v-model="newTag" required maxlength="50" class="input" placeholder="New tag" />
            </div>
            <button type="submit" :disabled="creating" class="btn-primary">
                {{ creating ? 'Adding...' : 'Add' }}
            </button>
        </form>

        <div v-if="loading" class="mt-4 space-y-3">
            <div v-for="n in 4" :key="n" class="h-14 animate-pulse rounded-xl bg-ink-100"></div>
        </div>

        <div v-else-if="tags.length" class="card mt-4 divide-y divide-ink-100">
            <div v-for="tag in tags" :key="tag.id" class="flex flex-wrap items-center justify-between gap-3 p-4">
                <div v-if="editingSlug === tag.slug" class="flex flex-1 items-center gap-2">
                    <input v-model="editName" maxlength="50" class="input !w-56" @keyup.enter="saveEdit" />
                    <button type="button" :disabled="saving" class="btn-primary !px-3 !py-1.5 text-sm" @click="saveEdit">
                        {{ saving ? 'Saving...' : 'Save' }}
                    </button>
                    <button type="button" class="btn-ghost !px-3 !py-1.5 text-sm" @click="editingSlug = null">Cancel</button>
                </div>

                <template v-else>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-ink-900">{{ tag.name }}</p>
                        <p class="text-xs text-ink-400">{{ tag.slug }} · {{ tag.threads_count }} thread(s)</p>
                    </div>
                    <div class="flex shrink-0 gap-1.5">
                        <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs" @click="startEdit(tag)">Rename</button>
                        <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs text-red-600" @click="remove(tag)">Delete</button>
                    </div>
                </template>
            </div>
        </div>

        <div v-else class="mt-4 rounded-xl border border-dashed border-ink-300 p-10 text-center">
            <p class="text-sm text-ink-500">No tags yet — create the first one above.</p>
        </div>
    </div>
</template>
