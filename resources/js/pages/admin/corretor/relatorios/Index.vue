<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Link } from '@inertiajs/vue3';
import { TrendingUp, Users, DollarSign } from 'lucide-vue-next';

defineProps<{
    relatorios: any[];
}>();

const tiposRelatorio = [
    {
        titulo: 'Relatório de Vendas',
        descricao: 'Análise completa de vendas e aluguéis',
        icon: TrendingUp,
        route: 'admin.corretor.relatorios.vendas',
        color: 'bg-blue-500',
    },
    {
        titulo: 'Relatório de Leads',
        descricao: 'Funil de conversão e performance de leads',
        icon: Users,
        route: 'admin.corretor.relatorios.leads',
        color: 'bg-green-500',
    },
    {
        titulo: 'Relatório Financeiro',
        descricao: 'Visão financeira e comissões',
        icon: DollarSign,
        route: 'admin.corretor.relatorios.financeiro',
        color: 'bg-purple-500',
    },
];
</script>

<template>
    <AuthLayout title="Relatórios">
        <div class="space-y-6">
            <h2 class="text-2xl font-bold text-gray-800">Relatórios</h2>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="relatorio in tiposRelatorio"
                    :key="relatorio.route"
                    :href="route(relatorio.route)"
                    class="flex flex-col rounded-lg bg-white p-6 shadow transition hover:shadow-lg"
                >
                    <div :class="[relatorio.color, 'mb-4 flex h-12 w-12 items-center justify-center rounded-lg']">
                        <component :is="relatorio.icon" class="h-6 w-6 text-white" />
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-800">{{ relatorio.titulo }}</h3>
                    <p class="text-sm text-gray-600">{{ relatorio.descricao }}</p>
                </Link>
            </div>
        </div>
    </AuthLayout>
</template>
