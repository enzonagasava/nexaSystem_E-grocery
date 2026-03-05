<script setup lang="ts">
import { BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps<{
    data: Array<{ label: string | number; total: number; tipo?: string }>;
    periodo: string;
}>();

const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

const chartData = computed(() => ({
    labels: props.data.map((item) => {
        if (item.tipo === 'mes' && typeof item.label === 'number') {
            return meses[item.label - 1];
        }
        return item.label;
    }),
    datasets: [
        {
            label: 'Novos Clientes',
            backgroundColor: '#00a63e',
            borderColor: '#4B5563',
            borderWidth: 1,
            data: props.data.map((item) => item.total),
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top' as const,
        },
        title: {
            display: true,
            text: 'Novos Clientes',
            font: {
                size: 16,
                weight: 'bold' as const,
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
            },
        },
    },
};
</script>

<template>
    <div class="h-full w-full rounded-lg bg-white p-4 shadow">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>
