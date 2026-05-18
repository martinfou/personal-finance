<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import BrandMark from '@/Components/BrandMark.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const highlights = [
    {
        icon: 'chart-bar',
        title: 'Tableau de bord',
        description:
            'Revenus, dépenses et balance en un regard. Graphiques par catégorie pour comprendre où va votre argent.',
    },
    {
        icon: 'wallet',
        title: 'Budgets mensuels',
        description:
            'Des enveloppes par catégorie, des alertes avant le dépassement, une vision claire du mois en cours.',
    },
    {
        icon: 'arrow-down-tray',
        title: 'Import CSV',
        description:
            'Relevés CIBC Visa, Tangerine, ScotiaBank et autres. Catégorisation assistée pour gagner du temps chaque mois.',
    },
];

const capabilities = [
    { icon: 'arrow-path', title: 'Transactions récurrentes', description: 'Abonnements et revenus appliqués automatiquement.' },
    { icon: 'flag', title: "Objectifs d'épargne", description: 'Suivez votre progression vers ce qui compte vraiment.' },
    { icon: 'receipt', title: 'Historique complet', description: 'Ajoutez, modifiez et filtrez chaque transaction.' },
];
</script>

<template>
    <Head title="Accueil" />

    <div class="min-h-screen bg-surface-50">
        <nav class="sticky top-0 z-40 border-b border-surface-100 bg-surface-50">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/">
                    <BrandMark />
                </Link>
                <nav v-if="canLogin" class="flex items-center gap-2">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="btn-primary text-sm"
                    >
                        Tableau de bord
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="btn-ghost text-sm">
                            Connexion
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="btn-primary text-sm"
                        >
                            Créer un compte
                        </Link>
                    </template>
                </nav>
            </div>
        </nav>

        <section class="mx-auto max-w-6xl px-4 pb-20 pt-16 sm:px-6 sm:pt-24 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-widest text-brand-700">
                    Finances personnelles
                </p>
                <h1 class="mt-4 font-display text-4xl font-semibold text-ink-900 sm:text-5xl lg:text-[3.25rem] lg:leading-[1.1]">
                    Maîtrisez votre budget avec calme et précision
                </h1>
                <p class="mt-6 max-w-xl text-lg text-ink-600">
                    Suivez revenus et dépenses, fixez des budgets, importez vos relevés.
                    Un outil sobre, privé, conçu pour le quotidien.
                </p>
                <div class="mt-10 flex flex-wrap items-center gap-3">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="route('register')"
                        class="btn-primary px-7 py-3 text-base"
                    >
                        Commencer gratuitement
                    </Link>
                    <Link
                        :href="$page.props.auth.user ? route('dashboard') : route('login')"
                        class="btn-secondary px-7 py-3 text-base"
                    >
                        {{ $page.props.auth.user ? 'Ouvrir le tableau de bord' : 'J\'ai déjà un compte' }}
                    </Link>
                </div>
            </div>

            <div class="mt-20 grid gap-6 md:grid-cols-3">
                <article
                    v-for="(item, index) in highlights"
                    :key="item.title"
                    :class="[
                        index === 0
                            ? 'rounded-2xl bg-ink-900 p-8 text-white shadow-lift md:p-10'
                            : 'card p-8',
                    ]"
                >
                    <span
                        :class="[
                            'inline-flex h-11 w-11 items-center justify-center rounded-xl',
                            index === 0 ? 'bg-white/10 text-white' : 'bg-brand-50 text-brand-700',
                        ]"
                    >
                        <AppIcon :name="item.icon" icon-class="h-5 w-5" />
                    </span>
                    <h2
                        :class="[
                            'mt-6 font-display text-xl font-semibold',
                            index === 0 ? 'text-2xl text-white' : 'text-ink-900',
                        ]"
                    >
                        {{ item.title }}
                    </h2>
                    <p
                        :class="[
                            'mt-3 text-sm leading-relaxed',
                            index === 0 ? 'text-ink-300' : 'text-ink-600',
                        ]"
                    >
                        {{ item.description }}
                    </p>
                </article>
            </div>

            <div class="mt-16 rounded-2xl border border-surface-100 bg-white px-6 py-2 shadow-soft sm:px-10">
                <div
                    v-for="item in capabilities"
                    :key="item.title"
                    class="feature-row"
                >
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-surface-75 text-ink-600"
                    >
                        <AppIcon :name="item.icon" icon-class="h-5 w-5" />
                    </span>
                    <div>
                        <h3 class="font-semibold text-ink-900">{{ item.title }}</h3>
                        <p class="mt-1 text-sm text-ink-500">{{ item.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-surface-100 py-10">
            <div class="mx-auto max-w-6xl px-4 text-center text-sm text-ink-400 sm:px-6 lg:px-8">
                Mon Budget — Application de finances personnelles
            </div>
        </footer>
    </div>
</template>
