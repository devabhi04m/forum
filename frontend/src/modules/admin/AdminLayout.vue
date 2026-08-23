<script setup>
import { useRoute } from 'vue-router';
import { useAuthStore } from '../auth/stores/auth';
import UserAvatar from '../../components/UserAvatar.vue';
import icons from './icons';

const route = useRoute();
const auth = useAuthStore();

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

function isActive(name) {
    return route.name === name;
}
</script>

<template>
    <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-6 md:flex-row md:py-8">
        <!-- left sidebar -->
        <aside class="w-full shrink-0 md:w-60">
            <div class="overflow-hidden rounded-2xl bg-ink-900 shadow-xl shadow-ink-900/10 md:sticky md:top-20">
                <!-- panel header -->
                <div class="flex items-center gap-3 border-b border-white/10 px-4 py-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 text-white shadow-inner">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="icons.shield" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-white">Super Admin</p>
                        <p class="text-[11px] text-ink-400">Forum control room</p>
                    </div>
                </div>

                <!-- horizontal scroll on mobile, vertical stack on desktop -->
                <nav class="flex gap-1 overflow-x-auto p-3 md:flex-col md:overflow-visible">
                    <template v-for="section in sections" :key="section.label">
                        <p class="mt-3 mb-1 hidden px-3 text-[10px] font-semibold tracking-widest text-ink-500 uppercase first:mt-0 md:block">
                            {{ section.label }}
                        </p>
                        <router-link
                            v-for="item in section.items"
                            :key="item.name"
                            :to="{ name: item.name }"
                            class="group relative flex shrink-0 items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition"
                            :class="isActive(item.name)
                                ? 'bg-gradient-to-r from-brand-500 to-brand-600 text-white shadow-md shadow-brand-900/40'
                                : 'text-ink-300 hover:bg-white/5 hover:text-white'"
                        >
                            <svg
                                class="h-4 w-4 shrink-0 transition"
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
                <div class="border-t border-white/10 p-3">
                    <div class="flex items-center gap-2.5 px-2 py-1.5">
                        <UserAvatar :name="auth.user?.name" size="sm" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-medium text-white">{{ auth.user?.name }}</p>
                            <p class="text-[10px] tracking-wide text-brand-300 uppercase">{{ auth.user?.role }}</p>
                        </div>
                    </div>
                    <router-link
                        :to="{ name: 'forum.home' }"
                        class="mt-1 flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-ink-400 transition hover:bg-white/5 hover:text-white"
                    >
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="icons.back" />
                        </svg>
                        Back to forum
                    </router-link>
                </div>
            </div>
        </aside>

        <!-- page content -->
        <div class="min-w-0 flex-1">
            <router-view />
        </div>
    </div>
</template>
