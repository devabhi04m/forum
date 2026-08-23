<script setup>
import AppHeader from './components/AppHeader.vue';
</script>

<template>
    <div class="min-h-screen bg-ink-50">
        <AppHeader />
        <main>
            <router-view />
        </main>
    </div>
</template>
