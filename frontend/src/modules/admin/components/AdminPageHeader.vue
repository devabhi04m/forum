<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    icon: { type: String, required: true },
    count: { type: [Number, String], default: null },
    countLabel: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3.5">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-md shadow-brand-200">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="icon" />
                </svg>
            </span>
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-ink-900">{{ title }}</h1>
                <p v-if="subtitle" class="mt-0.5 text-sm text-ink-500">{{ subtitle }}</p>
            </div>
        </div>
        <span
            v-if="count !== null"
            class="rounded-full border border-ink-200 bg-white px-3.5 py-1.5 text-sm font-medium text-ink-600 shadow-sm"
        >
            {{ count }} {{ countLabel }}
        </span>
    </div>
</template>
