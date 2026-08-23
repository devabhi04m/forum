<script setup>
import { onMounted, ref } from 'vue';
import adminApi from '../services/adminApi';
import icons from '../icons';
import AdminPageHeader from '../components/AdminPageHeader.vue';

const loading = ref(true);
const error = ref(null);
const success = ref(null);
const status = ref(null);

const form = ref({ users: 5, threads: 10, posts: 30 });
const importing = ref(false);
const deleting = ref(false);

async function loadStatus() {
    try {
        const { data } = await adminApi.getDummyStatus();
        status.value = data.data;
    } catch {
        error.value = 'Could not load the dummy data status.';
    } finally {
        loading.value = false;
    }
}

async function importData() {
    importing.value = true;
    error.value = null;
    success.value = null;
    try {
        const { data } = await adminApi.importDummyData({
            users: Number(form.value.users),
            threads: Number(form.value.threads),
            posts: Number(form.value.posts),
        });
        const c = data.data;
        success.value = `Generated ${c.users} users, ${c.threads} threads and ${c.posts} posts.`;
        await loadStatus();
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not generate dummy data.';
    } finally {
        importing.value = false;
    }
}

async function deleteData() {
    if (!confirm(`Delete ALL dummy data? This removes ${status.value?.users ?? 0} dummy users and everything they created. Real content is untouched.`)) return;
    deleting.value = true;
    error.value = null;
    success.value = null;
    try {
        const { data } = await adminApi.deleteDummyData();
        const d = data.data;
        success.value = `Deleted ${d.users} users, ${d.threads} threads and ${d.posts} posts.`;
        await loadStatus();
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not delete the dummy data.';
    } finally {
        deleting.value = false;
    }
}

onMounted(loadStatus);
</script>

<template>
    <div>
        <AdminPageHeader
            title="Dummy data"
            subtitle="Fill the forum with realistic demo content, then wipe it in one click."
            :icon="icons.database"
        />

        <p v-if="error" class="alert-error mt-4">{{ error }}</p>
        <p v-if="success" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
            {{ success }}
        </p>

        <div v-if="loading" class="mt-6 space-y-3">
            <div v-for="n in 2" :key="n" class="h-32 animate-pulse rounded-2xl bg-ink-100"></div>
        </div>

        <template v-else>
            <!-- current footprint -->
            <div class="mt-6 grid grid-cols-3 gap-3">
                <div class="card rounded-2xl p-4 text-center">
                    <p class="text-2xl font-bold text-ink-900">{{ status?.users ?? 0 }}</p>
                    <p class="text-xs font-medium text-ink-500">Dummy users</p>
                </div>
                <div class="card rounded-2xl p-4 text-center">
                    <p class="text-2xl font-bold text-ink-900">{{ status?.threads ?? 0 }}</p>
                    <p class="text-xs font-medium text-ink-500">Dummy threads</p>
                </div>
                <div class="card rounded-2xl p-4 text-center">
                    <p class="text-2xl font-bold text-ink-900">{{ status?.posts ?? 0 }}</p>
                    <p class="text-xs font-medium text-ink-500">Dummy posts</p>
                </div>
            </div>

            <!-- generate -->
            <div class="card mt-4 rounded-2xl p-5">
                <h2 class="text-sm font-semibold text-ink-900">Generate demo content</h2>
                <p class="mt-1 text-sm text-ink-500">
                    Creates fake members (with a <span class="font-mono text-xs">@dummy.forum</span> email) who write threads and replies
                    across your existing categories and tags, with votes and randomized timestamps so lists look lived-in.
                </p>

                <p v-if="status && !status.has_categories" class="alert-error mt-3">
                    You need at least one active category first — create one on the Categories page.
                </p>

                <form class="mt-4 flex flex-wrap items-end gap-3" @submit.prevent="importData">
                    <div class="w-28">
                        <label class="field-label" for="dd-users">Users</label>
                        <input id="dd-users" v-model="form.users" type="number" min="1" max="50" required class="input" />
                    </div>
                    <div class="w-28">
                        <label class="field-label" for="dd-threads">Threads</label>
                        <input id="dd-threads" v-model="form.threads" type="number" min="1" max="100" required class="input" />
                    </div>
                    <div class="w-28">
                        <label class="field-label" for="dd-posts">Posts</label>
                        <input id="dd-posts" v-model="form.posts" type="number" min="0" max="500" required class="input" />
                    </div>
                    <button type="submit" :disabled="importing || (status && !status.has_categories)" class="btn-primary">
                        <svg v-if="!importing" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="icons.database" />
                        </svg>
                        {{ importing ? 'Generating...' : 'Generate' }}
                    </button>
                </form>
            </div>

            <!-- danger zone -->
            <div class="card mt-4 rounded-2xl border-rose-200 p-5">
                <h2 class="text-sm font-semibold text-rose-700">Delete dummy data</h2>
                <p class="mt-1 text-sm text-ink-500">
                    Removes every dummy user along with all their threads, posts and votes.
                    Content created by real members is never touched.
                </p>
                <button
                    type="button"
                    class="mt-4 inline-flex cursor-pointer items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="deleting || !status?.users"
                    @click="deleteData"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="icons.trash" />
                    </svg>
                    {{ deleting ? 'Deleting...' : 'Delete all dummy data' }}
                </button>
                <p v-if="!status?.users" class="mt-2 text-xs text-ink-400">Nothing to delete right now.</p>
            </div>
        </template>
    </div>
</template>
