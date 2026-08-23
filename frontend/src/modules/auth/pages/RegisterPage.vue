<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();

const name = ref('');
const email = ref('');
const password = ref('');
const submitting = ref(false);

onMounted(() => {
    auth.error = null;
});

async function onSubmit() {
    submitting.value = true;
    try {
        await auth.register({ name: name.value, email: email.value, password: password.value });
        router.push({ name: 'forum.home' });
    } catch {
        // error surfaced via auth.error
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="mx-auto flex min-h-[70vh] max-w-sm flex-col justify-center px-4">
        <div class="card p-6 shadow-sm">
            <h1 class="text-xl font-semibold tracking-tight text-ink-900">Create an account</h1>
            <p class="mt-1 text-sm text-ink-500">Join the discussion.</p>

            <form class="mt-6 space-y-4" @submit.prevent="onSubmit">
                <div>
                    <label class="field-label" for="register-name">Name</label>
                    <input id="register-name" v-model="name" type="text" required class="input" placeholder="Jane Doe" />
                </div>
                <div>
                    <label class="field-label" for="register-email">Email</label>
                    <input id="register-email" v-model="email" type="email" required class="input" placeholder="you@example.com" />
                </div>
                <div>
                    <label class="field-label" for="register-password">Password</label>
                    <input id="register-password" v-model="password" type="password" required minlength="8" class="input" placeholder="At least 8 characters" />
                </div>

                <p v-if="auth.error" class="alert-error">{{ auth.error }}</p>

                <button type="submit" :disabled="submitting" class="btn-primary w-full">
                    {{ submitting ? 'Creating account...' : 'Sign up' }}
                </button>
            </form>
        </div>

        <p class="mt-4 text-center text-sm text-ink-500">
            Already have an account?
            <router-link :to="{ name: 'auth.login' }" class="font-medium text-brand-600 hover:underline">Log in</router-link>
        </p>
    </div>
</template>
