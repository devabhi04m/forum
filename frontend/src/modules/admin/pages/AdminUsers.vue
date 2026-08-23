<script setup>
import { onMounted, ref } from 'vue';
import adminApi from '../services/adminApi';
import { useAuthStore } from '../../auth/stores/auth';
import { timeAgo } from '../../../utils/date';

const auth = useAuthStore();

const loading = ref(false);
const error = ref(null);
const users = ref([]);
const meta = ref(null);
const search = ref('');
const page = ref(1);

async function load() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await adminApi.getUsers({ q: search.value || undefined, page: page.value });
        users.value = data.data ?? [];
        meta.value = data.meta ?? null;
    } catch {
        error.value = 'Could not load users.';
    } finally {
        loading.value = false;
    }
}

function onSearch() {
    page.value = 1;
    load();
}

function goTo(newPage) {
    page.value = newPage;
    load();
}

async function toggleBan(user) {
    const action = user.banned_at ? 'Unban' : 'Ban';
    if (!confirm(`${action} ${user.name}?`)) return;
    try {
        const { data } = await adminApi.toggleBan(user.id);
        user.banned_at = (data.data ?? data).banned_at;
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not update the ban.';
    }
}

async function changeRole(user, event) {
    const role = event.target.value;
    if (role === user.role) return;
    if (role === 'admin' && !confirm(`Make ${user.name} an admin? Admins control everything and cannot be demoted from here.`)) {
        event.target.value = user.role;
        return;
    }
    try {
        const { data } = await adminApi.setRole(user.id, role);
        user.role = (data.data ?? data).role;
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not change the role.';
        event.target.value = user.role;
    }
}

onMounted(load);
</script>

<template>
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink-900">Users</h1>
        <p class="mt-1 text-sm text-ink-500">Search members, change roles, ban and unban accounts.</p>

        <form class="mt-4 flex gap-2" @submit.prevent="onSearch">
            <input v-model="search" type="search" class="input max-w-xs" placeholder="Search by name or email..." />
            <button type="submit" class="btn-primary !px-3 !py-2">Search</button>
        </form>

        <p v-if="error" class="alert-error mt-4">{{ error }}</p>

        <div v-if="loading" class="mt-4 space-y-3">
            <div v-for="n in 5" :key="n" class="h-16 animate-pulse rounded-xl bg-ink-100"></div>
        </div>

        <div v-else-if="users.length" class="card mt-4 divide-y divide-ink-100">
            <div v-for="user in users" :key="user.id" class="flex flex-wrap items-center justify-between gap-3 p-4">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-ink-900">
                        {{ user.name }}
                        <span v-if="user.id === auth.user?.id" class="ml-1 rounded bg-ink-100 px-1.5 py-0.5 text-[10px] font-semibold text-ink-500 uppercase">You</span>
                        <span v-if="user.role !== 'user'" class="ml-1 rounded bg-brand-100 px-1.5 py-0.5 text-[10px] font-semibold text-brand-700 uppercase">{{ user.role }}</span>
                        <span v-if="user.banned_at" class="ml-1 rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-semibold text-red-700 uppercase">Banned</span>
                    </p>
                    <p class="text-xs text-ink-400">
                        {{ user.email }} · {{ user.threads_count }} threads · {{ user.posts_count }} posts · joined {{ timeAgo(user.created_at) }}
                    </p>
                </div>
                <div v-if="user.role !== 'admin'" class="flex shrink-0 items-center gap-2">
                    <select class="input !w-auto !px-2 !py-1 text-xs" :value="user.role" @change="changeRole(user, $event)">
                        <option value="user">User</option>
                        <option value="moderator">Moderator</option>
                        <option value="admin">Admin</option>
                    </select>
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

        <div v-else class="mt-4 rounded-xl border border-dashed border-ink-300 p-10 text-center">
            <p class="text-sm text-ink-500">No users match that search.</p>
        </div>

        <div v-if="meta && meta.last_page > 1" class="mt-4 flex items-center justify-between text-sm text-ink-500">
            <button type="button" class="btn-ghost !px-3 !py-1.5" :disabled="page <= 1" @click="goTo(page - 1)">← Prev</button>
            <span>Page {{ meta.current_page }} of {{ meta.last_page }} · {{ meta.total }} users</span>
            <button type="button" class="btn-ghost !px-3 !py-1.5" :disabled="page >= meta.last_page" @click="goTo(page + 1)">Next →</button>
        </div>
    </div>
</template>
