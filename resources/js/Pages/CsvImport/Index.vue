<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ banks: Array });

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
    uploadForm.post(route('csv-import.preview'), { preserveScroll: true });
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
            <div>
                <h2 class="font-display text-2xl font-semibold text-ink-900">Importation CSV</h2>
                <p class="mt-1 text-sm text-ink-500">Relevés bancaires canadiens</p>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">
            <div class="card p-6">
                <h3 class="font-semibold text-ink-800">1. Choisir votre banque</h3>
                <p class="mt-1 text-sm text-ink-500">Format du relevé selon l'institution</p>
                <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                    <button
                        v-for="bank in banks"
                        :key="bank.id"
                        type="button"
                        :class="[
                            'rounded-xl border-2 p-4 text-left transition-colors',
                            selectedBank === bank.id
                                ? 'border-brand-600 bg-brand-50'
                                : 'border-surface-150 hover:border-ink-200',
                        ]"
                        @click="selectedBank = bank.id"
                    >
                        <p class="text-sm font-semibold text-ink-900">{{ bank.name }}</p>
                        <p class="mt-1 text-xs text-ink-500">{{ bank.is_generic ? 'Mappage manuel' : 'Format automatique' }}</p>
                    </button>
                </div>
            </div>

            <div class="card p-6">
                <h3 class="font-semibold text-ink-800">2. Téléverser le fichier</h3>
                <p class="mt-1 text-sm text-ink-500">Fichier .csv exporté de votre banque en ligne</p>
                <div
                    class="mt-4 cursor-pointer rounded-xl border-2 border-dashed p-8 text-center transition-colors"
                    :class="dragOver ? 'border-brand-500 bg-brand-50' : 'border-surface-150 hover:border-ink-300'"
                    @dragover.prevent="dragOver = true"
                    @dragleave="dragOver = false"
                    @drop.prevent="onDrop"
                    @click="$refs.fileInput.click()"
                >
                    <input ref="fileInput" type="file" accept=".csv" class="hidden" @change="onFileSelect" />
                    <div v-if="file">
                        <AppIcon name="arrow-down-tray" icon-class="mx-auto h-8 w-8 text-brand-700" />
                        <p class="mt-3 text-sm font-semibold text-ink-900">{{ file.name }}</p>
                        <p class="mt-1 text-xs text-ink-500">{{ formatBytes(file.size) }}</p>
                        <button type="button" class="mt-2 text-xs font-medium text-red-500" @click.stop="file = null">Retirer</button>
                    </div>
                    <div v-else>
                        <AppIcon name="arrow-down-tray" icon-class="mx-auto h-8 w-8 text-ink-400" />
                        <p class="mt-3 text-sm text-ink-600">
                            Glissez votre CSV ici ou <span class="font-semibold text-brand-700">parcourez</span>
                        </p>
                        <p class="mt-1 text-xs text-ink-400">Max. 2 Mo</p>
                    </div>
                </div>
            </div>

            <button
                type="button"
                class="btn-primary w-full py-3"
                :disabled="!file || !selectedBank || uploadForm.processing"
                @click="submit"
            >
                {{ uploadForm.processing ? 'Analyse en cours…' : 'Aperçu des transactions' }}
            </button>

            <Link :href="route('transactions.index')" class="block text-center text-sm text-ink-400 hover:text-ink-600">
                Retour aux transactions
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
