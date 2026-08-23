<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../modules/auth/stores/auth';
import UserAvatar from './UserAvatar.vue';
import NotificationBell from '../modules/notifications/components/NotificationBell.vue';

const auth = useAuthStore();
const router = useRouter();

const searchTerm = ref('');

function onSearch() {
    const q = searchTerm.value.trim();
    if (!q) return;
    router.push({ name: 'search', query: { q } });
    searchTerm.value = '';
}

function onLogout() {
    auth.logout();
    router.push({ name: 'forum.home' });
}
</script>

<template>
    <header class="sticky top-0 z-10 border-b border-ink-200 bg-white/85 shadow-[0_1px_2px_rgba(15,23,42,0.04)] backdrop-blur">
        <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-3">
            <router-link :to="{ name: 'forum.home' }" class="flex items-center gap-2.5 font-semibold text-ink-900">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-sm text-white shadow-sm">F</span>
                <span class="text-[15px] tracking-tight">Forum</span>
            </router-link>

            <form class="hidden max-w-xs flex-1 px-4 md:block" @submit.prevent="onSearch">
                <input
                    v-model="searchTerm"
                    type="search"
                    class="input !py-1.5"
                    placeholder="Search..."
                    aria-label="Search threads"
                />
            </form>

            <nav class="flex items-center gap-2 text-sm">
                <template v-if="auth.isAuthenticated">
                    <router-link :to="{ name: 'threads.create' }" class="btn-primary !px-3 !py-1.5">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                        </svg>
                        New thread
                    </router-link>
                    <router-link
                        v-if="auth.isAdmin"
                        :to="{ name: 'admin.dashboard' }"
                        class="btn-ghost !px-2.5 !py-1.5 text-ink-500"
                    >
                        Admin
                    </router-link>
                    <router-link
                        v-else-if="auth.isModerator"
                        :to="{ name: 'moderation' }"
                        class="btn-ghost !px-2.5 !py-1.5 text-ink-500"
                    >
                        Mod
                    </router-link>
                    <NotificationBell />
                    <router-link
                        :to="{ name: 'profile' }"
                        class="ml-1 flex items-center gap-2 rounded-full py-1 pr-2 pl-2 transition hover:bg-ink-100"
                        title="Your profile"
                    >
                        <UserAvatar :name="auth.user?.name" size="sm" />
                        <span class="hidden text-ink-700 sm:inline">{{ auth.user?.name }}</span>
                    </router-link>
                    <button type="button" class="btn-ghost !px-2.5 !py-1.5 text-ink-500" @click="onLogout">Log out</button>
                </template>
                <template v-else>
                    <router-link :to="{ name: 'auth.login' }" class="btn-ghost !px-3 !py-1.5">Log in</router-link>
                    <router-link :to="{ name: 'auth.register' }" class="btn-primary !px-3 !py-1.5">Sign up</router-link>
                </template>
            </nav>
        </div>
    </header>
</template>
