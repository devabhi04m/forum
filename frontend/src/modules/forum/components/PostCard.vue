<script setup>
import { computed } from 'vue';
import UserAvatar from '../../../components/UserAvatar.vue';
import VoteButtons from './VoteButtons.vue';
import { useForumStore } from '../stores/forum';
import { useAuthStore } from '../../auth/stores/auth';
import { timeAgo } from '../../../utils/date';

const props = defineProps({
    post: { type: Object, required: true },
    isAuthor: { type: Boolean, default: false },
});

const emit = defineEmits(['report']);

const forum = useForumStore();
const auth = useAuthStore();

const canDelete = computed(
    () => auth.user && (auth.user.id === props.post.user?.id || auth.isModerator),
);

function onVote(vote) {
    forum.votePost(props.post.id, vote);
}

async function onDelete() {
    if (!confirm('Delete this reply?')) return;
    await forum.deletePost(props.post.id);
}
</script>

<template>
    <div class="card p-4">
        <div class="flex items-center gap-2.5">
            <UserAvatar :name="post.user?.name" size="md" />
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-ink-800">{{ post.user?.name }}</span>
                    <span v-if="isAuthor" class="rounded bg-brand-100 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-brand-700 uppercase">Author</span>
                </div>
                <span class="text-xs text-ink-400">{{ timeAgo(post.created_at) }}</span>
            </div>
        </div>
        <p class="mt-3 text-sm leading-relaxed whitespace-pre-line text-ink-700">{{ post.content }}</p>
        <div class="mt-3 flex items-center justify-between gap-2">
            <VoteButtons :score="post.likes_count ?? 0" :my-vote="post.my_vote ?? 0" @vote="onVote" />
            <div class="flex gap-1 text-xs">
                <button
                    v-if="auth.isAuthenticated"
                    type="button"
                    class="cursor-pointer rounded px-2 py-1 text-ink-400 transition hover:bg-ink-100 hover:text-ink-600"
                    @click="emit('report')"
                >
                    Report
                </button>
                <button
                    v-if="canDelete"
                    type="button"
                    class="cursor-pointer rounded px-2 py-1 text-ink-400 transition hover:bg-red-50 hover:text-red-600"
                    @click="onDelete"
                >
                    Delete
                </button>
            </div>
        </div>
    </div>
</template>
