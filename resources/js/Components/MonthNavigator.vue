<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    year: { type: Number, required: true },
    month: { type: Number, required: true },
    monthLabel: { type: String, default: '' },
    basePath: { type: String, required: true },
});

const monthNames = [
    'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre',
];

const label = computed(() => props.monthLabel || `${monthNames[props.month - 1]} ${props.year}`);

const isCurrentMonth = computed(() => {
    const now = new Date();
    return props.year === now.getFullYear() && props.month === now.getMonth() + 1;
});

function navigate(delta) {
    let m = props.month + delta;
    let y = props.year;
    if (m > 12) { m = 1; y++; }
    if (m < 1) { m = 12; y--; }
    router.get(props.basePath, { year: y, month: m }, { preserveState: true, replace: true });
}

function goToToday() {
    const now = new Date();
    router.get(props.basePath, { year: now.getFullYear(), month: now.getMonth() + 1 }, { preserveState: true, replace: true });
}
</script>

<template>
    <div class="flex flex-wrap items-center justify-center gap-3">
        <button
            type="button"
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-surface-150 bg-white text-ink-500 shadow-sm hover:border-ink-200 hover:text-ink-800"
            @click="navigate(-1)"
        >
            <AppIcon name="chevron-left" icon-class="h-5 w-5" />
        </button>
        <span class="min-w-[10rem] text-center font-display text-lg font-semibold text-ink-800">
            {{ label }}
        </span>
        <button
            type="button"
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-surface-150 bg-white text-ink-500 shadow-sm hover:border-ink-200 hover:text-ink-800"
            @click="navigate(1)"
        >
            <AppIcon name="chevron-right" icon-class="h-5 w-5" />
        </button>
        <button
            v-if="!isCurrentMonth"
            type="button"
            class="btn-secondary text-sm"
            @click="goToToday"
        >
            Aujourd'hui
        </button>
    </div>
</template>
