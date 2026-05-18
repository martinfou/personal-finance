<script setup>
import CategoryBadge from '@/Components/CategoryBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import TypeToggle from '@/Components/TypeToggle.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatMoney } from '@/utils/money';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ recurrings: Array, categories: Array });

const showForm = ref(false);
const form = useForm({
    category_id: props.categories.filter((c) => c.type === 'expense')[0]?.id || null,
    amount: '',
    type: 'expense',
    description: '',
    frequency: 'monthly',
    day_of_month: new Date().getDate(),
    next_date: new Date().toISOString().split('T')[0],
});

const filteredCategories = ref(props.categories.filter((c) => c.type === form.type));

watch(
    () => form.type,
    (type) => {
        filteredCategories.value = props.categories.filter((c) => c.type === type);
        form.category_id = filteredCategories.value[0]?.id || null;
    },
);

const frequencies = [
    { value: 'daily', label: 'Chaque jour' },
    { value: 'weekly', label: 'Chaque semaine' },
    { value: 'monthly', label: 'Chaque mois' },
    { value: 'yearly', label: 'Chaque année' },
];

function submit() {
    form.post(route('recurring.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showForm.value = false;
            form.reset('amount', 'description');
        },
    });
}

function toggleActive(r) {
    router.put(route('recurring.update', r.id), { is_active: !r.is_active }, { preserveScroll: true });
}

function destroy(id) {
    if (confirm('Supprimer cette transaction récurrente ?')) {
        router.delete(route('recurring.destroy', id), { preserveScroll: true });
    }
}

function processAll() {
    router.post(route('recurring.process'), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Récurrent" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="font-display text-2xl font-semibold text-ink-900">Transactions récurrentes</h2>
                    <p class="mt-1 text-sm text-ink-500">Revenus et dépenses automatiques</p>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="btn-secondary text-sm" @click="processAll">Traiter maintenant</button>
                    <button type="button" class="btn-primary text-sm" @click="showForm = !showForm">
                        {{ showForm ? 'Annuler' : 'Ajouter' }}
                    </button>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-4xl space-y-6">
            <p class="text-sm text-ink-500">
                Configurez abonnements et revenus réguliers. « Traiter maintenant » crée les transactions dues.
            </p>

            <div v-if="showForm" class="card p-6">
                <h3 class="mb-4 font-semibold text-ink-800">Nouvelle récurrence</h3>
                <form @submit.prevent="submit" class="grid grid-cols-1 items-end gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div class="lg:col-span-2">
                        <label class="label">Type</label>
                        <TypeToggle v-model="form.type" class="mt-1" />
                    </div>
                    <div>
                        <label class="label">Montant</label>
                        <input v-model="form.amount" type="number" step="0.01" min="0" required class="input mt-1" />
                    </div>
                    <div>
                        <label class="label">Catégorie</label>
                        <select v-model="form.category_id" class="input mt-1">
                            <option v-for="c in filteredCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Fréquence</label>
                        <select v-model="form.frequency" class="input mt-1">
                            <option v-for="f in frequencies" :key="f.value" :value="f.value">{{ f.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Jour du mois</label>
                        <input v-model="form.day_of_month" type="number" min="1" max="31" class="input mt-1" />
                    </div>
                    <div>
                        <label class="label">Description</label>
                        <input v-model="form.description" type="text" class="input mt-1" placeholder="Ex: Netflix" />
                    </div>
                    <div>
                        <label class="label">Prochaine date</label>
                        <input v-model="form.next_date" type="date" required class="input mt-1" />
                    </div>
                    <button type="submit" class="btn-primary" :disabled="form.processing">Ajouter</button>
                </form>
            </div>

            <div v-if="recurrings.length > 0" class="space-y-3">
                <div
                    v-for="r in recurrings"
                    :key="r.id"
                    class="card flex items-center justify-between gap-4 p-5"
                >
                    <div class="flex items-center gap-4">
                        <button
                            type="button"
                            :class="[
                                'h-10 w-10 rounded-xl text-xs font-semibold transition-colors',
                                r.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-surface-100 text-ink-400',
                            ]"
                            @click="toggleActive(r)"
                        >
                            {{ r.is_active ? 'Actif' : 'Pause' }}
                        </button>
                        <div>
                            <CategoryBadge :name="r.category.name" :color="r.category.color" />
                            <p v-if="r.description" class="mt-1 text-xs text-ink-500">{{ r.description }}</p>
                            <p class="mt-0.5 text-xs text-ink-400">
                                {{ r.frequency === 'monthly' ? `Chaque mois le ${r.day_of_month}` : r.frequency }}
                                · Prochain: {{ r.next_date }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span :class="['text-sm font-semibold', r.type === 'income' ? 'text-emerald-600' : 'text-red-500']">
                            {{ r.type === 'income' ? '+' : '-' }}{{ formatMoney(r.amount) }}
                        </span>
                        <button type="button" class="text-xs text-red-500 hover:text-red-600" @click="destroy(r.id)">Supprimer</button>
                    </div>
                </div>
            </div>

            <EmptyState v-else title="Aucune récurrence" description="Ajoutez un salaire, un loyer ou un abonnement.">
                <template #action>
                    <button type="button" class="btn-primary text-sm" @click="showForm = true">Ajouter une récurrence</button>
                </template>
            </EmptyState>

            <Link :href="route('dashboard')" class="block text-center text-sm text-ink-400 hover:text-ink-600">Retour au tableau de bord</Link>
        </div>
    </AuthenticatedLayout>
</template>
