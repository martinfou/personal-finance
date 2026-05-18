<script setup>
import EmptyState from '@/Components/EmptyState.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatMoney } from '@/utils/money';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ goals: Array });

const showForm = ref(false);
const showAddFunds = ref(null);
const form = useForm({ name: '', target_amount: '', current_amount: '0', deadline: '' });
const fundForm = useForm({ current_amount: '' });

function submit() {
    form.post(route('goals.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showForm.value = false;
            form.reset();
        },
    });
}

function addFunds(goal) {
    showAddFunds.value = goal.id;
    fundForm.current_amount = '';
}

function saveFunds(goal) {
    router.put(route('goals.update', goal.id), { current_amount: fundForm.current_amount }, {
        preserveScroll: true,
        onSuccess: () => { showAddFunds.value = null; },
    });
}

function destroy(id) {
    if (confirm('Supprimer cet objectif ?')) {
        router.delete(route('goals.destroy', id), { preserveScroll: true });
    }
}

const progressColor = (p) =>
    p >= 100 ? 'bg-emerald-500' : p >= 50 ? 'bg-brand-600' : p >= 25 ? 'bg-amber-500' : 'bg-ink-300';

function statusLabel(g) {
    if (g.progress >= 100) return 'Objectif atteint';
    if (g.days_left !== null && g.days_left < 0) return 'Échéance dépassée';
    return 'En cours';
}
</script>

<template>
    <Head title="Objectifs" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-display text-2xl font-semibold text-ink-900">Objectifs d'épargne</h2>
                    <p class="mt-1 text-sm text-ink-500">Suivez ce qui compte à long terme</p>
                </div>
                <button type="button" class="btn-primary text-sm shrink-0" @click="showForm = !showForm">
                    {{ showForm ? 'Annuler' : 'Nouvel objectif' }}
                </button>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">
            <div v-if="showForm" class="card p-6">
                <h3 class="mb-4 font-semibold text-ink-800">Nouvel objectif</h3>
                <form @submit.prevent="submit" class="grid grid-cols-1 items-end gap-4 md:grid-cols-2">
                    <div>
                        <label class="label">Nom</label>
                        <input v-model="form.name" type="text" required class="input mt-1" placeholder="Ex: Fonds d'urgence" />
                    </div>
                    <div>
                        <label class="label">Montant cible</label>
                        <input v-model="form.target_amount" type="number" step="0.01" min="1" required class="input mt-1" />
                    </div>
                    <div>
                        <label class="label">Déjà épargné</label>
                        <input v-model="form.current_amount" type="number" step="0.01" min="0" class="input mt-1" />
                    </div>
                    <div>
                        <label class="label">Date cible (optionnelle)</label>
                        <input v-model="form.deadline" type="date" class="input mt-1" />
                    </div>
                    <button type="submit" class="btn-primary md:col-span-2" :disabled="form.processing">Créer l'objectif</button>
                </form>
            </div>

            <div v-if="goals.length > 0" class="grid grid-cols-1 gap-4">
                <div v-for="g in goals" :key="g.id" class="card p-6">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-ink-900">{{ g.name }}</p>
                            <p class="mt-0.5 text-xs text-ink-500">
                                {{ formatMoney(g.current_amount) }} / {{ formatMoney(g.target_amount) }}
                                <span v-if="g.days_left !== null && g.days_left >= 0"> · {{ g.days_left }} jours restants</span>
                                <span v-if="g.days_left !== null && g.days_left < 0" class="text-red-500"> · Échéance dépassée</span>
                            </p>
                            <p class="mt-1 text-2xs font-semibold uppercase tracking-wide text-brand-700">{{ statusLabel(g) }}</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" class="btn-ghost text-xs" @click="addFunds(g)">Ajouter</button>
                            <button type="button" class="text-xs text-red-500 hover:text-red-600" @click="destroy(g.id)">Supprimer</button>
                        </div>
                    </div>
                    <div v-if="showAddFunds === g.id" class="mb-3 flex gap-2">
                        <input v-model="fundForm.current_amount" type="number" step="0.01" min="0" placeholder="Montant total actuel" class="input flex-1" />
                        <button type="button" class="btn-primary text-xs" :disabled="fundForm.processing" @click="saveFunds(g)">Sauvegarder</button>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-surface-100">
                        <div :class="['h-full rounded-full transition-all duration-500', progressColor(g.progress)]" :style="{ width: Math.min(g.progress, 100) + '%' }" />
                    </div>
                    <div class="mt-1 flex justify-between text-xs">
                        <span class="font-medium text-ink-600">{{ g.progress }}%</span>
                        <span v-if="g.remaining > 0" class="text-ink-500">{{ formatMoney(g.remaining) }} restant</span>
                        <span v-else class="font-medium text-emerald-600">Atteint</span>
                    </div>
                </div>
            </div>

            <EmptyState v-else title="Aucun objectif" description="Créez un objectif pour visualiser votre progression.">
                <template #action>
                    <button type="button" class="btn-primary text-sm" @click="showForm = true">Créer votre premier objectif</button>
                </template>
            </EmptyState>

            <Link :href="route('dashboard')" class="block text-center text-sm text-ink-400 hover:text-ink-600">Retour au tableau de bord</Link>
        </div>
    </AuthenticatedLayout>
</template>
