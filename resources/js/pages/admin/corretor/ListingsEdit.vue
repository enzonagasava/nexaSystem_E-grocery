<script setup lang="ts">
/**
 * Página de edição do anúncio com formulário completo para editar dados do imóvel.
 * Permite editar status do anúncio plus dados básicos, valores e características do imóvel.
 */
import { ref, computed, watch } from 'vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import axios from 'axios'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { Button } from '@/components/ui/button'
import Input from '@/components/ui/input/Input.vue'
import Select from '@/components/ui/select/Select.vue'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'
import {
  ArrowLeft,
  Save,
  Loader2,
  ChevronDown,
  Eye,
} from 'lucide-vue-next'

// Constants
import { IMOVEL_STATUS } from '@/constants/imovelStatus'
import { CONDICAO_IMOVEL } from '@/constants/condicaoImovel'

// Interfaces
interface ListingData {
  id: number
  anuncio_ativo: boolean
  anuncio_status: string | null
  created_at: string
  updated_at: string
  imovel: ImovelData
}

interface ImovelData {
  id: number
  codigo: string | null
  nome: string
  descricao: string | null
  status: string | null
  categoria: string | null
  finalidade: string | null
  modalidade: string | null
  condicao: string | null
  exclusividade: boolean
  endereco: {
    cep: string | null
    estado: string | null
    cidade: string | null
    bairro: string | null
    endereco: string | null
    numero: string | null
    complemento: string | null
    referencia: string | null
    mostrar_endereco_completo: boolean
  }
  valores: {
    valor_venda: number | string | null
    valor_locacao: number | string | null
    valor_condominio: number | string | null
    valor_iptu: number | string | null
    aceita_financiamento: boolean
    aceita_permuta: boolean
    comissao_percent: number | string | null
    comissao_valor: number | string | null
  }
  caracteristicas: {
    area_total: number | string | null
    area_construida: number | string | null
    area_util: number | string | null
    quartos: number | null
    suites: number | null
    banheiros: number | null
    vagas: number | null
    salas: number | null
    andar: number | null
    ano_construcao: number | null
    mobilia: string | null
    itens: string[] | null
    areas_lazer: string | null
    varanda: boolean
  }
  proprietario: {
    nome: string | null
    telefone: string | null
    email: string | null
    documento: string | null
  }
  imagens: Array<{ id: number; url: string | null; ordem: number; original_name: string }>
  plantas: Array<{ id: number; url: string; original_name: string; mime_type: string }>
  videos: Array<{ id: number; url: string; original_name: string; mime_type: string }>
  imageUrl: string | null
}

const props = defineProps<{
  listing: ListingData
}>()

const page = usePage()

// Estado
const saving = ref(false)
const error = ref<string | null>(null)
const successMessage = ref<string | null>(null)

// Seções expandidas
const expandedSections = ref({
  anuncio: true,
  basico: true,
  valores: true,
  caracteristicas: false,
  endereco: false,
  midia: false,
})

// Formulário
const form = ref({
  // Anúncio
  anuncio_ativo: props.listing.anuncio_ativo,
  anuncio_status: props.listing.anuncio_status || '',
  
  // Básico do imóvel
  nome: props.listing.imovel.nome || '',
  codigo: props.listing.imovel.codigo || '',
  descricao: props.listing.imovel.descricao || '',
  status: props.listing.imovel.status || '',
  categoria: props.listing.imovel.categoria || '',
  condicao: props.listing.imovel.condicao || '',
  exclusividade: props.listing.imovel.exclusividade || false,
  
  // Valores
  valor_venda: props.listing.imovel.valores.valor_venda?.toString() || '',
  valor_locacao: props.listing.imovel.valores.valor_locacao?.toString() || '',
  valor_condominio: props.listing.imovel.valores.valor_condominio?.toString() || '',
  valor_iptu: props.listing.imovel.valores.valor_iptu?.toString() || '',
  aceita_financiamento: props.listing.imovel.valores.aceita_financiamento || false,
  aceita_permuta: props.listing.imovel.valores.aceita_permuta || false,
  comissao_percent: props.listing.imovel.valores.comissao_percent?.toString() || '',
  comissao_valor: props.listing.imovel.valores.comissao_valor?.toString() || '',
  
  // Características
  quartos: props.listing.imovel.caracteristicas.quartos?.toString() || '',
  suites: props.listing.imovel.caracteristicas.suites?.toString() || '',
  banheiros: props.listing.imovel.caracteristicas.banheiros?.toString() || '',
  vagas: props.listing.imovel.caracteristicas.vagas?.toString() || '',
  salas: props.listing.imovel.caracteristicas.salas?.toString() || '',
  area_total: props.listing.imovel.caracteristicas.area_total?.toString() || '',
  area_construida: props.listing.imovel.caracteristicas.area_construida?.toString() || '',
  area_util: props.listing.imovel.caracteristicas.area_util?.toString() || '',
  ano_construcao: props.listing.imovel.caracteristicas.ano_construcao?.toString() || '',
  mobilia: props.listing.imovel.caracteristicas.mobilia || '',
  varanda: props.listing.imovel.caracteristicas.varanda || false,
  areas_lazer: props.listing.imovel.caracteristicas.areas_lazer || '',
  
  // Endereço
  cep: props.listing.imovel.endereco.cep || '',
  estado: props.listing.imovel.endereco.estado || '',
  cidade: props.listing.imovel.endereco.cidade || '',
  bairro: props.listing.imovel.endereco.bairro || '',
  endereco: props.listing.imovel.endereco.endereco || '',
  numero: props.listing.imovel.endereco.numero || '',
  complemento: props.listing.imovel.endereco.complemento || '',
  referencia: props.listing.imovel.endereco.referencia || '',
})

// Computed
const hasChanges = computed(() => {
  return (
    form.value.anuncio_ativo !== props.listing.anuncio_ativo ||
    form.value.anuncio_status !== (props.listing.anuncio_status || '') ||
    form.value.nome !== (props.listing.imovel.nome || '') ||
    form.value.descricao !== (props.listing.imovel.descricao || '') ||
    form.value.status !== (props.listing.imovel.status || '') ||
    form.value.valor_venda !== (props.listing.imovel.valores.valor_venda?.toString() || '') ||
    form.value.valor_locacao !== (props.listing.imovel.valores.valor_locacao?.toString() || '')
  )
})

// Métodos
function toggleSection(section: keyof typeof expandedSections.value) {
  expandedSections.value[section] = !expandedSections.value[section]
}

async function saveChanges() {
  saving.value = true
  error.value = null
  successMessage.value = null
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    
    const payload = {
      anuncio_ativo: form.value.anuncio_ativo,
      anuncio_status: form.value.anuncio_status || null,
      
      imovel: {
        nome: form.value.nome,
        codigo: form.value.codigo || null,
        descricao: form.value.descricao || null,
        status: form.value.status,
        categoria: form.value.categoria || null,
        condicao: form.value.condicao || null,
        exclusividade: form.value.exclusividade,
        
        valor_venda: form.value.valor_venda ? parseFloat(form.value.valor_venda) : null,
        valor_locacao: form.value.valor_locacao ? parseFloat(form.value.valor_locacao) : null,
        valor_condominio: form.value.valor_condominio ? parseFloat(form.value.valor_condominio) : null,
        valor_iptu: form.value.valor_iptu ? parseFloat(form.value.valor_iptu) : null,
        aceita_financiamento: form.value.aceita_financiamento,
        aceita_permuta: form.value.aceita_permuta,
        comissao_percent: form.value.comissao_percent ? parseFloat(form.value.comissao_percent) : null,
        comissao_valor: form.value.comissao_valor ? parseFloat(form.value.comissao_valor) : null,
        
        quartos: form.value.quartos ? parseInt(form.value.quartos) : null,
        suites: form.value.suites ? parseInt(form.value.suites) : null,
        banheiros: form.value.banheiros ? parseInt(form.value.banheiros) : null,
        vagas: form.value.vagas ? parseInt(form.value.vagas) : null,
        salas: form.value.salas ? parseInt(form.value.salas) : null,
        area_total: form.value.area_total ? parseFloat(form.value.area_total) : null,
        area_construida: form.value.area_construida ? parseFloat(form.value.area_construida) : null,
        area_util: form.value.area_util ? parseFloat(form.value.area_util) : null,
        ano_construcao: form.value.ano_construcao ? parseInt(form.value.ano_construcao) : null,
        mobilia: form.value.mobilia || null,
        varanda: form.value.varanda,
        areas_lazer: form.value.areas_lazer || null,
        
        cep: form.value.cep || null,
        estado: form.value.estado || null,
        cidade: form.value.cidade || null,
        bairro: form.value.bairro || null,
        endereco: form.value.endereco || null,
        numero: form.value.numero || null,
        complemento: form.value.complemento || null,
        referencia: form.value.referencia || null,
      }
    }
    
    await axios.put(`/admin/corretor/listings/${props.listing.id}`, payload, {
      headers: {
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      withCredentials: true,
    })
    
    successMessage.value = 'Alterações salvas com sucesso!'
    
    setTimeout(() => {
      successMessage.value = null
    }, 3000)
  } catch (e: any) {
    console.error('Erro ao salvar', e)
    error.value = e.response?.data?.message || 'Não foi possível salvar as alterações.'
  } finally {
    saving.value = false
  }
}

async function buscarCep() {
  const cep = form.value.cep.replace(/\D/g, '')
  if (cep.length !== 8) return
  
  try {
    const resp = await fetch(`https://viacep.com.br/ws/${cep}/json/`)
    const data = await resp.json()
    
    if (!data.erro) {
      form.value.endereco = data.logradouro || ''
      form.value.bairro = data.bairro || ''
      form.value.cidade = data.localidade || ''
      form.value.estado = data.uf || ''
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
  form.value.cep = value
}
</script>

<template>
  <Head>
    <title>Editar Anúncio - {{ listing.imovel.nome }}</title>
    <meta name="description" :content="`Editar anúncio: ${listing.imovel.nome}`" />
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
              <h1 class="text-2xl font-bold text-foreground">Editar Anúncio</h1>
              <p class="text-sm text-muted-foreground mt-1">{{ listing.imovel.nome }} - Código: {{ listing.imovel.codigo || 'N/A' }}</p>
            </div>
            
            <div class="flex items-center gap-3">
              <Link :href="route('admin.corretor.listings.show', listing.id)">
                <Button variant="outline" size="sm">
                  <Eye class="w-4 h-4 mr-2" />
                  Ver Detalhes
                </Button>
              </Link>
              
              <Button 
                variant="primary" 
                :disabled="saving"
                @click="saveChanges"
              >
                <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                <Save v-else class="w-4 h-4 mr-2" />
                Salvar
              </Button>
            </div>
          </div>
        </div>

        <!-- Mensagens -->
        <div v-if="error" class="mb-4 p-4 bg-destructive/10 border border-destructive/20 rounded-lg text-destructive">
          {{ error }}
        </div>
        <div v-if="successMessage" class="mb-4 p-4 bg-green-500/10 border border-green-500/20 rounded-lg text-green-600">
          {{ successMessage }}
        </div>

        <!-- Formulário -->
        <div class="space-y-6">
          <!-- Seção: Status do Anúncio -->
          <div class="bg-card rounded-xl border">
            <button 
              class="w-full flex items-center justify-between p-4 text-left"
              type="button"
              @click="toggleSection('anuncio')"
            >
              <h2 class="text-lg font-semibold">Status do Anúncio</h2>
              <ChevronDown 
                class="w-5 h-5 text-muted-foreground transition-transform" 
                :class="{ 'rotate-180': expandedSections.anuncio }"
              />
            </button>
            
            <div v-show="expandedSections.anuncio" class="px-4 pb-4 space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <Label>Anúncio Ativo</Label>
                  <p class="text-sm text-muted-foreground">Quando ativo, o anúncio estará visível publicamente</p>
                </div>
                <Switch v-model="form.anuncio_ativo" />
              </div>
              
              <div>
                <Label for="anuncio_status">Observações do Anúncio</Label>
                <textarea 
                  id="anuncio_status"
                  v-model="form.anuncio_status" 
                  placeholder="Adicione observações sobre o status do anúncio..."
                  class="mt-1 w-full rounded-md border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm shadow-xs focus:border-[var(--ring)] focus:ring-[var(--ring)]/50 focus:ring-[3px] outline-none"
                  rows="3"
                />
              </div>
            </div>
          </div>

          <!-- Seção: Dados Básicos -->
          <div class="bg-card rounded-xl border">
            <button 
              class="w-full flex items-center justify-between p-4 text-left"
              type="button"
              @click="toggleSection('basico')"
            >
              <h2 class="text-lg font-semibold">Dados Básicos do Imóvel</h2>
              <ChevronDown 
                class="w-5 h-5 text-muted-foreground transition-transform" 
                :class="{ 'rotate-180': expandedSections.basico }"
              />
            </button>
            
            <div v-show="expandedSections.basico" class="px-4 pb-4 space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <Label for="nome">Nome do Imóvel *</Label>
                  <Input id="nome" v-model="form.nome" placeholder="Ex: Apartamento Centro" class="mt-1" />
                </div>
                <div>
                  <Label for="codigo">Código</Label>
                  <Input id="codigo" v-model="form.codigo" placeholder="Código interno" class="mt-1" />
                </div>
              </div>
              
              <div>
                <Label for="descricao">Descrição</Label>
                <textarea 
                  id="descricao"
                  v-model="form.descricao" 
                  placeholder="Descreva o imóvel..."
                  class="mt-1 w-full rounded-md border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm shadow-xs focus:border-[var(--ring)] focus:ring-[var(--ring)]/50 focus:ring-[3px] outline-none"
                  rows="4"
                />
              </div>
              
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <Label for="status">Status</Label>
                  <Select id="status" v-model="form.status" class="mt-1">
                    <option value="">Selecione...</option>
                    <option v-for="opt in IMOVEL_STATUS" :key="opt.value" :value="opt.value">
                      {{ opt.label }}
                    </option>
                  </Select>
                </div>
                <div>
                  <Label for="categoria">Categoria</Label>
                  <Input id="categoria" v-model="form.categoria" placeholder="Ex: Apartamento" class="mt-1" />
                </div>
                <div>
                  <Label for="condicao">Condição</Label>
                  <Select id="condicao" v-model="form.condicao" class="mt-1">
                    <option value="">Selecione...</option>
                    <option v-for="opt in CONDICAO_IMOVEL" :key="opt.value" :value="opt.value">
                      {{ opt.label }}
                    </option>
                  </Select>
                </div>
              </div>
              
              <div class="flex items-center gap-2">
                <Switch v-model="form.exclusividade" />
                <Label for="exclusividade">Imóvel com Exclusividade</Label>
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
                  <Input 
                    id="valor_venda" 
                    v-model="form.valor_venda" 
                    type="number" 
                    step="0.01" 
                    placeholder="0,00" 
                    class="mt-1" 
                  />
                </div>
                <div>
                  <Label for="valor_locacao">Valor de Locação (R$/mês)</Label>
                  <Input 
                    id="valor_locacao" 
                    v-model="form.valor_locacao" 
                    type="number" 
                    step="0.01" 
                    placeholder="0,00" 
                    class="mt-1" 
                  />
                </div>
              </div>
              
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <Label for="valor_condominio">Condomínio (R$/mês)</Label>
                  <Input 
                    id="valor_condominio" 
                    v-model="form.valor_condominio" 
                    type="number" 
                    step="0.01" 
                    placeholder="0,00" 
                    class="mt-1" 
                  />
                </div>
                <div>
                  <Label for="valor_iptu">IPTU (R$/ano)</Label>
                  <Input 
                    id="valor_iptu" 
                    v-model="form.valor_iptu" 
                    type="number" 
                    step="0.01" 
                    placeholder="0,00" 
                    class="mt-1" 
                  />
                </div>
              </div>
              
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <Label for="comissao_percent">Comissão (%)</Label>
                  <Input 
                    id="comissao_percent" 
                    v-model="form.comissao_percent" 
                    type="number" 
                    step="0.01" 
                    placeholder="0,00" 
                    class="mt-1" 
                  />
                </div>
                <div>
                  <Label for="comissao_valor">Comissão Fixa (R$)</Label>
                  <Input 
                    id="comissao_valor" 
                    v-model="form.comissao_valor" 
                    type="number" 
                    step="0.01" 
                    placeholder="0,00" 
                    class="mt-1" 
                  />
                </div>
              </div>
              
              <div class="flex flex-wrap gap-6">
                <div class="flex items-center gap-2">
                  <Switch v-model="form.aceita_financiamento" />
                  <Label for="aceita_financiamento">Aceita Financiamento</Label>
                </div>
                <div class="flex items-center gap-2">
                  <Switch v-model="form.aceita_permuta" />
                  <Label for="aceita_permuta">Aceita Permuta</Label>
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
                  <Input id="quartos" v-model="form.quartos" type="number" min="0" class="mt-1" />
                </div>
                <div>
                  <Label for="suites">Suítes</Label>
                  <Input id="suites" v-model="form.suites" type="number" min="0" class="mt-1" />
                </div>
                <div>
                  <Label for="banheiros">Banheiros</Label>
                  <Input id="banheiros" v-model="form.banheiros" type="number" min="0" class="mt-1" />
                </div>
                <div>
                  <Label for="vagas">Vagas</Label>
                  <Input id="vagas" v-model="form.vagas" type="number" min="0" class="mt-1" />
                </div>
              </div>
              
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                  <Label for="salas">Salas</Label>
                  <Input id="salas" v-model="form.salas" type="number" min="0" class="mt-1" />
                </div>
                <div>
                  <Label for="ano_construcao">Ano Construção</Label>
                  <Input id="ano_construcao" v-model="form.ano_construcao" type="number" min="1900" max="2100" class="mt-1" />
                </div>
                <div>
                  <Label for="mobilia">Mobília</Label>
                  <Select id="mobilia" v-model="form.mobilia" class="mt-1">
                    <option value="">Selecione...</option>
                    <option value="mobiliado">Mobiliado</option>
                    <option value="semi-mobiliado">Semi-mobiliado</option>
                    <option value="sem-mobilia">Sem mobília</option>
                  </Select>
                </div>
                <div class="flex items-end">
                  <div class="flex items-center gap-2">
                    <Switch v-model="form.varanda" />
                    <Label for="varanda">Varanda</Label>
                  </div>
                </div>
              </div>
              
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <Label for="area_total">Área Total (m²)</Label>
                  <Input id="area_total" v-model="form.area_total" type="number" step="0.01" class="mt-1" />
                </div>
                <div>
                  <Label for="area_construida">Área Construída (m²)</Label>
                  <Input id="area_construida" v-model="form.area_construida" type="number" step="0.01" class="mt-1" />
                </div>
                <div>
                  <Label for="area_util">Área Útil (m²)</Label>
                  <Input id="area_util" v-model="form.area_util" type="number" step="0.01" class="mt-1" />
                </div>
              </div>
              
              <div>
                <Label for="areas_lazer">Áreas de Lazer</Label>
                <textarea 
                  id="areas_lazer"
                  v-model="form.areas_lazer" 
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
                    :value="form.cep"
                    placeholder="00000-000" 
                    class="mt-1"
                    maxlength="9"
                    @input="formatCep"
                    @blur="buscarCep"
                  />
                </div>
                <div>
                  <Label for="estado">Estado</Label>
                  <Input id="estado" v-model="form.estado" placeholder="UF" maxlength="2" class="mt-1" />
                </div>
                <div>
                  <Label for="cidade">Cidade</Label>
                  <Input id="cidade" v-model="form.cidade" placeholder="Cidade" class="mt-1" />
                </div>
              </div>
              
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <Label for="bairro">Bairro</Label>
                  <Input id="bairro" v-model="form.bairro" placeholder="Bairro" class="mt-1" />
                </div>
                <div>
                  <Label for="endereco_rua">Endereço</Label>
                  <Input id="endereco_rua" v-model="form.endereco" placeholder="Rua/Avenida" class="mt-1" />
                </div>
              </div>
              
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <Label for="numero">Número</Label>
                  <Input id="numero" v-model="form.numero" placeholder="Nº" class="mt-1" />
                </div>
                <div>
                  <Label for="complemento">Complemento</Label>
                  <Input id="complemento" v-model="form.complemento" placeholder="Apto, Bloco..." class="mt-1" />
                </div>
                <div>
                  <Label for="referencia">Referência</Label>
                  <Input id="referencia" v-model="form.referencia" placeholder="Próximo a..." class="mt-1" />
                </div>
              </div>
            </div>
          </div>

          <!-- Seção: Mídia -->
          <div class="bg-card rounded-xl border">
            <button 
              class="w-full flex items-center justify-between p-4 text-left"
              type="button"
              @click="toggleSection('midia')"
            >
              <h2 class="text-lg font-semibold">Mídia do Imóvel</h2>
              <ChevronDown 
                class="w-5 h-5 text-muted-foreground transition-transform" 
                :class="{ 'rotate-180': expandedSections.midia }"
              />
            </button>
            
            <div v-show="expandedSections.midia" class="px-4 pb-4">
              <p class="text-sm text-muted-foreground mb-4">
                Para gerenciar imagens, vídeos e plantas, acesse a edição completa do imóvel.
              </p>
              
              <div v-if="listing.imovel.imagens?.length" class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                <div 
                  v-for="img in listing.imovel.imagens.slice(0, 6)" 
                  :key="img.id" 
                  class="aspect-square rounded-lg overflow-hidden bg-muted"
                >
                  <img :src="img.url || ''" :alt="img.original_name" class="w-full h-full object-cover" />
                </div>
                <div v-if="listing.imovel.imagens.length > 6" class="aspect-square rounded-lg bg-muted flex items-center justify-center text-muted-foreground text-sm">
                  +{{ listing.imovel.imagens.length - 6 }}
                </div>
              </div>
              <div v-else class="text-sm text-muted-foreground">
                Nenhuma imagem cadastrada
              </div>
              
              <div class="mt-4 flex gap-4 text-sm text-muted-foreground">
                <span>{{ listing.imovel.imagens?.length || 0 }} imagens</span>
                <span>{{ listing.imovel.videos?.length || 0 }} vídeos</span>
                <span>{{ listing.imovel.plantas?.length || 0 }} plantas</span>
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
              :disabled="saving"
              @click="saveChanges"
            >
              <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
              <Save v-else class="w-4 h-4 mr-2" />
              Salvar Alterações
            </Button>
          </div>
        </div>
      </div>
    </div>
  </AuthLayout>
</template>
