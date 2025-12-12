<script setup lang="ts">
import { BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps<{
    data: Array<{ data: string; valor: number }>;
    periodo: string;
}>();

const chartData = computed(() => ({
    labels: props.data.map((item) => item.data),
    datasets: [
        {
            label: 'Valor Vendido (R$)',
            backgroundColor: '#00a63e',
            borderColor: '#374151',
            borderWidth: 1,
            data: props.data.map((item) => item.valor),
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
            text: 'Valor Vendido',
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
                callback: function (value: any) {
                    return 'R$ ' + value.toFixed(2);
                },
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
