<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    brandName?: string;
    showCorretorSubtitle?: boolean;
}>();

const isMenuOpen = ref(false);

const navItems = [
    { label: 'Início', href: '/' },
    { label: 'Funcionalidades', href: '#funcionalidades' },
    { label: 'Automação IA', href: '#automacao-ia' },
    { label: 'Planos', href: '#planos' },
    { label: 'Módulo Corretor', href: '/corretor' },
    { label: 'Contato', href: '#contato' },
];

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const closeMenu = () => {
    isMenuOpen.value = false;
};
</script>

<template>
    <header
        class="fixed left-0 right-0 top-0 z-50 border-b transition-colors"
        style="background-color: var(--landing-bg); border-color: var(--border);"
    >
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <Link
                href="/"
                class="flex items-center gap-2 font-bold"
                style="color: var(--landing-foreground);"
            >
                {{ brandName ?? 'NexaSystem E-Grocery' }}
                <span
                    v-if="showCorretorSubtitle"
                    class="text-sm font-normal opacity-80"
                    style="color: var(--landing-muted);"
                >
                    — Módulo Corretor
                </span>
            </Link>

            <!-- Desktop nav -->
            <nav class="hidden items-center gap-8 md:flex">
                <template v-for="item in navItems" :key="item.href">
                    <Link
                        v-if="item.href.startsWith('/')"
                        :href="item.href"
                        class="text-sm font-medium transition hover:opacity-80"
                        style="color: var(--landing-foreground);"
                    >
                        {{ item.label }}
                    </Link>
                    <a
                        v-else
                        :href="item.href"
                        class="text-sm font-medium transition hover:opacity-80"
                        style="color: var(--landing-foreground);"
                    >
                        {{ item.label }}
                    </a>
                </template>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                <Link
                    href="/login"
                    class="rounded-lg border px-4 py-2 text-sm font-medium transition hover:opacity-90"
                    style="border-color: var(--landing-foreground); color: var(--landing-foreground);"
                    @click="closeMenu"
                >
                    Entrar
                </Link>
                <Link
                    href="/register"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition hover:opacity-90"
                    style="background-color: var(--primary); color: var(--primary-foreground);"
                    @click="closeMenu"
                >
                    Testar Grátis
                </Link>
            </div>

            <!-- Mobile menu button -->
            <button
                type="button"
                class="inline-flex p-2 md:hidden"
                style="color: var(--landing-foreground);"
                aria-label="Abrir menu"
                @click="toggleMenu"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                        v-if="!isMenuOpen"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"
                    />
                    <path
                        v-else
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <!-- Mobile nav -->
        <div
            v-show="isMenuOpen"
            class="border-t md:hidden"
            style="background-color: var(--landing-bg); border-color: var(--border);"
        >
            <nav class="flex flex-col gap-1 px-4 py-4">
                <template v-for="item in navItems" :key="item.href">
                    <Link
                        v-if="item.href.startsWith('/')"
                        :href="item.href"
                        class="rounded-lg px-3 py-2 text-sm font-medium"
                        style="color: var(--landing-foreground);"
                        @click="closeMenu"
                    >
                        {{ item.label }}
                    </Link>
                    <a
                        v-else
                        :href="item.href"
                        class="rounded-lg px-3 py-2 text-sm font-medium"
                        style="color: var(--landing-foreground);"
                        @click="closeMenu"
                    >
                        {{ item.label }}
                    </a>
                </template>
                <Link
                    href="/login"
                    class="rounded-lg border px-3 py-2 text-sm font-medium"
                    style="border-color: var(--landing-foreground); color: var(--landing-foreground);"
                    @click="closeMenu"
                >
                    Entrar
                </Link>
                <Link
                    href="/register"
                    class="rounded-lg px-3 py-2 text-sm font-medium"
                    style="background-color: var(--primary); color: var(--primary-foreground);"
                    @click="closeMenu"
                >
                    Testar Grátis
                </Link>
            </nav>
        </div>
    </header>
</template>
