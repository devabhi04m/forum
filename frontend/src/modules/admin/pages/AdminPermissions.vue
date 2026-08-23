<script setup>
import { onMounted, ref } from 'vue';
import adminApi from '../services/adminApi';
import icons from '../icons';
import AdminPageHeader from '../components/AdminPageHeader.vue';

const loading = ref(true);
const error = ref(null);
const permissions = ref([]);

const newPermission = ref('');
const creating = ref(false);

async function load() {
    try {
        const { data } = await adminApi.getPermissions();
        permissions.value = data.data ?? [];
    } catch {
        error.value = 'Could not load permissions.';
    } finally {
        loading.value = false;
    }
}

async function createPermission() {
    creating.value = true;
    error.value = null;
    try {
        await adminApi.createPermission({ name: newPermission.value });
        newPermission.value = '';
        await load();
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not create the permission.';
    } finally {
        creating.value = false;
    }
}

async function deletePermission(permission) {
    if (!confirm(`Delete the "${permission.name}" permission? It will be removed from every role that has it.`)) return;
    error.value = null;
    try {
        await adminApi.deletePermission(permission.id);
        await load();
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not delete the permission.';
    }
}

onMounted(load);
</script>

<template>
    <div>
        <AdminPageHeader
            title="Permissions"
            subtitle="The building blocks roles are made of. Core ones are wired into the code."
            :icon="icons.lock"
            :count="loading ? null : permissions.length"
            count-label="permissions"
        />

        <p v-if="error" class="alert-error mt-4">{{ error }}</p>

        <form class="card mt-4 flex flex-wrap items-end gap-3 rounded-2xl p-4" @submit.prevent="createPermission">
            <div class="min-w-48 flex-1">
                <label class="field-label" for="perm-name">New permission</label>
                <input id="perm-name" v-model="newPermission" required maxlength="50" class="input" placeholder="e.g. export-data" />
            </div>
            <button type="submit" :disabled="creating" class="btn-primary">
                {{ creating ? 'Creating...' : 'Create' }}
            </button>
            <p class="w-full text-xs text-ink-400">
                Custom permissions do nothing until code (or a future feature) checks for them, but they can be
                assigned to roles right away. The admin role picks them up automatically.
            </p>
        </form>

        <div v-if="loading" class="mt-4 space-y-3">
            <div v-for="n in 5" :key="n" class="h-14 animate-pulse rounded-xl bg-ink-100"></div>
        </div>

        <div v-else-if="permissions.length" class="card mt-4 divide-y divide-ink-100 overflow-hidden rounded-2xl">
            <div
                v-for="permission in permissions"
                :key="permission.id"
                class="flex flex-wrap items-center justify-between gap-3 p-4 transition hover:bg-ink-50/70"
            >
                <div class="min-w-0">
                    <p class="font-mono text-sm text-ink-900">
                        {{ permission.name }}
                        <span v-if="permission.core" class="ml-1 rounded-full bg-ink-100 px-2 py-0.5 text-[10px] font-semibold text-ink-500 uppercase">Core</span>
                    </p>
                    <div class="mt-1 flex flex-wrap gap-1">
                        <span
                            v-for="role in permission.roles"
                            :key="role"
                            class="rounded-full bg-brand-50 px-2 py-0.5 text-[11px] font-medium text-brand-700 capitalize"
                        >
                            {{ role }}
                        </span>
                        <span v-if="!permission.roles.length" class="text-xs text-ink-400">not assigned to any role</span>
                    </div>
                </div>
                <button
                    v-if="!permission.core"
                    type="button"
                    class="btn-ghost !px-2.5 !py-1 text-xs text-rose-600"
                    @click="deletePermission(permission)"
                >
                    Delete
                </button>
            </div>
        </div>
    </div>
</template>
