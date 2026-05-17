<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ recurrings: Array, categories: Array });

const showForm = ref(false);
const form = useForm({
    category_id: props.categories.filter(c => c.type === 'expense')[0]?.id || null,
    amount: '',
    type: 'expense',
    description: '',
    frequency: 'monthly',
    day_of_month: now().getDate(),
    next_date: now().toISOString().split('T')[0],
});

function now() { return new Date(); }
const frequencies = [
    { value: 'daily', label: 'Chaque jour' },
    { value: 'weekly', label: 'Chaque semaine' },
    { value: 'monthly', label: 'Chaque mois' },
    { value: 'yearly', label: 'Chaque année' },
];

function submit() {
    form.post(route('recurring.store'), {
        preserveScroll: true,
        onSuccess: () => { showForm.value = false; form.reset('amount', 'description'); }
    });
}

function toggleActive(r) {
    router.put(route('recurring.update', r.id), { is_active: !r.is_active }, { preserveScroll: true });
}

function destroy(id) {
    if (confirm('Supprimer cette transaction récurrente ?'))
        router.delete(route('recurring.destroy', id), { preserveScroll: true });
}

function processAll() {
    router.post(route('recurring.process'), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Récurrent" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight">🔁 Transactions récurrentes</h2>
                <div class="flex gap-2">
                    <button @click="processAll" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700">⚡ Traiter maintenant</button>
                    <button @click="showForm = !showForm" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">{{ showForm ? 'Annuler' : '+ Ajouter' }}</button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">
                <p class="text-sm text-gray-400">Configurer les revenus et dépenses qui se répètent. Cliquez sur "Traiter maintenant" pour créer les transactions dues.</p>

                <div v-if="showForm" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-semibold text-gray-700 mb-4">Nouvelle récurrence</h3>
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                            <select v-model="form.type" class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="expense">💸 Dépense</option>
                                <option value="income">💰 Revenu</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Montant</label>
                            <input v-model="form.amount" type="number" step="0.01" min="0" placeholder="0.00" required class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Catégorie</label>
                            <select v-model="form.category_id" class="w-full rounded-lg border-gray-300 text-sm">
                                <option v-for="c in categories.filter(c => c.type === form.type)" :key="c.id" :value="c.id">{{ c.icon }} {{ c.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Fréquence</label>
                            <select v-model="form.frequency" class="w-full rounded-lg border-gray-300 text-sm">
                                <option v-for="f in frequencies" :key="f.value" :value="f.value">{{ f.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Jour du mois</label>
                            <input v-model="form.day_of_month" type="number" min="1" max="31" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                            <input v-model="form.description" type="text" placeholder="Ex: Salaire, Netflix..." class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Prochaine date</label>
                            <input v-model="form.next_date" type="date" required class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">Ajouter</button>
                    </form>
                </div>

                <div class="space-y-3">
                    <div v-for="r in recurrings" :key="r.id"
                        class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <button @click="toggleActive(r)" :class="['w-10 h-10 rounded-xl flex items-center justify-center text-lg transition-all', r.is_active ? 'bg-emerald-50' : 'bg-gray-100 opacity-50']">
                                {{ r.is_active ? '✅' : '⏸' }}
                            </button>
                            <div>
                                <p class="font-medium text-sm">{{ r.category.icon }} {{ r.category.name }} <span v-if="r.description" class="text-gray-400 font-normal">· {{ r.description }}</span></p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Tous les {{ r.frequency === 'monthly' ? 'mois le ' + r.day_of_month : r.frequency }} · Prochain: {{ r.next_date }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span :class="['font-semibold text-sm', r.type === 'income' ? 'text-emerald-600' : 'text-red-500']">
                                {{ r.type === 'income' ? '+' : '-' }}${{ Number(r.amount).toFixed(2) }}
                            </span>
                            <button @click="destroy(r.id)" class="text-red-400 hover:text-red-600 text-xs">✕</button>
                        </div>
                    </div>
                    <div v-if="recurrings.length === 0" class="text-center py-12 text-gray-400">
                        <p class="text-lg mb-2">Aucune transaction récurrente</p>
                        <button @click="showForm = true" class="text-indigo-600 text-sm">Ajouter un salaire ou abonnement →</button>
                    </div>
                </div>

                <Link :href="route('dashboard')" class="block text-center text-sm text-gray-400 hover:text-gray-600">← Retour</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
