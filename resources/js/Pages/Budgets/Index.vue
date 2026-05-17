<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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

function setCategory(id) { form.category_id = id; }

function submit() {
    form.post(route('budgets.store'), {
        preserveScroll: true,
        onSuccess: () => { showForm.value = false; form.reset('limit_amount'); },
    });
}

function remove(id) {
    if (confirm('Supprimer ce budget ?'))
        router.delete(route('budgets.destroy', id), { preserveScroll: true });
}

function changeMonth(delta) {
    let m = props.month + delta;
    let y = props.year;
    if (m > 12) { m = 1; y++; }
    if (m < 1) { m = 12; y--; }
    router.get('/budgets', { year: y, month: m }, { preserveState: true, replace: true });
}

function f(n) { return '$' + Number(n).toFixed(2); }

const unusedCategories = ref(props.categories.filter(c =>
    !props.budgets.some(b => b.category_id === c.id)
));

const barColor = (pct) =>
    pct >= 100 ? 'bg-red-500' :
    pct >= 75  ? 'bg-amber-500' :
    pct >= 50  ? 'bg-indigo-500' :
                 'bg-emerald-500';
</script>

<template>
    <Head title="Budgets" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-900">🎯 Budgets</h2>
                <button @click="showForm = !showForm"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-all shadow-sm">
                    <span class="text-lg leading-none">+</span>
                    <span>Nouveau budget</span>
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Month nav -->
                <div class="flex items-center justify-center gap-4">
                    <button @click="changeMonth(-1)" class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-500 transition-colors">←</button>
                    <span class="font-semibold text-base text-gray-700 min-w-[140px] text-center">{{ monthLabel }}</span>
                    <button @click="changeMonth(1)" class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-500 transition-colors">→</button>
                </div>

                <!-- Add form -->
                <div v-if="showForm" class="card p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Ajouter un budget</h3>
                    <form @submit.prevent="submit" class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Catégorie</label>
                            <select v-model="form.category_id" required class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option v-for="c in unusedCategories" :key="c.id" :value="c.id">{{ c.icon }} {{ c.name }}</option>
                            </select>
                        </div>
                        <div class="w-40">
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Limite ($)</label>
                            <input v-model="form.limit_amount" type="number" step="0.01" min="1" placeholder="0.00" required
                                class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <button type="submit" :disabled="form.processing"
                            class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 transition-all shadow-sm">
                            Enregistrer
                        </button>
                    </form>
                </div>

                <!-- Budget cards -->
                <div v-if="budgets.length > 0" class="space-y-4">
                    <div v-for="b in budgets" :key="b.id"
                        class="card p-5 card-hover">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ b.category_icon }}</span>
                                <div>
                                    <p class="font-semibold text-sm text-gray-800">{{ b.category_name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        <span :class="b.remaining >= 0 ? 'text-emerald-600' : 'text-red-500'">
                                            {{ f(b.spent) }}
                                        </span>
                                        / {{ f(b.limit) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span :class="['text-sm font-semibold', b.remaining >= 0 ? 'text-emerald-600' : 'text-red-500']">
                                    {{ b.remaining >= 0 ? 'Reste ' + f(b.remaining) : 'Dépassé ' + f(Math.abs(b.remaining)) }}
                                </span>
                                <button @click="remove(b.id)" class="text-gray-300 hover:text-red-400 text-sm transition-colors">✕</button>
                            </div>
                        </div>
                        <div class="w-full h-2.5 bg-gray-100 rounded-full overflow-hidden">
                            <div :class="['h-full rounded-full transition-all duration-500', barColor(b.percentage)]"
                                :style="{ width: Math.min(b.percentage, 100) + '%' }">
                            </div>
                        </div>
                        <div class="flex justify-between mt-1.5">
                            <span :class="['text-xs font-medium', b.percentage >= 100 ? 'text-red-500' : 'text-gray-400']">
                                {{ b.percentage }}%
                            </span>
                            <span class="text-xs text-gray-400">limite: {{ f(b.limit) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="text-center py-16 card">
                    <p class="text-4xl mb-3">🎯</p>
                    <p class="text-base font-medium text-gray-700">Aucun budget ce mois-ci</p>
                    <p class="text-sm text-gray-400 mt-1 mb-6">Fixez une limite par catégorie pour mieux contrôler vos dépenses</p>
                    <button @click="showForm = true" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-all shadow-sm">
                        Créer mon premier budget
                    </button>
                </div>

                <Link :href="route('dashboard')" class="block text-center text-sm text-gray-400 hover:text-gray-600 pt-2">← Retour au tableau de bord</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
