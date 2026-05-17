<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    headers: Array,
    preview: Array,
    total_rows: Number,
    bank: String,
    path: String,
});

const importForm = useForm({
    path: props.path,
    bank: props.bank,
    column_map: null,
});

function confirmImport() {
    importForm.post(route('csv-import.import'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Aperçu CSV" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight">📋 Aperçu de l'importation</h2>
                <span class="text-sm text-gray-500">{{ total_rows }} transactions détectées</span>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-semibold text-gray-700 mb-4">Aperçu des {{ preview.length }} premières lignes</h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th v-for="(h, i) in headers" :key="i" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ h }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(row, ri) in preview" :key="ri" class="border-t border-gray-100">
                                    <td v-for="(val, ci) in row" :key="ci" class="px-4 py-2 text-gray-600">
                                        {{ val }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-xs text-gray-400 mt-4">
                        Format détecté : <strong>{{ bank === 'generic' ? 'Générique' : bank }}</strong>
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex gap-4">
                    <button @click="confirmImport" :disabled="importForm.processing"
                        class="flex-1 py-3 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 disabled:opacity-50 transition-all">
                        {{ importForm.processing ? 'Importation en cours...' : `✅ Importer ${total_rows} transactions` }}
                    </button>
                    <Link :href="route('csv-import.index')"
                        class="py-3 px-6 border border-gray-300 rounded-xl text-gray-600 font-medium hover:bg-gray-50 text-center">
                        ← Modifier
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
