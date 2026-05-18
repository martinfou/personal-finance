<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import CategoryBadge from '@/Components/CategoryBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import MonthNavigator from '@/Components/MonthNavigator.vue';
import StatCard from '@/Components/StatCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatMoney } from '@/utils/money';
import { Head, Link, router } from '@inertiajs/vue3';
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    year: Number,
    month: Number,
    isCurrentMonth: Boolean,
    income: Number,
    expense: Number,
    balance: Number,
    previous: Object,
    byCategory: Array,
    recentTransactions: Array,
    budgets: Array,
});

const chartData = {
    labels: props.byCategory.map((c) => c.name),
    datasets: [{
        data: props.byCategory.map((c) => c.total),
        backgroundColor: props.byCategory.map((c) => c.color),
        borderWidth: 0,
    }],
};
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom', labels: { padding: 16, font: { size: 11 } } } },
    cutout: '65%',
};
</script>

<template>
    <Head title="Tableau de bord" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-display text-2xl font-semibold text-ink-900">Tableau de bord</h2>
                    <p class="mt-1 text-sm text-ink-500">
                        {{ isCurrentMonth ? 'Mois en cours' : 'Période sélectionnée' }}
                    </p>
                </div>
                <Link :href="route('transactions.index')" class="btn-ghost text-sm shrink-0">
                    Voir tout
                </Link>
            </div>
        </template>

        <div class="space-y-8">
            <MonthNavigator :year="year" :month="month" base-path="/dashboard" />

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <StatCard
                    label="Revenus"
                    :value="income"
                    :previous="previous.income"
                    value-class="text-emerald-600"
                />
                <StatCard
                    label="Dépenses"
                    :value="expense"
                    :previous="previous.expense"
                    value-class="text-red-500"
                    invert-delta
                />
                <StatCard
                    label="Balance"
                    :value="balance"
                    :previous="previous.balance"
                    :value-class="balance >= 0 ? 'text-brand-700' : 'text-red-500'"
                />
            </div>

            <section v-if="budgets.length > 0" class="card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-ink-800">Budgets du mois</h3>
                    <Link :href="route('budgets.index')" class="text-xs font-medium text-brand-700 hover:text-brand-800">
                        Gérer
                    </Link>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="b in budgets" :key="b.category" class="rounded-xl bg-surface-75 p-4">
                        <div class="mb-2 flex items-center justify-between gap-2">
                            <CategoryBadge :name="b.category" :color="b.color || '#6b6358'" />
                            <span class="text-xs text-ink-500">{{ formatMoney(b.spent) }} / {{ formatMoney(b.limit) }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-white">
                            <div
                                class="h-2 rounded-full transition-all duration-500"
                                :class="b.spent > b.limit ? 'bg-red-400' : 'bg-brand-600'"
                                :style="{ width: Math.min(100, (b.spent / b.limit) * 100) + '%' }"
                            />
                        </div>
                        <p v-if="b.remaining >= 0" class="mt-1.5 text-xs font-medium text-emerald-600">
                            {{ formatMoney(b.remaining) }} restant
                        </p>
                        <p v-else class="mt-1.5 text-xs font-medium text-red-500">
                            {{ formatMoney(Math.abs(b.remaining)) }} dépassement
                        </p>
                    </div>
                </div>
            </section>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="card p-6">
                    <h3 class="mb-4 text-sm font-semibold text-ink-800">Dépenses par catégorie</h3>
                    <div v-if="byCategory.length > 0" class="h-60">
                        <Doughnut :data="chartData" :options="chartOptions" />
                    </div>
                    <EmptyState
                        v-else
                        title="Aucune dépense"
                        description="Les dépenses de ce mois apparaîtront ici."
                    >
                        <template #icon>
                            <AppIcon name="chart-bar" icon-class="h-6 w-6" />
                        </template>
                    </EmptyState>
                </div>

                <div class="card p-6 lg:col-span-2">
                    <h3 class="mb-4 text-sm font-semibold text-ink-800">Transactions récentes</h3>
                    <div v-if="recentTransactions.length > 0" class="space-y-1">
                        <div
                            v-for="tx in recentTransactions"
                            :key="tx.id"
                            class="-mx-3 flex items-center justify-between rounded-xl px-3 py-2.5 transition-colors hover:bg-surface-75"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex h-9 w-9 items-center justify-center rounded-xl"
                                    :style="{ backgroundColor: tx.category.color + '22' }"
                                >
                                    <span
                                        class="h-2.5 w-2.5 rounded-full"
                                        :style="{ backgroundColor: tx.category.color }"
                                    />
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-ink-800">{{ tx.category.name }}</p>
                                    <p class="text-xs text-ink-500">
                                        {{ tx.date }}{{ tx.description ? ' · ' + tx.description : '' }}
                                    </p>
                                </div>
                            </div>
                            <span
                                :class="[
                                    'text-sm font-semibold',
                                    tx.type === 'income' ? 'text-emerald-600' : 'text-red-500',
                                ]"
                            >
                                {{ tx.type === 'income' ? '+' : '-' }}{{ formatMoney(tx.amount) }}
                            </span>
                        </div>
                    </div>
                    <EmptyState
                        v-else
                        title="Aucune transaction"
                        description="Ajoutez une transaction pour commencer le suivi."
                    >
                        <template #icon>
                            <AppIcon name="receipt" icon-class="h-6 w-6" />
                        </template>
                        <template #action>
                            <Link :href="route('transactions.index')" class="btn-primary text-sm">
                                Ajouter une transaction
                            </Link>
                        </template>
                    </EmptyState>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
