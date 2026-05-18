<script setup>
import CategoryBadge from '@/Components/CategoryBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import TypeToggle from '@/Components/TypeToggle.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatMoney } from '@/utils/money';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    transactions: Object,
    categories: Array,
});

const showForm = ref(false);

const form = useForm({
    amount: '',
    type: 'expense',
    category_id: null,
    description: '',
    date: new Date().toISOString().split('T')[0],
});

const filteredCategories = ref(props.categories.filter((c) => c.type === 'expense'));

watch(
    () => form.type,
    (type) => {
        filteredCategories.value = props.categories.filter((c) => c.type === type);
        form.category_id = filteredCategories.value[0]?.id || null;
    },
);

if (filteredCategories.value.length > 0 && !form.category_id) {
    form.category_id = filteredCategories.value[0].id;
}

function submit() {
    form.post(route('transactions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('amount', 'description');
            showForm.value = false;
        },
    });
}

function destroy(id) {
    if (confirm('Supprimer cette transaction ?')) {
        router.delete(route('transactions.destroy', id), { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Transactions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-display text-2xl font-semibold text-ink-900">Transactions</h2>
                    <p class="mt-1 text-sm text-ink-500">Historique et saisie manuelle</p>
                </div>
                <button type="button" class="btn-primary text-sm shrink-0" @click="showForm = !showForm">
                    {{ showForm ? 'Annuler' : 'Nouvelle transaction' }}
                </button>
            </div>
        </template>

        <div class="space-y-6">
            <div v-if="showForm" class="card p-6">
                <h3 class="mb-4 font-semibold text-ink-800">Ajouter une transaction</h3>
                <form @submit.prevent="submit" class="grid grid-cols-1 items-end gap-4 md:grid-cols-2 lg:grid-cols-6">
                    <div class="lg:col-span-2">
                        <label class="label">Type</label>
                        <TypeToggle v-model="form.type" class="mt-1" />
                    </div>
                    <div>
                        <label class="label" for="amount">Montant</label>
                        <input id="amount" v-model="form.amount" type="number" step="0.01" min="0" required class="input mt-1" placeholder="0.00" />
                    </div>
                    <div>
                        <label class="label" for="category">Catégorie</label>
                        <select id="category" v-model="form.category_id" required class="input mt-1">
                            <option v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">
                                {{ cat.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="label" for="description">Description</label>
                        <input id="description" v-model="form.description" type="text" class="input mt-1" placeholder="Optionnelle" />
                    </div>
                    <div>
                        <label class="label" for="date">Date</label>
                        <input id="date" v-model="form.date" type="date" required class="input mt-1" />
                    </div>
                    <button type="submit" class="btn-primary w-full lg:w-auto" :disabled="form.processing">
                        Ajouter
                    </button>
                </form>
                <p v-if="form.recentlySuccessful" class="mt-3 text-sm font-medium text-emerald-600">
                    Transaction ajoutée.
                </p>
            </div>

            <div class="card overflow-hidden">
                <div v-if="transactions.data.length > 0">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-surface-100 bg-surface-75 text-left text-xs font-semibold uppercase tracking-wider text-ink-500">
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Catégorie</th>
                                <th class="px-6 py-3">Description</th>
                                <th class="px-6 py-3 text-right">Montant</th>
                                <th class="w-20 px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-100">
                            <tr v-for="tx in transactions.data" :key="tx.id" class="hover:bg-surface-75">
                                <td class="px-6 py-4 text-sm text-ink-500">{{ tx.date }}</td>
                                <td class="px-6 py-4">
                                    <CategoryBadge
                                        v-if="tx.category"
                                        :name="tx.category.name"
                                        :color="tx.category.color"
                                    />
                                </td>
                                <td class="px-6 py-4 text-sm text-ink-600">{{ tx.description || '—' }}</td>
                                <td
                                    :class="[
                                        'px-6 py-4 text-right text-sm font-semibold',
                                        tx.type === 'income' ? 'text-emerald-600' : 'text-red-500',
                                    ]"
                                >
                                    {{ tx.type === 'income' ? '+' : '-' }}{{ formatMoney(tx.amount) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button type="button" class="text-sm text-red-500 hover:text-red-600" @click="destroy(tx.id)">
                                        Supprimer
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div
                        v-if="transactions.last_page > 1"
                        class="flex items-center justify-between border-t border-surface-100 px-6 py-4"
                    >
                        <Link v-if="transactions.prev_page_url" :href="transactions.prev_page_url" class="text-sm font-medium text-brand-700 hover:text-brand-800">
                            Précédent
                        </Link>
                        <span v-else />
                        <span class="text-xs text-ink-400">Page {{ transactions.current_page }} / {{ transactions.last_page }}</span>
                        <Link v-if="transactions.next_page_url" :href="transactions.next_page_url" class="text-sm font-medium text-brand-700 hover:text-brand-800">
                            Suivant
                        </Link>
                    </div>
                </div>
                <EmptyState
                    v-else
                    title="Aucune transaction"
                    description="Ajoutez votre première transaction pour alimenter le tableau de bord."
                >
                    <template #action>
                        <button type="button" class="btn-primary text-sm" @click="showForm = true">
                            Ajouter une transaction
                        </button>
                    </template>
                </EmptyState>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
