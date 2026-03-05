<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Home, MessageCircle, FileText, Users, TrendingUp, DollarSign, Eye, ShoppingCart, Download, Calendar, Clock } from 'lucide-vue-next';
import ChartLine from '@/components/charts/ChartLine.vue';
import ChartBar from '@/components/charts/ChartBar.vue';
import { ChartToggle } from '@/components/ui/toggle';
import ChartPie from '@/components/charts/ChartPie.vue';
import Button from '@/components/ui/button/Button.vue';
import ButtonCalendar from '@/components/ui/button/ButtonCalendar.vue';
import LabelWithTooltip from '@/components/ui/label/LabelWithTooltip.vue';
import { metricTooltips } from '@/constants/metricTooltips';
import { computed, ref, watch, onMounted } from 'vue';
import { useExport } from '@/composables/useExport';
import axios from 'axios';

interface TooltipItem {
  label: string;
  value: number;
}

const props = defineProps<{
  dashboardData?: {
    valorVendido?: Array<{ data: string; valor: number }>;
    imoveisVendidos?: Array<{ nome: string; quantidade: number }>;
    anunciosTipos?: Array<{ tipo: string; quantidade: number }>;
    comissoesPorImovel?: Array<{ imovel: string; comissao: number }>;
    novosChatsPorMes?: Array<{ mes: string; total: number }>;
    faturamento?: Array<{ data: string; total: number }>;
    locacao?: Array<{ data: string; total: number }>;
    historicoCompras?: any;
  };
  kpiData?: {
    vendasTotais: number;
    imoveisAtivos: number;
    visualizacoes: number;
    vendasFechadas: number;
  };
  periodoValor?: string;
  periodoComissoes?: string;
  periodoImoveis?: string;
  periodoChats?: string;
  modulo?: string;
}>();

// Mapeamento de tipos de anúncio para labels legíveis
const anuncioTiposLabels: Record<string, string> = {
  'Google_ads': 'Google Ads',
  'Instagram_ads': 'Instagram Ads',
  'Whatsapp_campaign': 'Campanha WhatsApp',
  'Site_anuncio': 'Anúncio do Site',
};
// Lê variável CSS com fallback (compatível SSR)
function getCSSVariable(varName: string, fallback: string) {
  try {
    if (typeof document !== 'undefined') {
      const v = getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
      if (v) return v;
    }
  } catch (e) {
    // ignore
  }
  return fallback;
}

// Cores por tipo de anúncio usando a padronização do projeto (CSS vars)
const anuncioTiposCores: Record<string, string> = {
  'Google_ads': getCSSVariable('--color-chart-1', '#4285F4'),
  'Instagram_ads': getCSSVariable('--color-chart-2', '#E1306C'),
  'Whatsapp_campaign': getCSSVariable('--color-chart-3', '#25D366'),
  'Site_anuncio': getCSSVariable('--color-chart-4', '#FF9800'),
};

// Valores mockados de orçamento e faturamento por tipo (por enquanto)
const anuncioTiposMock: Record<string, { orcamentoUnit: number; faturadoUnit: number }> = {
  'Google_ads': { orcamentoUnit: 1200, faturadoUnit: 850 },
  'Instagram_ads': { orcamentoUnit: 800, faturadoUnit: 520 },
  'Whatsapp_campaign': { orcamentoUnit: 350, faturadoUnit: 180 },
  'Site_anuncio': { orcamentoUnit: 150, faturadoUnit: 90 },
};

// ===== CARD DE EVENTOS DO CALENDÁRIO =====
interface CalendarEvent {
  id: string
  title: string
  start: string
  end?: string | null
  allDay?: boolean
  label?: string
  labelColor?: string
}

const calendarEvents = ref<CalendarEvent[]>([])
const calendarLoading = ref(false)
const calendarEventsPeriod = ref<7 | 15 | 30>(7)
const calendarEventModalOpen = ref(false)
const calendarEventSelected = ref<CalendarEvent | null>(null)
const calendarEventTab = ref<'today' | 'period'>('today')

function parseDateStr(v?: string | null): Date | null {
  if (!v) return null
  if (/^\d{4}-\d{2}-\d{2}$/.test(v)) {
    const [y, m, d] = v.split('-').map(Number)
    return new Date(y, m - 1, d)
  }
  const d = new Date(v as any)
  if (isNaN(d.getTime())) return null
  return d
}

function isSameDay(a: Date, b: Date) {
  return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate()
}

function extractTimeFromDatetime(dateStr: string | null | undefined): { hour: number; minute: number } {
  if (!dateStr) return { hour: 0, minute: 0 }
  
  const isoMatch = dateStr.match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(?::\d{2})?/)
  if (isoMatch) {
    const hour = parseInt(isoMatch[4], 10)
    const minute = parseInt(isoMatch[5], 10)
    return { hour, minute }
  }

  const timeMatch = dateStr.match(/[T\s](\d{2}):(\d{2})/)
  if (timeMatch) {
    const h = parseInt(timeMatch[1], 10)
    const m = parseInt(timeMatch[2], 10)
    return { hour: h, minute: m }
  }

  return { hour: 0, minute: 0 }
}

function formatEventTime(ev: CalendarEvent): string {
  const t = extractTimeFromDatetime(ev.start)
  const hh = String(t.hour).padStart(2, '0')
  const mm = String(t.minute).padStart(2, '0')
  return `${hh}:${mm}`
}

function openEventModal(event: CalendarEvent) {
  calendarEventSelected.value = event
  calendarEventModalOpen.value = true
}

function closeEventModal() {
  calendarEventModalOpen.value = false
  calendarEventSelected.value = null
}

async function loadCalendarEvents() {
  calendarLoading.value = true
  try {
    const res = await axios.get<CalendarEvent[]>(route('admin.corretor.calendar.events'))
    calendarEvents.value = res.data.map((e: any) => ({
      id: e.id,
      title: e.summary,
      start: e.start,
      end: e.end ?? null,
      allDay: !!e.allDay,
      label: e.label ?? null,
      labelColor: e.labelColor ?? null,
    }))
  } catch (err) {
    console.error('Failed to load calendar events', err)
    calendarEvents.value = []
  } finally {
    calendarLoading.value = false
  }
}

// Filtra eventos por período (próximos dias contando com hoje)
const filteredCalendarEvents = computed(() => {
  const now = new Date()
  const periodStart = new Date(now.getFullYear(), now.getMonth(), now.getDate()) // Início de hoje
  const periodEnd = new Date(periodStart)
  periodEnd.setDate(periodEnd.getDate() + (calendarEventsPeriod.value - 1)) // Próximos N dias

  return calendarEvents.value
    .filter((ev: CalendarEvent) => {
      const eventDate = parseDateStr(ev.start)
      if (!eventDate) return false
      // Filtra eventos de hoje em diante
      return eventDate >= periodStart && eventDate <= periodEnd
    })
    .sort((a: CalendarEvent, b: CalendarEvent) => {
      const aDate = parseDateStr(a.start)
      const bDate = parseDateStr(b.start)
      if (!aDate || !bDate) return 0
      
      // Ordena por data crescente (próximos eventos primeiro)
      const timeA = aDate.getTime()
      const timeB = bDate.getTime()
      
      if (Math.abs(timeB - timeA) < 1000) {
        // Mesma data, ordena por hora
        const aTime = extractTimeFromDatetime(a.start)
        const bTime = extractTimeFromDatetime(b.start)
        const aMinutes = aTime.hour * 60 + aTime.minute
        const bMinutes = bTime.hour * 60 + bTime.minute
        return aMinutes - bMinutes
      }
      
      return timeA - timeB
    })
})

// Separar eventos: hoje e anteriores
const todayEvents = computed(() => {
  return filteredCalendarEvents.value.filter((ev: CalendarEvent) => {
    const eventDate = parseDateStr(ev.start)
    return eventDate && isSameDay(eventDate, today)
  })
})

const otherEvents = computed(() => {
  return filteredCalendarEvents.value.filter((ev: CalendarEvent) => {
    const eventDate = parseDateStr(ev.start)
    return !eventDate || !isSameDay(eventDate, today)
  })
})

// Tabs dinâmicas do card de Anúncios — "Geral" + um por tipo existente
const anunciosTabs = computed(() => {
  const tabs: Array<{ key: string; label: string }> = [{ key: 'geral', label: 'Geral' }];
  for (const item of anunciosTiposComputado.value) {
    tabs.push({ key: item.tipo, label: item.label });
  }
  return tabs;
});
const anunciosTabAtivo = ref('geral');
const anunciosChartType = ref<'line' | 'bar'>('line');

// Filtro de período do card Anúncios
const anunciosPreset = ref<number | null>(14);
const anunciosCustomRange = ref(false);
const anunciosStartDate = ref<string | null>(null);
const anunciosEndDate = ref<string | null>(null);
const anunciosCalendarRange = ref<{ start: string | null; end: string | null }>({ start: null, end: null });

function anunciosSetPreset(p: number) {
  anunciosPreset.value = p;
  anunciosCustomRange.value = false;
  const r = parsePreset(p);
  anunciosStartDate.value = formatISODateLocal(r.start);
  anunciosEndDate.value = formatISODateLocal(r.end);
}

function anunciosHandleCalendarApply(range: { start: string | null; end: string | null }) {
  if (range.start && range.end) {
    anunciosCustomRange.value = true;
    anunciosPreset.value = null;
    anunciosStartDate.value = range.start;
    anunciosEndDate.value = range.end;
  }
}

// Inicializar datas do card anúncios
{
  const r = parsePreset(14);
  anunciosStartDate.value = formatISODateLocal(r.start);
  anunciosEndDate.value = formatISODateLocal(r.end);
}

// Gera dados mockados de timeline para o tipo e período selecionado
function generateAnuncioTimeline(tipo: string, days: number): Array<{ label: string; value: number }> {
  const tipoData = anunciosTiposComputado.value.find((t: any) => t.tipo === tipo);
  if (!tipoData) return [];

  const avgPerDay = Math.max(1, tipoData.quantidade / 30); // base em 30 dias
  const series: Array<{ label: string; value: number }> = [];
  for (let i = days - 1; i >= 0; i--) {
    const date = new Date();
    date.setDate(date.getDate() - i);
    series.push({
      label: formatDM(date),
      value: Math.max(0, Math.round(avgPerDay + (Math.random() - 0.5) * avgPerDay * 1.5)),
    });
  }
  return series;
}

// Computed: série timeline respeitando o período selecionado
const anuncioTimelineSeries = computed(() => {
  const tipo = anunciosTabAtivo.value;
  if (tipo === 'geral') return [];

  let rangeStart: Date;
  let rangeEnd: Date;
  if (anunciosCustomRange.value && anunciosStartDate.value && anunciosEndDate.value) {
    rangeStart = parseISODateLocal(anunciosStartDate.value);
    rangeEnd = parseISODateLocal(anunciosEndDate.value);
  } else if (anunciosPreset.value) {
    const r = parsePreset(anunciosPreset.value);
    rangeStart = r.start;
    rangeEnd = r.end;
  } else {
    const r = parsePreset(14);
    rangeStart = r.start;
    rangeEnd = r.end;
  }

  const diffDays = Math.round((rangeEnd.getTime() - rangeStart.getTime()) / (1000 * 60 * 60 * 24)) + 1;
  return generateAnuncioTimeline(tipo, diffDays);
});

// Métricas do tipo de anúncio selecionado
const anuncioSelectedMetrics = computed(() => {
  if (anunciosTabAtivo.value === 'geral') return null;
  return anunciosTiposComputado.value.find((t: any) => t.tipo === anunciosTabAtivo.value) || null;
});

// Computed: tipos de anúncio enriquecidos com cálculos
const anunciosTiposComputado = computed(() => {
  const raw = props.dashboardData?.anunciosTipos ?? [];
  if (raw.length === 0) return [];

  const totalQtd = raw.reduce((sum: number, item: any) => sum + item.quantidade, 0);

  return raw.map((item: any) => {
    const mock = anuncioTiposMock[item.tipo] ?? { orcamentoUnit: 500, faturadoUnit: 300 };
    const orcamento = item.quantidade * mock.orcamentoUnit;
    const faturado = item.quantidade * mock.faturadoUnit;
    const ticketMedio = item.quantidade > 0 ? orcamento / item.quantidade : 0;
    const percentual = totalQtd > 0 ? Math.round((item.quantidade / totalQtd) * 100) : 0;

    return {
      tipo: item.tipo,
      label: anuncioTiposLabels[item.tipo] || item.tipo,
      cor: anuncioTiposCores[item.tipo] || '#888',
      quantidade: item.quantidade,
      percentual,
      orcamento,
      ticketMedio,
      faturado,
    };
  });
});

const anunciosTotalQtd = computed(() => {
  return anunciosTiposComputado.value.reduce((sum: number, item: any) => sum + item.quantidade, 0);
});

const anunciosTotalOrcamento = computed(() => {
  return anunciosTiposComputado.value.reduce((sum: number, item: any) => sum + item.orcamento, 0);
});

const quickLinks = [
  {
    title: 'Anúncios',
    description: 'Gerenciar seus anúncios/imóveis',
    icon: Home,
    href: route('admin.corretor.listings.index'),
    color: 'bg-primary',
  },
  {
    title: 'Mensagens',
    description: 'Atendimento e conversas',
    icon: MessageCircle,
    href: '/admin/chat',
    color: 'bg-primary',
  },
  {
    title: 'Leads/Clientes',
    description: 'Visualizar interessados',
    icon: Users,
    href: '/admin/corretor/leads',
    color: 'bg-primary',
  },
  {
    title: 'Relatórios',
    description: 'Métricas e histórico',
    icon: FileText,
    href: '/admin/corretor/relatorios',
    color: 'bg-primary',
  },
];

// Helper para formatar valores em moeda
const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
  }).format(value);
};

// Helper para formatar números
const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('pt-BR').format(value);
};

// Cards KPI computados com dados reais
const kpiCards = computed(() => [
  {
    title: 'Vendas Totais',
    value: formatCurrency(props.kpiData?.vendasTotais ?? 0),
    subtitle: 'Total de vendas realizadas',
    icon: DollarSign,
    color: 'primary',
  },
  {
    title: 'Imóveis Ativos',
    value: String(props.kpiData?.imoveisAtivos ?? 0),
    subtitle: 'Disponíveis para venda',
    icon: Home,
    color: 'secondary',
  },
  {
    title: 'Visualizações',
    value: formatNumber(props.kpiData?.visualizacoes ?? 0),
    subtitle: 'Acessos aos anúncios',
    icon: Eye,
    color: 'accent',
  },
  {
    title: 'Negócios Fechados',
    value: String(props.kpiData?.vendasFechadas ?? 0),
    subtitle: 'Imóveis vendidos',
    icon: ShoppingCart,
    color: 'primary',
  },
]);

const metricOptions = [
  { key: 'faturamento', label: 'Faturamento' },
  { key: 'locacao', label: 'Locação' },
  { key: 'vendas', label: 'Vendas' },
  { key: 'comissoes', label: 'Comissões' },
  { key: 'novos_chats', label: 'Novos Chats' },
  { key: 'visualizacoes', label: 'Visualizações' },
];

const selectedMetric = ref('faturamento');
const chartType = ref<'line' | 'bar'>('line');
const preset = ref<number | null>(14);
const customRange = ref(false);
const startDate = ref<string | null>(null);
const endDate = ref<string | null>(null);
const calendarRange = ref<{ start: string | null; end: string | null }>({ start: null, end: null });
const selectedDateLabel = ref<string | null>(null);

const today = new Date();
function formatDM(date: Date) {
  return new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit' }).format(date);
}

// Formata uma Date para `YYYY-MM-DD` usando valores locais (evita shift por timezone)
function formatISODateLocal(d: Date) {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

// Parseia uma string ISO (YYYY-MM-DD) como data local, sem shift de timezone
function parseISODateLocal(dateString: string): Date {
  const [year, month, day] = dateString.split('-').map(Number);
  return new Date(year, month - 1, day);
}
function dateRangeArray(start: Date, end: Date) {
  const arr: Date[] = [];
  const cur = new Date(start);
  while (cur <= end) {
    arr.push(new Date(cur));
    cur.setDate(cur.getDate() + 1);
  }
  return arr;
}

function toLabel(d: Date) {
  return formatDM(d);
}

function buildSourceMap(items: Array<any>, keyName = 'data', valueName = 'valor') {
  const map = new Map<string, number>();
  (items || []).forEach((it: any) => {
    const k = it[keyName];
    const v = Number(it[valueName] ?? 0) || 0;
    map.set(k, v);
  });
  return map;
}

function buildHistoricoCountMap(items: Array<any>) {
  const map = new Map<string, number>();
  (items || []).forEach((it: any) => {
    const k = it.data ?? it.date ?? null;
    if (!k) return;
    let qtd = 0;
    if (typeof it.quantidade === 'number') qtd += it.quantidade;
    if (typeof it.vendas === 'number') qtd += it.vendas;
    if (typeof it.locacoes === 'number') qtd += it.locacoes;
    if (typeof it.valor === 'number' && (it.tipo === 'venda' || it.tipo === 'locacao')) qtd += 1;
    if (!qtd && (it.tipo === 'venda' || it.tipo === 'locacao')) qtd = 1;
    const prev = map.get(k) ?? 0;
    map.set(k, prev + qtd);
  });
  return map;
}

function randomBetween(min: number, max: number) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

function generateSeriesForRange(start: Date, end: Date, sourceMap: Map<string, number> | null, generator?: (d: Date) => number) {
  const days = dateRangeArray(start, end);
  return days.map((d) => {
    const label = toLabel(d);
    let value = 0;
    if (sourceMap && sourceMap.has(label)) value = sourceMap.get(label) as number;
    else if (generator) value = generator(d);
    return { label, value };
  });
}

function parsePreset(p: number) {
  const end = new Date();
  const start = new Date();
  start.setDate(end.getDate() - (p - 1));
  return { start, end };
}

watch(chartType, (newVal: 'line' | 'bar') => {
  try {
    localStorage.setItem('dashboard-chart-type', newVal)
  } catch (e) {
    // silent
  }
})

const selectedSeries = computed(() => {
  let rangeStart: Date;
  let rangeEnd: Date;
  if (customRange.value && startDate.value && endDate.value) {
    rangeStart = parseISODateLocal(startDate.value);
    rangeEnd = parseISODateLocal(endDate.value);
  } else if (preset.value && typeof preset.value === 'number') {
    const r = parsePreset(preset.value as number);
    rangeStart = r.start;
    rangeEnd = r.end;
  } else if (startDate.value && endDate.value) {
    rangeStart = parseISODateLocal(startDate.value);
    rangeEnd = parseISODateLocal(endDate.value);
  } else {
    const r = parsePreset(14);
    rangeStart = r.start;
    rangeEnd = r.end;
  }

  const vendasMap = buildSourceMap(props.dashboardData?.valorVendido ?? [], 'data', 'valor');
  const comissoesMap = buildSourceMap(props.dashboardData?.comissoesPorImovel ?? [], 'data', 'total');
  const chatsMap = buildSourceMap(props.dashboardData?.novosChatsPorMes ?? [], 'mes', 'total');
  const faturamentoMap = buildSourceMap(props.dashboardData?.faturamento ?? [], 'data', 'total');
  const locacaoMap = buildSourceMap(props.dashboardData?.locacao ?? [], 'data', 'total');
  const locacaoCountMap = buildSourceMap(props.dashboardData?.locacoesCount ?? [], 'data', 'quantidade');

  switch (selectedMetric.value) {
    case 'vendas': {
      const hist = props.dashboardData?.historicoCompras ?? null;
      if (hist && Array.isArray(hist) && hist.length > 0) {
        const histMap = buildHistoricoCountMap(hist);
        return generateSeriesForRange(rangeStart, rangeEnd, histMap, () => 0);
      }
      const fallbackMap = new Map<string, number>();
      (props.dashboardData?.valorVendido || []).forEach((it: any) => {
        const k = it.data;
        const v = Number(it.valor ?? 0) || 0;
        if (v > 0) {
          const prev = fallbackMap.get(k) ?? 0;
          fallbackMap.set(k, prev + 1);
        }
      });
      return generateSeriesForRange(rangeStart, rangeEnd, fallbackMap, () => 0);
    }
    case 'comissoes':
      return generateSeriesForRange(rangeStart, rangeEnd, comissoesMap, () => 0);
    case 'novos_chats':
      return generateSeriesForRange(rangeStart, rangeEnd, chatsMap, () => randomBetween(1, 10));
    case 'visualizacoes':
      return generateSeriesForRange(rangeStart, rangeEnd, null, () => randomBetween(50, 400));
    case 'faturamento':
      try {
        const hasVendas = vendasMap && vendasMap.size > 0;
        const hasLocacao = locacaoMap && locacaoMap.size > 0;
        if (hasVendas || hasLocacao) {
          const combined = new Map<string, number>();
          if (hasVendas) vendasMap.forEach((v: number, k: string) => combined.set(k, (combined.get(k) ?? 0) + Number(v)));
          if (hasLocacao) locacaoMap.forEach((v: number, k: string) => combined.set(k, (combined.get(k) ?? 0) + Number(v)));
          return generateSeriesForRange(rangeStart, rangeEnd, combined, () => 0);
        }
      } catch (e) {
      }
      return generateSeriesForRange(rangeStart, rangeEnd, faturamentoMap, () => 0);
    case 'locacao':
      return generateSeriesForRange(rangeStart, rangeEnd, locacaoCountMap, () => 0);
    default:
      return generateSeriesForRange(rangeStart, rangeEnd, null, () => 0);
  }
});

const chartTitle = computed(() => {
  const opt = metricOptions.find((m) => m.key === selectedMetric.value);
  return opt ? opt.label : 'Métrica';
});

const chartMetrics = computed(() => {
  const series = selectedSeries.value || [];
  const total = series.reduce((sum: number, item: any) => sum + (item.value || 0), 0);
  const days = series.length || 1;
  const average = total / days;

  let totalRevenue = 0;
  if (selectedMetric.value === 'vendas') {
    const vendasMap = buildSourceMap(props.dashboardData?.valorVendido ?? [], 'data', 'valor');
    series.forEach((item: any) => {
      const val = vendasMap.get(item.label);
      if (val) totalRevenue += val;
    });
  }

  let totalVendas = 0;
  let totalLocacoes = 0;
  if (selectedMetric.value === 'faturamento') {
    const vendasMap = buildSourceMap(props.dashboardData?.valorVendido ?? [], 'data', 'valor');
    const locacaoMap = buildSourceMap(props.dashboardData?.locacao ?? [], 'data', 'total');
    series.forEach((item: any) => {
      const vendas = vendasMap.get(item.label);
      const locacao = locacaoMap.get(item.label);
      if (vendas) totalVendas += vendas;
      if (locacao) totalLocacoes += locacao;
    });
  }

  let locacaoCountTotal = 0;
  let locacaoValueTotal = 0;
  if (selectedMetric.value === 'locacao') {
    const locacoesCountMap = buildSourceMap(props.dashboardData?.locacoesCount ?? [], 'data', 'quantidade');
    const locacaoValueMap = buildSourceMap(props.dashboardData?.locacao ?? [], 'data', 'total');
    series.forEach((item: any) => {
      const cnt = locacoesCountMap.get(item.label) ?? 0;
      const val = locacaoValueMap.get(item.label) ?? 0;
      locacaoCountTotal += Number(cnt);
      locacaoValueTotal += Number(val);
    });
  }

  const ticketMedio = (selectedMetric.value === 'vendas' && total > 0)
    ? totalRevenue / total
    : (selectedMetric.value === 'locacao' && locacaoCountTotal > 0)
    ? locacaoValueTotal / locacaoCountTotal
    : 0;

  switch (selectedMetric.value) {
    case 'vendas':
      return [
        { label: 'Total', value: formatNumber(Math.round(total)), sublabel: '' },
        { label: 'Valor Total', value: formatCurrency(totalRevenue), sublabel: '' },
        { label: 'Ticket médio', value: formatCurrency(ticketMedio), sublabel: '' },
      ];
    case 'novos_chats':
      return [
        { label: 'Total', value: formatNumber(Math.round(total)), sublabel: '' },
        { label: 'Média', value: formatNumber(Math.round(average)), sublabel: 'chats / dia' },
      ];
    case 'comissoes':
      return [
        { label: 'Total', value: formatCurrency(total), sublabel: '' },
      ];
    case 'visualizacoes':
      return [
        { label: 'Total', value: formatNumber(Math.round(total)), sublabel: '' },
        { label: 'Média', value: formatNumber(Math.round(average)), sublabel: 'visitas / dia' },
      ];
    case 'faturamento':
      return [
        { label: 'Faturamento Total', value: formatCurrency(totalVendas + totalLocacoes), sublabel: '' },
        { label: 'Total Vendas', value: formatCurrency(totalVendas), sublabel: '' },
        { label: 'Total Locações', value: formatCurrency(totalLocacoes), sublabel: '' },
      ];
    case 'locacao':
      return [
        { label: 'Total', value: formatNumber(Math.round(locacaoCountTotal)), sublabel: '' },
        { label: 'Valor Total', value: formatCurrency(locacaoValueTotal), sublabel: '' },
        { label: 'Ticket médio', value: formatCurrency(ticketMedio), sublabel: '' },
      ];
    default:
      return [
        { label: 'Total', value: formatNumber(Math.round(total)), sublabel: '' },
      ];
  }
});

// Computed para fornecer tooltipItems contextualizados para cada métrica
const tooltipItemsForMetric = computed(() => {
  const vendasMap = buildSourceMap(props.dashboardData?.valorVendido ?? [], 'data', 'valor');
  const locacaoMap = buildSourceMap(props.dashboardData?.locacao ?? [], 'data', 'total');
  const locacaoCountMap = buildSourceMap(props.dashboardData?.locacoesCount ?? [], 'data', 'quantidade');
  const comissoesMap = buildSourceMap(props.dashboardData?.comissoesPorImovel ?? [], 'data', 'total');
  const chatsMap = buildSourceMap(props.dashboardData?.novosChatsPorMes ?? [], 'mes', 'total');

  return (dateLabel: string): TooltipItem[] => {
    switch (selectedMetric.value) {
      case 'faturamento': {
        const vendas = vendasMap.get(dateLabel) ?? 0;
        const locacao = locacaoMap.get(dateLabel) ?? 0;
        return [
          { label: 'Vendas', value: vendas },
          { label: 'Locações', value: locacao },
        ];
      }
      case 'locacao': {
        const count = locacaoCountMap.get(dateLabel) ?? 0;
        const value = locacaoMap.get(dateLabel) ?? 0;
        return [
          { label: 'Quantidade', value: count },
          { label: 'Valor', value: value },
        ];
      }
      case 'vendas': {
        const valor = vendasMap.get(dateLabel) ?? 0;
        return [
          { label: 'Valor', value: valor },
        ];
      }
      case 'comissoes': {
        const comissao = comissoesMap.get(dateLabel) ?? 0;
        return [
          { label: 'Comissão', value: comissao },
        ];
      }
      case 'novos_chats': {
        const chats = chatsMap.get(dateLabel) ?? 0;
        return [
          { label: 'Chats', value: chats },
        ];
      }
      case 'visualizacoes': {
        // Visualizações são aleatórias, não têm breakdown
        return [];
      }
      default:
        return [];
    }
  };
});

// Computed para título do total no tooltip
const tooltipTitleForMetric = computed(() => {
  switch (selectedMetric.value) {
    case 'faturamento':
      return 'Faturamento Total';
    case 'locacao':
      return 'Total Locação';
    case 'vendas':
      return 'Total Vendas';
    case 'comissoes':
      return 'Total Comissões';
    case 'novos_chats':
      return 'Total Chats';
    case 'visualizacoes':
      return 'Total Vizualizações';
    default:
      return 'Total';
  }
});

// export
const exporter = useExport();
function getExportData() {
  const series = selectedSeries.value || [];
  return series.map((s: any) => ({
    Data: s.label,
    Valor: s.value,
    Metric: chartTitle.value,
  }));
}

function exportChart(format: 'csv' | 'json' | 'xlsx' = 'csv') {
  const data = getExportData();
  if (!data || data.length === 0) {
    return;
  }
  const from = startDate.value ?? '';
  const to = endDate.value ?? '';
  const filenameBase = `dashboard_${selectedMetric.value}_${from}_${to}`.replace(/\//g, '-');
  if (format === 'json') exporter.exportToJSON(data, `${filenameBase}.json`);
  else if (format === 'xlsx') exporter.exportToExcel(data, `${filenameBase}.xlsx`);
  else exporter.exportToCSV(data, `${filenameBase}.csv`);
}

function setPreset(p: number) {
  preset.value = p;
  customRange.value = false;
  const r = parsePreset(p);
  startDate.value = formatISODateLocal(r.start);
  endDate.value = formatISODateLocal(r.end);
}

function setCustom() {
  customRange.value = true;
  preset.value = null;
}

function handleCalendarApply(range: { start: string | null; end: string | null }) {
  if (range.start && range.end) {
    customRange.value = true;
    preset.value = null;
    startDate.value = range.start;
    endDate.value = range.end;
  }
}

watch(preset, (p: number | null) => {
  if (p !== null) {
    customRange.value = false;
    const r = parsePreset(p);
    startDate.value = formatISODateLocal(r.start);
    endDate.value = formatISODateLocal(r.end);
  }
});

function getMetricTooltip(label: string) {
  if (!label) return '';
  const l = label.toLowerCase();
  
  // Retorna tooltip específico baseado na métrica selecionada
  switch (selectedMetric.value) {
    case 'faturamento':
      if (l.includes('vendas')) return metricTooltips.faturamento_vendas;
      if (l.includes('locações') || l.includes('locacoes')) return metricTooltips.faturamento_locacoes;
      return metricTooltips.faturamento_total;
    
    case 'locacao':
      if (l.includes('ticket')) return metricTooltips.locacao_ticket;
      return metricTooltips.locacao_total;
    
    case 'vendas':
      if (l.includes('ticket')) return metricTooltips.vendas_ticket;
      if(l.includes('valor')) return metricTooltips.vendas_total_valor;
      return metricTooltips.vendas_total;
    
    case 'comissoes':
      if (l.includes('ticket')) return metricTooltips.comissoes_media;
      return metricTooltips.comissoes_total;
    
    case 'novos_chats':
      if (l.includes('ticket') || l.includes('média') || l.includes('media')) return metricTooltips.novos_chats_media;
      return metricTooltips.novos_chats_total;
    
    case 'visualizacoes':
      if (l.includes('média') || l.includes('media')) return metricTooltips.visualizacoes_media;
      return metricTooltips.visualizacoes_total;
    
    default:
      if (l.includes('ticket')) return metricTooltips.metric_ticket_medio;
      if (l.includes('média') || l.includes('media')) return metricTooltips.metric_media;
      return metricTooltips.metric_total;
  }
}

// initialize
{
  const initial = preset.value ?? 14;
  const r = parsePreset(initial);
  startDate.value = formatISODateLocal(r.start);
  endDate.value = formatISODateLocal(r.end);

  try {
    const saved = localStorage.getItem('dashboard-chart-type')
    if (saved === 'line' || saved === 'bar') chartType.value = saved
  } catch (e) {
    // ignore
  }
}

onMounted(() => {
  loadCalendarEvents()
})
</script>

<template>
  <Head>
    <title>Dashboard</title>
    <meta name="description" content="Dashboard do módulo Corretor" />
  </Head>

  <AuthLayout :modulo="modulo">
      <!-- Header -->
        <div>
          <h1 class="text-3xl font-semibold">Dashboard</h1>
          <p class="text-sm text-muted-foreground">Bem-vindo ao seu painel de controle</p>
        </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <!-- Quick Links -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <a
            v-for="(link, index) in quickLinks"
            :key="index"
            :href="link.href"
            class="bg-card rounded-lg border  p-6 hover:border-primary/30 hover:shadow-lg transition-all group cursor-pointer"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <h3 class="font-semibold text-foreground transition-colors">
                  {{ link.title }}
                </h3>
                <p class="text-sm text-foreground/60 mt-1">{{ link.description }}</p>
              </div>
              <component
                :is="link.icon"
                :class="[
                  'w-6 h-6 rounded transition-colors flex-shrink-0 text-foreground/60',
                  {
                    'group-hover:bg-primary/10 group-hover:text-primary': link.color === 'bg-primary',
                    'group-hover:bg-secondary/10 group-hover:text-secondary': link.color === 'bg-secondary',
                    'group-hover:bg-accent/10 group-hover:text-accent': link.color === 'bg-accent',
                  },
                ]"
              />
            </div>
          </a>
        </div>
        <!-- KPI Cards -->
        <div v-if="dashboardData" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div
            v-for="(card, index) in kpiCards"
            :key="index"
            class="bg-card rounded-lg border  p-6 hover:border-primary/30 transition-colors group"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <p class="text-sm font-medium text-foreground/60">{{ card.title }}</p>
                <p class="text-2xl font-bold text-foreground mt-2">{{ card.value }}</p>
                <p class="text-xs text-primary mt-2">{{ card.subtitle }}</p>
              </div>
              <component
                :is="card.icon"
                :class="[
                  'w-10 h-10 rounded-lg p-2 transition-colors',
                  {
                    'group-hover:bg-primary/10 group-hover:text-primary': card.color === 'primary',
                    'group-hover:bg-secondary/10 group-hover:text-secondary': card.color === 'secondary',
                    'group-hover:bg-accent/10 group-hover:text-accent': card.color === 'accent',
                  },
                ]"
              />
            </div>
          </div>
        </div>

        <!-- Charts -->
        <!-- <div v-if="dashboardData" class="grid grid-cols-1 lg:grid-cols-2 gap-6"> -->
          <!-- Valor Vendido (Linha) -->
          <!-- <div class="bg-card rounded-lg border  p-6">
            <ChartLine
              v-if="dashboardData.valorVendido && dashboardData.valorVendido.length > 0"
              :data="
                dashboardData.valorVendido.map((item: any) => ({
                  label: item.data,
                  value: item.valor,
                }))
              "
              title="Valor Vendido"
              height="350px"
            />
            <div v-else class="flex items-center justify-center h-[350px] text-foreground/40">
              Sem dados disponíveis
            </div>
          </div> -->

          <!-- Card Eventos do Calendário -->
          <div class="bg-card rounded-lg border p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
              <h2 class="text-lg font-semibold text-foreground flex items-center gap-2">
                <Calendar class="w-5 h-5" />
                Eventos
              </h2>
            </div>

            <!-- Tabs: Hoje + Períodos -->
            <div class="flex items-center gap-1 border-b mb-6 overflow-x-auto">
              <button
                @click="calendarEventTab = 'today'"
                :class="[
                  'px-3 py-2 text-sm font-medium transition-colors',
                  calendarEventTab === 'today'
                    ? 'border-b-2 border-primary text-primary'
                    : 'text-foreground/60 hover:text-foreground'
                ]"
              >
                Hoje
              </button>
              <button
                v-for="p in [7, 15, 30]"
                :key="`period-${p}`"
                @click="() => { calendarEventTab = 'period'; calendarEventsPeriod = p as 7 | 15 | 30 }"
                :class="[
                  'px-3 py-2 text-sm font-medium transition-colors',
                  calendarEventTab === 'period' && calendarEventsPeriod === p
                    ? 'border-b-2 border-primary text-primary'
                    : 'text-foreground/60 hover:text-foreground'
                ]"
              >
                {{ p }} dias
              </button>
            </div>

            <!-- Events List -->
            <div v-if="calendarLoading" class="flex items-center justify-center py-8 text-foreground/60">
              <span>Carregando eventos...</span>
            </div>
            <div v-else-if="calendarEventTab === 'today' && todayEvents.length === 0" class="flex items-center justify-center py-8 text-foreground/40">
              Nenhum evento hoje
            </div>
            <div v-else-if="calendarEventTab === 'period' && filteredCalendarEvents.length === 0" class="flex items-center justify-center py-8 text-foreground/40">
              Nenhum evento nos próximos {{ calendarEventsPeriod }} dias
            </div>
            <div v-else class="space-y-2 max-h-[400px] overflow-y-auto">
              <!-- Mostrar eventos de hoje quando aba "Hoje" está ativa -->
              <template v-if="calendarEventTab === 'today'">
                <div
                  v-for="ev in todayEvents"
                  :key="ev.id"
                  @click="openEventModal(ev)"
                  class="p-3 rounded-lg border border-primary/20 bg-primary/5 hover:bg-primary/10 transition-colors cursor-pointer"
                >
                  <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-2 h-2 rounded-full mt-1.5" :style="{ backgroundColor: ev.labelColor || 'var(--primary)' }"></div>
                    <div class="flex-1 min-w-0">
                      <p class="font-medium text-foreground truncate">{{ ev.title }}</p>
                      <div v-if="!ev.allDay" class="flex items-center gap-1 text-xs text-muted-foreground mt-1">
                        <Clock class="w-3 h-3" />
                        <span>{{ formatEventTime(ev) }}</span>
                      </div>
                      <div v-else class="text-xs text-muted-foreground mt-1">
                        Dia inteiro
                      </div>
                    </div>
                  </div>
                </div>
              </template>

              <!-- Mostrar eventos do período quando aba de período está ativa -->
              <template v-if="calendarEventTab === 'period'">
                <div
                  v-for="ev in filteredCalendarEvents"
                  :key="ev.id"
                  @click="openEventModal(ev)"
                  class="p-3 rounded-lg border border-border hover:border-primary/30 hover:bg-muted/30 transition-colors cursor-pointer"
                >
                  <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-2 h-2 rounded-full mt-1.5" :style="{ backgroundColor: ev.labelColor || 'var(--primary)' }"></div>
                    <div class="flex-1 min-w-0">
                      <p class="font-medium text-foreground truncate text-sm">{{ ev.title }}</p>
                      <div class="flex items-center gap-2 text-xs text-muted-foreground mt-1">
                        <span>{{ new Date(ev.start).toLocaleDateString('pt-BR') }}</span>
                        <span v-if="!ev.allDay">{{ formatEventTime(ev) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </div>

          <!-- Card Anúncios (estilo detalhado) -->
          <div class="bg-card rounded-lg border p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
              <h2 class="text-lg font-semibold text-foreground">Anúncios</h2>
              <ChartToggle v-if="anunciosTabAtivo !== 'geral'" v-model="anunciosChartType" />
            </div>

            <!-- Tabs dinâmicas (Geral + tipos de anúncio) -->
            <div class="flex items-center gap-1 border-b mb-6">
              <button
                v-for="tab in anunciosTabs"
                :key="tab.key"
                @click="anunciosTabAtivo = tab.key"
                :class="[
                  'px-3 py-2 text-sm font-medium transition-colors',
                  anunciosTabAtivo === tab.key
                    ? 'border-b-2 border-primary text-primary'
                    : 'text-foreground/60 hover:text-foreground'
                ]"
              >
                {{ tab.label }}
              </button>
            </div>

            <!-- ===== Tab "Geral": donut + tabela ===== -->
            <template v-if="anunciosTabAtivo === 'geral'">
              <!-- Resumo -->
              <div class="flex items-baseline gap-8 mb-6" v-if="anunciosTiposComputado.length > 0">
                <div>
                  <span class="text-3xl font-bold text-foreground">{{ anunciosTotalQtd }}</span>
                  <p class="text-sm text-foreground/60">Anúncios</p>
                </div>
                <div>
                  <span class="text-3xl font-bold text-foreground">{{ formatCurrency(anunciosTotalOrcamento) }}</span>
                  <p class="text-sm text-foreground/60">Orçamento total dos ads</p>
                </div>
              </div>

              <!-- Chart + Tabela -->
              <div v-if="anunciosTiposComputado.length > 0" class="flex flex-col lg:flex-row gap-6">
                <!-- Donut Chart -->
                <div class="w-full lg:w-[320px] flex-shrink-0">
                  <ChartPie
                    :data="anunciosTiposComputado.map((item: any) => ({
                      name: item.label,
                      value: item.quantidade,
                    }))"
                    :colors="anunciosTiposComputado.map((item: any) => item.cor)"
                    :show-legend="false"
                    :show-label="false"
                    title=""
                    height="320px"
                  />
                </div>

                <!-- Tabela -->
                <div class="flex-1 overflow-x-auto">
                  <table class="w-full text-sm">
                    <thead>
                      <tr class="border-b text-foreground/60">
                        <th class="text-left py-2 pr-4 font-medium">Tipo de Anúncio</th>
                        <th class="text-center py-2 px-3 font-medium">%</th>
                        <th class="text-center py-2 px-3 font-medium">Qtd</th>
                        <th class="text-right py-2 px-3 font-medium">Orçamento</th>
                        <th class="text-right py-2 px-3 font-medium">CPL</th>
                        <th class="text-right py-2 pl-3 font-medium">Leads</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in anunciosTiposComputado" :key="item.tipo" class="border-b border-border/50 hover:bg-muted/30 transition-colors">
                        <td class="py-2.5 pr-4 flex items-center gap-2">
                          <span class="w-3 h-3 rounded-sm inline-block" :style="{ backgroundColor: item.cor }"></span>
                          <span class="font-medium text-foreground">{{ item.label }}</span>
                        </td>
                        <td class="text-center py-2.5 px-3 text-foreground/80">{{ item.percentual }}%</td>
                        <td class="text-center py-2.5 px-3 text-foreground/80">{{ item.quantidade }}</td>
                        <td class="text-right py-2.5 px-3 text-foreground/80">{{ formatCurrency(item.orcamento) }}</td>
                        <td class="text-right py-2.5 px-3 text-foreground/80">{{ formatCurrency(item.ticketMedio) }}</td>
                        <td class="text-right py-2.5 pl-3 text-foreground/80">{{ item.faturado }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Fallback vazio -->
              <div v-else class="flex items-center justify-center h-[200px] text-foreground/40">
                Sem dados disponíveis
              </div>
            </template>

            <!-- ===== Tabs de tipo específico: métricas + gráfico timeline ===== -->
            <template v-else>
              <!-- Filtro de período -->
              <div class="flex items-center gap-3 mb-6">
                <label class="text-sm text-foreground/60">Períodos:</label>
                <div class="flex items-center gap-2">
                  <Button
                    v-for="p in [7,14,30,60]"
                    :key="p"
                    @click.prevent="() => anunciosSetPreset(p)"
                    :variant="anunciosPreset === p ? 'primary' : 'filter'"
                  >
                    Últimos {{ p }} dias
                  </Button>
                  <ButtonCalendar
                    v-model="anunciosCalendarRange"
                    :variant="anunciosCustomRange ? 'primary' : 'filter'"
                    @apply="anunciosHandleCalendarApply"
                  />
                </div>
              </div>

              <!-- Métricas do tipo selecionado -->
              <div class="flex flex-wrap gap-6 mb-6 pb-4" v-if="anuncioSelectedMetrics">
                <div class="flex flex-col">
                  <span class="text-sm text-foreground/60">Anúncios</span>
                  <span class="text-2xl font-bold text-foreground mt-1">{{ anuncioSelectedMetrics.quantidade }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-sm text-foreground/60">Participação</span>
                  <span class="text-2xl font-bold text-foreground mt-1">{{ anuncioSelectedMetrics.percentual }}%</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-sm text-foreground/60">Orçamento</span>
                  <span class="text-2xl font-bold text-foreground mt-1">{{ formatCurrency(anuncioSelectedMetrics.orcamento) }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-sm text-foreground/60">Ticket médio</span>
                  <span class="text-2xl font-bold text-foreground mt-1">{{ formatCurrency(anuncioSelectedMetrics.ticketMedio) }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-sm text-foreground/60">Leads</span>
                  <span class="text-2xl font-bold text-foreground mt-1">{{ anuncioSelectedMetrics.faturado }}</span>
                </div>
              </div>

              <!-- Gráfico timeline -->
              <ChartLine
                v-if="anunciosChartType === 'line' && anuncioTimelineSeries.length > 0"
                :key="'anuncio-' + anunciosTabAtivo + '-' + (anunciosPreset ?? 'custom') + '-' + (anunciosStartDate ?? '') + '-line'"
                :data="anuncioTimelineSeries"
                title=""
                height="300px"
                :primary-color="anuncioSelectedMetrics?.cor"
              />
              <ChartBar
                v-else-if="anunciosChartType === 'bar' && anuncioTimelineSeries.length > 0"
                :key="'anuncio-' + anunciosTabAtivo + '-' + (anunciosPreset ?? 'custom') + '-' + (anunciosStartDate ?? '') + '-bar'"
                :data="anuncioTimelineSeries"
                title=""
                height="300px"
                :color="anuncioSelectedMetrics?.cor"
              />
              <div v-else class="flex items-center justify-center h-[300px] text-foreground/40">
                Sem dados disponíveis
              </div>
            </template>
          </div>

          <!-- Novos Chats por Mês (Barras) -->
          <!-- <div class="bg-card rounded-lg border  p-6">
            <ChartBar
              v-if="dashboardData.novosChatsPorMes && dashboardData.novosChatsPorMes.length > 0"
              :data="
                dashboardData.novosChatsPorMes.map((item: any) => ({
                  label: item.mes,
                  value: item.total,
                }))
              "
              title="Novos Chats por Mês"
              height="350px"
            />
            <div v-else class="flex items-center justify-center h-[350px] text-foreground/40">
              Sem dados disponíveis
            </div>
          </div> -->

          <!-- Comissões por Dia (Barras) -->
          <!-- <div class="bg-card rounded-lg border  p-6">
            <ChartBar
              v-if="dashboardData.comissoesPorImovel && dashboardData.comissoesPorImovel.length > 0"
              :data="
                dashboardData.comissoesPorImovel.map((item: any) => ({
                  label: item.data,
                  value: item.total,
                }))
              "
              title="Comissões por Dia"
              height="350px"
              color="#4601FA"
              :is-currency="true"
            />
            <div v-else class="flex items-center justify-center h-[350px] text-foreground/40">
              Sem dados disponíveis
            </div>
          </div> -->
        <!-- </div> -->

        <!-- Fallback (sem dados) -->
        <!-- <div v-else class="bg-card rounded-lg border  p-8 text-center">
          <p class="text-foreground/60">Carregando dados do dashboard...</p>
        </div> -->

        <!-- Bottom: selectable metric timeline -->
        <div class="bg-card rounded-lg border  p-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
            <div class="flex items-center gap-3">
              <label class="text-sm text-foreground/60">Períodos:</label>
              <div class="flex items-center gap-2">
                <Button
                  v-for="p in [7,14,30,60]"
                  :key="p"
                  @click.prevent="() => setPreset(p)"
                  :variant="preset === p ? 'primary' : 'filter'"
                >
                  Últimos {{ p }} dias
                </Button>
                <ButtonCalendar
                  v-model="calendarRange"
                  :variant="customRange ? 'primary' : 'filter'"
                  @apply="handleCalendarApply"
                />
              </div>
            </div>
            <ChartToggle v-model="chartType" class="ml-4" />
          </div>
            <div class="flex items-center gap-3 border-b mb-6">
              <div role="tablist" class="flex items-center gap-2">
                <button
                  v-for="opt in metricOptions"
                  :key="opt.key"
                  role="tab"
                  :aria-selected="selectedMetric === opt.key"
                  @click="selectedMetric = opt.key"
                  :class="[
                    'px-3 py-2 text-sm font-medium rounded-t-md focus:outline-none',
                    selectedMetric === opt.key ? 'border-b-2 border-primary text-primary' : 'text-foreground/60 hover:text-foreground'
                  ]"
                >
                  {{ opt.label }}
                </button>
              </div>
              <Button class="ml-2 flex items-center" variant="ghost" @click.prevent="() => exportChart('csv')">
                <Download class="w-4 h-4 mr-2" />
                Exportar
              </Button>
            </div>
          

          <!-- Aggregated Metrics -->
          <div class="flex flex-wrap gap-6 mb-6 pb-4">
            <div
              v-for="(metric, index) in chartMetrics"
              :key="index"
              class="flex flex-col"
            >
              <LabelWithTooltip :label="metric.label" :tooltip="getMetricTooltip(metric.label)" class="text-sm text-foreground/60" />
              <span class="text-2xl font-bold text-foreground mt-1">{{ metric.value }}</span>
              <span v-if="metric.sublabel" class="text-xs text-foreground/50 mt-0.5">{{ metric.sublabel }}</span>
            </div>
          </div>

          <ChartLine
            v-if="chartType === 'line'"
            :key="selectedMetric + '|' + (preset ?? 'custom') + '|' + (startDate ?? '') + '|' + (endDate ?? '') + '|line'"
            :data="selectedSeries"
            :title="chartTitle"
            height="300px"
            :is-currency="selectedMetric === 'comissoes' || selectedMetric === 'faturamento'"
            :tooltip-items-resolver="tooltipItemsForMetric"
            :tooltip-title="tooltipTitleForMetric"
          />
          <ChartBar
            v-else
            :key="selectedMetric + '|' + (preset ?? 'custom') + '|' + (startDate ?? '') + '|' + (endDate ?? '') + '|bar'"
            :data="selectedSeries"
            :title="chartTitle"
            height="300px"
            :is-currency="selectedMetric === 'comissoes' || selectedMetric === 'faturamento'"
            :tooltip-items-resolver="tooltipItemsForMetric"
            :tooltip-title="tooltipTitleForMetric"
          />
        </div>

        <!-- Modal de Detalhes do Evento -->
        <div v-if="calendarEventModalOpen && calendarEventSelected" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div class="bg-card rounded-lg border p-6 w-full max-w-md shadow-lg">
            <!-- Header do Modal -->
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: calendarEventSelected.labelColor || 'var(--primary)' }"></div>
                <h2 class="text-lg font-semibold text-foreground">{{ calendarEventSelected.title }}</h2>
              </div>
              <button @click="closeEventModal" class="text-foreground/60 hover:text-foreground">
                <span class="text-2xl">&times;</span>
              </button>
            </div>

            <!-- Detalhes do Evento -->
            <div class="space-y-4 mb-6">
              <!-- Data -->
              <div>
                <p class="text-sm text-foreground/60">Data</p>
                <p class="font-medium text-foreground">{{ new Date(calendarEventSelected.start).toLocaleDateString('pt-BR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
              </div>

              <!-- Horário -->
              <div v-if="!calendarEventSelected.allDay">
                <p class="text-sm text-foreground/60">Horário</p>
                <p class="font-medium text-foreground flex items-center gap-2">
                  <Clock class="w-4 h-4" />
                  {{ formatEventTime(calendarEventSelected) }}
                </p>
              </div>
              <div v-else>
                <p class="text-sm text-foreground/60">Tipo</p>
                <p class="font-medium text-foreground">Dia inteiro</p>
              </div>

              <!-- Etiqueta -->
              <div v-if="calendarEventSelected.label">
                <p class="text-sm text-foreground/60">Etiqueta</p>
                <div class="flex items-center gap-2">
                  <div class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: calendarEventSelected.labelColor || '#888' }"></div>
                  <span class="font-medium text-foreground">{{ calendarEventSelected.label }}</span>
                </div>
              </div>
            </div>

            <!-- Ações -->
            <div class="flex gap-2">
              <Button variant="secondary" class="flex-1" @click="closeEventModal">Fechar</Button>
              <Button class="flex-1" @click="() => { closeEventModal(); window.location.href = route('admin.corretor.calendar.index') }">
                Ver Calendário
              </Button>
            </div>
          </div>
        </div>
      </div>
  </AuthLayout>
</template>

