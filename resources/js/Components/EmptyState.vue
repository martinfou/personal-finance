<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="py-14 text-center">
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-surface-100 text-ink-400">
            <slot name="icon" />
        </div>
        <p class="font-display text-lg font-semibold text-ink-900">{{ title }}</p>
        <p v-if="description" class="mx-auto mt-2 max-w-sm text-sm text-ink-500">{{ description }}</p>
        <div v-if="$slots.action" class="mt-6">
            <slot name="action" />
        </div>
    </div>
</template>
