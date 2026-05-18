<script setup>
defineProps({
    name: { type: String, required: true },
    color: { type: String, default: '#6b6358' },
    showName: { type: Boolean, default: true },
});
</script>

<template>
    <span class="inline-flex items-center gap-2">
        <span
            class="h-2.5 w-2.5 shrink-0 rounded-full ring-2 ring-white"
            :style="{ backgroundColor: color }"
            aria-hidden="true"
        />
        <span v-if="showName" class="text-sm text-ink-800">{{ name }}</span>
    </span>
</template>
