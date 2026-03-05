<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import * as echarts from 'echarts';

interface DataPoint {
  name: string;
  value: number;
}

const props = defineProps<{
  data: DataPoint[];
  title?: string;
  height?: string;
  colors?: string[];
  showLegend?: boolean;
  showLabel?: boolean;
  cardless?: boolean;
}>();

const chartContainer = ref<HTMLElement | null>(null);
let chartInstance: any = null;
let mutationObserver: MutationObserver | null = null;

const getCSSVariable = (variable: string): string => {
  try {
    return getComputedStyle(document.documentElement).getPropertyValue(variable).trim();
  } catch (e) {
    return '';
  }
};

const chartHeight = () => props.height || '350px';

const initChart = () => {
  if (!chartContainer.value) return;

  const primaryColor = getCSSVariable('--primary') || '#512388';
  const secondaryColor = getCSSVariable('--secondary') || '#4601FA';
  const accentColor = getCSSVariable('--accent') || '#433993';
  const chartColor1 = getCSSVariable('--color-chart-1') || primaryColor;
  const chartColor2 = getCSSVariable('--color-chart-2') || '#7769FA';
  const chartColor3 = getCSSVariable('--color-chart-3') || accentColor;
  const chartColor4 = getCSSVariable('--color-chart-4') || '#F59E0B';
  const backgroundColor = getCSSVariable('--background') || '#fff';
  const textColor = getCSSVariable('--foreground') || '#000';

  const defaultColors = props.colors || [chartColor1, chartColor2, chartColor3, chartColor4].filter(Boolean);

  if (!chartInstance) {
    chartInstance = echarts.init(chartContainer.value);
  }

  const option = {
    title: {
      text: props.title || '',
      left: 'center',
      textStyle: { fontSize: 16, fontWeight: 'bold', color: textColor },
    },
    tooltip: { trigger: 'item' as const, formatter: '{a} <br/>{b}: {c} ({d}%)' },
    legend: { show: props.showLegend ?? true, orient: 'vertical' as const, left: 'left', top: 'middle', textStyle: { color: textColor } },
    series: [
      {
        name: 'Quantidade',
        type: 'pie',
        radius: ['40%', '70%'],
        avoidLabelOverlap: false,
        itemStyle: { borderRadius: 10, borderColor: backgroundColor, borderWidth: 2 },
        label: { show: props.showLabel ?? false, color: textColor },
        labelLine: { show: false },
        emphasis: { label: { show: false } },
        data: (props.data || []).map((d: DataPoint) => ({ value: d.value, name: d.name })),
        color: defaultColors,
      },
    ],
  };

  chartInstance.setOption(option);

  // garantir redraw após render
  setTimeout(() => { try { chartInstance && chartInstance.resize(); } catch (e) {} }, 50);
};

const updateTheme = () => {
  if (!chartInstance) return;
  const backgroundColor = getCSSVariable('--background') || '#fff';
  const textColor = getCSSVariable('--foreground') || '#000';
  const colors = props.colors || [getCSSVariable('--color-chart-1') || '#512388', getCSSVariable('--color-chart-2') || '#7769FA', getCSSVariable('--color-chart-3') || '#433993'];
  chartInstance.setOption({ title: { textStyle: { color: textColor } }, legend: { textStyle: { color: textColor } }, series: [{ label: { color: textColor }, itemStyle: { borderColor: backgroundColor }, color: colors }] });
  try { chartInstance.resize(); } catch (e) {}
};

const onWindowResize = () => { try { chartInstance && chartInstance.resize(); } catch (e) {} };

onMounted(() => {
  initChart();

  mutationObserver = new MutationObserver((mutations) => {
    for (const m of mutations) {
      if (m.type === 'attributes' && (m as any).attributeName === 'class') {
        setTimeout(() => updateTheme(), 10);
      }
    }
  });
  mutationObserver.observe(document.documentElement, { attributes: true });

  window.addEventListener('resize', onWindowResize);

  // atualizar quando dados mudarem
  watch(() => props.data, () => {
    try {
      if (chartInstance) {
        chartInstance.setOption({ series: [{ data: (props.data || []).map((d: DataPoint) => ({ value: d.value, name: d.name })), color: props.colors || undefined }] });
        chartInstance.resize();
      }
    } catch (e) {}
  }, { deep: true });

  watch(() => props.colors, () => updateTheme());

  watch(() => props.height, () => { try { chartInstance && chartInstance.resize(); } catch (e) {} });
});
</script>

<template>
  <div v-if="!props.cardless" class="bg-card rounded-lg shadow p-4">
    <div ref="chartContainer" :style="{ width: '100%', height: props.height || '350px' }"></div>
  </div>
  <div v-else>
    <div ref="chartContainer" :style="{ width: '100%', height: props.height || '350px' }"></div>
  </div>
</template>
