<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../auth/stores/auth';
import UserAvatar from '../../../components/UserAvatar.vue';

const props = defineProps({
    // async fn that does the actual API call
    send: { type: Function, required: true },
    disabled: { type: Boolean, default: false },
});

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const content = ref('');
const submitting = ref(false);
const error = ref(null);

async function onSubmit() {
    if (!content.value.trim()) return;

    error.value = null;
    submitting.value = true;
    try {
        await props.send(content.value);
        content.value = '';
    } catch {
        error.value = 'Could not post your reply. Please try again.';
    } finally {
        submitting.value = false;
    }
}

function goToLogin() {
    router.push({ name: 'auth.login', query: { redirect: route.fullPath } });
}
</script>

<template>
    <div v-if="disabled" class="rounded-xl border border-dashed border-ink-300 bg-ink-50 p-5 text-center text-sm text-ink-500">
        This thread is locked, so new replies are off.
    </div>

    <div v-else-if="!auth.isAuthenticated" class="rounded-xl border border-dashed border-ink-300 p-5 text-center text-sm text-ink-500">
        <button type="button" class="font-medium text-brand-600 hover:underline" @click="goToLogin">Log in</button>
        to join the conversation.
    </div>

    <form v-else class="card p-4" @submit.prevent="onSubmit">
        <div class="flex items-start gap-3">
            <UserAvatar :name="auth.user?.name" size="md" />
            <textarea
                v-model="content"
                :disabled="submitting"
                rows="3"
                required
                placeholder="Write a reply..."
                class="input resize-none disabled:bg-ink-50"
            ></textarea>
        </div>
        <p v-if="error" class="alert-error mt-2">{{ error }}</p>
        <div class="mt-3 flex justify-end">
            <button type="submit" :disabled="submitting || !content.trim()" class="btn-primary">
                {{ submitting ? 'Posting...' : 'Post reply' }}
            </button>
        </div>
    </form>
</template>
