<script setup lang="ts">
/**
 * Modal expandido de detalhes e edição completa de imóvel.
 * Permite editar todos os campos principais organizados em seções colapsáveis.
 */
import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import {
  Dialog,
  DialogScrollContent,
  DialogHeader,
  DialogFooter,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import Select from '@/components/ui/select/Select.vue'
import Switch from '@/components/ui/switch/Switch.vue'
import { IMOVEL_STATUS } from '@/constants/imovelStatus'
import { CONDICAO_IMOVEL } from '@/constants/condicaoImovel'
import { Save, X, Loader2, ChevronDown, Eye } from 'lucide-vue-next'

// Interfaces
interface ImovelBasico {
  id: number
  nome: string
  status?: string
  descricao?: string | null
  cidade?: string | null
  imageUrl?: string | null
  codigo?: string | null
  condicao?: string | null
}

interface ImovelDetalhado extends ImovelBasico {
  codigo?: string | null
  categoria?: string | null
  exclusividade?: boolean
  endereco?: {
    cep?: string | null
    endereco?: string | null
    numero?: string | null
    bairro?: string | null
    cidade?: string | null
    estado?: string | null
    complemento?: string | null
    referencia?: string | null
    andar?: string | null
    torre?: string | null
    mostrar_endereco_completo?: boolean
  }
  valores?: {
    valor_venda?: number | string | null
    valor_locacao?: number | string | null
    valor_condominio?: number | string | null
    valor_iptu?: number | string | null
    gas?: number | string | null
    luz?: number | string | null
    aceita_financiamento?: boolean
    aceita_permuta?: boolean
    comissao_percent?: number | string | null
    comissao_valor?: number | string | null
  }
  caracteristicas?: {
    quartos?: number | null
    suites?: number | null
    banheiros?: number | null
    vagas?: number | null
    salas?: number | null
    area_construida?: number | string | null
    area_util?: number | string | null
    area_total?: number | string | null
    area_terreno_largura?: number | string | null
    area_terreno_comprimento?: number | string | null
    ano_construcao?: number | string | null
    mobilia?: string | null
    varanda?: boolean
    areas_lazer?: string | null
    itens?: string | null
  }
  proprietario?: {
    nome?: string | null
    telefone?: string | null
    email?: string | null
  }
  imagens?: Array<{ id: number; url?: string | null }>
  listing?: {
    id?: number | null
    anuncio_ativo?: boolean | null
    anuncio_status?: string | null
    created_at?: string | null
    updated_at?: string | null
  } | null
}

const props = defineProps<{
  open: boolean
  imovel: ImovelBasico | null
  startEditing?: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'updated', imovel: ImovelBasico): void
}>()

// Estado
const loadingDetails = ref(false)
const saving = ref(false)
const toggleLoading = ref(false)
const error = ref<string | null>(null)
const detalhes = ref<ImovelDetalhado | null>(null)
const isEditing = ref(false)

// Seções expandidas
const expandedSections = ref<Record<string, boolean>>({
  basic: true,
  listing: false,
  location: false,
  values: false,
  characteristics: false,
  owner: false,
})

// Formulário
const form = ref({
  // Básico
  codigo: '',
  nome: '',
  status: '',
  condicao: '',
  categoria: '',
  descricao: '',
  exclusividade: false,
  
  // Localização
  cep: '',
  endereco: '',
  numero: '',
  bairro: '',
  cidade: '',
  estado: '',
  complemento: '',
  referencia: '',
  andar: '',
  torre: '',
  mostrar_endereco_completo: false,
  
  // Valores
  valor_venda: '',
  valor_locacao: '',
  valor_condominio: '',
  valor_iptu: '',
  gas: '',
  luz: '',
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
  area_construida: '',
  area_util: '',
  area_total: '',
  area_terreno_largura: '',
  area_terreno_comprimento: '',
  ano_construcao: '',
  mobilia: '',
  varanda: false,
  areas_lazer: '',
  itens: '',
  
  // Proprietário
  proprietario_nome: '',
  proprietario_telefone: '',
  proprietario_email: '',
  
  // Anúncio
  anuncio_ativo: false,
  anuncio_status: '',
})

// Watchers
watch(() => props.open, async (isOpen: boolean) => {
  if (isOpen && props.imovel) {
    error.value = null
    // Respect optional startEditing prop so parent can open modal directly in edit mode
    isEditing.value = props.startEditing ?? false
    expandedSections.value = { basic: true, listing: false, location: false, values: false, characteristics: false, owner: false }
    await fetchDetails(props.imovel.id)
  }
})

// Funções
async function fetchDetails(id: number) {
  loadingDetails.value = true
  error.value = null
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    const resp = await axios.get(`/admin/corretor/imoveis/${id}`, {
      headers: {
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
      withCredentials: true,
    })
    
    const data = resp.data?.imovel || resp.data
    detalhes.value = data
    
    // Preenche formulário com valores atuais
    form.value = {
      codigo: data.codigo || '',
      nome: data.nome || '',
      status: data.status || '',
      condicao: data.condicao || '',
      categoria: data.categoria || '',
      descricao: data.descricao || '',
      exclusividade: data.exclusividade || false,
      
      cep: data.endereco?.cep || '',
      endereco: data.endereco?.endereco || '',
      numero: data.endereco?.numero || '',
      bairro: data.endereco?.bairro || '',
      cidade: data.endereco?.cidade || '',
      estado: data.endereco?.estado || '',
      complemento: data.endereco?.complemento || '',
      referencia: data.endereco?.referencia || '',
      andar: data.endereco?.andar || '',
      torre: data.endereco?.torre || '',
      mostrar_endereco_completo: data.endereco?.mostrar_endereco_completo || false,
      
      valor_venda: data.valores?.valor_venda?.toString() || '',
      valor_locacao: data.valores?.valor_locacao?.toString() || '',
      valor_condominio: data.valores?.valor_condominio?.toString() || '',
      valor_iptu: data.valores?.valor_iptu?.toString() || '',
      gas: data.valores?.gas?.toString() || '',
      luz: data.valores?.luz?.toString() || '',
      aceita_financiamento: data.valores?.aceita_financiamento || false,
      aceita_permuta: data.valores?.aceita_permuta || false,
      comissao_percent: data.valores?.comissao_percent?.toString() || '',
      comissao_valor: data.valores?.comissao_valor?.toString() || '',
      
      quartos: data.caracteristicas?.quartos?.toString() || '',
      suites: data.caracteristicas?.suites?.toString() || '',
      banheiros: data.caracteristicas?.banheiros?.toString() || '',
      vagas: data.caracteristicas?.vagas?.toString() || '',
      salas: data.caracteristicas?.salas?.toString() || '',
      area_construida: data.caracteristicas?.area_construida?.toString() || '',
      area_util: data.caracteristicas?.area_util?.toString() || '',
      area_total: data.caracteristicas?.area_total?.toString() || '',
      area_terreno_largura: data.caracteristicas?.area_terreno_largura?.toString() || '',
      area_terreno_comprimento: data.caracteristicas?.area_terreno_comprimento?.toString() || '',
      ano_construcao: data.caracteristicas?.ano_construcao?.toString() || '',
      mobilia: data.caracteristicas?.mobilia || '',
      varanda: data.caracteristicas?.varanda || false,
      areas_lazer: data.caracteristicas?.areas_lazer || '',
      itens: data.caracteristicas?.itens || '',
      
      proprietario_nome: data.proprietario?.nome || '',
      proprietario_telefone: data.proprietario?.telefone || '',
      proprietario_email: data.proprietario?.email || '',
      
      anuncio_ativo: data.listing?.anuncio_ativo || false,
      anuncio_status: data.listing?.anuncio_status || '',
    }
  } catch (e: any) {
    console.error('Erro ao buscar detalhes do imóvel', e)
    error.value = 'Não foi possível carregar os detalhes do imóvel.'
  } finally {
    loadingDetails.value = false
  }
}

async function saveChanges() {
  if (!props.imovel) return
  
  saving.value = true
  error.value = null
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    const payload: Record<string, any> = {
      // Básico
      codigo: form.value.codigo || null,
      nome: form.value.nome,
      status: form.value.status,
      condicao: form.value.condicao,
      categoria: form.value.categoria,
      descricao: form.value.descricao,
      exclusividade: form.value.exclusividade,
      
      // Localização
      cep: form.value.cep || null,
      endereco: form.value.endereco || null,
      numero: form.value.numero || null,
      bairro: form.value.bairro || null,
      cidade: form.value.cidade || null,
      estado: form.value.estado || null,
      complemento: form.value.complemento || null,
      referencia: form.value.referencia || null,
      andar: form.value.andar || null,
      torre: form.value.torre || null,
      mostrar_endereco_completo: form.value.mostrar_endereco_completo,
      
      // Valores
      valor_venda: form.value.valor_venda ? parseFloat(form.value.valor_venda) : null,
      valor_locacao: form.value.valor_locacao ? parseFloat(form.value.valor_locacao) : null,
      valor_condominio: form.value.valor_condominio ? parseFloat(form.value.valor_condominio) : null,
      valor_iptu: form.value.valor_iptu ? parseFloat(form.value.valor_iptu) : null,
      gas: form.value.gas ? parseFloat(form.value.gas) : null,
      luz: form.value.luz ? parseFloat(form.value.luz) : null,
      aceita_financiamento: form.value.aceita_financiamento,
      aceita_permuta: form.value.aceita_permuta,
      comissao_percent: form.value.comissao_percent ? parseFloat(form.value.comissao_percent) : null,
      comissao_valor: form.value.comissao_valor ? parseFloat(form.value.comissao_valor) : null,
      
      // Características
      quartos: form.value.quartos ? parseInt(form.value.quartos) : null,
      suites: form.value.suites ? parseInt(form.value.suites) : null,
      banheiros: form.value.banheiros ? parseInt(form.value.banheiros) : null,
      vagas: form.value.vagas ? parseInt(form.value.vagas) : null,
      salas: form.value.salas ? parseInt(form.value.salas) : null,
      area_construida: form.value.area_construida ? parseFloat(form.value.area_construida) : null,
      area_util: form.value.area_util ? parseFloat(form.value.area_util) : null,
      area_total: form.value.area_total ? parseFloat(form.value.area_total) : null,
      area_terreno_largura: form.value.area_terreno_largura ? parseFloat(form.value.area_terreno_largura) : null,
      area_terreno_comprimento: form.value.area_terreno_comprimento ? parseFloat(form.value.area_terreno_comprimento) : null,
      ano_construcao: form.value.ano_construcao ? parseInt(form.value.ano_construcao) : null,
      mobilia: form.value.mobilia || null,
      varanda: form.value.varanda,
      areas_lazer: form.value.areas_lazer || null,
      itens: form.value.itens || null,
      
      // Proprietário (não é editado no modal, mas mantém para completude)
      proprietario_nome: form.value.proprietario_nome || null,
      proprietario_telefone: form.value.proprietario_telefone || null,
      proprietario_email: form.value.proprietario_email || null,
      
      // Anúncio
      anuncio_ativo: form.value.anuncio_ativo,
      anuncio_status: form.value.anuncio_status || null,
    }
    
    const resp = await axios.put(`/admin/corretor/imoveis/${props.imovel.id}`, payload, {
      headers: {
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
      withCredentials: true,
    })
    
    const updatedData = resp.data?.imovel || resp.data

    // Emit the full updated payload so parent can replace the item atomically
    const toEmit = Object.assign({ id: props.imovel.id }, updatedData)
    // Ensure critical fields exist on the emitted payload so parent can update the card immediately
    toEmit.condicao = updatedData.condicao ?? form.value.condicao ?? detalhes.value?.condicao ?? props.imovel?.condicao ?? null
    toEmit.status = updatedData.status ?? form.value.status ?? detalhes.value?.status ?? props.imovel?.status ?? null
    toEmit.codigo = updatedData.codigo ?? form.value.codigo ?? detalhes.value?.codigo ?? props.imovel?.codigo ?? null
    if (!toEmit.imageUrl) toEmit.imageUrl = props.imovel.imageUrl
    emit('updated', toEmit)

    await fetchDetails(props.imovel.id)

    isEditing.value = props.startEditing ?? false
  } catch (e: any) {
    console.error('Erro ao salvar imóvel', e)
    error.value = e.response?.data?.message || 'Não foi possível salvar as alterações.'
  } finally {
    saving.value = false
  }
}

function openShowPage() {
  if (!props.imovel) return
  router.visit(route('admin.corretor.imoveis.show', { id: props.imovel.id }))
}

function handleClose() {
  emit('close')
}

function handleCancelEdit() {
  // If the parent opened the modal already in edit mode (startEditing=true),
  // treat Cancel as closing the modal to avoid switching back to the internal
  // 'view' state on pages like the Imovel details page. Otherwise, just
  // leave the modal open and switch to view mode.
  if (props.startEditing) {
    emit('close')
    return
  }

  isEditing.value = false
}

async function toggleAnuncioAtivo() {
  if (!props.imovel || !detalhes.value?.listing) return
  
  const checked = !detalhes.value.listing.anuncio_ativo
  toggleLoading.value = true
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    const resp = await axios.put(`/admin/corretor/listings/${detalhes.value.listing.id}`, { anuncio_ativo: checked }, {
      headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
      withCredentials: true,
    })
    
    const listing = resp?.data?.listing ?? resp?.data
    if (listing && detalhes.value) {
      detalhes.value.listing = listing
      form.value.anuncio_ativo = listing.anuncio_ativo || false
    }
  } catch (err) {
    console.error('Falha ao atualizar anúncio', err)
  } finally {
    toggleLoading.value = false
  }
}

function toggleSection(section: string) {
  expandedSections.value[section] = !expandedSections.value[section]
}

function formatCurrency(value: number | string | null | undefined): string {
  if (!value) return '—'
  const num = typeof value === 'string' ? parseFloat(value) : value
  if (isNaN(num)) return '—'
  return num.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

function formatDate(value: string | null | undefined): string {
  if (!value) return '—'
  try {
    const date = new Date(value)
    return date.toLocaleString('pt-BR', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })
  } catch {
    return '—'
  }
}

const imagemPrincipal = computed(() => {
  if (detalhes.value?.imagens?.length) {
    return detalhes.value.imagens[0].url
  }
  return props.imovel?.imageUrl
})

const enderecoFormatado = computed(() => {
  const end = detalhes.value?.endereco
  if (!end) return null
  const partes = [end.endereco, end.numero, end.bairro, end.cidade, end.estado].filter(Boolean)
  return partes.length ? partes.join(', ') : null
})
</script>

<template>
  <Dialog :open="open" @update:open="(val: boolean) => !val && handleClose()">
      <DialogScrollContent class="max-w-3xl relative">
        <DialogHeader class="flex flex-row items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
          <DialogTitle class="text-xl font-semibold truncate">
            {{ isEditing ? 'Editar Imóvel' : (detalhes?.nome || imovel?.nome || 'Detalhes do Imóvel') }}
          </DialogTitle>
          <DialogDescription v-if="enderecoFormatado && !isEditing" class="text-sm text-muted mt-1 truncate">
            {{ enderecoFormatado }}
          </DialogDescription>
        </div>
        <div class="flex items-center gap-2">
          <Button v-if="!isEditing" variant="ghost" size="sm" @click="openShowPage" class="flex-shrink-0 inline-flex items-center gap-1" title="Abrir página do imóvel">
            <Eye class="w-4 h-4" />
            <span class="text-sm">Ver página</span>
          </Button>
          <Button v-if="!isEditing" variant="secondary" size="sm" @click="isEditing = true" class="flex-shrink-0" :disabled="loadingDetails" title="Editar">
            Editar
          </Button>
        </div>
      </DialogHeader>

      <!-- Carregando -->
      <div v-if="loadingDetails" class="flex items-center justify-center py-12">
        <Loader2 class="w-8 h-8 animate-spin text-primary" />
      </div>

      <!-- Erro -->
      <div v-else-if="error && !detalhes" class="text-center py-8">
        <p class="text-destructive mb-4">{{ error }}</p>
        <Button variant="outline" @click="imovel && fetchDetails(imovel.id)">Tentar novamente</Button>
      </div>

      <!-- Conteúdo -->
      <div v-else class="space-y-4">
        <!-- Imagem principal -->
        <div v-if="imagemPrincipal && !isEditing" class="w-full h-48 rounded-lg overflow-hidden bg-muted">
          <img :src="imagemPrincipal" :alt="detalhes?.nome || imovel?.nome" class="w-full h-full object-cover" />
        </div>
        <div v-else-if="!isEditing" class="w-full h-48 rounded-lg bg-muted flex items-center justify-center text-muted-foreground">
          Sem imagem
        </div>

        <!-- MODO VISUALIZAÇÃO -->
        <div v-if="!isEditing" class="space-y-3">
          <!-- Seção: Informações Básicas -->
          <div class="border rounded-lg">
            <Button variant="section" @click="toggleSection('basic')">
              <span class="font-medium">Informações Básicas</span>
              <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.basic ? 'rotate-180' : '']" />
            </Button>
            <div v-if="expandedSections.basic" class="border-t px-4 py-3 bg-secondary/20 space-y-2 text-sm">
              <div class="grid grid-cols-2 gap-4">
                <div><span class="text-muted-foreground">Código:</span> <span class="ml-2 font-medium">{{ detalhes?.codigo || '—' }}</span></div>
                <div><span class="text-muted-foreground">Nome:</span> <span class="ml-2 font-medium">{{ detalhes?.nome || '—' }}</span></div>
                <div><span class="text-muted-foreground">Status:</span> <span class="ml-2 font-medium">{{ IMOVEL_STATUS.find((s: any) => s.value === detalhes?.status)?.label || detalhes?.status || '—' }}</span></div>
                <div><span class="text-muted-foreground">Condição:</span> <span class="ml-2 font-medium">{{ CONDICAO_IMOVEL.find((c: any) => c.value === detalhes?.condicao)?.label || detalhes?.condicao || '—' }}</span></div>
                <div><span class="text-muted-foreground">Categoria:</span> <span class="ml-2 font-medium">{{ detalhes?.categoria || '—' }}</span></div>
                <div><span class="text-muted-foreground">Exclusividade:</span> <span class="ml-2 font-medium">{{ detalhes?.exclusividade ? 'Sim' : 'Não' }}</span></div>
              </div>
              <div v-if="detalhes?.descricao"><span class="text-muted-foreground">Descrição:</span> <p class="text-sm mt-1 whitespace-pre-wrap">{{ detalhes.descricao }}</p></div>
            </div>
          </div>

          <!-- Seção: Localização -->
          <div v-if="detalhes?.endereco" class="border rounded-lg">
            <Button variant="section" @click="toggleSection('location')">
              <span class="font-medium">Localização</span>
              <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.location ? 'rotate-180' : '']" />
            </Button>
            <div v-if="expandedSections.location" class="border-t px-4 py-3 bg-secondary/20 space-y-2 text-sm">
              <div class="grid grid-cols-2 gap-4">
                <div><span class="text-muted-foreground">CEP:</span> <span class="ml-2 font-medium">{{ detalhes.endereco.cep || '—' }}</span></div>
                <div><span class="text-muted-foreground">Endereço:</span> <span class="ml-2 font-medium">{{ detalhes.endereco.endereco || '—' }}</span></div>
                <div><span class="text-muted-foreground">Número:</span> <span class="ml-2 font-medium">{{ detalhes.endereco.numero || '—' }}</span></div>
                <div><span class="text-muted-foreground">Bairro:</span> <span class="ml-2 font-medium">{{ detalhes.endereco.bairro || '—' }}</span></div>
                <div><span class="text-muted-foreground">Cidade:</span> <span class="ml-2 font-medium">{{ detalhes.endereco.cidade || '—' }}</span></div>
                <div><span class="text-muted-foreground">Estado:</span> <span class="ml-2 font-medium">{{ detalhes.endereco.estado || '—' }}</span></div>
                <div class="col-span-2"><span class="text-muted-foreground">Complemento:</span> <span class="ml-2 font-medium">{{ detalhes.endereco.complemento || '—' }}</span></div>
              </div>
            </div>
          </div>

          <!-- Seção: Valores -->
          <div v-if="detalhes?.valores" class="border rounded-lg">
            <Button variant="section" @click="toggleSection('values')">
              <span class="font-medium">Valores</span>
              <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.values ? 'rotate-180' : '']" />
            </Button>
            <div v-if="expandedSections.values" class="border-t px-4 py-3 bg-secondary/20 space-y-2 text-sm">
              <div class="grid grid-cols-2 gap-4">
                <div><span class="text-muted-foreground">Venda:</span> <span class="ml-2 font-medium">{{ formatCurrency(detalhes.valores.valor_venda) }}</span></div>
                <div><span class="text-muted-foreground">Locação:</span> <span class="ml-2 font-medium">{{ formatCurrency(detalhes.valores.valor_locacao) }}</span></div>
                <div><span class="text-muted-foreground">Condomínio:</span> <span class="ml-2 font-medium">{{ formatCurrency(detalhes.valores.valor_condominio) }}</span></div>
                <div><span class="text-muted-foreground">IPTU:</span> <span class="ml-2 font-medium">{{ formatCurrency(detalhes.valores.valor_iptu) }}</span></div>
                <div><span class="text-muted-foreground">Gás:</span> <span class="ml-2 font-medium">{{ formatCurrency(detalhes.valores.gas) }}</span></div>
                <div><span class="text-muted-foreground">Luz:</span> <span class="ml-2 font-medium">{{ formatCurrency(detalhes.valores.luz) }}</span></div>
              </div>
            </div>
          </div>

          <!-- Seção: Características -->
          <div v-if="detalhes?.caracteristicas" class="border rounded-lg">
            <Button variant="section" @click="toggleSection('characteristics')">
              <span class="font-medium">Características</span>
              <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.characteristics ? 'rotate-180' : '']" />
            </Button>
            <div v-if="expandedSections.characteristics" class="border-t px-4 py-3 bg-secondary/20 space-y-2 text-sm">
              <div class="grid grid-cols-3 gap-4">
                <div><span class="text-muted-foreground">Quartos:</span> <span class="ml-2 font-medium">{{ detalhes.caracteristicas.quartos ?? '—' }}</span></div>
                <div><span class="text-muted-foreground">Suítes:</span> <span class="ml-2 font-medium">{{ detalhes.caracteristicas.suites ?? '—' }}</span></div>
                <div><span class="text-muted-foreground">Banheiros:</span> <span class="ml-2 font-medium">{{ detalhes.caracteristicas.banheiros ?? '—' }}</span></div>
                <div><span class="text-muted-foreground">Vagas:</span> <span class="ml-2 font-medium">{{ detalhes.caracteristicas.vagas ?? '—' }}</span></div>
                <div><span class="text-muted-foreground">Salas:</span> <span class="ml-2 font-medium">{{ detalhes.caracteristicas.salas ?? '—' }}</span></div>
                <div><span class="text-muted-foreground">Ano:</span> <span class="ml-2 font-medium">{{ detalhes.caracteristicas.ano_construcao ?? '—' }}</span></div>
                <div class="col-span-3"><span class="text-muted-foreground">Área Construída:</span> <span class="ml-2 font-medium">{{ detalhes.caracteristicas.area_construida ? `${detalhes.caracteristicas.area_construida} m²` : '—' }}</span></div>
              </div>
            </div>
          </div>

          <!-- Seção: Proprietário -->
          <div v-if="detalhes?.proprietario" class="border rounded-lg">
            <Button variant="section" @click="toggleSection('owner')">
              <span class="font-medium">Proprietário</span>
              <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.owner ? 'rotate-180' : '']" />
            </Button>
            <div v-if="expandedSections.owner" class="border-t px-4 py-3 bg-secondary/20 space-y-2 text-sm">
              <div class="grid grid-cols-2 gap-4">
                <div><span class="text-muted-foreground">Nome:</span> <span class="ml-2 font-medium">{{ detalhes.proprietario.nome || '—' }}</span></div>
                <div><span class="text-muted-foreground">Telefone:</span> <span class="ml-2 font-medium">{{ detalhes.proprietario.telefone || '—' }}</span></div>
                <div class="col-span-2"><span class="text-muted-foreground">E-mail:</span> <span class="ml-2 font-medium">{{ detalhes.proprietario.email || '—' }}</span></div>
              </div>
            </div>
          </div>

          <!-- Seção: Anúncio -->
          <div v-if="detalhes?.listing" class="border rounded-lg">
            <Button variant="section" @click="toggleSection('listing')">
              <span class="font-medium">Anúncio</span>
              <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.listing ? 'rotate-180' : '']" />
            </Button>
            <div v-if="expandedSections.listing" class="border-t px-4 py-3 bg-secondary/20 space-y-3 text-sm">
              <div class="grid grid-cols-2 gap-4">
                <div><span class="text-muted-foreground">ID Anúncio:</span> <span class="ml-2 font-medium">{{ detalhes.listing.id || '—' }}</span></div>
                <div class="flex items-center justify-between">
                  <div>
                  <span class="text-muted-foreground">Status:</span>
                  <span v-if="detalhes.listing.anuncio_ativo" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200">
                      Ativo
                    </span>
                    <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-200">
                      Inativo
                    </span>
                  </div>
                  <div class="flex items-center">
                      <Switch
                        :model-value="detalhes?.listing?.anuncio_ativo === true"
                        :loading="toggleLoading"
                        :aria-label="`Ativar anúncio do imóvel ${detalhes?.nome}`"
                        @update:model-value="toggleAnuncioAtivo"
                      />
                    </div>
                </div>
              </div>
              <div v-if="detalhes.listing.anuncio_status">
                <span class="text-muted-foreground">Status Customizado:</span> <span class="ml-2 font-medium">{{ detalhes.listing.anuncio_status }}</span>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div><span class="text-muted-foreground">Publicado em:</span> <span class="ml-2 font-medium">{{ formatDate(detalhes.listing.created_at) }}</span></div>
                <div><span class="text-muted-foreground">Atualizado em:</span> <span class="ml-2 font-medium">{{ formatDate(detalhes.listing.updated_at) }}</span></div>
              </div>
            </div>
          </div>
        </div>

        <!-- MODO EDIÇÃO -->
        <div v-else class="space-y-6">
          <!-- Seção: Informações Básicas (EDIÇÃO) -->
          <div class="border rounded-lg p-4 space-y-4">
            <h3 class="font-medium">Informações Básicas</h3>
            <Input id="edit-codigo" label="Código" v-model="form.codigo" placeholder="Código interno" />
            <Input id="edit-nome" label="Nome" v-model="form.nome" placeholder="Nome do imóvel" required />
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-[var(--text-primary)] mb-1">Status</label>
                <Select v-model="form.status" class="w-full">
                  <option v-for="s in IMOVEL_STATUS" :key="s.value" :value="s.value">{{ s.label }}</option>
                </Select>
              </div>
              <div>
                <label class="block text-sm font-medium text-[var(--text-primary)] mb-1">Condição</label>
                <Select v-model="form.condicao" class="w-full">
                  <option value="">Selecione...</option>
                  <option v-for="c in CONDICAO_IMOVEL" :key="c.value" :value="c.value">{{ c.label }}</option>
                </Select>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-categoria" label="Categoria" v-model="form.categoria" placeholder="Casa, Apartamento..." />
              <div class="flex items-end pb-1">
                <div class="inline-flex items-center gap-3 text-sm cursor-pointer">
                  <Switch v-model="form.exclusividade" />
                  <span class="text-sm">Exclusividade</span>
                </div>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-[var(--text-primary)] mb-1">Descrição</label>
              <textarea v-model="form.descricao" rows="3" class="w-full rounded-md border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm shadow-xs focus:border-[var(--ring)] focus:ring-[var(--ring)]/50 focus:ring-[3px] outline-none" placeholder="Descrição do imóvel" />
            </div>
          </div>

          <!-- Seção: Localização (EDIÇÃO) -->
          <div class="border rounded-lg p-4 space-y-4">
            <h3 class="font-medium">Localização</h3>
            <Input id="edit-cep" label="CEP" v-model="form.cep" placeholder="00000-000" />
            <Input id="edit-endereco" label="Endereço" v-model="form.endereco" placeholder="Rua..." />
            <div class="grid grid-cols-3 gap-4">
              <Input id="edit-numero" label="Número" v-model="form.numero" placeholder="123" />
              <Input id="edit-andar" label="Andar" v-model="form.andar" placeholder="5º" />
              <Input id="edit-torre" label="Torre" v-model="form.torre" placeholder="A" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-bairro" label="Bairro" v-model="form.bairro" placeholder="Bairro" />
              <Input id="edit-cidade" label="Cidade" v-model="form.cidade" placeholder="Cidade" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-estado" label="Estado" v-model="form.estado" placeholder="SP" />
              <Input id="edit-complemento" label="Complemento" v-model="form.complemento" placeholder="Apt 101" />
            </div>
            <Input id="edit-referencia" label="Referência" v-model="form.referencia" placeholder="Próximo a..." />
            <div class="inline-flex items-center gap-3 text-sm cursor-pointer">
              <Switch v-model="form.mostrar_endereco_completo" />
              <span class="text-sm">Mostrar endereço completo</span>
            </div>
          </div>

          <!-- Seção: Valores (EDIÇÃO) -->
          <div class="border rounded-lg p-4 space-y-4">
            <h3 class="font-medium">Valores</h3>
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-valor-venda" label="Valor Venda (R$)" v-model="form.valor_venda" type="number" step="0.01" placeholder="0,00" />
              <Input id="edit-valor-locacao" label="Valor Locação (R$)" v-model="form.valor_locacao" type="number" step="0.01" placeholder="0,00" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-valor-condominio" label="Condomínio (R$)" v-model="form.valor_condominio" type="number" step="0.01" placeholder="0,00" />
              <Input id="edit-valor-iptu" label="IPTU (R$)" v-model="form.valor_iptu" type="number" step="0.01" placeholder="0,00" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-gas" label="Gás (R$)" v-model="form.gas" type="number" step="0.01" placeholder="0,00" />
              <Input id="edit-luz" label="Luz (R$)" v-model="form.luz" type="number" step="0.01" placeholder="0,00" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-comissao-percent" label="Comissão (%)" v-model="form.comissao_percent" type="number" step="0.01" placeholder="0" />
              <Input id="edit-comissao-valor" label="Comissão (R$)" v-model="form.comissao_valor" type="number" step="0.01" placeholder="0,00" />
            </div>
            <div class="flex gap-4">
              <div class="inline-flex items-center gap-3 text-sm cursor-pointer">
                <Switch v-model="form.aceita_financiamento" />
                <span class="text-sm">Aceita Financiamento</span>
              </div>
              <div class="inline-flex items-center gap-3 text-sm cursor-pointer">
                <Switch v-model="form.aceita_permuta" />
                <span class="text-sm">Aceita Permuta</span>
              </div>
            </div>
          </div>

          <!-- Seção: Características (EDIÇÃO) -->
          <div class="border rounded-lg p-4 space-y-4">
            <h3 class="font-medium">Características</h3>
            <div class="grid grid-cols-3 gap-4">
              <Input id="edit-quartos" label="Quartos" v-model="form.quartos" type="number" min="0" />
              <Input id="edit-suites" label="Suítes" v-model="form.suites" type="number" min="0" />
              <Input id="edit-banheiros" label="Banheiros" v-model="form.banheiros" type="number" min="0" />
            </div>
            <div class="grid grid-cols-3 gap-4">
              <Input id="edit-vagas" label="Vagas" v-model="form.vagas" type="number" min="0" />
              <Input id="edit-salas" label="Salas" v-model="form.salas" type="number" min="0" />
              <Input id="edit-ano-construcao" label="Ano Construção" v-model="form.ano_construcao" type="number" min="1900" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-area-construida" label="Área Construída (m²)" v-model="form.area_construida" type="number" step="0.01" placeholder="0,00" />
              <Input id="edit-area-total" label="Área Total (m²)" v-model="form.area_total" type="number" step="0.01" placeholder="0,00" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-area-util" label="Área Útil (m²)" v-model="form.area_util" type="number" step="0.01" placeholder="0,00" />
              <div>
                <label class="block text-sm font-medium text-[var(--text-primary)] mb-1">Mobília</label>
                <Select v-model="form.mobilia" class="w-full">
                  <option value="">Selecione...</option>
                  <option value="mobiliado">Mobiliado</option>
                  <option value="semi">Semi-mobiliado</option>
                  <option value="nao">Não mobiliado</option>
                </Select>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-terreno-largura" label="Terreno Largura (m)" v-model="form.area_terreno_largura" type="number" step="0.01" />
              <Input id="edit-terreno-comprimento" label="Terreno Comprimento (m)" v-model="form.area_terreno_comprimento" type="number" step="0.01" />
            </div>
            <div class="inline-flex items-center gap-3 text-sm cursor-pointer">
              <Switch v-model="form.varanda" />
              <span class="text-sm">Tem Varanda</span>
            </div>
            <Input id="edit-areas-lazer" label="Áreas de Lazer" v-model="form.areas_lazer" placeholder="Piscina, churrasqueira..." />
            <Input id="edit-itens" label="Itens/Amenidades" v-model="form.itens" placeholder="Elevador, guarda-roupa..." />
          </div>

          <!-- Seção: Anúncio (EDIÇÃO)
          <div v-if="detalhes?.listing" class="border rounded-lg p-4 space-y-4">
            <h3 class="font-medium">Anúncio</h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-[var(--text-primary)]">Status de Publicação</label>
                <div class="inline-flex items-center gap-3 text-sm cursor-pointer">
                  <Switch v-model="form.anuncio_ativo" />
                  <span class="text-sm">{{ form.anuncio_ativo ? 'Ativo' : 'Inativo' }}</span>
                </div>
              </div>
              <Input id="edit-anuncio-status" label="Status Customizado" v-model="form.anuncio_status" placeholder="Ex: Em análise, Moderado..." />
              <div class="grid grid-cols-2 gap-4 pt-2 border-t">
                <div class="text-sm">
                  <span class="text-muted-foreground block mb-1">Publicado em:</span>
                  <span class="font-medium">{{ formatDate(detalhes.listing?.created_at) }}</span>
                </div>
                <div class="text-sm">
                  <span class="text-muted-foreground block mb-1">Atualizado em:</span>
                  <span class="font-medium">{{ formatDate(detalhes.listing?.updated_at) }}</span>
                </div>
              </div>
            </div>
          </div> -->

          <!-- Erro ao salvar -->
          <div v-if="error" class="text-sm text-destructive bg-destructive/10 p-3 rounded">{{ error }}</div>
        </div>
      </div>

      <DialogFooter class="mt-6">
        <div class="flex w-full justify-end">
          <div class="flex gap-2">
            <template v-if="isEditing">
              <Button variant="ghost" @click="handleCancelEdit" :disabled="saving">
                Cancelar
              </Button>
              <Button @click="saveChanges" :disabled="saving" loadingPosition="start" variant="primary">
                <X v-if="saving" class="w-4 h-4 mr-1 animate-spin" />
                <Save v-else class="w-4 h-4 mr-1" />
                Salvar
              </Button>
            </template>
          </div>
        </div>
      </DialogFooter>
    </DialogScrollContent>
  </Dialog>
</template>


