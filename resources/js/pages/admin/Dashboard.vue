<script setup lang="ts">
import DashboardAdmin from '@/components/admin/dashboard/DashboardAdmin.vue';
import DashboardClient from '@/components/app/dashboard/DashboardClient.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const user = computed(() => page.props.auth?.user || null);

const userCargo = user.value.cargo_id;

defineProps<{
    dashboardData?: {
        valorVendido: Array<{ data: string; valor: number }>;
        produtosVendidos: Array<{ nome: string; quantidade: number }>;
        clientesPorMes: Array<{ label: string | number; total: number; tipo?: string }>;
        entregasPorDia: Array<{ data: string; total: number }>;
    };
    periodoValor?: string;
    periodoEntregas?: string;
    periodoProdutos?: string;
    periodoClientes?: string;
}>();
</script>

<template>
    <Head>
        <title>Família Mogi - Dashboard</title>
        <meta name="description" content="Dashboard da Família Mogi" />
    </Head>

    <component :is="userCargo === 1 ? AuthLayout : userCargo === 2 ? AppLayout : 'div'">
        <div class="flex min-h-screen justify-center px-4 py-12 text-black sm:px-6 lg:px-8">
            <div class="w-full">
                <DashboardAdmin
                    v-if="userCargo === 1 && dashboardData"
                    :dashboard-data="dashboardData"
                    :periodo-valor="periodoValor || '30'"
                    :periodo-entregas="periodoEntregas || '30'"
                    :periodo-produtos="periodoProdutos || '30'"
                    :periodo-clientes="periodoClientes || '30'"
                />
                <DashboardClient v-else-if="userCargo === 2" />
                <div v-else>Usuário sem dashboard definido.</div>
            </div>
        </div>
    </component>
</template>
