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
            backgroundColor: Array((props.data || []).length || 10).fill(getComputedStyle(document.documentElement).getPropertyValue('--hover-primary') || '#512388'),
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
    <div class="h-full w-full rounded-lg  p-4 shadow">
        <Doughnut :data="chartData" :options="chartOptions" />
    </div>
</template>
