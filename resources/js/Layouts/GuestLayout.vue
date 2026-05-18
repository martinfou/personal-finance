<script setup>
import BrandMark from '@/Components/BrandMark.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen lg:grid lg:grid-cols-2">
        <div
            class="relative hidden bg-ink-900 px-12 py-16 lg:flex lg:flex-col lg:justify-between"
        >
            <Link href="/" class="relative z-10">
                <BrandMark wrapper-class="[&_span]:text-white [&_.text-ink-400]:text-ink-300 [&_.bg-ink-900]:bg-white/10 [&_.bg-ink-900]:text-white" />
            </Link>

            <div class="relative z-10 max-w-md">
                <p class="font-display text-3xl font-semibold leading-snug text-white">
                    Vos finances, avec clarté et sérénité.
                </p>
                <p class="mt-4 text-sm leading-relaxed text-ink-300">
                    Suivez revenus et dépenses, fixez des budgets, importez vos relevés.
                    Un espace privé, pensé pour le quotidien.
                </p>
            </div>

            <p class="relative z-10 text-xs text-ink-400">
                &copy; {{ new Date().getFullYear() }} Mon Budget
            </p>
        </div>

        <div class="flex min-h-screen flex-col justify-center bg-surface-50 px-6 py-12 sm:px-12">
            <Link href="/" class="mb-10 lg:hidden">
                <BrandMark />
            </Link>

            <div class="mx-auto w-full max-w-md">
                <div class="card p-8 sm:p-10">
                    <slot />
                </div>
            </div>

            <p class="mt-8 text-center text-xs text-ink-400 lg:hidden">
                &copy; {{ new Date().getFullYear() }} Mon Budget
            </p>
        </div>
    </div>
</template>
