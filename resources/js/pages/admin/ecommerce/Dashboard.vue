<script setup lang="ts">
import DashboardAdmin from '@/components/admin/ecommerce/dashboard/DashboardAdmin.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ShoppingCart, Package, Users, TrendingUp } from 'lucide-vue-next';

const props = defineProps<{
    modulo?: string;
    dashboardData?: {
        valorVendido: Array<{ data: string; valor: number }>;
        produtosVendidos: Array<{ nome: string; quantidade: number }>;
        clientesPorMes: Array<{ label: string | number; total: number; tipo?: string }>;
        entregasPorDia: Array<{ data: string; total: number }>;
        historicoCompras: {
            data: Array<unknown>;
            current_page: number;
            last_page: number;
            prev_page_url: string | null;
            next_page_url: string | null;
        };
    };
    periodoValor?: string;
    periodoEntregas?: string;
    periodoProdutos?: string;
    periodoClientes?: string;
}>();

const quickLinks = [
    {
        title: 'Pedidos',
        description: 'Gerenciar pedidos de clientes',
        icon: ShoppingCart,
        href: route('admin.ecommerce.pedidos.index'),
        color: 'bg-blue-500',
    },
    {
        title: 'Produtos',
        description: 'Cadastrar e editar produtos',
        icon: Package,
        href: route('admin.ecommerce.produtos.config'),
        color: 'bg-green-500',
    },
    {
        title: 'Clientes',
        description: 'Visualizar clientes cadastrados',
        icon: Users,
        href: route('admin.ecommerce.clientes.index'),
        color: 'bg-purple-500',
    },
    {
        title: 'Relatórios',
        description: 'Análise de vendas e métricas',
        icon: TrendingUp,
        href: '#',
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
                <!-- Quick Links -->
                <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <Link
                        v-for="link in quickLinks"
                        :key="link.title"
                        :href="link.href"
                        class="rounded-lg p-6 shadow transition-shadow hover:shadow-lg"
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
                    </Link>
                </div>

                <!-- Dashboard com gráficos quando há dados -->
                <DashboardAdmin
                    v-if="props.dashboardData && props.periodoValor !== undefined"
                    :dashboard-data="props.dashboardData"
                    :periodo-valor="props.periodoValor ?? '30'"
                    :periodo-entregas="props.periodoEntregas ?? '30'"
                    :periodo-produtos="props.periodoProdutos ?? '30'"
                    :periodo-clientes="props.periodoClientes ?? '30'"
                />

                <div v-else class="rounded-lg p-6 shadow">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        Bem-vindo ao Painel E-commerce
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Gerencie seus produtos, pedidos e clientes.
                    </p>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
