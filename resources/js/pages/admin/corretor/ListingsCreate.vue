<script setup lang="ts">
/**
 * Página de criação de anúncio.
 * Permite vincular um imóvel existente ou criar um novo imóvel inline.
 * Um imóvel pode ter vários anúncios (1:N).
 */
import { ref, computed, watch } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import AuthLayout from '@/layouts/AuthLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import Select from '@/components/ui/select/Select.vue'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'
import Checkbox from '@/components/ui/checkbox/Checkbox.vue'
import {
  ArrowLeft,
  Save,
  Loader2,
  ChevronDown,
  Search,
  Home,
  Plus,
  LinkIcon,
  MapPin,
  X,
  Image,
  AlertCircle,
} from 'lucide-vue-next'

// Constants
import { IMOVEL_STATUS } from '@/constants/imovelStatus'
import { CONDICAO_IMOVEL } from '@/constants/condicaoImovel'
import { ANUNCIO_TIPOS, ANUNCIO_TIPOS_LABELS } from '@/constants/anuncioTypes'

// Interfaces
interface ImovelResumo {
  id: number
  nome: string
  codigo: string | null
  categoria: string | null
  cidade: string | null
  bairro: string | null
  valor_venda: number | string | null
  valor_locacao: number | string | null
  listings_count: number
  imageUrl: string | null
}

const props = defineProps<{
  imoveis: ImovelResumo[]
}>()

const page = usePage()

// Estado principal
const mode = ref<'existing' | 'new'>('existing')
const saving = ref(false)
const error = ref<string | null>(null)
const validationErrors = ref<Record<string, string[]>>({})

// Modo existente: seleção de imóvel
const searchQuery = ref('')
const selectedImovelId = ref<number | null>(null)

// Seções colapsáveis (modo novo)
const expandedSections = ref({
  anuncio: true,
  basico: true,
  valores: false,
  caracteristicas: false,
  endereco: false,
  proprietario: true,
})

// Dados do anúncio
const anuncioForm = ref({
  anuncio_ativo: true,
  anuncio_status: '',
  anuncio_tipos: [] as string[],
})

// Formulário do imóvel (modo novo)
const imovelForm = ref({
  nome: '',
  codigo: '',
  descricao: '',
  status: 'ativo',
  categoria: '',
  modalidade: 'venda',
  condicao: '',
  exclusividade: false,
  // Valores
  valor_venda: '',
  valor_locacao: '',
  valor_condominio: '',
  valor_iptu: '',
  aceita_financiamento: false,
  aceita_permuta: false,
  comissao_percent: '',
  comissao_valor: '',
  // Características
  quartos: '',
  suites: '',
  banheiros: '',
  vagas: '',
  salas: '',
  area_total: '',
  area_construida: '',
  area_util: '',
  ano_construcao: '',
  mobilia: '',
  varanda: false,
  areas_lazer: '',
  // Endereço
  cep: '',
  estado: '',
  cidade: '',
  bairro: '',
  endereco: '',
  numero: '',
  complemento: '',
  referencia: '',
  mostrar_endereco_completo: false,
  // Proprietário
  proprietario_nome: '',
  proprietario_telefone: '',
  proprietario_email: '',
  proprietario_documento: '',
})

// Computed
const filteredImoveis = computed(() => {
  if (!searchQuery.value.trim()) return props.imoveis
  const q = searchQuery.value.toLowerCase()
  return props.imoveis.filter((i: ImovelResumo) =>
    (i.nome && i.nome.toLowerCase().includes(q)) ||
    (i.codigo && i.codigo.toLowerCase().includes(q)) ||
    (i.cidade && i.cidade.toLowerCase().includes(q)) ||
    (i.bairro && i.bairro.toLowerCase().includes(q)) ||
    (i.categoria && i.categoria.toLowerCase().includes(q))
  )
})

const selectedImovel = computed(() => {
  if (!selectedImovelId.value) return null
  return props.imoveis.find((i: ImovelResumo) => i.id === selectedImovelId.value) || null
})

const canSubmit = computed(() => {
  // Validação comum: pelo menos um tipo de anúncio selecionado
  if (anuncioForm.value.anuncio_tipos.length === 0) {
    return false
  }

  if (mode.value === 'existing') {
    return selectedImovelId.value !== null
  }
  // Modo novo: campos obrigatórios
  return (
    imovelForm.value.proprietario_nome.trim() !== '' &&
    imovelForm.value.status.trim() !== '' &&
    imovelForm.value.modalidade.trim() !== '' &&
    imovelForm.value.condicao.trim() !== '' &&
    imovelForm.value.categoria.trim() !== ''
  )
})

// Métodos
function toggleSection(section: keyof typeof expandedSections.value) {
  expandedSections.value[section] = !expandedSections.value[section]
}

function selectImovel(id: number) {
  selectedImovelId.value = id
}

function clearSelection() {
  selectedImovelId.value = null
  searchQuery.value = ''
}

function formatCurrency(value: number | string | null | undefined): string {
  if (!value) return '—'
  const num = typeof value === 'string' ? parseFloat(value) : value
  if (isNaN(num)) return '—'
  return num.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

function getFieldError(field: string): string | null {
  const errs = validationErrors.value[field]
  return errs && errs.length > 0 ? errs[0] : null
}

async function buscarCep() {
  const cep = imovelForm.value.cep.replace(/\D/g, '')
  if (cep.length !== 8) return

  try {
    const resp = await fetch(`https://viacep.com.br/ws/${cep}/json/`)
    const data = await resp.json()

    if (!data.erro) {
      imovelForm.value.endereco = data.logradouro || ''
      imovelForm.value.bairro = data.bairro || ''
      imovelForm.value.cidade = data.localidade || ''
      imovelForm.value.estado = data.uf || ''
    }
  } catch (e) {
    console.error('Erro ao buscar CEP', e)
  }
}

function formatCep(event: Event) {
  const input = event.target as HTMLInputElement
  let value = input.value.replace(/\D/g, '')
  if (value.length > 5) {
    value = value.slice(0, 5) + '-' + value.slice(5, 8)
  }
  imovelForm.value.cep = value
}

async function submitForm() {
  saving.value = true
  error.value = null
  validationErrors.value = {}

  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''

    let payload: Record<string, any> = {
      mode: mode.value,
      anuncio_ativo: anuncioForm.value.anuncio_ativo,
      anuncio_status: anuncioForm.value.anuncio_status || null,
      anuncio_tipos: anuncioForm.value.anuncio_tipos,
    }

    if (mode.value === 'existing') {
      payload.imovel_id = selectedImovelId.value
    } else {
      payload.imovel = {
        nome: imovelForm.value.nome || null,
        codigo: imovelForm.value.codigo || null,
        descricao: imovelForm.value.descricao || null,
        status: imovelForm.value.status,
        categoria: imovelForm.value.categoria,
        modalidade: imovelForm.value.modalidade,
        condicao: imovelForm.value.condicao,
        exclusividade: imovelForm.value.exclusividade,
        // Valores
        valor_venda: imovelForm.value.valor_venda ? parseFloat(imovelForm.value.valor_venda) : null,
        valor_locacao: imovelForm.value.valor_locacao ? parseFloat(imovelForm.value.valor_locacao) : null,
        valor_condominio: imovelForm.value.valor_condominio ? parseFloat(imovelForm.value.valor_condominio) : null,
        valor_iptu: imovelForm.value.valor_iptu ? parseFloat(imovelForm.value.valor_iptu) : null,
        aceita_financiamento: imovelForm.value.aceita_financiamento,
        aceita_permuta: imovelForm.value.aceita_permuta,
        comissao_percent: imovelForm.value.comissao_percent ? parseFloat(imovelForm.value.comissao_percent) : null,
        comissao_valor: imovelForm.value.comissao_valor ? parseFloat(imovelForm.value.comissao_valor) : null,
        // Características
        quartos: imovelForm.value.quartos ? parseInt(imovelForm.value.quartos) : null,
        suites: imovelForm.value.suites ? parseInt(imovelForm.value.suites) : null,
        banheiros: imovelForm.value.banheiros ? parseInt(imovelForm.value.banheiros) : null,
        vagas: imovelForm.value.vagas ? parseInt(imovelForm.value.vagas) : null,
        salas: imovelForm.value.salas ? parseInt(imovelForm.value.salas) : null,
        area_total: imovelForm.value.area_total ? parseFloat(imovelForm.value.area_total) : null,
        area_construida: imovelForm.value.area_construida ? parseFloat(imovelForm.value.area_construida) : null,
        area_util: imovelForm.value.area_util ? parseFloat(imovelForm.value.area_util) : null,
        ano_construcao: imovelForm.value.ano_construcao ? parseInt(imovelForm.value.ano_construcao) : null,
        mobilia: imovelForm.value.mobilia || null,
        varanda: imovelForm.value.varanda,
        areas_lazer: imovelForm.value.areas_lazer || null,
        // Endereço
        cep: imovelForm.value.cep || null,
        estado: imovelForm.value.estado || null,
        cidade: imovelForm.value.cidade || null,
        bairro: imovelForm.value.bairro || null,
        endereco: imovelForm.value.endereco || null,
        numero: imovelForm.value.numero || null,
        complemento: imovelForm.value.complemento || null,
        referencia: imovelForm.value.referencia || null,
        mostrar_endereco_completo: imovelForm.value.mostrar_endereco_completo,
        // Proprietário
        proprietario_nome: imovelForm.value.proprietario_nome,
        proprietario_telefone: imovelForm.value.proprietario_telefone || null,
        proprietario_email: imovelForm.value.proprietario_email || null,
        proprietario_documento: imovelForm.value.proprietario_documento || null,
      }
    }

    const response = await axios.post('/admin/corretor/listings', payload, {
      headers: {
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      withCredentials: true,
    })

    // Redirecionar para a página de detalhes do anúncio criado
    const listingId = response.data?.listing?.id
    if (listingId) {
      window.location.href = `/admin/corretor/listings/${listingId}`
    } else {
      window.location.href = '/admin/corretor/listings'
    }
  } catch (e: any) {
    console.error('Erro ao criar anúncio', e)

    if (e.response?.status === 422 && e.response?.data?.errors) {
      validationErrors.value = e.response.data.errors
      error.value = 'Corrija os erros no formulário.'
    } else {
      error.value = e.response?.data?.error || e.response?.data?.message || 'Não foi possível criar o anúncio. Tente novamente.'
    }
  } finally {
    saving.value = false
  }
}

// Limpar erros ao trocar de modo
watch(mode, () => {
  error.value = null
  validationErrors.value = {}
})
</script>

<template>
  <Head>
    <title>Criar Anúncio</title>
    <meta name="description" content="Criar um novo anúncio vinculado a um imóvel" />
  </Head>

  <AuthLayout :modulo="String(page.props.modulo)">
    <div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
          <Link :href="route('admin.corretor.listings.index')" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-4">
            <ArrowLeft class="w-4 h-4 mr-1" />
            Voltar para Anúncios
          </Link>

          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h1 class="text-2xl font-bold text-foreground">Criar Anúncio</h1>
              <p class="text-sm text-muted-foreground mt-1">
                Vincule um imóvel existente ou crie um novo para associar ao anúncio
              </p>
            </div>

            <Button
              variant="primary"
              :disabled="saving || !canSubmit"
              @click="submitForm"
            >
              <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
              <Save v-else class="w-4 h-4 mr-2" />
              Criar Anúncio
            </Button>
          </div>
        </div>

        <!-- Mensagens de erro -->
        <div v-if="error" class="mb-4 p-4 bg-destructive/10 border border-destructive/20 rounded-lg text-destructive flex items-start gap-2">
          <AlertCircle class="w-5 h-5 flex-shrink-0 mt-0.5" />
          <div>
            <p>{{ error }}</p>
            <ul v-if="Object.keys(validationErrors).length" class="mt-2 text-sm space-y-1">
              <li v-for="(errs, field) in validationErrors" :key="field">
                {{ errs[0] }}
              </li>
            </ul>
          </div>
        </div>

        <!-- Seletor de Modo -->
        <div class="mb-6">
          <div class="grid grid-cols-2 gap-3">
            <button
              type="button"
              class="flex items-center gap-3 p-4 rounded-xl border-2 transition-all text-left"
              :class="mode === 'existing'
                ? 'border-primary bg-primary/5'
                : 'border-transparent bg-card hover:border-muted'"
              @click="mode = 'existing'"
            >
              <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="mode === 'existing' ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'">
                <LinkIcon class="w-5 h-5" />
              </div>
              <div>
                <p class="font-semibold text-foreground">Vincular Imóvel Existente</p>
                <p class="text-xs text-muted-foreground">Selecione um imóvel já cadastrado</p>
              </div>
            </button>

            <button
              type="button"
              class="flex items-center gap-3 p-4 rounded-xl border-2 transition-all text-left"
              :class="mode === 'new'
                ? 'border-primary bg-primary/5'
                : 'border-transparent bg-card hover:border-muted'"
              @click="mode = 'new'"
            >
              <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="mode === 'new' ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'">
                <Plus class="w-5 h-5" />
              </div>
              <div>
                <p class="font-semibold text-foreground">Criar Novo Imóvel</p>
                <p class="text-xs text-muted-foreground">Cadastre um imóvel e vincule ao anúncio</p>
              </div>
            </button>
          </div>
        </div>

        <div class="space-y-6">
          <!-- ========================================= -->
          <!-- MODO EXISTENTE: Seleção de Imóvel -->
          <!-- ========================================= -->
          <template v-if="mode === 'existing'">
            <!-- Imóvel selecionado -->
            <div v-if="selectedImovel" class="bg-card rounded-xl border border-primary p-4">
              <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-foreground">Imóvel Selecionado</h2>
                <button
                  type="button"
                  class="text-muted-foreground hover:text-destructive transition-colors"
                  @click="clearSelection"
                >
                  <X class="w-5 h-5" />
                </button>
              </div>

              <div class="flex gap-4">
                <div class="w-24 h-24 rounded-lg overflow-hidden bg-muted flex-shrink-0">
                  <img v-if="selectedImovel.imageUrl" :src="selectedImovel.imageUrl" :alt="selectedImovel.nome" class="w-full h-full object-cover" />
                  <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground">
                    <Image class="w-8 h-8" />
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <h3 class="font-semibold text-foreground truncate">{{ selectedImovel.nome || 'Sem nome' }}</h3>
                  <p v-if="selectedImovel.codigo" class="text-xs text-muted-foreground">Código: {{ selectedImovel.codigo }}</p>
                  <p v-if="selectedImovel.cidade" class="text-sm text-muted-foreground flex items-center gap-1 mt-1">
                    <MapPin class="w-3 h-3" />
                    {{ [selectedImovel.bairro, selectedImovel.cidade].filter(Boolean).join(', ') }}
                  </p>
                  <div class="flex items-center gap-3 mt-2">
                    <span v-if="selectedImovel.valor_venda" class="text-sm font-medium text-primary">
                      {{ formatCurrency(selectedImovel.valor_venda) }}
                    </span>
                    <span v-if="selectedImovel.valor_locacao" class="text-sm text-muted-foreground">
                      {{ formatCurrency(selectedImovel.valor_locacao) }}/mês
                    </span>
                    <span class="text-xs text-muted-foreground bg-muted rounded-full px-2 py-0.5">
                      {{ selectedImovel.listings_count }} anúncio(s) vinculado(s)
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Busca e lista de imóveis -->
            <div v-else class="bg-card rounded-xl border p-4">
              <h2 class="text-lg font-semibold mb-4">Selecione um Imóvel</h2>

              <!-- Campo de busca -->
              <div class="relative mb-4">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Buscar por nome, código, cidade, bairro..."
                  class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-[var(--border)] bg-[var(--card)] text-sm shadow-xs focus:border-[var(--ring)] focus:ring-[var(--ring)]/50 focus:ring-[3px] outline-none"
                />
              </div>

              <!-- Lista de imóveis -->
              <div class="space-y-2 max-h-[400px] overflow-y-auto">
                <button
                  v-for="imovel in filteredImoveis"
                  :key="imovel.id"
                  type="button"
                  class="w-full flex items-center gap-3 p-3 rounded-lg border border-transparent hover:border-primary hover:bg-primary/5 transition-all text-left"
                  @click="selectImovel(imovel.id)"
                >
                  <div class="w-16 h-16 rounded-lg overflow-hidden bg-muted flex-shrink-0">
                    <img v-if="imovel.imageUrl" :src="imovel.imageUrl" :alt="imovel.nome" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground">
                      <Home class="w-5 h-5" />
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                      <h3 class="font-medium text-foreground truncate">{{ imovel.nome || 'Sem nome' }}</h3>
                      <span v-if="imovel.categoria" class="text-xs bg-muted text-muted-foreground rounded-full px-2 py-0.5">{{ imovel.categoria }}</span>
                    </div>
                    <p v-if="imovel.codigo" class="text-xs text-muted-foreground">Cód: {{ imovel.codigo }}</p>
                    <div class="flex items-center gap-3 mt-0.5">
                      <p v-if="imovel.cidade" class="text-xs text-muted-foreground flex items-center gap-1">
                        <MapPin class="w-3 h-3" />
                        {{ [imovel.bairro, imovel.cidade].filter(Boolean).join(', ') }}
                      </p>
                      <span class="text-xs text-muted-foreground">
                        {{ imovel.listings_count }} anúncio(s)
                      </span>
                    </div>
                  </div>
                  <div class="text-right flex-shrink-0">
                    <p v-if="imovel.valor_venda" class="text-sm font-medium text-primary">
                      {{ formatCurrency(imovel.valor_venda) }}
                    </p>
                    <p v-if="imovel.valor_locacao" class="text-xs text-muted-foreground">
                      {{ formatCurrency(imovel.valor_locacao) }}/mês
                    </p>
                  </div>
                </button>

                <div v-if="filteredImoveis.length === 0" class="text-center py-8 text-muted-foreground">
                  <Home class="w-10 h-10 mx-auto mb-2 opacity-50" />
                  <p v-if="searchQuery">Nenhum imóvel encontrado para "{{ searchQuery }}"</p>
                  <p v-else>Nenhum imóvel cadastrado</p>
                  <button
                    type="button"
                    class="mt-2 text-sm text-primary hover:underline"
                    @click="mode = 'new'"
                  >
                    Criar um novo imóvel
                  </button>
                </div>
              </div>
            </div>
          </template>

          <!-- ========================================= -->
          <!-- MODO NOVO: Formulário de Criação de Imóvel -->
          <!-- ========================================= -->
          <template v-if="mode === 'new'">
            <!-- Seção: Proprietário (obrigatório) -->
            <div class="bg-card rounded-xl border" :class="getFieldError('imovel.proprietario_nome') ? 'border-destructive' : ''">
              <button
                class="w-full flex items-center justify-between p-4 text-left"
                type="button"
                @click="toggleSection('proprietario')"
              >
                <h2 class="text-lg font-semibold">
                  Proprietário
                  <span class="text-xs text-destructive ml-1">*obrigatório</span>
                </h2>
                <ChevronDown
                  class="w-5 h-5 text-muted-foreground transition-transform"
                  :class="{ 'rotate-180': expandedSections.proprietario }"
                />
              </button>

              <div v-show="expandedSections.proprietario" class="px-4 pb-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <Label for="proprietario_nome">Nome do Proprietário *</Label>
                    <Input
                      id="proprietario_nome"
                      v-model="imovelForm.proprietario_nome"
                      placeholder="Nome completo do proprietário"
                      class="mt-1"
                      :class="getFieldError('imovel.proprietario_nome') ? 'border-destructive' : ''"
                    />
                    <p v-if="getFieldError('imovel.proprietario_nome')" class="text-xs text-destructive mt-1">
                      {{ getFieldError('imovel.proprietario_nome') }}
                    </p>
                  </div>
                  <div>
                    <Label for="proprietario_telefone">Telefone</Label>
                    <Input id="proprietario_telefone" v-model="imovelForm.proprietario_telefone" placeholder="(00) 00000-0000" class="mt-1" />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <Label for="proprietario_email">E-mail</Label>
                    <Input id="proprietario_email" v-model="imovelForm.proprietario_email" type="email" placeholder="email@exemplo.com" class="mt-1" />
                  </div>
                  <div>
                    <Label for="proprietario_documento">CPF/CNPJ</Label>
                    <Input id="proprietario_documento" v-model="imovelForm.proprietario_documento" placeholder="Documento" class="mt-1" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Seção: Dados Básicos do Imóvel -->
            <div class="bg-card rounded-xl border">
              <button
                class="w-full flex items-center justify-between p-4 text-left"
                type="button"
                @click="toggleSection('basico')"
              >
                <h2 class="text-lg font-semibold">
                  Dados Básicos do Imóvel
                  <span class="text-xs text-destructive ml-1">*obrigatório</span>
                </h2>
                <ChevronDown
                  class="w-5 h-5 text-muted-foreground transition-transform"
                  :class="{ 'rotate-180': expandedSections.basico }"
                />
              </button>

              <div v-show="expandedSections.basico" class="px-4 pb-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <Label for="nome">Nome do Imóvel</Label>
                    <Input id="nome" v-model="imovelForm.nome" placeholder="Ex: Apartamento Centro" class="mt-1" />
                  </div>
                  <div>
                    <Label for="codigo">Código</Label>
                    <Input id="codigo" v-model="imovelForm.codigo" placeholder="Código interno" class="mt-1" />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <Label for="modalidade">Modalidade *</Label>
                    <Select id="modalidade" v-model="imovelForm.modalidade" class="mt-1">
                      <option value="">Selecione...</option>
                      <option value="venda">Venda</option>
                      <option value="locacao">Locação</option>
                    </Select>
                    <p v-if="getFieldError('imovel.modalidade')" class="text-xs text-destructive mt-1">
                      {{ getFieldError('imovel.modalidade') }}
                    </p>
                  </div>
                  <div>
                    <Label for="categoria">Categoria *</Label>
                    <Select id="categoria" v-model="imovelForm.categoria" class="mt-1">
                      <option value="">Selecione...</option>
                      <option value="apartamento">Apartamento</option>
                      <option value="casa">Casa</option>
                      <option value="terreno">Terreno</option>
                      <option value="comercial">Comercial</option>
                    </Select>
                    <p v-if="getFieldError('imovel.categoria')" class="text-xs text-destructive mt-1">
                      {{ getFieldError('imovel.categoria') }}
                    </p>
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <Label for="status">Status *</Label>
                    <Select id="status" v-model="imovelForm.status" class="mt-1">
                      <option value="">Selecione...</option>
                      <option v-for="opt in IMOVEL_STATUS" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                      </option>
                    </Select>
                    <p v-if="getFieldError('imovel.status')" class="text-xs text-destructive mt-1">
                      {{ getFieldError('imovel.status') }}
                    </p>
                  </div>
                  <div>
                    <Label for="condicao">Condição *</Label>
                    <Select id="condicao" v-model="imovelForm.condicao" class="mt-1">
                      <option value="">Selecione...</option>
                      <option v-for="opt in CONDICAO_IMOVEL" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                      </option>
                    </Select>
                    <p v-if="getFieldError('imovel.condicao')" class="text-xs text-destructive mt-1">
                      {{ getFieldError('imovel.condicao') }}
                    </p>
                  </div>
                </div>

                <div>
                  <Label for="descricao">Descrição</Label>
                  <textarea
                    id="descricao"
                    v-model="imovelForm.descricao"
                    placeholder="Descreva o imóvel..."
                    class="mt-1 w-full rounded-md border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm shadow-xs focus:border-[var(--ring)] focus:ring-[var(--ring)]/50 focus:ring-[3px] outline-none"
                    rows="4"
                  />
                </div>

                <div class="flex items-center gap-2">
                  <Switch v-model="imovelForm.exclusividade" />
                  <Label>Imóvel com Exclusividade</Label>
                </div>
              </div>
            </div>

            <!-- Seção: Valores -->
            <div class="bg-card rounded-xl border">
              <button
                class="w-full flex items-center justify-between p-4 text-left"
                type="button"
                @click="toggleSection('valores')"
              >
                <h2 class="text-lg font-semibold">Valores</h2>
                <ChevronDown
                  class="w-5 h-5 text-muted-foreground transition-transform"
                  :class="{ 'rotate-180': expandedSections.valores }"
                />
              </button>

              <div v-show="expandedSections.valores" class="px-4 pb-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <Label for="valor_venda">Valor de Venda (R$)</Label>
                    <Input id="valor_venda" v-model="imovelForm.valor_venda" type="number" step="0.01" placeholder="0,00" class="mt-1" />
                  </div>
                  <div>
                    <Label for="valor_locacao">Valor de Locação (R$/mês)</Label>
                    <Input id="valor_locacao" v-model="imovelForm.valor_locacao" type="number" step="0.01" placeholder="0,00" class="mt-1" />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <Label for="valor_condominio">Condomínio (R$/mês)</Label>
                    <Input id="valor_condominio" v-model="imovelForm.valor_condominio" type="number" step="0.01" placeholder="0,00" class="mt-1" />
                  </div>
                  <div>
                    <Label for="valor_iptu">IPTU (R$/ano)</Label>
                    <Input id="valor_iptu" v-model="imovelForm.valor_iptu" type="number" step="0.01" placeholder="0,00" class="mt-1" />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <Label for="comissao_percent">Comissão (%)</Label>
                    <Input id="comissao_percent" v-model="imovelForm.comissao_percent" type="number" step="0.01" placeholder="0,00" class="mt-1" />
                  </div>
                  <div>
                    <Label for="comissao_valor">Comissão Fixa (R$)</Label>
                    <Input id="comissao_valor" v-model="imovelForm.comissao_valor" type="number" step="0.01" placeholder="0,00" class="mt-1" />
                  </div>
                </div>

                <div class="flex flex-wrap gap-6">
                  <div class="flex items-center gap-2">
                    <Switch v-model="imovelForm.aceita_financiamento" />
                    <Label>Aceita Financiamento</Label>
                  </div>
                  <div class="flex items-center gap-2">
                    <Switch v-model="imovelForm.aceita_permuta" />
                    <Label>Aceita Permuta</Label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Seção: Características -->
            <div class="bg-card rounded-xl border">
              <button
                class="w-full flex items-center justify-between p-4 text-left"
                type="button"
                @click="toggleSection('caracteristicas')"
              >
                <h2 class="text-lg font-semibold">Características</h2>
                <ChevronDown
                  class="w-5 h-5 text-muted-foreground transition-transform"
                  :class="{ 'rotate-180': expandedSections.caracteristicas }"
                />
              </button>

              <div v-show="expandedSections.caracteristicas" class="px-4 pb-4 space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                  <div>
                    <Label for="quartos">Quartos</Label>
                    <Input id="quartos" v-model="imovelForm.quartos" type="number" min="0" class="mt-1" />
                  </div>
                  <div>
                    <Label for="suites">Suítes</Label>
                    <Input id="suites" v-model="imovelForm.suites" type="number" min="0" class="mt-1" />
                  </div>
                  <div>
                    <Label for="banheiros">Banheiros</Label>
                    <Input id="banheiros" v-model="imovelForm.banheiros" type="number" min="0" class="mt-1" />
                  </div>
                  <div>
                    <Label for="vagas">Vagas</Label>
                    <Input id="vagas" v-model="imovelForm.vagas" type="number" min="0" class="mt-1" />
                  </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                  <div>
                    <Label for="salas">Salas</Label>
                    <Input id="salas" v-model="imovelForm.salas" type="number" min="0" class="mt-1" />
                  </div>
                  <div>
                    <Label for="ano_construcao">Ano Construção</Label>
                    <Input id="ano_construcao" v-model="imovelForm.ano_construcao" type="number" min="1900" max="2100" class="mt-1" />
                  </div>
                  <div>
                    <Label for="mobilia">Mobília</Label>
                    <Select id="mobilia" v-model="imovelForm.mobilia" class="mt-1">
                      <option value="">Selecione...</option>
                      <option value="mobiliado">Mobiliado</option>
                      <option value="semi-mobiliado">Semi-mobiliado</option>
                      <option value="sem-mobilia">Sem mobília</option>
                    </Select>
                  </div>
                  <div class="flex items-end">
                    <div class="flex items-center gap-2">
                      <Switch v-model="imovelForm.varanda" />
                      <Label>Varanda</Label>
                    </div>
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div>
                    <Label for="area_total">Área Total (m²)</Label>
                    <Input id="area_total" v-model="imovelForm.area_total" type="number" step="0.01" class="mt-1" />
                  </div>
                  <div>
                    <Label for="area_construida">Área Construída (m²)</Label>
                    <Input id="area_construida" v-model="imovelForm.area_construida" type="number" step="0.01" class="mt-1" />
                  </div>
                  <div>
                    <Label for="area_util">Área Útil (m²)</Label>
                    <Input id="area_util" v-model="imovelForm.area_util" type="number" step="0.01" class="mt-1" />
                  </div>
                </div>

                <div>
                  <Label for="areas_lazer">Áreas de Lazer</Label>
                  <textarea
                    id="areas_lazer"
                    v-model="imovelForm.areas_lazer"
                    placeholder="Ex: Piscina, churrasqueira, salão de festas..."
                    class="mt-1 w-full rounded-md border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm shadow-xs focus:border-[var(--ring)] focus:ring-[var(--ring)]/50 focus:ring-[3px] outline-none"
                    rows="2"
                  />
                </div>
              </div>
            </div>

            <!-- Seção: Endereço -->
            <div class="bg-card rounded-xl border">
              <button
                class="w-full flex items-center justify-between p-4 text-left"
                type="button"
                @click="toggleSection('endereco')"
              >
                <h2 class="text-lg font-semibold">Endereço</h2>
                <ChevronDown
                  class="w-5 h-5 text-muted-foreground transition-transform"
                  :class="{ 'rotate-180': expandedSections.endereco }"
                />
              </button>

              <div v-show="expandedSections.endereco" class="px-4 pb-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div>
                    <Label for="cep">CEP</Label>
                    <Input
                      id="cep"
                      :value="imovelForm.cep"
                      placeholder="00000-000"
                      class="mt-1"
                      maxlength="9"
                      @input="formatCep"
                      @blur="buscarCep"
                    />
                  </div>
                  <div>
                    <Label for="estado">Estado</Label>
                    <Input id="estado" v-model="imovelForm.estado" placeholder="UF" maxlength="2" class="mt-1" />
                  </div>
                  <div>
                    <Label for="cidade">Cidade</Label>
                    <Input id="cidade" v-model="imovelForm.cidade" placeholder="Cidade" class="mt-1" />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <Label for="bairro">Bairro</Label>
                    <Input id="bairro" v-model="imovelForm.bairro" placeholder="Bairro" class="mt-1" />
                  </div>
                  <div>
                    <Label for="endereco_rua">Endereço</Label>
                    <Input id="endereco_rua" v-model="imovelForm.endereco" placeholder="Rua/Avenida" class="mt-1" />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div>
                    <Label for="numero">Número</Label>
                    <Input id="numero" v-model="imovelForm.numero" placeholder="Nº" class="mt-1" />
                  </div>
                  <div>
                    <Label for="complemento">Complemento</Label>
                    <Input id="complemento" v-model="imovelForm.complemento" placeholder="Apto, Bloco..." class="mt-1" />
                  </div>
                  <div>
                    <Label for="referencia">Referência</Label>
                    <Input id="referencia" v-model="imovelForm.referencia" placeholder="Próximo a..." class="mt-1" />
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <Switch v-model="imovelForm.mostrar_endereco_completo" />
                  <Label>Mostrar endereço completo no anúncio</Label>
                </div>
              </div>
            </div>
          </template>

          <!-- ========================================= -->
          <!-- SEÇÃO COMUM: Configurações do Anúncio -->
          <!-- ========================================= -->
          <div class="bg-card rounded-xl border">
            <button
              class="w-full flex items-center justify-between p-4 text-left"
              type="button"
              @click="toggleSection('anuncio')"
            >
              <h2 class="text-lg font-semibold">Configurações do Anúncio</h2>
              <ChevronDown
                class="w-5 h-5 text-muted-foreground transition-transform"
                :class="{ 'rotate-180': expandedSections.anuncio }"
              />
            </button>

            <div v-show="expandedSections.anuncio" class="px-4 pb-4 space-y-4">
              <div>
                <Label class="block mb-3">
                  Plataformas de Anúncio
                  <span class="text-xs text-destructive ml-1">*obrigatório</span>
                </Label>
                <p class="text-sm text-muted-foreground mb-3">Selecione ao menos uma plataforma onde o anúncio será criado</p>
                
                <div class="space-y-3">
                  <div v-for="(label, tipo) in ANUNCIO_TIPOS_LABELS" :key="tipo">
                    <label :for="`tipo-${tipo}`" class="flex items-center gap-3 cursor-pointer">
                      <Checkbox
                        :id="`tipo-${tipo}`"
                        :model-value="anuncioForm.anuncio_tipos.includes(tipo)"
                        @update:modelValue="(checked: boolean) => {
                          if (checked && !anuncioForm.anuncio_tipos.includes(tipo)) {
                            anuncioForm.anuncio_tipos.push(tipo)
                          } else if (!checked) {
                            anuncioForm.anuncio_tipos = anuncioForm.anuncio_tipos.filter((t: string) => t !== tipo)
                          }
                        }"
                      />
                      <span class="text-sm font-medium">{{ label }}</span>
                    </label>
                  </div>
                </div>

                <p v-if="anuncioForm.anuncio_tipos.length === 0" class="mt-2 text-xs text-destructive">
                  Selecione ao menos uma plataforma
                </p>
              </div>

              <div class="flex items-center justify-between">
                <div>
                  <Label>Anúncio Ativo</Label>
                  <p class="text-sm text-muted-foreground">Quando ativo, o anúncio estará visível publicamente</p>
                </div>
                <Switch v-model="anuncioForm.anuncio_ativo" />
              </div>

              <div>
                <Label for="anuncio_status">Observações do Anúncio</Label>
                <textarea
                  id="anuncio_status"
                  v-model="anuncioForm.anuncio_status"
                  placeholder="Adicione observações sobre o anúncio..."
                  class="mt-1 w-full rounded-md border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm shadow-xs focus:border-[var(--ring)] focus:ring-[var(--ring)]/50 focus:ring-[3px] outline-none"
                  rows="3"
                />
              </div>
            </div>
          </div>

          <!-- Botões de Ação -->
          <div class="flex justify-end gap-4 pt-4">
            <Link :href="route('admin.corretor.listings.index')">
              <Button variant="outline">Cancelar</Button>
            </Link>
            <Button
              variant="primary"
              :disabled="saving || !canSubmit"
              @click="submitForm"
            >
              <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
              <Save v-else class="w-4 h-4 mr-2" />
              Criar Anúncio
            </Button>
          </div>
        </div>
      </div>
    </div>
  </AuthLayout>
</template>
