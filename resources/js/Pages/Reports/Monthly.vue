<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import CategoryBadge from '@/Components/CategoryBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import StatCard from '@/Components/StatCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatMoney } from '@/utils/money';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    year: Number,
    month: Number,
    monthLabel: String,
    income: Number,
    expense: Number,
    balance: Number,
    savingsRate: Number,
    categories: Array,
    alerts: Array,
    previousMonth: Object,
    months: Array,
});

const monthNames = ['Janvier','Février','Mars','Avril','Mai','Juin',
                    'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

function changeMonth(delta) {
    let m = props.month + delta;
    let y = props.year;
    if (m < 1) { m = 12; y--; }
    if (m > 12) { m = 1; y++; }
    router.get(route('reports.monthly', { year: y, month: m }), {}, { preserveState: true });
}

function exportCsv() {
    window.open(route('reports.export-csv', { year: props.year, month: props.month }));
}

const hasPrevious = computed(() => props.previousMonth !== null);
const expenseTrend = computed(() => {
    if (!hasPrevious.value || !props.previousMonth.expense) return 0;
    return Math.round(((props.expense - props.previousMonth.expense) / props.previousMonth.expense) * 100);
});
const savingsTrend = computed(() => {
    if (!hasPrevious.value) return 0;
    return Math.round(props.savingsRate - props.previousMonth.savingsRate);
});

function alertClass(type) {
    return type === 'danger'
        ? 'border-red-200 bg-red-50 text-red-800'
        : 'border-amber-200 bg-amber-50 text-amber-800';
}

function alertIcon(type) {
    return type === 'danger' ? 'exclamation-circle' : 'exclamation-triangle';
}

function statusColor(status) {
    if (status === 'overspent') return 'text-red-600';
    if (status === 'warning') return 'text-amber-600';
    return 'text-emerald-600';
}

function barColor(status) {
    if (status === 'overspent') return 'bg-red-500';
    if (status === 'warning') return 'bg-amber-500';
    return 'bg-emerald-500';
}
</script>

<template>
    <Head :title="'Rapport - ' + monthLabel" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
            <!-- Navigation & Export -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button @click="changeMonth(-1)" class="btn-secondary btn-icon" title="Mois précédent">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </button>
                    <h1 class="font-display text-2xl font-semibold text-ink-900">{{ monthLabel }}</h1>
                    <button @click="changeMonth(1)" class="btn-secondary btn-icon" title="Mois suivant">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <Link :href="route('reports.index')" class="btn-ghost text-sm">← Tous les rapports</Link>
                    <button @click="exportCsv" class="btn-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Exporter CSV
                    </button>
                </div>
            </div>

            <!-- Alerts -->
            <div v-if="alerts.length > 0" class="space-y-2">
                <div
                    v-for="alert in alerts"
                    :key="alert.category_name"
                    :class="['rounded-xl border p-4', alertClass(alert.type)]"
                >
                    <div class="flex items-center gap-3">
                        <span class="shrink-0 text-lg">{{ alert.icon }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium">{{ alert.message }}</p>
                            <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-white/60">
                                <div
                                    :class="['h-full rounded-full transition-all', alert.type === 'danger' ? 'bg-red-500' : 'bg-amber-500']"
                                    :style="{ width: Math.min(alert.percentage, 100) + '%' }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Revenus"
                    :value="formatMoney(income)"
                    variant="positive"
                />
                <StatCard
                    title="Dépenses"
                    :value="formatMoney(expense)"
                    variant="negative"
                />
                <StatCard
                    title="Balance"
                    :value="formatMoney(balance)"
                    :variant="balance >= 0 ? 'positive' : 'negative'"
                />
                <StatCard
                    title="Taux d'épargne"
                    :value="savingsRate + '%'"
                    :variant="savingsRate >= 15 ? 'positive' : savingsRate > 0 ? 'neutral' : 'negative'"
                />
            </div>

            <!-- Previous Month Comparison -->
            <div v-if="hasPrevious" class="rounded-xl border border-surface-200 bg-white p-6">
                <h3 class="font-display text-lg font-semibold text-ink-900">Comparaison avec le mois précédent</h3>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-surface-50 p-3">
                        <p class="text-xs text-ink-500">Dépenses</p>
                        <p class="mt-1 text-lg font-semibold" :class="expenseTrend > 0 ? 'text-red-600' : 'text-emerald-600'">
                            {{ expenseTrend > 0 ? '↑' : '↓' }} {{ Math.abs(expenseTrend) }}%
                        </p>
                        <p class="text-xs text-ink-400">vs {{ monthNames[previousMonth.month - 1] }}</p>
                    </div>
                    <div class="rounded-lg bg-surface-50 p-3">
                        <p class="text-xs text-ink-500">Taux d'épargne</p>
                        <p class="mt-1 text-lg font-semibold" :class="savingsTrend >= 0 ? 'text-emerald-600' : 'text-red-600'">
                            {{ savingsTrend >= 0 ? '↑' : '↓' }} {{ Math.abs(savingsTrend) }} pts
                        </p>
                        <p class="text-xs text-ink-400">vs {{ monthNames[previousMonth.month - 1] }}</p>
                    </div>
                    <div class="rounded-lg bg-surface-50 p-3">
                        <p class="text-xs text-ink-500">Balance M-1</p>
                        <p class="mt-1 text-lg font-semibold" :class="previousMonth.balance >= 0 ? 'text-emerald-600' : 'text-red-600'">
                            {{ formatMoney(previousMonth.balance) }}
                        </p>
                        <p class="text-xs text-ink-400">{{ monthNames[previousMonth.month - 1] }}</p>
                    </div>
                </div>
            </div>

            <!-- Category Budget Breakdown -->
            <div>
                <h2 class="font-display text-xl font-semibold text-ink-900">Budget par catégorie</h2>
                <p class="mt-1 text-sm text-ink-500">Dépenses réelles vs budget alloué</p>

                <div v-if="categories.length === 0" class="mt-6">
                    <EmptyState
                        title="Aucun budget ce mois-ci"
                        description="Configurez des budgets dans la section Budgets pour voir l'analyse ici."
                    >
                        <template #action>
                            <Link :href="route('budgets.index')" class="btn-primary">
                                Configurer les budgets
                            </Link>
                        </template>
                    </EmptyState>
                </div>

                <div v-else class="mt-4 space-y-3">
                    <div
                        v-for="cat in categories"
                        :key="cat.id"
                        class="card"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="shrink-0 text-xl">{{ cat.category_icon }}</span>
                                <div class="min-w-0">
                                    <p class="font-medium text-ink-900 truncate">{{ cat.category_name }}</p>
                                    <p class="text-xs text-ink-400">
                                        {{ formatMoney(cat.spent) }} / {{ formatMoney(cat.limit) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span :class="['text-sm font-semibold', statusColor(cat.status)]">
                                    {{ cat.percentage }}%
                                </span>
                                <span class="text-sm text-ink-400 w-16 text-right">
                                    {{ formatMoney(cat.remaining) }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-surface-100">
                            <div
                                :class="['h-full rounded-full transition-all', barColor(cat.status)]"
                                :style="{ width: Math.min(cat.percentage, 100) + '%' }"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nav hint -->
            <div class="text-center text-xs text-ink-400">
                <Link :href="route('reports.index')" class="hover:text-brand-600">Voir tous les mois</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
