<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    months: Array,
});

const monthNames = ['Janvier','Février','Mars','Avril','Mai','Juin',
                    'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
</script>

<template>
    <Head title="Rapports" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
            <div>
                <h1 class="font-display text-3xl font-semibold text-ink-900">Rapports budgétaires</h1>
                <p class="mt-1 text-sm text-ink-500">
                    Analyse budget vs dépenses réelles, mois par mois.
                </p>
            </div>

            <div v-if="months.length === 0" class="py-12">
                <EmptyState
                    title="Aucun rapport disponible"
                    description="Créez d'abord des budgets pour voir vos rapports mensuels."
                />
            </div>

            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="m in months"
                    :key="`${m.year}-${m.month}`"
                    :href="route('reports.monthly', { year: m.year, month: m.month })"
                    class="card card-interactive flex items-center gap-4"
                >
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-brand-100 text-brand-600">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-display text-lg font-semibold text-ink-900">{{ m.label }}</p>
                        <p class="text-sm text-ink-500">Budget mensuel</p>
                    </div>
                    <svg class="ml-auto h-5 w-5 shrink-0 text-ink-300" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
