<script setup lang="ts">
import { ArcElement, Chart as ChartJS, Legend, Title, Tooltip } from 'chart.js';
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';

ChartJS.register(Title, Tooltip, Legend, ArcElement);

const props = defineProps<{
    data: Array<{ nome: string; quantidade: number }>;
    periodo: string;
}>();

const chartData = computed(() => ({
    labels: props.data.map((item) => item.nome),
    datasets: [
        {
            label: 'Quantidade Vendida',
            backgroundColor: ['#00a63e', '#006E2A', '#008234', '#00973A', '#00A63E', '#25B755', '#52C873', '#7ED993', '#A9E5B3', '#D4F2D9'],
            data: props.data.map((item) => item.quantidade),
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'bottom' as const,
        },
        title: {
            display: true,
            text: 'Produtos Vendidos',
            font: {
                size: 16,
                weight: 'bold' as const,
            },
        },
        tooltip: {
            callbacks: {
                label: function (context: any) {
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    return `${label}: ${value} unidades`;
                },
            },
        },
    },
};
</script>

<template>
    <div class="h-full w-full rounded-lg bg-white p-4 shadow">
        <Doughnut :data="chartData" :options="chartOptions" />
    </div>
</template>
