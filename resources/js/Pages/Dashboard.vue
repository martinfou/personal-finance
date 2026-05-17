<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    year: Number, month: Number, income: Number,
    expense: Number, balance: Number,
    byCategory: Array, recentTransactions: Array, budgets: Array,
});

const monthNames = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

function changeMonth(delta) {
    let m = props.month + delta;
    let y = props.year;
    if (m > 12) { m = 1; y++; } if (m < 1) { m = 12; y--; }
    router.get('/dashboard', { year: y, month: m }, { preserveState: true, replace: true });
}

const f = (n) => '$' + Number(n).toFixed(2);

const chartData = {
    labels: props.byCategory.map(c => `${c.icon} ${c.name}`),
    datasets: [{
        data: props.byCategory.map(c => c.total),
        backgroundColor: props.byCategory.map(c => c.color),
        borderWidth: 0,
    }],
};
const chartOptions = {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom', labels: { padding: 16, font: { size: 11 } } } },
    cutout: '65%',
};
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Tableau de bord</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Aperçu de vos finances</p>
                </div>
                <Link :href="route('transactions.index')" class="btn-ghost text-sm">
                    Voir tout →
                </Link>
            </div>
        </template>

        <div class="py-6 space-y-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Month Nav -->
                <div class="flex items-center justify-center gap-3 mb-8">
                    <button @click="changeMonth(-1)" class="w-9 h-9 flex items-center justify-center rounded-xl border border-surface-100 hover:bg-surface-50 text-gray-400 hover:text-gray-600 transition-colors">←</button>
                    <span class="font-semibold text-base text-gray-700 min-w-[150px] text-center">{{ monthNames[month - 1] }} {{ year }}</span>
                    <button @click="changeMonth(1)" class="w-9 h-9 flex items-center justify-center rounded-xl border border-surface-100 hover:bg-surface-50 text-gray-400 hover:text-gray-600 transition-colors">→</button>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="card p-5">
                        <p class="stat-label">Revenus</p>
                        <p class="stat-value text-emerald-500 mt-1">{{ f(income) }}</p>
                    </div>
                    <div class="card p-5">
                        <p class="stat-label">Dépenses</p>
                        <p class="stat-value text-red-400 mt-1">{{ f(expense) }}</p>
                    </div>
                    <div class="card p-5">
                        <p class="stat-label">Balance</p>
                        <p :class="['stat-value mt-1', balance >= 0 ? 'text-brand-600' : 'text-red-500']">{{ f(balance) }}</p>
                    </div>
                </div>

                <!-- Main grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Chart -->
                    <div class="card p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">📊 Dépenses</h3>
                        <div v-if="byCategory.length > 0" class="h-60">
                            <Doughnut :data="chartData" :options="chartOptions" />
                        </div>
                        <p v-else class="text-sm text-gray-300 text-center py-12">Aucune dépense</p>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="lg:col-span-2 card p-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">📋 Récentes</h3>
                        <div v-if="recentTransactions.length > 0" class="space-y-1">
                            <div v-for="tx in recentTransactions" :key="tx.id"
                                class="flex items-center justify-between py-2.5 px-3 -mx-3 rounded-xl hover:bg-surface-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-surface-50 text-lg">{{ tx.category.icon }}</span>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">{{ tx.category.name }}</p>
                                        <p class="text-xs text-gray-400">{{ tx.date }}{{ tx.description ? ' · ' + tx.description : '' }}</p>
                                    </div>
                                </div>
                                <span :class="['text-sm font-semibold', tx.type === 'income' ? 'text-emerald-500' : 'text-red-400']">
                                    {{ tx.type === 'income' ? '+' : '-' }}{{ f(tx.amount) }}
                                </span>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <p class="text-sm text-gray-300 mb-3">Aucune transaction</p>
                            <Link :href="route('transactions.index')" class="btn-primary text-sm">Ajouter une transaction</Link>
                        </div>
                    </div>
                </div>

                <!-- Budgets -->
                <div v-if="budgets.length > 0" class="mt-6 card p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">🎯 Budgets du mois</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="b in budgets" :key="b.category" class="p-4 rounded-xl bg-surface-50">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">{{ b.icon }} {{ b.category }}</span>
                                <span class="text-xs text-gray-400">{{ f(b.spent) }} / {{ f(b.limit) }}</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-white overflow-hidden">
                                <div class="h-2 rounded-full transition-all duration-500"
                                    :class="b.spent > b.limit ? 'bg-red-400' : 'bg-brand-500'"
                                    :style="{ width: Math.min(100, (b.spent / b.limit) * 100) + '%' }">
                                </div>
                            </div>
                            <p v-if="b.remaining >= 0" class="text-xs text-emerald-500 mt-1.5 font-medium">{{ f(b.remaining) }} restant</p>
                            <p v-else class="text-xs text-red-400 mt-1.5 font-medium">{{ f(Math.abs(b.remaining)) }} dépassement</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
