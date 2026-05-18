<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});
</script>

<template>
    <Head title="Profil" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="font-display text-2xl font-semibold text-ink-900">Profil</h2>
                <p class="mt-1 text-sm text-ink-500">Compte et sécurité</p>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">
            <div class="card p-6 sm:p-8">
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" class="max-w-xl" />
            </div>
            <div class="card p-6 sm:p-8">
                <UpdatePasswordForm class="max-w-xl" />
            </div>
            <div class="card p-6 sm:p-8">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
