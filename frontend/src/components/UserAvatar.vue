<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, default: '?' },
    size: { type: String, default: 'md' }, // sm | md | lg
});

const PALETTE = [
    'bg-brand-100 text-brand-700',
    'bg-emerald-100 text-emerald-700',
    'bg-amber-100 text-amber-700',
    'bg-rose-100 text-rose-700',
    'bg-sky-100 text-sky-700',
    'bg-violet-100 text-violet-700',
];

const initials = computed(() => {
    const parts = (props.name || '?').trim().split(/\s+/);
    return parts.slice(0, 2).map((p) => p[0]).join('').toUpperCase();
});

const color = computed(() => {
    let hash = 0;
    for (const ch of props.name || '') hash = (hash * 31 + ch.charCodeAt(0)) % 997;
    return PALETTE[hash % PALETTE.length];
});

const sizeClass = computed(() => ({
    sm: 'h-6 w-6 text-[10px]',
    md: 'h-8 w-8 text-xs',
    lg: 'h-10 w-10 text-sm',
})[props.size]);
</script>

<template>
    <span
        class="inline-flex shrink-0 items-center justify-center rounded-full font-semibold select-none"
        :class="[color, sizeClass]"
        :title="name"
    >
        {{ initials }}
    </span>
</template>
