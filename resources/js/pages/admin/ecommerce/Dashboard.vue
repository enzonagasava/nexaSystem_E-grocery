<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ShoppingCart, Package, Users, TrendingUp } from 'lucide-vue-next';

defineProps<{
    modulo: string;
}>();

const quickLinks = [
    {
        title: 'Pedidos',
        description: 'Gerenciar pedidos de clientes',
        icon: ShoppingCart,
        href: '#', // Futuro: route('admin.ecommerce.pedidos.index')
        color: 'bg-blue-500',
    },
    {
        title: 'Produtos',
        description: 'Cadastrar e editar produtos',
        icon: Package,
        href: '#', // Futuro: route('admin.ecommerce.produtos.index')
        color: 'bg-green-500',
    },
    {
        title: 'Clientes',
        description: 'Visualizar clientes cadastrados',
        icon: Users,
        href: '#', // Futuro: route('admin.ecommerce.clientes.index')
        color: 'bg-purple-500',
    },
    {
        title: 'Relatórios',
        description: 'Análise de vendas e métricas',
        icon: TrendingUp,
        href: '#', // Futuro: route('admin.ecommerce.relatorios.index')
        color: 'bg-orange-500',
    },
];
</script>

<template>
    <Head>
        <title>E-commerce - Dashboard</title>
        <meta name="description" content="Dashboard do módulo E-commerce" />
    </Head>

    <AuthLayout>
        <div class="flex min-h-screen justify-center px-4 py-12 sm:px-6 lg:px-8">
            <div class="w-full">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Dashboard E-commerce
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Gerencie seus produtos, pedidos e clientes
                    </p>
                </div>

                <!-- Quick Links Grid -->
                <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="link in quickLinks"
                        :key="link.title"
                        class="rounded-lg bg-white p-6 shadow transition-shadow hover:shadow-lg dark:bg-gray-800"
                    >
                        <div class="flex items-center">
                            <div :class="[link.color, 'rounded-lg p-3']">
                                <component :is="link.icon" class="h-6 w-6 text-white" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ link.title }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ link.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Placeholder Content -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        Bem-vindo ao Painel E-commerce
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Este é o dashboard do módulo de e-commerce. Aqui você poderá
                        gerenciar produtos, pedidos, clientes e acompanhar métricas de vendas.
                    </p>
                    <div
                        class="mt-4 rounded-lg border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-600"
                    >
                        <p class="text-gray-500 dark:text-gray-400">
                            Os gráficos e estatísticas serão exibidos aqui quando houver dados.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
