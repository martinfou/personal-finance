<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'flex w-full items-center gap-2 rounded-lg bg-brand-50 px-3 py-2.5 text-base font-medium text-brand-800'
        : 'flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-base font-medium text-ink-600 hover:bg-surface-100 hover:text-ink-900',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
