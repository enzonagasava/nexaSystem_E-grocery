<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import ChartClientes from './ChartClientes.vue';
import ChartEntregas from './ChartEntregas.vue';
import ChartProdutosVendidos from './ChartProdutosVendidos.vue';
import ChartValorVendido from './ChartValorVendido.vue';
import PeriodSelector from './PeriodSelector.vue';

const props = defineProps<{
    dashboardData: {
        valorVendido: Array<{ data: string; valor: number }>;
        produtosVendidos: Array<{ nome: string; quantidade: number }>;
        clientesPorMes: Array<{ label: string | number; total: number; tipo?: string }>;
        entregasPorDia: Array<{ data: string; total: number }>;
    };
    periodoValor: string;
    periodoEntregas: string;
    periodoProdutos: string;
    periodoClientes: string;
}>();

// Inicializa com valores vindos do backend
const periodoValor = ref(props.periodoValor);
const periodoEntregas = ref(props.periodoEntregas);
const periodoProdutos = ref(props.periodoProdutos);
const periodoClientes = ref(props.periodoClientes);

// Atualiza refs quando props mudam
watch(
    () => props.periodoValor,
    (newVal) => (periodoValor.value = newVal),
);
watch(
    () => props.periodoEntregas,
    (newVal) => (periodoEntregas.value = newVal),
);
watch(
    () => props.periodoProdutos,
    (newVal) => (periodoProdutos.value = newVal),
);
watch(
    () => props.periodoClientes,
    (newVal) => (periodoClientes.value = newVal),
);

const atualizarDashboard = () => {
    router.get(
        route('admin.dashboard'),
        {
            periodoValor: periodoValor.value,
            periodoEntregas: periodoEntregas.value,
            periodoProdutos: periodoProdutos.value,
            periodoClientes: periodoClientes.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const handlePeriodoValor = (value: string) => {
    periodoValor.value = value;
    atualizarDashboard();
};

const handlePeriodoEntregas = (value: string) => {
    periodoEntregas.value = value;
    atualizarDashboard();
};

const handlePeriodoProdutos = (value: string) => {
    periodoProdutos.value = value;
    atualizarDashboard();
};

const handlePeriodoClientes = (value: string) => {
    periodoClientes.value = value;
    atualizarDashboard();
};
</script>

<template>
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard Administrativo</h1>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Gráfico de Valor Vendido -->
            <div class="space-y-2">
                <PeriodSelector v-model="periodoValor" @update:model-value="handlePeriodoValor" />
                <div class="h-80">
                    <ChartValorVendido :data="dashboardData.valorVendido" :periodo="periodoValor" />
                </div>
            </div>

            <!-- Gráfico de Entregas -->
            <div class="space-y-2">
                <PeriodSelector v-model="periodoEntregas" @update:model-value="handlePeriodoEntregas" />
                <div class="h-80">
                    <ChartEntregas :data="dashboardData.entregasPorDia" :periodo="periodoEntregas" />
                </div>
            </div>
            <!-- Gráfico de Produtos Vendidos -->
            <div class="space-y-2">
                <PeriodSelector v-model="periodoProdutos" @update:model-value="handlePeriodoProdutos" />
                <div class="h-80">
                    <ChartProdutosVendidos :data="dashboardData.produtosVendidos" :periodo="periodoProdutos" />
                </div>
            </div>

            <!-- Gráfico de Clientes -->
            <div class="space-y-2">
                <PeriodSelector v-model="periodoClientes" @update:model-value="handlePeriodoClientes" />
                <div class="h-80">
                    <ChartClientes :data="dashboardData.clientesPorMes" :periodo="periodoClientes" />
                </div>
            </div>
        </div>
    </div>
</template>
