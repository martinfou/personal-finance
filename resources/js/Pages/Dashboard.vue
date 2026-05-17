<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    year: Number,
    month: Number,
    income: Number,
    expense: Number,
    balance: Number,
    byCategory: Array,
    recentTransactions: Array,
    budgets: Array,
});

const monthNames = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

function changeMonth(delta) {
    let m = props.month + delta;
    let y = props.year;
    if (m > 12) { m = 1; y++; }
    if (m < 1) { m = 12; y--; }
    router.get('/dashboard', { year: y, month: m }, { preserveState: true, replace: true });
}

function formatAmount(amount) {
    return '$' + Number(amount).toFixed(2);
}

const chartData = {
    labels: props.byCategory.map(c => `${c.icon} ${c.name}`),
    datasets: [{
        data: props.byCategory.map(c => c.total),
        backgroundColor: props.byCategory.map(c => c.color),
        borderWidth: 0,
    }],
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } },
    },
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight">Dashboard</h2>
                <Link :href="route('transactions.index')" class="text-sm text-indigo-600 hover:text-indigo-800">
                    Voir toutes les transactions →
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Month Navigation -->
                <div class="flex items-center justify-center gap-4 mb-6">
                    <button @click="changeMonth(-1)" class="px-3 py-1 rounded-lg border hover:bg-gray-50 text-lg">←</button>
                    <span class="font-semibold text-lg min-w-[160px] text-center">{{ monthNames[month - 1] }} {{ year }}</span>
                    <button @click="changeMonth(1)" class="px-3 py-1 rounded-lg border hover:bg-gray-50 text-lg">→</button>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Revenus</p>
                        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ formatAmount(income) }}</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Dépenses</p>
                        <p class="text-2xl font-bold text-red-500 mt-1">{{ formatAmount(expense) }}</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Balance</p>
                        <p :class="['text-2xl font-bold mt-1', balance >= 0 ? 'text-blue-600' : 'text-red-600']">
                            {{ formatAmount(balance) }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Chart -->
                    <div class="lg:col-span-1 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="font-semibold text-gray-700 mb-4">📊 Dépenses par catégorie</h3>
                        <div v-if="byCategory.length > 0" class="h-64">
                            <Doughnut :data="chartData" :options="chartOptions" />
                        </div>
                        <p v-else class="text-gray-400 text-sm text-center py-12">Aucune dépense ce mois-ci</p>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="font-semibold text-gray-700 mb-4">📋 Transactions récentes</h3>
                        <div v-if="recentTransactions.length > 0">
                            <div v-for="tx in recentTransactions" :key="tx.id"
                                 class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl">{{ tx.category.icon }}</span>
                                    <div>
                                        <p class="text-sm font-medium">{{ tx.category.name }}</p>
                                        <p class="text-xs text-gray-400">{{ tx.date }} {{ tx.description ? '· ' + tx.description : '' }}</p>
                                    </div>
                                </div>
                                <span :class="['font-semibold', tx.type === 'income' ? 'text-emerald-600' : 'text-red-500']">
                                    {{ tx.type === 'income' ? '+' : '-' }}{{ formatAmount(tx.amount) }}
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-gray-400 text-sm text-center py-12">
                            Aucune transaction pour ce mois.
                            <Link :href="route('transactions.index')" class="text-indigo-600 hover:text-indigo-800 block mt-2">
                                Ajouter une transaction →
                            </Link>
                        </p>
                    </div>
                </div>

                <!-- Budgets -->
                <div v-if="budgets.length > 0" class="mt-6 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-semibold text-gray-700 mb-4">🎯 Budgets du mois</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="budget in budgets" :key="budget.category" class="p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium">{{ budget.icon }} {{ budget.category }}</span>
                                <span class="text-xs text-gray-500">{{ formatAmount(budget.spent) }} / {{ formatAmount(budget.limit) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-300"
                                     :class="budget.spent > budget.limit ? 'bg-red-500' : 'bg-indigo-500'"
                                     :style="{ width: Math.min(100, (budget.spent / budget.limit) * 100) + '%' }">
                                </div>
                            </div>
                            <p v-if="budget.remaining >= 0" class="text-xs text-emerald-600 mt-1">
                                {{ formatAmount(budget.remaining) }} restant
                            </p>
                            <p v-else class="text-xs text-red-500 mt-1">
                                {{ formatAmount(Math.abs(budget.remaining)) }} de dépassement
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
