<script setup>
import { computed, onMounted, ref } from 'vue';
import adminApi from '../services/adminApi';
import icons from '../icons';
import AdminPageHeader from '../components/AdminPageHeader.vue';

const loading = ref(true);
const error = ref(null);
const categories = ref([]);

const newCategory = ref({ name: '', description: '', icon: '', parent_id: '' });
const creating = ref(false);

const editingSlug = ref(null);
const editForm = ref({ name: '', description: '', icon: '', sort_order: 0 });
const saving = ref(false);

// quick picks for the icon field; anything typed/pasted works too
const EMOJI = ['💻', '🎮', '🎬', '🎵', '📚', '🏀', '✈️', '🍕', '🔧', '💬', '📣', '❓'];

// parents first, each followed by its children, with depth for indenting
const flat = computed(() =>
    categories.value.flatMap((category) => [
        { ...category, depth: 0 },
        ...(category.children ?? []).map((child) => ({ ...child, depth: 1 })),
    ]),
);

async function load() {
    try {
        const { data } = await adminApi.getCategories();
        categories.value = data.data ?? [];
    } catch {
        error.value = 'Could not load categories.';
    } finally {
        loading.value = false;
    }
}

async function create() {
    creating.value = true;
    error.value = null;
    try {
        await adminApi.createCategory({
            name: newCategory.value.name,
            description: newCategory.value.description || null,
            icon: newCategory.value.icon || null,
            parent_id: newCategory.value.parent_id || null,
        });
        newCategory.value = { name: '', description: '', icon: '', parent_id: '' };
        await load();
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not create the category.';
    } finally {
        creating.value = false;
    }
}

function startEdit(category) {
    editingSlug.value = category.slug;
    editForm.value = {
        name: category.name,
        description: category.description ?? '',
        icon: category.icon ?? '',
        sort_order: category.sort_order ?? 0,
    };
}

async function saveEdit() {
    saving.value = true;
    error.value = null;
    try {
        await adminApi.updateCategory(editingSlug.value, {
            name: editForm.value.name,
            description: editForm.value.description || null,
            icon: editForm.value.icon || null,
            sort_order: Number(editForm.value.sort_order) || 0,
        });
        editingSlug.value = null;
        await load();
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not save the category.';
    } finally {
        saving.value = false;
    }
}

async function toggleActive(category) {
    error.value = null;
    try {
        await adminApi.updateCategory(category.slug, { is_active: !category.is_active });
        await load();
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not update the category.';
    }
}

async function remove(category) {
    if (!confirm(`Delete "${category.name}"? Only works if it has no threads or subcategories.`)) return;
    error.value = null;
    try {
        await adminApi.deleteCategory(category.slug);
        await load();
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not delete it.';
    }
}

onMounted(load);
</script>

<template>
    <div>
        <AdminPageHeader
            title="Categories"
            subtitle="Create, edit, reorder, activate and delete forum categories."
            :icon="icons.categories"
            :count="loading ? null : flat.length"
            count-label="categories"
        />

        <p v-if="error" class="alert-error mt-4">{{ error }}</p>

        <form class="card mt-4 rounded-2xl p-4" @submit.prevent="create">
            <div class="flex flex-wrap items-end gap-3">
                <div class="w-20">
                    <label class="field-label" for="cat-icon">Icon</label>
                    <input id="cat-icon" v-model="newCategory.icon" maxlength="16" class="input text-center text-lg" placeholder="🙂" />
                </div>
                <div class="min-w-40 flex-1">
                    <label class="field-label" for="cat-name">Name</label>
                    <input id="cat-name" v-model="newCategory.name" required maxlength="100" class="input" placeholder="New category" />
                </div>
                <div class="min-w-40 flex-1">
                    <label class="field-label" for="cat-desc">Description</label>
                    <input id="cat-desc" v-model="newCategory.description" maxlength="500" class="input" placeholder="Optional" />
                </div>
                <div class="min-w-40">
                    <label class="field-label" for="cat-parent">Parent</label>
                    <select id="cat-parent" v-model="newCategory.parent_id" class="input">
                        <option value="">None (top level)</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>
                <button type="submit" :disabled="creating" class="btn-primary">
                    {{ creating ? 'Adding...' : 'Add' }}
                </button>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-1">
                <span class="mr-1 text-xs text-ink-400">Quick picks:</span>
                <button
                    v-for="emoji in EMOJI"
                    :key="emoji"
                    type="button"
                    class="rounded-lg px-1.5 py-0.5 text-lg transition hover:bg-ink-100"
                    :class="newCategory.icon === emoji && 'bg-brand-50 ring-1 ring-brand-300'"
                    @click="newCategory.icon = newCategory.icon === emoji ? '' : emoji"
                >
                    {{ emoji }}
                </button>
            </div>
        </form>

        <div v-if="loading" class="mt-4 space-y-3">
            <div v-for="n in 4" :key="n" class="h-16 animate-pulse rounded-xl bg-ink-100"></div>
        </div>

        <div v-else-if="flat.length" class="card mt-4 divide-y divide-ink-100 overflow-hidden rounded-2xl">
            <div v-for="category in flat" :key="category.id" class="p-4 transition hover:bg-ink-50/70">
                <!-- edit mode -->
                <div v-if="editingSlug === category.slug" class="flex flex-wrap items-end gap-3">
                    <div class="w-20">
                        <label class="field-label">Icon</label>
                        <input v-model="editForm.icon" maxlength="16" class="input text-center text-lg" placeholder="🙂" />
                    </div>
                    <div class="min-w-36 flex-1">
                        <label class="field-label">Name</label>
                        <input v-model="editForm.name" required maxlength="100" class="input" />
                    </div>
                    <div class="min-w-36 flex-1">
                        <label class="field-label">Description</label>
                        <input v-model="editForm.description" maxlength="500" class="input" />
                    </div>
                    <div class="w-24">
                        <label class="field-label">Order</label>
                        <input v-model="editForm.sort_order" type="number" min="0" class="input" />
                    </div>
                    <div class="flex gap-2">
                        <button type="button" :disabled="saving" class="btn-primary !px-3 !py-2" @click="saveEdit">
                            {{ saving ? 'Saving...' : 'Save' }}
                        </button>
                        <button type="button" class="btn-ghost !px-3 !py-2" @click="editingSlug = null">Cancel</button>
                    </div>
                </div>

                <!-- display mode -->
                <div v-else class="flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0" :class="category.depth && 'pl-5'">
                        <p class="text-sm font-medium text-ink-900">
                            <span v-if="category.icon" class="mr-1">{{ category.icon }}</span>
                            {{ category.name }}
                            <span v-if="!category.is_active" class="ml-1 rounded-full bg-ink-100 px-2 py-0.5 text-[10px] font-semibold text-ink-500 uppercase">Inactive</span>
                        </p>
                        <p class="text-xs text-ink-400">
                            {{ category.threads_count ?? 0 }} threads · order {{ category.sort_order ?? 0 }}
                            <span v-if="category.description"> · {{ category.description }}</span>
                        </p>
                    </div>
                    <div class="flex shrink-0 gap-1.5">
                        <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs" @click="startEdit(category)">Edit</button>
                        <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs" @click="toggleActive(category)">
                            {{ category.is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs text-red-600" @click="remove(category)">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="mt-4 rounded-xl border border-dashed border-ink-300 p-10 text-center">
            <p class="text-sm text-ink-500">No categories yet — create the first one above.</p>
        </div>
    </div>
</template>
