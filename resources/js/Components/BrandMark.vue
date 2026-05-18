<script setup>
defineProps({
    wrapperClass: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <span :class="['inline-flex items-center gap-2.5', wrapperClass]">
        <span
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-ink-900 text-white shadow-sm"
            aria-hidden="true"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 6v12m-4-3.5c0-2.2 1.8-4 4-4s4 1.8 4 4M7 9.5h10"
                />
            </svg>
        </span>
        <span class="flex flex-col leading-none">
            <span class="font-display text-lg font-semibold tracking-tight text-ink-900">Mon Budget</span>
            <span class="mt-0.5 text-2xs font-medium uppercase tracking-widest text-ink-400">Finances personnelles</span>
        </span>
    </span>
</template>
