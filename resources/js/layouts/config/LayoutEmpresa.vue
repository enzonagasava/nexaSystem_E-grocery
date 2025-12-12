<script setup lang="ts">
import { Button } from '@/components/ui/button';
import HeadingSmall from '@/components/ui/header/HeadingSmall.vue';
import { Separator } from '@/components/ui/separator';
import type { NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

const sidebarNavItems: NavItem[] = [
    { title: 'Informações da Empresa', href: '/empresa/config/geral' },
    { title: 'Logo', href: '/empresa/config/logo' },
    { title: 'Redes Sociais', href: '/empresa/config/redes-sociais' },
];

const page = usePage();
const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';
</script>

<template>
    <div>
        <HeadingSmall title="Configurações da Empresa" description="Gerencie os dados e preferências da sua empresa." />

        <div class="flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12">
            <!-- Sidebar -->
            <aside class="w-full max-w-xs lg:w-60">
                <nav class="flex flex-col space-y-1">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': currentPath === item.href }]"
                        as-child
                    >
                        <Link :href="item.href">{{ item.title }}</Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 md:hidden" />

            <!-- Conteúdo da página -->
            <div class="flex-1">
                <slot />
            </div>
        </div>
    </div>
</template>
