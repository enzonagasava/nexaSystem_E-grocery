<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
import ChartLine from '@/components/charts/ChartLine.vue';
import ChartBar from '@/components/charts/ChartBar.vue';
import ChartPie from '@/components/charts/ChartPie.vue';

const props = defineProps<{}>();

// Função para obter cores do CSS
const getCSSVariable = (variable: string): string => {
  return getComputedStyle(document.documentElement).getPropertyValue(variable).trim();
};

const hexToRgba = (hex: string, alpha = 1) => {
  if (!hex) return `rgba(0,0,0,${alpha})`;
  hex = hex.replace('#', '').trim();
  if (hex.length === 3) {
    hex = hex.split('').map((c) => c + c).join('');
  }
  const int = parseInt(hex, 16);
  const r = (int >> 16) & 255;
  const g = (int >> 8) & 255;
  const b = int & 255;
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

const getRGBAFromVar = (varName: string, alpha = 0.12) => {
  const hex = getCSSVariable(varName) || '#512388';
  return hexToRgba(hex, alpha);
};

// Dados para os gráficos
const dadosValorVendido = [
  { label: 'Jan', value: 250000 },
  { label: 'Fev', value: 320000 },
  { label: 'Mar', value: 180000 },
  { label: 'Abr', value: 450000 },
  { label: 'Mai', value: 380000 },
  { label: 'Jun', value: 520000 },
];

const dadosQuantidade = [
  { label: 'Jan', value: 5 },
  { label: 'Fev', value: 7 },
  { label: 'Mar', value: 4 },
  { label: 'Abr', value: 9 },
  { label: 'Mai', value: 8 },
  { label: 'Jun', value: 11 },
];

const dadosComissao = [
  { label: 'Jan', value: 12500 },
  { label: 'Fev', value: 16000 },
  { label: 'Mar', value: 9000 },
  { label: 'Abr', value: 22500 },
  { label: 'Mai', value: 19000 },
  { label: 'Jun', value: 26000 },
];

const dadosTiposImoveis = [
  { name: 'Apartamento', value: 18 },
  { name: 'Casa', value: 12 },
  { name: 'Terreno', value: 6 },
  { name: 'Comercial', value: 8 },
];
</script>

<template>
  <Head>
    <title>Relatórios - Corretor</title>
  </Head>
  <AuthLayout>
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-2 text-foreground">Relatórios</h1>
      <p class="text-muted-foreground mb-8">Dashboard e relatórios do módulo Corretor</p>
      
      <!-- Grid de Cards com Resumo -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-card rounded-lg shadow p-6 border-l-4" style="border-color: var(--primary);">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-muted-foreground text-sm font-medium">Total Vendido</p>
              <h3 class="text-2xl font-bold text-card-foreground mt-1">R$ 2,10M</h3>
              <p class="text-text-success text-sm mt-1">+12% no mês</p>
            </div>
            <div class="p-3 rounded-full" :style="{ backgroundColor: getRGBAFromVar('--primary', 0.12) }">
              <svg class="w-8 h-8" :style="{ color: getCSSVariable('--primary') }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-card rounded-lg shadow p-6 border-l-4" style="border-color: var(--secondary);">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-muted-foreground text-sm font-medium">Imóveis Vendidos</p>
              <h3 class="text-2xl font-bold text-card-foreground mt-1">44</h3>
              <p class="text-text-success text-sm mt-1">+8% no mês</p>
            </div>
            <div class="p-3 rounded-full" :style="{ backgroundColor: getRGBAFromVar('--secondary', 0.12) }">
              <svg class="w-8 h-8" :style="{ color: getCSSVariable('--secondary') }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-card rounded-lg shadow p-6 border-l-4" style="border-color: var(--accent);">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-muted-foreground text-sm font-medium">Comissões</p>
              <h3 class="text-2xl font-bold text-card-foreground mt-1">R$ 105K</h3>
              <p class="text-text-success text-sm mt-1">+15% no mês</p>
            </div>
            <div class="p-3 rounded-full" :style="{ backgroundColor: getRGBAFromVar('--accent', 0.12) }">
              <svg class="w-8 h-8" :style="{ color: getCSSVariable('--accent') }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-card rounded-lg shadow p-6 border-l-4" style="border-color: var(--color-chart-2);">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-muted-foreground text-sm font-medium">Taxa Conversão</p>
              <h3 class="text-2xl font-bold text-card-foreground mt-1">32%</h3>
              <p class="text-text-success text-sm mt-1">+5% no mês</p>
            </div>
            <div class="p-3 rounded-full" :style="{ backgroundColor: getRGBAFromVar('--color-chart-2', 0.12) }">
              <svg class="w-8 h-8" :style="{ color: getCSSVariable('--color-chart-2') }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Grid de Gráficos -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Gráfico de Valor Vendido -->
        <ChartLine :data="dadosValorVendido" title="Valor Total Vendido" :primary-color="getCSSVariable('--primary')" />

        <!-- Gráfico de Quantidade Vendida -->
        <ChartBar :data="dadosQuantidade" title="Quantidade de Imóveis Vendidos" :color="getCSSVariable('--secondary')" />

        <!-- Gráfico de Comissão -->
        <ChartBar :data="dadosComissao" title="Comissões Recebidas" :color="getCSSVariable('--accent')" is-currency />

        <!-- Gráfico de Tipos de Imóveis -->
        <ChartPie :data="dadosTiposImoveis" title="Imóveis por Tipo" />
      </div>
    </div>
  </AuthLayout>
</template>
