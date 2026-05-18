<script setup>
import { computed } from 'vue';
import { formatDelta, formatMoney } from '@/utils/money';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: Number, required: true },
    previous: { type: Number, default: 0 },
    valueClass: { type: String, default: 'text-ink-900' },
    invertDelta: { type: Boolean, default: false },
});

const comparison = computed(() => formatDelta(props.value, props.previous));

const deltaClass = computed(() => {
    if (comparison.value.tone === 'neutral') return 'text-ink-400';
    const positive = comparison.value.delta > 0;
    const good = props.invertDelta ? !positive : positive;
    return good ? 'text-emerald-600' : 'text-red-500';
});
</script>

<template>
    <div class="stat">
        <p class="stat-label">{{ label }}</p>
        <p :class="['stat-value mt-2', valueClass]">{{ formatMoney(value) }}</p>
        <p :class="['mt-2 text-xs font-medium', deltaClass]">{{ comparison.label }}</p>
    </div>
</template>
