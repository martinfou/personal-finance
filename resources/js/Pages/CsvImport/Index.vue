<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ banks: Array });

const file = ref(null);
const selectedBank = ref('');
const dragOver = ref(false);
const uploadForm = useForm({ file: null, bank: '' });

function onFileSelect(e) {
    file.value = e.target.files[0];
}

function onDrop(e) {
    dragOver.value = false;
    const f = e.dataTransfer?.files[0];
    if (f && f.name.endsWith('.csv')) {
        file.value = f;
    }
}

function submit() {
    if (!file.value || !selectedBank.value) return;
    uploadForm.file = file.value;
    uploadForm.bank = selectedBank.value;
    uploadForm.post(route('csv-import.preview'), {
        preserveScroll: true,
    });
}

function formatBytes(bytes) {
    if (bytes < 1024) return bytes + ' B';
    return (bytes / 1024).toFixed(1) + ' KB';
}
</script>

<template>
    <Head title="Import CSV" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight">📥 Importation CSV</h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Step 1: Choose Bank -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-semibold text-gray-700 mb-1">1. Choisir votre banque</h3>
                    <p class="text-sm text-gray-400 mb-4">Sélectionnez votre institution financière pour le format du relevé</p>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <button v-for="bank in banks" :key="bank.id"
                            @click="selectedBank = bank.id"
                            :class="['p-4 rounded-xl border-2 text-left transition-all',
                                selectedBank === bank.id
                                    ? 'border-indigo-500 bg-indigo-50'
                                    : 'border-gray-200 hover:border-gray-300']">
                            <p class="font-medium text-sm">{{ bank.name }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ bank.is_generic ? 'Mappage manuel' : 'Format automatique' }}</p>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Upload File -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-semibold text-gray-700 mb-1">2. Téléverser le fichier CSV</h3>
                    <p class="text-sm text-gray-400 mb-4">Format .csv depuis votre banque en ligne</p>

                    <div @dragover.prevent="dragOver = true" @dragleave="dragOver = false" @drop.prevent="onDrop"
                        :class="['border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all',
                            dragOver ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300 hover:border-gray-400']"
                        @click="$refs.fileInput.click()">

                        <input ref="fileInput" type="file" accept=".csv" class="hidden" @change="onFileSelect" />

                        <div v-if="file">
                            <p class="text-3xl mb-2">📄</p>
                            <p class="font-medium text-sm">{{ file.name }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ formatBytes(file.size) }}</p>
                            <button @click.stop="file = null" class="text-xs text-red-500 mt-2">Retirer</button>
                        </div>
                        <div v-else>
                            <p class="text-3xl mb-2">📂</p>
                            <p class="text-sm text-gray-500">Glissez votre fichier CSV ici ou <span class="text-indigo-600 font-medium">parcourez</span></p>
                            <p class="text-xs text-gray-400 mt-1">Fichier .csv seulement, max 2 MB</p>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <button @click="submit" :disabled="!file || !selectedBank || uploadForm.processing"
                    class="w-full py-3 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    {{ uploadForm.processing ? 'Analyse en cours...' : 'Aperçu des transactions →' }}
                </button>

                <Link :href="route('transactions.index')" class="block text-center text-sm text-gray-400 hover:text-gray-600">
                    ← Retour aux transactions
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
