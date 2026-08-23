<script setup>
import { onMounted, ref } from 'vue';
import adminApi from '../services/adminApi';
import icons from '../icons';
import AdminPageHeader from '../components/AdminPageHeader.vue';

const loading = ref(true);
const error = ref(null);
const success = ref(null);
const roles = ref([]);
const allPermissions = ref([]);

const newRole = ref('');
const creating = ref(false);

// permission name -> checked, keyed per role id
const drafts = ref({});
const savingId = ref(null);

async function load() {
    try {
        const [rolesRes, permsRes] = await Promise.all([adminApi.getRoles(), adminApi.getPermissions()]);
        roles.value = rolesRes.data.data ?? [];
        allPermissions.value = (permsRes.data.data ?? []).map((p) => p.name);
        drafts.value = Object.fromEntries(
            roles.value.map((role) => [role.id, new Set(role.permissions)]),
        );
    } catch {
        error.value = 'Could not load roles.';
    } finally {
        loading.value = false;
    }
}

function toggle(role, permission) {
    const set = drafts.value[role.id];
    set.has(permission) ? set.delete(permission) : set.add(permission);
}

function isDirty(role) {
    const set = drafts.value[role.id];
    if (!set) return false;
    return set.size !== role.permissions.length || !role.permissions.every((p) => set.has(p));
}

async function saveRole(role) {
    savingId.value = role.id;
    error.value = null;
    success.value = null;
    try {
        await adminApi.updateRole(role.id, { permissions: [...drafts.value[role.id]] });
        success.value = `Saved permissions for "${role.name}".`;
        await load();
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not save the role.';
    } finally {
        savingId.value = null;
    }
}

async function createRole() {
    creating.value = true;
    error.value = null;
    success.value = null;
    try {
        await adminApi.createRole({ name: newRole.value });
        newRole.value = '';
        await load();
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not create the role.';
    } finally {
        creating.value = false;
    }
}

async function deleteRole(role) {
    if (!confirm(`Delete the "${role.name}" role? Its ${role.users_count} member(s) become plain users.`)) return;
    error.value = null;
    try {
        await adminApi.deleteRole(role.id);
        await load();
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not delete the role.';
    }
}

onMounted(load);
</script>

<template>
    <div>
        <AdminPageHeader
            title="Roles"
            subtitle="Which role can do what. Tick permissions and save, or add custom roles."
            :icon="icons.key"
            :count="loading ? null : roles.length"
            count-label="roles"
        />

        <p v-if="error" class="alert-error mt-4">{{ error }}</p>
        <p v-if="success" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
            {{ success }}
        </p>

        <form class="card mt-4 flex flex-wrap items-end gap-3 rounded-2xl p-4" @submit.prevent="createRole">
            <div class="min-w-48 flex-1">
                <label class="field-label" for="role-name">New role</label>
                <input id="role-name" v-model="newRole" required maxlength="50" class="input" placeholder="e.g. editor" />
            </div>
            <button type="submit" :disabled="creating" class="btn-primary">
                {{ creating ? 'Creating...' : 'Create role' }}
            </button>
        </form>

        <div v-if="loading" class="mt-4 space-y-3">
            <div v-for="n in 3" :key="n" class="h-40 animate-pulse rounded-2xl bg-ink-100"></div>
        </div>

        <div v-else class="mt-4 space-y-4">
            <div v-for="role in roles" :key="role.id" class="card overflow-hidden rounded-2xl">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-100 bg-ink-50/60 px-4 py-3">
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-semibold text-ink-900 capitalize">{{ role.name }}</h2>
                        <span v-if="role.built_in" class="rounded-full bg-ink-100 px-2 py-0.5 text-[10px] font-semibold text-ink-500 uppercase">Built-in</span>
                        <span v-if="role.locked" class="rounded-full bg-brand-100 px-2 py-0.5 text-[10px] font-semibold text-brand-700 uppercase">All permissions</span>
                        <span class="text-xs text-ink-400">{{ role.users_count }} member(s)</span>
                    </div>
                    <div class="flex gap-1.5">
                        <button
                            v-if="!role.locked"
                            type="button"
                            class="btn-primary !px-3 !py-1.5 text-sm"
                            :disabled="savingId === role.id || !isDirty(role)"
                            @click="saveRole(role)"
                        >
                            {{ savingId === role.id ? 'Saving...' : 'Save' }}
                        </button>
                        <button
                            v-if="!role.built_in"
                            type="button"
                            class="btn-ghost !px-2.5 !py-1.5 text-sm text-rose-600"
                            @click="deleteRole(role)"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <p v-if="role.locked" class="px-4 py-3 text-sm text-ink-500">
                    The admin role always has every permission, including new custom ones — it can't be edited or locked out.
                </p>
                <div v-else class="grid grid-cols-1 gap-1 p-4 sm:grid-cols-2 lg:grid-cols-3">
                    <label
                        v-for="permission in allPermissions"
                        :key="permission"
                        class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-ink-700 transition hover:bg-ink-50"
                    >
                        <input
                            type="checkbox"
                            class="h-4 w-4 rounded border-ink-300 text-brand-600 accent-brand-600"
                            :checked="drafts[role.id]?.has(permission)"
                            @change="toggle(role, permission)"
                        />
                        <span class="font-mono text-xs">{{ permission }}</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</template>
