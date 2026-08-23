<script setup>
import { onMounted, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const email = ref('');
const password = ref('');
const submitting = ref(false);

onMounted(() => {
    auth.error = null;
});

async function onSubmit() {
    submitting.value = true;
    try {
        await auth.login({ email: email.value, password: password.value });
        router.push(route.query.redirect || { name: 'forum.home' });
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
            <h1 class="text-xl font-semibold tracking-tight text-ink-900">Log in</h1>
            <p class="mt-1 text-sm text-ink-500">Welcome back to the forum.</p>

            <form class="mt-6 space-y-4" @submit.prevent="onSubmit">
                <div>
                    <label class="field-label" for="login-email">Email</label>
                    <input id="login-email" v-model="email" type="email" required class="input" placeholder="you@example.com" />
                </div>
                <div>
                    <label class="field-label" for="login-password">Password</label>
                    <input id="login-password" v-model="password" type="password" required class="input" placeholder="••••••••" />
                </div>

                <p v-if="auth.error" class="alert-error">{{ auth.error }}</p>

                <button type="submit" :disabled="submitting" class="btn-primary w-full">
                    {{ submitting ? 'Logging in...' : 'Log in' }}
                </button>
            </form>
        </div>

        <p class="mt-4 text-center text-sm text-ink-500">
            Don't have an account?
            <router-link :to="{ name: 'auth.register' }" class="font-medium text-brand-600 hover:underline">Sign up</router-link>
        </p>
    </div>
</template>
