<script setup>
import AppIcon from '@/Components/AppIcon.vue';
import BrandMark from '@/Components/BrandMark.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const showingNavigationDropdown = ref(false);

const navItems = [
    { route: 'dashboard', match: 'dashboard', icon: 'chart-bar', label: 'Tableau de bord' },
    { route: 'transactions.index', match: 'transactions*', icon: 'receipt', label: 'Transactions' },
    { route: 'csv-import.index', match: 'csv-import*', icon: 'arrow-down-tray', label: 'Import CSV' },
    { route: 'recurring.index', match: 'recurring*', icon: 'arrow-path', label: 'Récurrent' },
    { route: 'goals.index', match: 'goals*', icon: 'flag', label: 'Objectifs' },
    { route: 'budgets.index', match: 'budgets*', icon: 'wallet', label: 'Budgets' },
];
</script>

<template>
    <div class="min-h-screen bg-surface-50">
        <nav class="sticky top-0 z-40 border-b border-surface-100 bg-surface-50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between gap-4">
                    <div class="flex min-w-0 flex-1 items-center gap-6">
                        <Link :href="route('dashboard')" class="shrink-0">
                            <BrandMark wrapper-class="hidden sm:inline-flex" />
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-ink-900 text-white sm:hidden">
                                <AppIcon name="logo" icon-class="h-5 w-5" />
                            </span>
                        </Link>

                        <div class="hidden items-center gap-0.5 lg:flex">
                            <NavLink
                                v-for="item in navItems"
                                :key="item.route"
                                :href="route(item.route)"
                                :active="route().current(item.match)"
                            >
                                <AppIcon :name="item.icon" icon-class="h-4 w-4 shrink-0" />
                                {{ item.label }}
                            </NavLink>
                        </div>
                    </div>

                    <div class="hidden items-center sm:flex">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-xl border border-surface-150 bg-white px-3 py-2 text-sm font-medium text-ink-600 shadow-sm hover:border-ink-200 hover:text-ink-900"
                                >
                                    {{ $page.props.auth.user.name }}
                                    <svg class="h-4 w-4 text-ink-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Profil</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Déconnexion
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <button
                        class="inline-flex items-center justify-center rounded-lg p-2 text-ink-500 hover:bg-surface-100 lg:hidden"
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                    >
                        <span class="sr-only">Menu</span>
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path
                                :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                            <path
                                :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <div v-show="showingNavigationDropdown" class="border-t border-surface-100 lg:hidden">
                <div class="space-y-1 px-4 py-3">
                    <ResponsiveNavLink
                        v-for="item in navItems"
                        :key="item.route"
                        :href="route(item.route)"
                        :active="route().current(item.match)"
                    >
                        <AppIcon :name="item.icon" icon-class="h-5 w-5" />
                        {{ item.label }}
                    </ResponsiveNavLink>
                </div>
                <div class="border-t border-surface-100 px-4 py-4">
                    <p class="text-sm font-medium text-ink-900">{{ $page.props.auth.user.name }}</p>
                    <p class="text-xs text-ink-500">{{ $page.props.auth.user.email }}</p>
                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')">Profil</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                            Déconnexion
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <header v-if="$slots.header" class="mb-8">
                <slot name="header" />
            </header>
            <slot />
        </main>
    </div>
</template>
