<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Building2, Users, Phone, DollarSign, Mail, BarChart3, CalendarDays, TrendingUp } from 'lucide-vue-next';

defineProps<{
    modulo: string;
}>();

// Cards de estatísticas (placeholder - integrar com dados reais futuramente)
const stats = [
    { label: 'Imóveis Ativos', value: '0', icon: Building2, color: 'bg-blue-500' },
    { label: 'Leads do Mês', value: '0', icon: Users, color: 'bg-green-500' },
    { label: 'Visitas Agendadas', value: '0', icon: CalendarDays, color: 'bg-yellow-500' },
    { label: 'Vendas/Aluguéis', value: '0', icon: TrendingUp, color: 'bg-purple-500' },
];
</script>

<template>
    <AuthLayout title="Dashboard - Corretor de Imóveis">
        <div class="space-y-6">
            <!-- Header -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h2 class="text-xl font-semibold text-gray-800">Bem-vindo ao Painel do Corretor</h2>
                <p class="mt-1 text-gray-600">Gerencie seus imóveis, leads e agendamentos de visitas.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="stat in stats"
                    :key="stat.label"
                    class="flex items-center rounded-lg bg-white p-6 shadow"
                >
                    <div :class="[stat.color, 'flex h-12 w-12 items-center justify-center rounded-lg']">
                        <component :is="stat.icon" class="h-6 w-6 text-white" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">{{ stat.label }}</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stat.value }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Ações Rápidas</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        :href="route('admin.corretor.imoveis.create')"
                        class="flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-4 text-gray-600 transition hover:border-blue-500 hover:text-blue-500"
                    >
                        <Building2 class="mr-2 h-5 w-5" />
                        Cadastrar Imóvel
                    </Link>

                    <Link
                        :href="route('admin.corretor.leads.create')"
                        class="flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-4 text-gray-600 transition hover:border-green-500 hover:text-green-500"
                    >
                        <Users class="mr-2 h-5 w-5" />
                        Novo Lead
                    </Link>

                    <Link
                        :href="route('admin.corretor.contatos.create')"
                        class="flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-4 text-gray-600 transition hover:border-purple-500 hover:text-purple-500"
                    >
                        <Phone class="mr-2 h-5 w-5" />
                        Adicionar Contato
                    </Link>

                    <Link
                        :href="route('admin.corretor.financeiro.create')"
                        class="flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-4 text-gray-600 transition hover:border-yellow-500 hover:text-yellow-500"
                    >
                        <DollarSign class="mr-2 h-5 w-5" />
                        Novo Financiamento
                    </Link>

                    <Link
                        :href="route('admin.corretor.emails.create')"
                        class="flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-4 text-gray-600 transition hover:border-indigo-500 hover:text-indigo-500"
                    >
                        <Mail class="mr-2 h-5 w-5" />
                        Campanha de Email
                    </Link>

                    <Link
                        :href="route('admin.corretor.relatorios.index')"
                        class="flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-4 text-gray-600 transition hover:border-teal-500 hover:text-teal-500"
                    >
                        <BarChart3 class="mr-2 h-5 w-5" />
                        Ver Relatórios
                    </Link>
                </div>
            </div>

            <!-- Recent Activity Placeholder -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Atividade Recente</h3>
                <div class="flex items-center justify-center py-8 text-gray-400">
                    <p>Nenhuma atividade recente para exibir.</p>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
