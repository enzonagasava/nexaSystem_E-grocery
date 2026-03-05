<script setup lang="ts">
import { BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps<{
    data: Array<{ data: string; total: number }>;
    periodo: string;
}>();

const chartData = computed(() => ({
    labels: props.data.map((item) => item.data),
    datasets: [
        {
            label: 'Entregas Realizadas',
            backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--hover-primary') || '#512388',
            borderColor: getComputedStyle(document.documentElement).getPropertyValue('--hover-primary') || '#512388',
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
            text: 'Entregas Realizadas',
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
    <div class="h-full w-full rounded-lg  p-4 shadow">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>
