<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, Link } from '@innertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ goals: Array });

const showForm = ref(false);
const showAddFunds = ref(null);
const form = useForm({ name: '', target_amount: '', current_amount: '0', deadline: '' });
const fundForm = useForm({ current_amount: '' });

function submit() {
    form.post(route('goals.store'), {
        preserveScroll: true,
        onSuccess: () => { showForm.value = false; form.reset(); }
    });
}

function addFunds(goal) {
    showAddFunds.value = goal.id;
    fundForm.current_amount = '';
}

function saveFunds(goal) {
    router.put(route('goals.update', goal.id), { current_amount: fundForm.current_amount }, {
        preserveScroll: true,
        onSuccess: () => showAddFunds.value = null,
    });
}

function destroy(id) {
    if (confirm('Supprimer cet objectif ?'))
        router.delete(route('goals.destroy', id), { preserveScroll: true });
}

const progressColor = (p) => p >= 100 ? 'bg-emerald-500' : p >= 50 ? 'bg-indigo-500' : p >= 25 ? 'bg-amber-500' : 'bg-gray-400';
const statusIcon = (g) => g.progress >= 100 ? '🎉' : g.days_left !== null && g.days_left < 0 ? '⏰' : '🎯';
</script>

<template>
    <Head title="Objectifs" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight">🎯 Objectifs d'épargne</h2>
                <button @click="showForm = !showForm" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">{{ showForm ? 'Annuler' : '+ Nouvel objectif' }}</button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">

                <div v-if="showForm" class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-semibold text-gray-700 mb-4">Nouvel objectif d'épargne</h3>
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nom</label>
                            <input v-model="form.name" type="text" placeholder="Ex: Voyage au Japon" required class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Montant cible ($)</label>
                            <input v-model="form.target_amount" type="number" step="0.01" min="1" required class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Déjà épargné ($)</label>
                            <input v-model="form.current_amount" type="number" step="0.01" min="0" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Date cible (optionnelle)</label>
                            <input v-model="form.deadline" type="date" class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <button type="submit" :disabled="form.processing" class="md:col-span-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">Créer l'objectif</button>
                    </form>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div v-for="g in goals" :key="g.id"
                        class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <p class="font-semibold text-gray-700">{{ statusIcon(g) }} {{ g.name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    ${{ Number(g.current_amount).toFixed(0) }} / ${{ Number(g.target_amount).toFixed(0) }}
                                    <span v-if="g.days_left !== null && g.days_left >= 0"> · {{ g.days_left }} jours restants</span>
                                    <span v-if="g.days_left !== null && g.days_left < 0" class="text-red-500"> · Date dépassée!</span>
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button @click="addFunds(g)" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">💰 Ajouter</button>
                                <button @click="destroy(g.id)" class="text-xs text-red-400 hover:text-red-600">✕</button>
                            </div>
                        </div>

                        <div v-if="showAddFunds === g.id" class="mb-3 flex gap-2">
                            <input v-model="fundForm.current_amount" type="number" step="0.01" min="0" placeholder="Montant total actuel"
                                class="flex-1 rounded-lg border-gray-300 text-sm">
                            <button @click="saveFunds(g)" :disabled="fundForm.processing"
                                class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-medium hover:bg-emerald-700 disabled:opacity-50">Sauvegarder</button>
                        </div>

                        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                            <div :class="['h-full rounded-full transition-all duration-500', progressColor(g.progress)]"
                                :style="{ width: Math.min(g.progress, 100) + '%' }">
                            </div>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-xs font-medium" :class="g.progress >= 100 ? 'text-emerald-600' : 'text-gray-500'">{{ g.progress }}%</span>
                            <span v-if="g.remaining > 0" class="text-xs text-gray-400">${{ Number(g.remaining).toFixed(0) }} restant</span>
                            <span v-else class="text-xs text-emerald-600 font-medium">✅ Atteint!</span>
                        </div>
                    </div>

                    <div v-if="goals.length === 0" class="text-center py-12 text-gray-400">
                        <p class="text-lg mb-2">Aucun objectif d'épargne</p>
                        <button @click="showForm = true" class="text-indigo-600 text-sm">Créer votre premier objectif →</button>
                    </div>
                </div>

                <Link :href="route('dashboard')" class="block text-center text-sm text-gray-400 hover:text-gray-600">← Retour</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
