<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    transactions: Object,
    categories: Array,
});

const showForm = ref(false);
const typeFilter = ref('expense');

const form = useForm({
    amount: '',
    type: 'expense',
    category_id: null,
    description: '',
    date: new Date().toISOString().split('T')[0],
});

const filteredCategories = ref(props.categories.filter(c => c.type === 'expense'));

function setType(type) {
    form.type = type;
    filteredCategories.value = props.categories.filter(c => c.type === type);
    form.category_id = filteredCategories.value[0]?.id || null;
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

function formatAmount(amount) {
    return '$' + Number(amount).toFixed(2);
}

// Set default category
if (filteredCategories.value.length > 0 && !form.category_id) {
    form.category_id = filteredCategories.value[0].id;
}
</script>

<template>
    <Head title="Transactions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight">Transactions</h2>
                <button @click="showForm = !showForm"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                    {{ showForm ? 'Annuler' : '+ Nouvelle transaction' }}
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Add Transaction Form -->
                <div v-if="showForm" class="mb-6 bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-semibold text-gray-700 mb-4">Ajouter une transaction</h3>
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                        <!-- Type toggle -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                            <div class="flex gap-2">
                                <button type="button" @click="setType('expense')"
                                    :class="['px-3 py-1.5 rounded-lg text-sm font-medium border transition-all',
                                        form.type === 'expense' ? 'bg-red-50 border-red-300 text-red-600' : 'bg-white border-gray-200 text-gray-500']">
                                    💸 Dépense
                                </button>
                                <button type="button" @click="setType('income')"
                                    :class="['px-3 py-1.5 rounded-lg text-sm font-medium border transition-all',
                                        form.type === 'income' ? 'bg-emerald-50 border-emerald-300 text-emerald-600' : 'bg-white border-gray-200 text-gray-500']">
                                    💰 Revenu
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Montant</label>
                            <input v-model="form.amount" type="number" step="0.01" min="0" placeholder="0.00" required
                                class="w-full rounded-lg border-gray-300 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Catégorie</label>
                            <select v-model="form.category_id" required
                                class="w-full rounded-lg border-gray-300 text-sm">
                                <option v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">
                                    {{ cat.icon }} {{ cat.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                            <input v-model="form.description" type="text" placeholder="Optionnelle"
                                class="w-full rounded-lg border-gray-300 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                            <input v-model="form.date" type="date" required
                                class="w-full rounded-lg border-gray-300 text-sm">
                        </div>

                        <button type="submit" :disabled="form.processing"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                            Ajouter
                        </button>
                    </form>
                    <p v-if="form.recentlySuccessful" class="text-emerald-600 text-sm mt-2">✅ Transaction ajoutée!</p>
                </div>

                <!-- Transactions Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div v-if="transactions.data.length > 0">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <th class="px-6 py-3">Date</th>
                                    <th class="px-6 py-3">Catégorie</th>
                                    <th class="px-6 py-3">Description</th>
                                    <th class="px-6 py-3 text-right">Montant</th>
                                    <th class="px-6 py-3 text-right w-20">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="tx in transactions.data" :key="tx.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ tx.date }}</td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm">{{ tx.category?.icon }} {{ tx.category?.name }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ tx.description || '-' }}</td>
                                    <td :class="['px-6 py-4 text-sm font-semibold text-right',
                                        tx.type === 'income' ? 'text-emerald-600' : 'text-red-500']">
                                        {{ tx.type === 'income' ? '+' : '-' }}{{ formatAmount(tx.amount) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="destroy(tx.id)" class="text-red-400 hover:text-red-600 text-sm">✕</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Pagination -->
                        <div v-if="transactions.last_page > 1" class="px-6 py-4 border-t flex justify-between items-center">
                            <Link v-if="transactions.prev_page_url" :href="transactions.prev_page_url"
                                class="text-sm text-indigo-600 hover:text-indigo-800">← Précédent</Link>
                            <span v-else></span>
                            <span class="text-xs text-gray-400">Page {{ transactions.current_page }} / {{ transactions.last_page }}</span>
                            <Link v-if="transactions.next_page_url" :href="transactions.next_page_url"
                                class="text-sm text-indigo-600 hover:text-indigo-800">Suivant →</Link>
                        </div>
                    </div>
                    <div v-else class="text-center py-12 text-gray-400">
                        <p class="text-lg mb-2">Aucune transaction</p>
                        <button @click="showForm = true" class="text-indigo-600 hover:text-indigo-800 text-sm">
                            Ajouter votre première transaction →
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
