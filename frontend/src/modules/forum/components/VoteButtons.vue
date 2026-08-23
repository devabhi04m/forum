<script setup>
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../auth/stores/auth';

const props = defineProps({
    score: { type: Number, default: 0 },
    myVote: { type: Number, default: 0 },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['vote']);

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

function cast(direction) {
    if (props.disabled) return;
    if (!auth.isAuthenticated) {
        router.push({ name: 'auth.login', query: { redirect: route.fullPath } });
        return;
    }
    // clicking the same arrow again removes the vote
    emit('vote', props.myVote === direction ? 0 : direction);
}
</script>

<template>
    <div class="inline-flex items-center gap-0.5 rounded-lg border border-ink-200 bg-white p-0.5">
        <button
            type="button"
            class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-md transition"
            :class="myVote === 1 ? 'bg-brand-50 text-brand-600' : 'text-ink-400 hover:bg-ink-100 hover:text-ink-600'"
            :disabled="disabled"
            aria-label="Upvote"
            @click="cast(1)"
        >
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 5l6 7H4l6-7z" clip-rule="evenodd" />
            </svg>
        </button>
        <span class="min-w-6 px-0.5 text-center text-sm font-semibold tabular-nums" :class="score < 0 ? 'text-red-600' : 'text-ink-700'">
            {{ score }}
        </span>
        <button
            type="button"
            class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-md transition"
            :class="myVote === -1 ? 'bg-red-50 text-red-600' : 'text-ink-400 hover:bg-ink-100 hover:text-ink-600'"
            :disabled="disabled"
            aria-label="Downvote"
            @click="cast(-1)"
        >
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 15l-6-7h12l-6 7z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</template>
