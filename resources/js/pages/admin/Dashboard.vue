<script setup lang="ts">
<<<<<<< HEAD
=======
import DashboardAdmin from '@/components/admin/dashboard/DashboardAdmin.vue';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
import DashboardClient from '@/components/app/dashboard/DashboardClient.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
<<<<<<< HEAD
=======
import { computed } from 'vue';
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f

const page = usePage();


// Use Inertia page props directly and cast to `any` to avoid `unknown` typing issues
const pp = page.props as any;
const user = pp.auth?.user ?? pp.user ?? {};
const userCargo = user?.cargo_id ?? 0;

const dashboardData = pp.dashboardData as any;
const periodoValor = pp.periodoValor;
const periodoEntregas = pp.periodoEntregas;
const periodoProdutos = pp.periodoProdutos;
const periodoClientes = pp.periodoClientes;

<<<<<<< HEAD
=======
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
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
</script>

<template>
    <Head>
        <title>Nexa - Dashboard</title>
        <meta name="description" content="Dashboard da Nexa" />
    </Head>

    <component :is="userCargo === 1 ? AuthLayout : userCargo === 2 ? AppLayout : 'div'">
        <div class="flex min-h-screen justify-center px-4 py-12 text-black sm:px-6 lg:px-8">
            <div class="w-full">
<<<<<<< HEAD
                <!-- <DashboardAdmin
=======
                <DashboardAdmin
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
                    v-if="userCargo === 1 && dashboardData"
                    :dashboard-data="dashboardData"
                    :periodo-valor="periodoValor || '30'"
                    :periodo-entregas="periodoEntregas || '30'"
                    :periodo-produtos="periodoProdutos || '30'"
                    :periodo-clientes="periodoClientes || '30'"
<<<<<<< HEAD
                /> -->
                <!-- <DashboardClient v-else-if="userCargo === 2" />
                <div v-else>Usuário sem dashboard definido.</div> -->
=======
                />
                <DashboardClient v-else-if="userCargo === 2" />
                <div v-else>Usuário sem dashboard definido.</div>
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
            </div>
        </div>
    </component>
</template>
