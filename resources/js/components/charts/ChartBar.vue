<script setup lang="ts">
import { onMounted, ref } from 'vue';
import * as echarts from 'echarts';

interface DataPoint {
  label: string;
  value: number;
}

interface TooltipItem {
  label: string;
  value: number;
}

const props = defineProps<{
  data: DataPoint[];
  title: string;
  height?: string;
  color?: string;
  isCurrency?: boolean;
  tooltipItems?: TooltipItem[];
  tooltipItemsResolver?: (dateLabel: string) => TooltipItem[];
  tooltipTitle?: string;
}>();

const chartContainer = ref<HTMLElement | null>(null);
let chartInstance: any = null;
let mutationObserver: MutationObserver | null = null;

const getCSSVariable = (variable: string): string => {
  return getComputedStyle(document.documentElement).getPropertyValue(variable).trim();
};

const initChart = () => {
  if (!chartContainer.value) return;

  const barColor = props.color || getCSSVariable('--secondary') || '#4601FA';
  const secondaryColor = getCSSVariable('--color-chart-2') || '#7769FA';
  const textColor = getCSSVariable('--foreground') || '#000';

  if (!chartInstance) {
    chartInstance = echarts.init(chartContainer.value);
  }

  const formatter = props.isCurrency
    ? (value: number) => `R$ ${(value / 1000).toFixed(0)}k`
    : (value: number) => `${value.toString()}`;

  const option = {
    title: {
      text: props.title,
      left: 'center',
      textStyle: {
        fontSize: 16,
        fontWeight: 'bold' as const,
        color: textColor,
      },
    },
    tooltip: {
      trigger: 'axis' as const,
      renderMode: 'html',
      backgroundColor: 'transparent',
      borderWidth: 0,
      padding: 0,
      formatter: (params: any) => {
        const p = Array.isArray(params) ? params[0] : params;
        const date = p?.name ?? '';
        let html = `<div class="echarts-tooltip"><div class="echarts-tooltip__date">${date}</div>`;
        
        // Se houver resolver, usa ele para obter items dinâmicos
        // Se não, tenta usar os items estáticos
        const items = props.tooltipItemsResolver ? props.tooltipItemsResolver(date) : props.tooltipItems || [];
        
        if (items && items.length > 0) {
          html += `<div class="echarts-tooltip__items">`;
          items.forEach((item: TooltipItem) => {
            const formattedValue = props.isCurrency
              ? `R$ ${Number(item.value).toLocaleString('pt-BR', { minimumFractionDigits: 0 })}`
              : `${Number(item.value).toLocaleString('pt-BR')}`;
            html += `<div class="echarts-tooltip__row"><span class="echarts-tooltip__label">${item.label}</span><span class="echarts-tooltip__item-value">${formattedValue}</span></div>`;
          });
          html += `</div>`;
        }
        
        // Se houver valor no ponto (total), mostra separador e total
        if (p?.value !== undefined && items && items.length > 0) {
          let totalLabel = props.tooltipTitle || 'Total';
          const totalValue = props.isCurrency
            ? `R$ ${Number(p.value).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`
            : `${Number(p.value).toLocaleString('pt-BR')}`;
          html += `<div class="echarts-tooltip__separator"></div><div class="echarts-tooltip__total"><span class="echarts-tooltip__total-label">${totalLabel}</span><span class="echarts-tooltip__total-value">${totalValue}</span></div>`;
        }
        
        html += `</div>`;
        return html;
      },
    },
    xAxis: {
      type: 'category',
      data: props.data.map((d: DataPoint) => d.label),
      axisLabel: {
        color: textColor,
      },
    },
    yAxis: {
      type: 'value',
      minInterval: props.isCurrency ? undefined : 1,
      axisLabel: {
        formatter: formatter,
        color: textColor,
      },
    },
    series: [
      {
        data: props.data.map((d: DataPoint) => (d.value === 0 ? null : d.value)),
        type: 'bar',
        itemStyle: {
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            { offset: 0, color: barColor },
            { offset: 1, color: secondaryColor },
          ]),
        },
        label: {
          show: true,
          position: 'top',
          color: textColor,
          formatter: (val: any) => {
            const v = val?.value;
            if (v == null || v === 0) return '';
            return props.isCurrency ? `R$ ${(v / 1000).toFixed(1)}k` : v;
          },
        },
      },
    ],
    grid: {
      left: '3%',
      right: '4%',
      bottom: '3%',
      containLabel: true,
    },
  };

  chartInstance.setOption(option);
};

const updateTheme = () => {
  if (chartInstance) {
    const textColor = getCSSVariable('--foreground') || '#000';
    const barColor = props.color || getCSSVariable('--secondary') || '#4601FA';
    const secondaryColor = getCSSVariable('--color-chart-2') || '#7769FA';

    const formatter = props.isCurrency
      ? (value: number) => `R$ ${(value / 1000).toFixed(0)}k`
      : (value: number) => value.toString();

    chartInstance.setOption({
      title: { textStyle: { color: textColor } },
      xAxis: { axisLabel: { color: textColor } },
      yAxis: {
        axisLabel: {
          formatter: formatter,
          color: textColor,
        },
      },
      series: [
        {
          itemStyle: {
            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
              { offset: 0, color: barColor },
              { offset: 1, color: secondaryColor },
            ]),
          },
          label: { color: textColor },
        },
      ],
    });

    chartInstance.resize();
  }
};

const onWindowResize = () => {
  if (chartInstance) chartInstance.resize();
};

onMounted(() => {
  initChart();

  // Observer para detectar mudanças de tema
  mutationObserver = new MutationObserver((mutations) => {
    for (const m of mutations) {
      if (m.type === 'attributes' && (m as any).attributeName === 'class') {
        setTimeout(() => updateTheme(), 10);
      }
    }
  });
  mutationObserver.observe(document.documentElement, { attributes: true });

  window.addEventListener('resize', onWindowResize);
});
</script>

<template>
  <div class="bg-card rounded-lg shadow p-4">
    <div ref="chartContainer" :style="{ width: '100%', height: height || '350px' }"></div>
  </div>
</template>
