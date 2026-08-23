<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../auth/stores/auth';
import UserAvatar from '../../components/UserAvatar.vue';
import NotificationBell from '../notifications/components/NotificationBell.vue';
import icons from './icons';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const sidebarOpen = ref(false);

const sections = [
    {
        label: 'Overview',
        items: [{ name: 'admin.dashboard', label: 'Dashboard', icon: icons.dashboard }],
    },
    {
        label: 'Content',
        items: [
            { name: 'admin.threads', label: 'Threads', icon: icons.threads },
            { name: 'admin.posts', label: 'Posts', icon: icons.posts },
            { name: 'admin.categories', label: 'Categories', icon: icons.categories },
            { name: 'admin.tags', label: 'Tags', icon: icons.tags },
        ],
    },
    {
        label: 'Community',
        items: [
            { name: 'admin.users', label: 'Users', icon: icons.users },
            { name: 'admin.reports', label: 'Reports', icon: icons.reports },
        ],
    },
];

const pageTitle = computed(() => {
    for (const section of sections) {
        const hit = section.items.find((item) => item.name === route.name);
        if (hit) return hit.label;
    }
    return 'Admin';
});

// close the mobile drawer after navigating
watch(() => route.name, () => {
    sidebarOpen.value = false;
});

function isActive(name) {
    return route.name === name;
}

function onLogout() {
    auth.logout();
    router.push({ name: 'forum.home' });
}
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-ink-100/70">
        <!-- mobile backdrop -->
        <transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-30 bg-ink-900/60 backdrop-blur-sm md:hidden"
                @click="sidebarOpen = false"
            ></div>
        </transition>

        <!-- sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col bg-ink-900 shadow-2xl shadow-ink-900/30 transition-transform duration-200 md:static md:z-auto md:translate-x-0 md:shadow-none"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- brand -->
            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 text-white shadow-inner">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="icons.shield" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-white">Super Admin</p>
                    <p class="text-[11px] text-ink-400">Forum control room</p>
                </div>
                <button
                    type="button"
                    class="ml-auto rounded-lg p-1.5 text-ink-400 transition hover:bg-white/10 hover:text-white md:hidden"
                    aria-label="Close menu"
                    @click="sidebarOpen = false"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- nav -->
            <nav class="flex-1 space-y-1 overflow-y-auto p-3">
                <template v-for="section in sections" :key="section.label">
                    <p class="mt-4 mb-1.5 px-3 text-[10px] font-semibold tracking-widest text-ink-500 uppercase first:mt-1">
                        {{ section.label }}
                    </p>
                    <router-link
                        v-for="item in section.items"
                        :key="item.name"
                        :to="{ name: item.name }"
                        class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                        :class="isActive(item.name)
                            ? 'bg-gradient-to-r from-brand-500 to-brand-600 text-white shadow-lg shadow-brand-900/40'
                            : 'text-ink-300 hover:bg-white/5 hover:text-white'"
                    >
                        <svg
                            class="h-[18px] w-[18px] shrink-0 transition"
                            :class="isActive(item.name) ? 'text-white' : 'text-ink-500 group-hover:text-brand-300'"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                        </svg>
                        {{ item.label }}
                    </router-link>
                </template>
            </nav>

            <!-- signed-in user + exit -->
            <div class="shrink-0 border-t border-white/10 p-3">
                <div class="flex items-center gap-2.5 rounded-xl bg-white/5 px-3 py-2.5">
                    <UserAvatar :name="auth.user?.name" size="md" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-medium text-white">{{ auth.user?.name }}</p>
                        <p class="text-[10px] tracking-wide text-brand-300 uppercase">{{ auth.user?.role }}</p>
                    </div>
                </div>
                <router-link
                    :to="{ name: 'forum.home' }"
                    class="mt-2 flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-ink-400 transition hover:bg-white/5 hover:text-white"
                >
                    <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="icons.back" />
                    </svg>
                    Back to forum
                </router-link>
            </div>
        </aside>

        <!-- main column -->
        <div class="flex min-w-0 flex-1 flex-col">
            <!-- topbar -->
            <header class="flex h-16 shrink-0 items-center justify-between gap-3 border-b border-ink-200 bg-white/85 px-4 backdrop-blur md:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        class="rounded-lg p-2 text-ink-500 transition hover:bg-ink-100 hover:text-ink-800 md:hidden"
                        aria-label="Open menu"
                        @click="sidebarOpen = true"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <p class="truncate text-sm text-ink-400">
                        Admin <span class="mx-1.5 text-ink-300">/</span>
                        <span class="font-semibold text-ink-900">{{ pageTitle }}</span>
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-1.5">
                    <NotificationBell />
                    <router-link :to="{ name: 'forum.home' }" class="btn-ghost hidden !px-3 !py-1.5 text-sm sm:inline-flex">
                        View forum
                    </router-link>
                    <button
                        type="button"
                        class="btn-ghost !px-3 !py-1.5 text-sm text-ink-500"
                        @click="onLogout"
                    >
                        Log out
                    </button>
                </div>
            </header>

            <!-- page content, scrolls independently -->
            <main class="flex-1 overflow-y-auto">
                <div class="mx-auto w-full max-w-6xl px-4 py-6 md:px-8 md:py-8">
                    <router-view v-slot="{ Component }">
                        <transition name="admin-page" mode="out-in">
                            <component :is="Component" />
                        </transition>
                    </router-view>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.admin-page-enter-active,
.admin-page-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.admin-page-enter-from {
    opacity: 0;
    transform: translateY(6px);
}
.admin-page-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>
