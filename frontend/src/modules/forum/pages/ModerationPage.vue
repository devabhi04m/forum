<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import moderationApi from '../services/moderationApi';
import { useAuthStore } from '../../auth/stores/auth';
import { timeAgo } from '../../../utils/date';

const auth = useAuthStore();
const router = useRouter();

const tab = ref('reports');
const loading = ref(false);
const error = ref(null);

const reports = ref(null);
const stats = ref(null);
const users = ref(null);
const categories = ref(null);

const newCategory = ref({ name: '', description: '', parent_id: '' });
const creatingCategory = ref(false);

const tabs = computed(() => [
    { key: 'reports', label: 'Reports' },
    { key: 'stats', label: 'Stats' },
    ...(auth.isAdmin
        ? [
              { key: 'users', label: 'Users' },
              { key: 'categories', label: 'Categories' },
          ]
        : []),
]);

async function load(which) {
    loading.value = true;
    error.value = null;
    try {
        if (which === 'reports') {
            const { data } = await moderationApi.getReports();
            reports.value = data.data ?? [];
        } else if (which === 'stats') {
            const { data } = await moderationApi.getStats();
            stats.value = data.data ?? data;
        } else if (which === 'users') {
            const { data } = await moderationApi.getUsers();
            users.value = data.data ?? [];
        } else if (which === 'categories') {
            const { data } = await moderationApi.getAllCategories();
            categories.value = data.data ?? [];
        }
    } catch {
        error.value = 'Could not load this tab.';
    } finally {
        loading.value = false;
    }
}

async function review(report, status) {
    await moderationApi.reviewReport(report.id, status);
    reports.value = reports.value.filter((r) => r.id !== report.id);
}

async function toggleBan(user) {
    const action = user.banned_at ? 'Unban' : 'Ban';
    if (!confirm(`${action} ${user.name}?`)) return;
    const { data } = await moderationApi.toggleBan(user.id);
    user.banned_at = (data.data ?? data).banned_at;
}

async function setRole(user, role) {
    const { data } = await moderationApi.setRole(user.id, role);
    user.role = (data.data ?? data).role;
}

async function createCategory() {
    creatingCategory.value = true;
    try {
        await moderationApi.createCategory({
            name: newCategory.value.name,
            description: newCategory.value.description || null,
            parent_id: newCategory.value.parent_id || null,
        });
        newCategory.value = { name: '', description: '', parent_id: '' };
        await load('categories');
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not create the category.';
    } finally {
        creatingCategory.value = false;
    }
}

async function toggleCategoryActive(category) {
    await moderationApi.updateCategory(category.slug, { is_active: !category.is_active });
    await load('categories');
}

async function deleteCategory(category) {
    if (!confirm(`Delete "${category.name}"? Only works if it's empty.`)) return;
    try {
        await moderationApi.deleteCategory(category.slug);
        await load('categories');
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not delete it.';
    }
}

onMounted(() => {
    if (!auth.isModerator) {
        router.replace({ name: 'forum.home' });
        return;
    }
    load(tab.value);
});

watch(tab, load);
</script>

<template>
    <div class="mx-auto max-w-4xl px-4 py-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink-900">Moderation</h1>
        <p class="mt-1 text-sm text-ink-500">Reports, stats{{ auth.isAdmin ? ', users and categories' : '' }}.</p>

        <div class="mt-6 flex gap-2">
            <button
                v-for="t in tabs"
                :key="t.key"
                type="button"
                class="chip"
                :class="tab === t.key && '!border-brand-400 !bg-brand-50 !text-brand-700'"
                @click="tab = t.key"
            >
                {{ t.label }}
            </button>
        </div>

        <p v-if="error" class="alert-error mt-4">{{ error }}</p>

        <div v-if="loading" class="mt-6 space-y-3">
            <div v-for="n in 3" :key="n" class="h-20 animate-pulse rounded-xl bg-ink-100"></div>
        </div>

        <!-- Reports -->
        <template v-else-if="tab === 'reports'">
            <div v-if="reports?.length" class="mt-6 space-y-3">
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
                            <button type="button" class="btn-primary !px-3 !py-1.5 text-sm" @click="review(report, 'resolved')">
                                Resolve
                            </button>
                            <button type="button" class="btn-ghost !px-3 !py-1.5 text-sm" @click="review(report, 'dismissed')">
                                Dismiss
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else-if="reports" class="mt-6 rounded-xl border border-dashed border-ink-300 p-10 text-center">
                <p class="text-sm text-ink-500">No open reports. All quiet.</p>
            </div>
        </template>

        <!-- Stats -->
        <template v-else-if="tab === 'stats'">
            <div v-if="stats" class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="card p-4 text-center">
                    <p class="text-2xl font-semibold text-ink-900">{{ stats.users }}</p>
                    <p class="text-xs text-ink-500">Users <span v-if="stats.users_this_week">(+{{ stats.users_this_week }} this week)</span></p>
                </div>
                <div class="card p-4 text-center">
                    <p class="text-2xl font-semibold text-ink-900">{{ stats.threads }}</p>
                    <p class="text-xs text-ink-500">Threads <span v-if="stats.threads_this_week">(+{{ stats.threads_this_week }})</span></p>
                </div>
                <div class="card p-4 text-center">
                    <p class="text-2xl font-semibold text-ink-900">{{ stats.posts }}</p>
                    <p class="text-xs text-ink-500">Posts <span v-if="stats.posts_this_week">(+{{ stats.posts_this_week }})</span></p>
                </div>
                <div class="card p-4 text-center">
                    <p class="text-2xl font-semibold" :class="stats.open_reports ? 'text-red-600' : 'text-ink-900'">{{ stats.open_reports }}</p>
                    <p class="text-xs text-ink-500">Open reports</p>
                </div>
            </div>
        </template>

        <!-- Users (admin) -->
        <template v-else-if="tab === 'users'">
            <div v-if="users?.length" class="card mt-6 divide-y divide-ink-100">
                <div v-for="user in users" :key="user.id" class="flex flex-wrap items-center justify-between gap-3 p-4">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-ink-900">
                            {{ user.name }}
                            <span v-if="user.role !== 'user'" class="ml-1 rounded bg-brand-100 px-1.5 py-0.5 text-[10px] font-semibold text-brand-700 uppercase">{{ user.role }}</span>
                            <span v-if="user.banned_at" class="ml-1 rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-semibold text-red-700 uppercase">Banned</span>
                        </p>
                        <p class="text-xs text-ink-400">{{ user.email }} · {{ user.threads_count }} threads · {{ user.posts_count }} posts</p>
                    </div>
                    <div v-if="user.role !== 'admin'" class="flex shrink-0 gap-2">
                        <button
                            type="button"
                            class="btn-ghost !px-2.5 !py-1 text-xs"
                            @click="setRole(user, user.role === 'moderator' ? 'user' : 'moderator')"
                        >
                            {{ user.role === 'moderator' ? 'Demote' : 'Make mod' }}
                        </button>
                        <button
                            type="button"
                            class="btn-ghost !px-2.5 !py-1 text-xs"
                            :class="user.banned_at ? 'text-emerald-600' : 'text-red-600'"
                            @click="toggleBan(user)"
                        >
                            {{ user.banned_at ? 'Unban' : 'Ban' }}
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Categories (admin) -->
        <template v-else-if="tab === 'categories'">
            <form class="card mt-6 flex flex-wrap items-end gap-3 p-4" @submit.prevent="createCategory">
                <div class="min-w-40 flex-1">
                    <label class="field-label" for="cat-name">Name</label>
                    <input id="cat-name" v-model="newCategory.name" required maxlength="100" class="input" placeholder="New category" />
                </div>
                <div class="min-w-40 flex-1">
                    <label class="field-label" for="cat-parent">Parent</label>
                    <select id="cat-parent" v-model="newCategory.parent_id" class="input">
                        <option value="">None (top level)</option>
                        <option v-for="c in categories ?? []" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>
                <button type="submit" :disabled="creatingCategory" class="btn-primary">
                    {{ creatingCategory ? 'Adding...' : 'Add' }}
                </button>
            </form>

            <div v-if="categories?.length" class="card mt-4 divide-y divide-ink-100">
                <template v-for="category in categories" :key="category.id">
                    <div
                        v-for="c in [category, ...(category.children ?? [])]"
                        :key="c.id"
                        class="flex items-center justify-between gap-3 p-4"
                    >
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink-900" :class="c.parent_id && 'pl-5'">
                                {{ c.name }}
                                <span v-if="!c.is_active" class="ml-1 rounded bg-ink-100 px-1.5 py-0.5 text-[10px] font-semibold text-ink-500 uppercase">Inactive</span>
                            </p>
                            <p class="text-xs text-ink-400" :class="c.parent_id && 'pl-5'">{{ c.threads_count ?? 0 }} threads</p>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs" @click="toggleCategoryActive(c)">
                                {{ c.is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                            <button type="button" class="btn-ghost !px-2.5 !py-1 text-xs text-red-600" @click="deleteCategory(c)">
                                Delete
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>
</template>
