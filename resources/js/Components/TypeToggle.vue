<script setup>
const model = defineModel({ type: String, required: true });

defineProps({
    expenseLabel: { type: String, default: 'Dépense' },
    incomeLabel: { type: String, default: 'Revenu' },
});
</script>

<template>
    <div class="inline-flex rounded-xl border border-surface-150 bg-surface-75 p-1" role="group">
        <button
            type="button"
            :class="[
                'rounded-lg px-3 py-1.5 text-sm font-semibold transition-colors',
                model === 'expense'
                    ? 'bg-white text-red-600 shadow-sm'
                    : 'text-ink-500 hover:text-ink-800',
            ]"
            @click="model = 'expense'"
        >
            {{ expenseLabel }}
        </button>
        <button
            type="button"
            :class="[
                'rounded-lg px-3 py-1.5 text-sm font-semibold transition-colors',
                model === 'income'
                    ? 'bg-white text-emerald-600 shadow-sm'
                    : 'text-ink-500 hover:text-ink-800',
            ]"
            @click="model = 'income'"
        >
            {{ incomeLabel }}
        </button>
    </div>
</template>
