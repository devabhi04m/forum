<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();

const sections = [
    {
        label: 'Overview',
        items: [{ name: 'admin.dashboard', label: 'Dashboard', icon: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75' }],
    },
    {
        label: 'Content',
        items: [
            { name: 'admin.threads', label: 'Threads', icon: 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z' },
            { name: 'admin.posts', label: 'Posts', icon: 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 011.037-.443 48.282 48.282 0 005.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z' },
            { name: 'admin.categories', label: 'Categories', icon: 'M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z' },
            { name: 'admin.tags', label: 'Tags', icon: 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z' },
        ],
    },
    {
        label: 'Community',
        items: [
            { name: 'admin.users', label: 'Users', icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z' },
            { name: 'admin.reports', label: 'Reports', icon: 'M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5' },
        ],
    },
];

function isActive(name) {
    return route.name === name;
}
</script>

<template>
    <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-8 md:flex-row">
        <!-- left sidebar -->
        <aside class="w-full shrink-0 md:w-56">
            <div class="md:sticky md:top-20">
                <p class="hidden px-3 text-xs font-semibold tracking-wider text-ink-400 uppercase md:block">Super admin</p>

                <!-- horizontal scroll on mobile, vertical stack on desktop -->
                <nav class="mt-2 flex gap-1 overflow-x-auto pb-2 md:flex-col md:overflow-visible md:pb-0">
                    <template v-for="section in sections" :key="section.label">
                        <p class="mt-4 mb-1 hidden px-3 text-[11px] font-semibold tracking-wider text-ink-400 uppercase first:mt-0 md:block">
                            {{ section.label }}
                        </p>
                        <router-link
                            v-for="item in section.items"
                            :key="item.name"
                            :to="{ name: item.name }"
                            class="flex shrink-0 items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition"
                            :class="isActive(item.name)
                                ? 'bg-brand-50 text-brand-700'
                                : 'text-ink-600 hover:bg-ink-100 hover:text-ink-900'"
                        >
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                            </svg>
                            {{ item.label }}
                        </router-link>
                    </template>

                    <router-link
                        :to="{ name: 'forum.home' }"
                        class="mt-0 flex shrink-0 items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap text-ink-400 transition hover:bg-ink-100 hover:text-ink-700 md:mt-6"
                    >
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                        Back to forum
                    </router-link>
                </nav>
            </div>
        </aside>

        <!-- page content -->
        <div class="min-w-0 flex-1">
            <router-view />
        </div>
    </div>
</template>
