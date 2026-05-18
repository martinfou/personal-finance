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
    importForm.post(route('csv-import.import'), { preserveScroll: true });
}
</script>

<template>
    <Head title="Aperçu CSV" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="font-display text-2xl font-semibold text-ink-900">Aperçu de l'importation</h2>
                    <p class="mt-1 text-sm text-ink-500">{{ total_rows }} transactions détectées</p>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-4xl space-y-6">
            <div class="card overflow-hidden p-6">
                <h3 class="mb-4 font-semibold text-ink-800">Premières lignes ({{ preview.length }})</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-surface-100 bg-surface-75">
                                <th
                                    v-for="(h, i) in headers"
                                    :key="i"
                                    class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-ink-500"
                                >
                                    {{ h }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, ri) in preview" :key="ri" class="border-t border-surface-100">
                                <td v-for="(val, ci) in row" :key="ci" class="px-4 py-2 text-ink-600">{{ val }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-4 text-xs text-ink-500">
                    Format : <strong class="text-ink-800">{{ bank === 'generic' ? 'Générique' : bank }}</strong>
                </p>
            </div>

            <div class="flex flex-wrap gap-4">
                <button
                    type="button"
                    class="btn-primary flex-1 py-3"
                    :disabled="importForm.processing"
                    @click="confirmImport"
                >
                    {{ importForm.processing ? 'Importation…' : `Importer ${total_rows} transactions` }}
                </button>
                <Link :href="route('csv-import.index')" class="btn-secondary py-3 px-6">Modifier</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
