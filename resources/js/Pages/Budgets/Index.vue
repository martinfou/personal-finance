<script setup>
import CategoryBadge from '@/Components/CategoryBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import MonthNavigator from '@/Components/MonthNavigator.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatMoney } from '@/utils/money';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    budgets: Array,
    categories: Array,
    year: Number,
    month: Number,
    monthLabel: String,
    monthStr: String,
});

const showForm = ref(false);
const form = useForm({ category_id: null, limit_amount: '', month: props.monthStr });

const unusedCategories = ref(
    props.categories.filter((c) => !props.budgets.some((b) => b.category_id === c.id)),
);

function submit() {
    form.post(route('budgets.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showForm.value = false;
            form.reset('limit_amount');
        },
    });
}

function remove(id) {
    if (confirm('Supprimer ce budget ?')) {
        router.delete(route('budgets.destroy', id), { preserveScroll: true });
    }
}

const barColor = (pct) =>
    pct >= 100 ? 'bg-red-500' : pct >= 75 ? 'bg-amber-500' : pct >= 50 ? 'bg-brand-600' : 'bg-emerald-500';
</script>

<template>
    <Head title="Budgets" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-display text-2xl font-semibold text-ink-900">Budgets</h2>
                    <p class="mt-1 text-sm text-ink-500">Limites par catégorie, mois par mois</p>
                </div>
                <button type="button" class="btn-primary text-sm shrink-0" @click="showForm = !showForm">
                    {{ showForm ? 'Annuler' : 'Nouveau budget' }}
                </button>
            </div>
        </template>

        <div class="mx-auto max-w-4xl space-y-6">
            <MonthNavigator :year="year" :month="month" :month-label="monthLabel" base-path="/budgets" />

            <div v-if="showForm" class="card p-6">
                <h3 class="mb-4 font-semibold text-ink-800">Ajouter un budget</h3>
                <form @submit.prevent="submit" class="flex flex-wrap items-end gap-4">
                    <div class="min-w-[200px] flex-1">
                        <label class="label">Catégorie</label>
                        <select v-model="form.category_id" required class="input mt-1">
                            <option v-for="c in unusedCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div class="w-40">
                        <label class="label">Limite</label>
                        <input v-model="form.limit_amount" type="number" step="0.01" min="1" required class="input mt-1" placeholder="0.00" />
                    </div>
                    <button type="submit" class="btn-primary" :disabled="form.processing">Enregistrer</button>
                </form>
            </div>

            <div v-if="budgets.length > 0" class="space-y-4">
                <div v-for="b in budgets" :key="b.id" class="card-interactive p-5">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <CategoryBadge :name="b.category_name" :color="b.category_color || '#6b6358'" />
                        <div class="flex items-center gap-3">
                            <span :class="['text-sm font-semibold', b.remaining >= 0 ? 'text-emerald-600' : 'text-red-500']">
                                {{ b.remaining >= 0 ? 'Reste ' + formatMoney(b.remaining) : 'Dépassé ' + formatMoney(Math.abs(b.remaining)) }}
                            </span>
                            <button type="button" class="text-sm text-ink-400 hover:text-red-500" @click="remove(b.id)">Supprimer</button>
                        </div>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-surface-100">
                        <div
                            :class="['h-full rounded-full transition-all duration-500', barColor(b.percentage)]"
                            :style="{ width: Math.min(b.percentage, 100) + '%' }"
                        />
                    </div>
                    <div class="mt-1.5 flex justify-between text-xs">
                        <span :class="['font-medium', b.percentage >= 100 ? 'text-red-500' : 'text-ink-500']">{{ b.percentage }}%</span>
                        <span class="text-ink-400">{{ formatMoney(b.spent) }} / {{ formatMoney(b.limit) }}</span>
                    </div>
                </div>
            </div>

            <EmptyState
                v-else
                title="Aucun budget ce mois-ci"
                description="Fixez une limite par catégorie pour mieux contrôler vos dépenses."
            >
                <template #action>
                    <button type="button" class="btn-primary text-sm" @click="showForm = true">Créer mon premier budget</button>
                </template>
            </EmptyState>

            <Link :href="route('dashboard')" class="block text-center text-sm text-ink-400 hover:text-ink-600">Retour au tableau de bord</Link>
        </div>
    </AuthenticatedLayout>
</template>
