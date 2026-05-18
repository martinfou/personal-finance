<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Connexion" />

        <h1 class="font-display text-2xl font-semibold text-ink-900">Connexion</h1>
        <p class="mt-1 text-sm text-ink-500">Accédez à votre espace Mon Budget</p>

        <div v-if="status" class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ status }}
        </div>

        <form class="mt-8 space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Courriel" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Mot de passe" />
                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="current-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center">
                <Checkbox name="remember" v-model:checked="form.remember" />
                <span class="ms-2 text-sm text-ink-600">Se souvenir de moi</span>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm font-medium text-brand-700 hover:text-brand-800"
                >
                    Mot de passe oublié ?
                </Link>
                <PrimaryButton class="ms-auto w-full sm:w-auto" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Se connecter
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
