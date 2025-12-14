<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import ChartClientes from './ChartClientes.vue';
import ChartEntregas from './ChartEntregas.vue';
import ChartProdutosVendidos from './ChartProdutosVendidos.vue';
import ChartValorVendido from './ChartValorVendido.vue';
import PeriodSelector from './PeriodSelector.vue';
import HistoricoDePedidos from './HistoricoDePedidos.vue';


const props = defineProps<{
    dashboardData: {
        valorVendido: Array<{ data: string; valor: number }>;
        produtosVendidos: Array<{ nome: string; quantidade: number }>;
        clientesPorMes: Array<{ label: string | number; total: number; tipo?: string }>;
        entregasPorDia: Array<{ data: string; total: number }>;

        historicoCompras: {
            data: Array<any>;
            current_page: number;
            last_page: number;
            prev_page_url: string | null;
            next_page_url: string | null;
        };
    };
    periodoValor: string;
    periodoEntregas: string;
    periodoProdutos: string;
    periodoClientes: string;
}>();

// =============================
// HISTÓRICO DE COMPRAS
// =============================
const historico = ref({
    data: [],
    current_page: 1,
    last_page: 1,
    next_page_url: null,
    prev_page_url: null,
});

// Atualiza inicial e quando o backend mudar
watch(
    () => props.dashboardData.historicoCompras,
    (novo) => {
        if (novo) historico.value = novo;
    },
    { immediate: true }
);

// Paginação
const carregarPagina = (url: string | null) => {
    if (!url) return;

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    });
};

// =============================
// PERÍODOS DOS GRÁFICOS
// =============================
const periodoValor = ref(props.periodoValor);
const periodoEntregas = ref(props.periodoEntregas);
const periodoProdutos = ref(props.periodoProdutos);
const periodoClientes = ref(props.periodoClientes);

watch(() => props.periodoValor, v => periodoValor.value = v);
watch(() => props.periodoEntregas, v => periodoEntregas.value = v);
watch(() => props.periodoProdutos, v => periodoProdutos.value = v);
watch(() => props.periodoClientes, v => periodoClientes.value = v);

const atualizarDashboard = () => {
    router.get(route("admin.dashboard"), {
        periodoValor: periodoValor.value,
        periodoEntregas: periodoEntregas.value,
        periodoProdutos: periodoProdutos.value,
        periodoClientes: periodoClientes.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Handlers dos selects
const handlePeriodoValor = v => { periodoValor.value = v; atualizarDashboard(); };
const handlePeriodoEntregas = v => { periodoEntregas.value = v; atualizarDashboard(); };
const handlePeriodoProdutos = v => { periodoProdutos.value = v; atualizarDashboard(); };
const handlePeriodoClientes = v => { periodoClientes.value = v; atualizarDashboard(); };
</script>

<template>
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard Administrativo</h1>

        <!-- GRÁFICOS -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <div class="space-y-2">
                <PeriodSelector v-model="periodoValor" @update:model-value="handlePeriodoValor" />
                <div class="h-80">
                    <ChartValorVendido :data="dashboardData.valorVendido" :periodo="periodoValor" />
                </div>
            </div>

            <div class="space-y-2">
                <PeriodSelector v-model="periodoEntregas" @update:model-value="handlePeriodoEntregas" />
                <div class="h-80">
                    <ChartEntregas :data="dashboardData.entregasPorDia" :periodo="periodoEntregas" />
                </div>
            </div>

            <div class="space-y-2">
                <PeriodSelector v-model="periodoProdutos" @update:model-value="handlePeriodoProdutos" />
                <div class="h-80">
                    <ChartProdutosVendidos :data="dashboardData.produtosVendidos" :periodo="periodoProdutos" />
                </div>
            </div>

            <div class="space-y-2">
                <PeriodSelector v-model="periodoClientes" @update:model-value="handlePeriodoClientes" />
                <div class="h-80">
                    <ChartClientes :data="dashboardData.clientesPorMes" :periodo="periodoClientes" />
                </div>
            </div>
        </div>
            <HistoricoDePedidos :historico="historico" :carregar-pagina="carregarPagina"/>
    </div>
</template>
