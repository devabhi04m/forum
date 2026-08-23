<script setup>
import { ref, watch } from 'vue';
import forumApi from '../services/forumApi';

const props = defineProps({
    open: { type: Boolean, default: false },
    // { type: 'thread', slug } or { type: 'post', id }
    target: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const reason = ref('');
const submitting = ref(false);
const error = ref(null);
const sent = ref(false);

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        reason.value = '';
        error.value = null;
        sent.value = false;
    }
});

async function onSubmit() {
    error.value = null;
    submitting.value = true;
    try {
        if (props.target?.type === 'post') {
            await forumApi.reportPost(props.target.id, reason.value);
        } else {
            await forumApi.reportThread(props.target.slug, reason.value);
        }
        sent.value = true;
    } catch (err) {
        error.value = err.response?.data?.message || 'Could not send the report.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-ink-900/40" @click="emit('close')"></div>

            <div class="card relative w-full max-w-md p-5 shadow-xl">
                <template v-if="sent">
                    <h2 class="text-lg font-semibold text-ink-900">Report sent</h2>
                    <p class="mt-2 text-sm text-ink-500">Thanks. A moderator will take a look.</p>
                    <div class="mt-4 flex justify-end">
                        <button type="button" class="btn-primary" @click="emit('close')">Close</button>
                    </div>
                </template>

                <form v-else @submit.prevent="onSubmit">
                    <h2 class="text-lg font-semibold text-ink-900">
                        Report this {{ target?.type === 'post' ? 'reply' : 'thread' }}
                    </h2>
                    <p class="mt-1 text-sm text-ink-500">Tell us what's wrong and a moderator will review it.</p>

                    <textarea
                        v-model="reason"
                        rows="4"
                        required
                        minlength="5"
                        maxlength="500"
                        class="input mt-4 resize-none"
                        placeholder="What's the problem?"
                    ></textarea>

                    <p v-if="error" class="alert-error mt-3">{{ error }}</p>

                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="btn-ghost" @click="emit('close')">Cancel</button>
                        <button type="submit" :disabled="submitting" class="btn-primary">
                            {{ submitting ? 'Sending...' : 'Send report' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
