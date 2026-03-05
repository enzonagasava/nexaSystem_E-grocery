<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    brandName?: string;
}>();

const currentYear = new Date().getFullYear();

const productLinks = [
    { label: 'Funcionalidades', href: '#funcionalidades' },
    { label: 'Automação IA', href: '#automacao-ia' },
    { label: 'Planos', href: '#planos' },
];

const companyLinks = [
    { label: 'Sobre Nós', href: '/about-us' },
    { label: 'Blog', href: '#' },
    { label: 'Carreiras', href: '#' },
];

const supportLinks = [
    { label: 'Central de Ajuda', href: '#' },
    { label: 'Termos de Uso', href: '#' },
    { label: 'Política de Privacidade', href: '#' },
];
</script>

<template>
    <footer
        id="contato"
        class="border-t"
        style="background-color: var(--landing-bg); border-color: var(--border);"
    >
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <Link
                        href="/"
                        class="text-lg font-bold"
                        style="color: var(--landing-foreground);"
                    >
                        {{ brandName ?? 'NexaSystem' }}
                    </Link>
                    <p
                        class="mt-2 max-w-xs text-sm"
                        style="color: var(--landing-muted);"
                    >
                        CRM com IA para automatizar suas vendas e conquistar mais clientes.
                    </p>
                </div>
                <div>
                    <h3
                        class="text-sm font-semibold uppercase tracking-wider"
                        style="color: var(--landing-foreground);"
                    >
                        Produto
                    </h3>
                    <ul class="mt-4 space-y-2">
                        <li v-for="link in productLinks" :key="link.href">
                            <a
                                :href="link.href"
                                class="text-sm transition hover:opacity-80"
                                style="color: var(--landing-muted);"
                            >
                                {{ link.label }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h3
                        class="text-sm font-semibold uppercase tracking-wider"
                        style="color: var(--landing-foreground);"
                    >
                        Empresa
                    </h3>
                    <ul class="mt-4 space-y-2">
                        <li v-for="link in companyLinks" :key="link.href">
                            <Link
                                v-if="link.href !== '#'"
                                :href="link.href"
                                class="text-sm transition hover:opacity-80"
                                style="color: var(--landing-muted);"
                            >
                                {{ link.label }}
                            </Link>
                            <a
                                v-else
                                href="#"
                                class="text-sm transition hover:opacity-80"
                                style="color: var(--landing-muted);"
                            >
                                {{ link.label }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h3
                        class="text-sm font-semibold uppercase tracking-wider"
                        style="color: var(--landing-foreground);"
                    >
                        Suporte
                    </h3>
                    <ul class="mt-4 space-y-2">
                        <li v-for="link in supportLinks" :key="link.href">
                            <a
                                :href="link.href"
                                class="text-sm transition hover:opacity-80"
                                style="color: var(--landing-muted);"
                            >
                                {{ link.label }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div
                class="mt-8 border-t pt-8 text-center text-sm"
                style="border-color: var(--border); color: var(--landing-muted);"
            >
                © {{ currentYear }} {{ brandName ?? 'NexaSystem' }}. Todos os direitos reservados.
            </div>
        </div>
    </footer>
</template>
