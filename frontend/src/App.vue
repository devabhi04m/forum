<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AppHeader from './components/AppHeader.vue';

const route = useRoute();

// the admin panel brings its own full-screen shell, so skip the forum chrome there
const fullscreen = computed(() => route.matched.some((record) => record.meta.requiresAdmin));
</script>

<template>
    <div class="min-h-screen bg-ink-50">
        <AppHeader v-if="!fullscreen" />
        <main>
            <router-view />
        </main>
    </div>
</template>
