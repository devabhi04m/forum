<script setup>
import UserAvatar from '../../../components/UserAvatar.vue';
import { timeAgo } from '../../../utils/date';

defineProps({
    thread: { type: Object, required: true },
});
</script>

<template>
    <router-link
        :to="{ name: 'threads.show', params: { slug: thread.slug } }"
        class="card group flex items-start gap-3 p-4 transition hover:border-brand-300 hover:shadow-md hover:shadow-brand-100/50"
    >
        <UserAvatar :name="thread.user?.name" size="lg" />

        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <span v-if="thread.is_pinned" class="rounded bg-amber-100 px-1.5 py-0.5 text-[11px] font-semibold text-amber-700">Pinned</span>
                <span v-if="thread.is_locked" class="rounded bg-ink-100 px-1.5 py-0.5 text-[11px] font-semibold text-ink-500">Locked</span>
                <h3 class="truncate font-medium text-ink-900 group-hover:text-brand-700">{{ thread.title }}</h3>
            </div>
            <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-ink-500">{{ thread.content }}</p>

            <div class="mt-2.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-ink-400">
                <span class="font-medium text-ink-500">{{ thread.user?.name }}</span>
                <span aria-hidden="true">·</span>
                <span>{{ timeAgo(thread.last_post_at || thread.created_at) }}</span>
                <span
                    v-for="tag in thread.tags ?? []"
                    :key="tag.id"
                    class="rounded-full bg-brand-50 px-2 py-0.5 font-medium text-brand-600"
                >
                    {{ tag.name }}
                </span>
            </div>
        </div>

        <div class="flex shrink-0 flex-col items-center gap-0.5 rounded-lg bg-ink-50 px-3 py-2 text-center">
            <span class="text-sm font-semibold text-ink-700">{{ thread.replies_count ?? 0 }}</span>
            <span class="text-[10px] tracking-wide text-ink-400 uppercase">{{ (thread.replies_count ?? 0) === 1 ? 'reply' : 'replies' }}</span>
        </div>
    </router-link>
</template>
