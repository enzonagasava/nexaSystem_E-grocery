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
  primaryColor?: string;
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

  const primaryColor = props.primaryColor || getCSSVariable('--primary') || '#512388';
  const textColor = getCSSVariable('--foreground') || '#000';

  if (!chartInstance) {
    chartInstance = echarts.init(chartContainer.value);
  }

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
        formatter: (value: number) => {
          if (props.isCurrency) return `R$ ${(value / 1000).toFixed(0)}k`;
          const v = Math.round(value);
          if (Math.abs(v) >= 1000) return `${(v / 1000).toFixed(0)}k`;
          return `${v}`;
        },
        color: textColor,
      },
    },
    series: [
      {
        data: props.data.map((d: DataPoint) => (props.isCurrency ? d.value : Math.round(d.value))),
        type: 'line',
        smooth: true,
        lineStyle: {
          width: 3,
          color: primaryColor,
        },
        itemStyle: {
          color: primaryColor,
        },
        areaStyle: {
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            { offset: 0, color: primaryColor + '4D' },
            { offset: 1, color: primaryColor + '0D' },
          ]),
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
    const primaryColor = props.primaryColor || getCSSVariable('--primary') || '#512388';

    chartInstance.setOption({
      title: { textStyle: { color: textColor } },
      xAxis: { axisLabel: { color: textColor } },
      yAxis: { axisLabel: { color: textColor } },
      series: [
        {
          lineStyle: { color: primaryColor },
          itemStyle: { color: primaryColor },
          areaStyle: {
            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
              { offset: 0, color: primaryColor + '4D' },
              { offset: 1, color: primaryColor + '0D' },
            ]),
          },
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
