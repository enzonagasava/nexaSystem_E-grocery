<!-- resources/js/components/admin/corretor/contatos/modal/ContatoDetailsModal.vue -->
<script setup lang="ts">
import { ref, watch, computed, reactive } from 'vue'
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
import { Button } from '@/components/ui/button'
import Input from '@/components/ui/input/Input.vue'
import Select from '@/components/ui/select/Select.vue'
import { 
  Maximize2, Save, X, Loader2, ChevronDown, Phone, Mail, User, 
  MapPin, CreditCard, Settings, Briefcase, Calendar, Star, Award,
  FileText, Building, Users, Heart, Clock,
  Contact2Icon
} from 'lucide-vue-next'
import { useToastStore } from '@/stores/toast'
import type { ContatoForm, ContatoListagem } from '@/types/forms/contato-form'

const props = defineProps<{
  open: boolean
  contato: ContatoListagem | null
  corretores?: Array<{ id: number; nome: string }>
  status: any
}>()


const emit = defineEmits<{
  (e: 'close'): void
  (e: 'updated', contato: ContatoListagem): void
}>()

const toast = useToastStore()

// Estado
const loadingDetails = ref(false)
const saving = ref(false)
const error = ref<string | null>(null)
const detalhes = ref<ContatoListagem | null>(null)
const isEditing = ref(false)
const contato = props.contato
const status = props.status
console.log(status)

// Seções expandidas
const expandedSections = ref<Record<string, boolean>>({
  basic: true,
  professional: false,
  contacts: false,
  location: false,
  bank: false,
  documents: false,
  preferences: false,
  management: false,
})

// Formulário reativo
const form = reactive<ContatoForm>({
  nome_completo: props.contato?.nome_completo || '',
  email: props.contato?.email || '',
  contatos: props.contato?.contatos || [{ numero: '', tipo: 'WhatsApp', principal: true }],
  genero: props.contato?.genero || '',
  data_nascimento: props.contato?.data_nascimento || '',
  
  cpf: props.contato?.cpf || '',
  rg: props.contato?.rg || '',
  cnh: props.contato?.cnh || '',
  
  cep: props.contato?.cep || '',
  rua: props.contato?.rua || '',
  bairro: props.contato?.bairro || '',
  cidade: props.contato?.cidade || '',
  estado: props.contato?.estado || '',
  complemento: props.contato?.complemento || '',
  numero: props.contato?.numero || '',
  
  profissao: props.contato?.profissao || '',
  empresa: props.contato?.empresa || '',
  renda_mensal: props.contato?.renda_mensal || null,
  
  banco_nome: props.contato?.banco_nome || '',
  banco_codigo: props.contato?.banco_codigo || '',
  agencia: props.contato?.agencia || '',
  conta: props.contato?.conta || '',
  conta_tipo: props.contato?.conta_tipo || '',
  pix: props.contato?.pix || '',
  pix_tipo: props.contato?.pix_tipo || '',
  
  corretor_id: props.contato?.corretor_id || null,
  status: props.contato?.status || 'ativo',
  tipo_relacao: props.contato?.tipo_relacao || 'cliente',
  nivel_interesse: props.contato?.nivel_interesse || 3,
  
  preferencias_imoveis: props.contato?.preferencias_imoveis || [],
  observacoes: props.contato?.observacoes || null,
})

// Watchers
watch(() => props.open, async (isOpen) => {
  if (isOpen && props.contato) {
    error.value = null
    isEditing.value = false
    expandedSections.value = {
      basic: true,
      professional: false,
      contacts: false,
      location: false,
      bank: false,
      documents: false,
      preferences: false,
      management: false,
    }
    await fetchDetails(props.contato.id)
  }
})

// Funções
async function fetchDetails(id: number) {
  loadingDetails.value = true
  error.value = null
  try {
    const xsrfToken = document.cookie
      .split('; ')
      .find(row => row.startsWith('XSRF-TOKEN='))
      ?.split('=')[1] || ''

    const resp = await fetch(`/admin/corretor/contatos/${id}/show`, {
      method: 'GET',
      headers: {
        'X-XSRF-TOKEN': decodeURIComponent(xsrfToken),
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      credentials: 'include',
    })

    if (!resp.ok) throw new Error('Erro ao carregar detalhes')
    
    const data = await resp.json()
    const contatoData = data.contato || data
    
    detalhes.value = contatoData
    // Preenche formulário com valores atuais
    Object.assign(form, {
      nome_completo: contatoData.nome_completo || '',
      email: contatoData.email || '',
      contatos: Array.isArray(contatoData.contatos) ? contatoData.contatos : [{ numero: '', tipo: 'WhatsApp', principal: true }],
      genero: contatoData.genero || '',
      data_nascimento: contatoData.data_nascimento || '',
      
      cpf: contatoData.cpf || '',
      rg: contatoData.rg || '',
      cnh: contatoData.cnh || '',
      
      cep: contatoData.cep || '',
      rua: contatoData.rua || '',
      bairro: contatoData.bairro || '',
      cidade: contatoData.cidade || '',
      estado: contatoData.estado || '',
      complemento: contatoData.complemento || '',
      numero: contatoData.numero || '',
      
      profissao: contatoData.profissao || '',
      empresa: contatoData.empresa || '',
      renda_mensal: contatoData.renda_mensal || null,
      
      banco_nome: contatoData.banco_nome || '',
      banco_codigo: contatoData.banco_codigo || '',
      agencia: contatoData.agencia || '',
      conta: contatoData.conta || '',
      conta_tipo: contatoData.conta_tipo || '',
      pix: contatoData.pix || '',
      pix_tipo: contatoData.pix_tipo || '',
      
      corretor_id: contatoData.corretor_id || null,
      status: contatoData.status || 'ativo',
      tipo_relacao: contatoData.tipo_relacao || 'cliente',
      nivel_interesse: contatoData.nivel_interesse || 3,
      
      preferencias_imoveis: contatoData.preferencias_imoveis || [],
      observacoes: contatoData.observacoes || null,
    })
  } catch (e: any) {
    console.error('Erro ao buscar detalhes do contato', e)
    error.value = 'Não foi possível carregar os detalhes do contato.'
  } finally {
    loadingDetails.value = false
  }
}

async function saveChanges() {
  if (!props.contato) return
  
  saving.value = true
  error.value = null
  
  try {
    const xsrfToken = getCookie('XSRF-TOKEN');
    const resp = await fetch(`/admin/corretor/contatos/${props.contato.id}`, {
      method: 'PUT',
      headers: {
        'X-XSRF-TOKEN': xsrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json', 
      },
      credentials: 'include',
      body:JSON.stringify(form)
    })
    const data = await resp.json();

    const updatedData = data

    // Emit updated data
    const toEmit: ContatoListagem = {
      id: props.contato.id,
      nome_completo: updatedData.nome_completo || form.nome_completo,
      email: updatedData.email || form.email,
      contatos: updatedData.contatos || form.contatos,
      status: updatedData.status || form.status,
      tipo_relacao: updatedData.tipo_relacao || form.tipo_relacao,
      corretor_id: updatedData.corretor_id || form.corretor_id,
      corretor: updatedData.corretor || detalhes.value?.corretor,
      created_at: detalhes.value?.created_at,
      cidade: updatedData.cidade || detalhes.value?.cidade,
      estado: updatedData.estado || detalhes.value?.estado,
      profissao: updatedData.profissao || detalhes.value?.profissao,
      empresa: updatedData.empresa || detalhes.value?.empresa,
      renda_mensal: updatedData.renda_mensal || detalhes.value?.renda_mensal,
      data_nascimento: updatedData.data_nascimento || detalhes.value?.data_nascimento,
    }
    emit('updated', toEmit)

    // Update local details
    detalhes.value = Object.assign({}, detalhes.value || {}, updatedData)
    toast.show('Contato atualizado com sucesso!', 'success')
    isEditing.value = false
  } catch (e: any) {
    console.error('Erro ao salvar contato', e)
    error.value = e.response?.data?.message || 'Não foi possível salvar as alterações.'
    toast.show(error.value, 'error')
  } finally {
    saving.value = false
  }
}

function openFullScreen() {
  if (!props.contato) return
  emit('close')
  router.visit(route('admin.corretor.contatos.show', { id: props.contato.id }))
}

function handleClose() {
  emit('close')
}

function toggleSection(section: string) {
  expandedSections.value[section] = !expandedSections.value[section]
}

// Formatações
function formatDate(value: string | null | undefined): string {
  if (!value) return '—'
  try {
    const date = new Date(value)
    return date.toLocaleDateString('pt-BR')
  } catch {
    return '—'
  }
}

function formatCurrency(value: number | null | undefined): string {
  if (!value) return '—'
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
}

// Computed properties
const idade = computed(() => {
  if (!detalhes.value?.data_nascimento) return null
  const hoje = new Date()
  const nasc = new Date(detalhes.value.data_nascimento)
  let idade = hoje.getFullYear() - nasc.getFullYear()
  const m = hoje.getMonth() - nasc.getMonth()
  if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) {
    idade--
  }
  return idade
})

const contatoPrincipal = computed(() => {
  const contatos = detalhes.value?.contatos || []
  return contatos.find(c => c.principal) || contatos[0]
})

const enderecoCompleto = computed(() => {
  const end = detalhes.value
  if (!end?.rua) return null
  const partes = [
    end.rua,
    end.numero,
    end.bairro,
    end.cidade,
    end.estado
  ].filter(Boolean)
  return partes.join(', ')
})


const tipoRelacaoConfig = computed(() => {
  const config: Record<string, { label: string; icon: any; color: string }> = {
    'cliente': { label: 'Cliente', icon: Star, color: 'text-yellow-600' },
    'investidor': { label: 'Investidor', icon: Award, color: 'text-purple-600' },
    'parceiro': { label: 'Parceiro', icon: Users, color: 'text-blue-600' },
    'indicador': { label: 'Indicador', icon: Heart, color: 'text-green-600' },
    'proprietario': { label: 'Proprietário', icon: Building, color: 'text-orange-600' }
  }
  return config[detalhes.value?.tipo_relacao || '']
})


console.log(contato)
const nivelInteresseLabel = computed(() => {
  const map: Record<number, { label: string; class: string }> = {
    1: { label: 'Muito Baixo', class: 'bg-red-100 text-red-800' },
    2: { label: 'Baixo', class: 'bg-orange-100 text-orange-800' },
    3: { label: 'Médio', class: 'bg-yellow-100 text-yellow-800' },
    4: { label: 'Alto', class: 'bg-blue-100 text-blue-800' },
    5: { label: 'Muito Alto', class: 'bg-green-100 text-green-800' }
  }
  return map[detalhes.value?.nivel_interesse || 0] || { label: '—', class: '' }
})

</script>

<template>
  <Dialog :open="open" @update:open="(val: boolean) => !val && handleClose()">
    <DialogScrollContent class="max-w-4xl">
      <DialogHeader class="flex flex-row items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1">
            <DialogTitle class="text-xl font-semibold truncate flex items-center gap-2">
              <User class="w-5 h-5" />
              {{ isEditing ? 'Editar Contato' : (detalhes?.nome_completo || contato?.nome_completo || 'Detalhes do Contato') }}
            </DialogTitle>
            
            <!-- spans no header -->
            <template v-if="!isEditing && detalhes">
              <span v-if="tipoRelacaoConfig" :class="tipoRelacaoConfig.color" variant="outline" class="ml-2">
                <component :is="tipoRelacaoConfig.icon" class="w-3 h-3 mr-1" />
                {{ tipoRelacaoConfig.label }}
              </span>
              <span variant="outline">
                {{ detalhes.status.nome }}
              </span>
            </template>
          </div>
          
          <DialogDescription v-if="detalhes?.email && !isEditing" class="text-sm text-muted mt-1 truncate flex items-center gap-1">
            <Mail class="w-3 h-3" /> {{ detalhes.email }}
          </DialogDescription>
        </div>
        
        <Button v-if="!isEditing" variant="ghost" size="sm" @click="openFullScreen" class="flex-shrink-0" title="Abrir em tela cheia">
          <Maximize2 class="w-4 h-4 mr-1" />
          Tela cheia
        </Button>
      </DialogHeader>

      <!-- Carregando -->
      <div v-if="loadingDetails" class="flex items-center justify-center py-12">
        <Loader2 class="w-8 h-8 animate-spin text-primary" />
      </div>

      <!-- Erro -->
      <div v-else-if="error && !detalhes" class="text-center py-8">
        <p class="text-destructive mb-4">{{ error }}</p>
        <Button variant="outline" @click="contato && fetchDetails(contato.id)">Tentar novamente</Button>
      </div>

      <!-- Conteúdo -->
      <div v-else class="space-y-4">
        <!-- MODO VISUALIZAÇÃO -->
        <div v-if="!isEditing" class="space-y-3">
          <!-- Seção: Informações Básicas -->
          <div class="border rounded-lg">
            <button @click="toggleSection('basic')" class="w-full flex items-center justify-between p-4 hover:bg-secondary/50 rounded-t-lg">
              <span class="font-medium flex items-center gap-2">
                <User class="w-4 h-4" />
                Informações Pessoais
              </span>
              <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.basic ? 'rotate-180' : '']" />
            </button>
            <div v-if="expandedSections.basic" class="border-t px-4 py-3 bg-secondary/20 space-y-3 text-sm">
              <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                  <span class="text-muted-foreground block text-xs">Nome Completo</span>
                  <span class="font-medium">{{ detalhes?.nome_completo || '—' }}</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">Email</span>
                  <span class="font-medium">{{ detalhes?.email || '—' }}</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">CPF</span>
                  <span class="font-medium">{{ detalhes?.cpf || '—' }}</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">RG</span>
                  <span class="font-medium">{{ detalhes?.rg || '—' }}</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">CNH</span>
                  <span class="font-medium">{{ detalhes?.cnh || '—' }}</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">Gênero</span>
                  <span class="font-medium">{{ detalhes.genero || '-' }}</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">Nascimento</span>
                  <span class="font-medium">{{ formatDate(detalhes?.data_nascimento) }}</span>
                  <span v-if="idade" class="text-xs text-muted-foreground ml-1">({{ idade }} anos)</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">Cadastrado como Contato</span>
                  <span class="font-medium">{{ formatDate(Contact2Icon?.created_at) }}</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">Nível de Interesse</span>
                  <span :class="nivelInteresseLabel.class" size="sm">
                    {{ nivelInteresseLabel.label }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Seção: Profissional -->
          <div class="border rounded-lg">
            <button @click="toggleSection('professional')" class="w-full flex items-center justify-between p-4 hover:bg-secondary/50 rounded-t-lg">
              <span class="font-medium flex items-center gap-2">
                <Briefcase class="w-4 h-4" />
                Informações Profissionais
              </span>
              <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.professional ? 'rotate-180' : '']" />
            </button>
            <div v-if="expandedSections.professional" class="border-t px-4 py-3 bg-secondary/20 space-y-3 text-sm">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <span class="text-muted-foreground block text-xs">Profissão</span>
                  <span class="font-medium">{{ detalhes?.profissao || '—' }}</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">Empresa</span>
                  <span class="font-medium">{{ detalhes?.empresa || '—' }}</span>
                </div>
                <div class="col-span-2">
                  <span class="text-muted-foreground block text-xs">Renda Mensal</span>
                  <span class="font-medium text-green-600">{{ formatCurrency(detalhes?.renda_mensal) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Seção: Contatos -->
          <div v-if="detalhes?.contatos?.length" class="border rounded-lg">
            <button @click="toggleSection('contacts')" class="w-full flex items-center justify-between p-4 hover:bg-secondary/50 rounded-t-lg">
              <span class="font-medium flex items-center gap-2">
                <Phone class="w-4 h-4" />
                Contatos
              </span>
              <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.contacts ? 'rotate-180' : '']" />
            </button>
            <div v-if="expandedSections.contacts" class="border-t px-4 py-3 bg-secondary/20 space-y-3 text-sm">
              <div v-for="(contato, idx) in detalhes.contatos" :key="idx" class="flex items-center justify-between py-1 border-b last:border-0">
                <div class="flex items-center gap-2">
                  <span class="text-muted-foreground">{{ contato.tipo }}:</span>
                  <span class="font-medium">{{ contato.numero }}</span>
                  <span v-if="contato.principal" variant="outline" size="sm" class="bg-primary/10 text-primary">
                    Principal
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Seção: Localização -->
          <div v-if="detalhes?.rua" class="border rounded-lg">
            <button @click="toggleSection('location')" class="w-full flex items-center justify-between p-4 hover:bg-secondary/50 rounded-t-lg">
              <span class="font-medium flex items-center gap-2">
                <MapPin class="w-4 h-4" />
                Endereço
              </span>
              <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.location ? 'rotate-180' : '']" />
            </button>
            <div v-if="expandedSections.location" class="border-t px-4 py-3 bg-secondary/20 space-y-3 text-sm">
              <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                  <span class="text-muted-foreground block text-xs">CEP</span>
                  <span class="font-medium">{{ detalhes.cep || '—' }}</span>
                </div>
                <div class="col-span-2">
                  <span class="text-muted-foreground block text-xs">Rua</span>
                  <span class="font-medium">{{ detalhes.rua || '—' }}, {{ detalhes.numero || 's/n' }}</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">Bairro</span>
                  <span class="font-medium">{{ detalhes.bairro || '—' }}</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">Cidade</span>
                  <span class="font-medium">{{ detalhes.cidade || '—' }}</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">Estado</span>
                  <span class="font-medium">{{ detalhes.estado || '—' }}</span>
                </div>
                <div class="col-span-3">
                  <span class="text-muted-foreground block text-xs">Complemento</span>
                  <span class="font-medium">{{ detalhes.complemento || '—' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Seção: Dados Bancários -->
          <div v-if="detalhes?.banco_nome || detalhes?.pix" class="border rounded-lg">
            <button @click="toggleSection('bank')" class="w-full flex items-center justify-between p-4 hover:bg-secondary/50 rounded-t-lg">
              <span class="font-medium flex items-center gap-2">
                <CreditCard class="w-4 h-4" />
                Dados Bancários
              </span>
              <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.bank ? 'rotate-180' : '']" />
            </button>
            <div v-if="expandedSections.bank" class="border-t px-4 py-3 bg-secondary/20 space-y-3 text-sm">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <span class="text-muted-foreground block text-xs">Banco</span>
                  <span class="font-medium">{{ detalhes.banco_nome || '—' }}</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">Código</span>
                  <span class="font-medium">{{ detalhes.banco_codigo || '—' }}</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">Agência</span>
                  <span class="font-medium">{{ detalhes.agencia || '—' }}</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">Conta</span>
                  <span class="font-medium">{{ detalhes.conta || '—' }}</span>
                  <span v-if="detalhes.conta_tipo" class="text-xs text-muted-foreground ml-1">({{ detalhes.conta_tipo }})</span>
                </div>
              </div>
              <div v-if="detalhes.pix" class="border-t pt-2 mt-2">
                <span class="text-muted-foreground block text-xs">PIX</span>
                <div class="mt-1 font-medium">{{ detalhes.pix }}</div>
                <span v-if="detalhes.pix_tipo" class="text-xs text-muted-foreground">({{ detalhes.pix_tipo }})</span>
              </div>
            </div>
          </div>

          <!-- Seção: Documentos -->
          <div v-if="detalhes?.documentos_entregues?.length" class="border rounded-lg">
            <button @click="toggleSection('documents')" class="w-full flex items-center justify-between p-4 hover:bg-secondary/50 rounded-t-lg">
              <span class="font-medium flex items-center gap-2">
                <FileText class="w-4 h-4" />
                Documentos
              </span>
              <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.documents ? 'rotate-180' : '']" />
            </button>
            <div v-if="expandedSections.documents" class="border-t px-4 py-3 bg-secondary/20 space-y-3 text-sm">
              <div v-for="doc in detalhes.documentos_entregues" :key="doc" class="flex items-center gap-2">
                <FileText class="w-4 h-4 text-muted-foreground" />
                <span>{{ doc }}</span>
              </div>
            </div>
          </div>

          <!-- Seção: Preferências -->
          <div v-if="detalhes?.preferencias_imoveis?.length" class="border rounded-lg">
            <button @click="toggleSection('preferences')" class="w-full flex items-center justify-between p-4 hover:bg-secondary/50 rounded-t-lg">
              <span class="font-medium flex items-center gap-2">
                <Heart class="w-4 h-4" />
                Preferências de Imóveis
              </span>
              <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.preferences ? 'rotate-180' : '']" />
            </button>
            <div v-if="expandedSections.preferences" class="border-t px-4 py-3 bg-secondary/20 space-y-3 text-sm">
              <pre class="text-xs">{{ JSON.stringify(detalhes.preferencias_imoveis, null, 2) }}</pre>
            </div>
          </div>

          <!-- Seção: Gerenciamento -->
          <div class="border rounded-lg">
            <button @click="toggleSection('management')" class="w-full flex items-center justify-between p-4 hover:bg-secondary/50 rounded-t-lg">
              <span class="font-medium flex items-center gap-2">
                <Settings class="w-4 h-4" />
                Gerenciamento
              </span>
              <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.management ? 'rotate-180' : '']" />
            </button>
            <div v-if="expandedSections.management" class="border-t px-4 py-3 bg-secondary/20 space-y-3 text-sm">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <span class="text-muted-foreground block text-xs">Corretor</span>
                  <span class="font-medium">{{ detalhes?.corretor?.nome || 'Não atribuído' }}</span>
                </div>
                <div>
                  <span class="text-muted-foreground block text-xs">Último Contato</span>
                  <span class="font-medium flex items-center gap-1">
                    <Clock class="w-3 h-3" />
                    {{ formatDate(detalhes?.ultimo_contato) }}
                  </span>
                </div>
              </div>
              
              <div v-if="detalhes?.observacoes" class="border-t pt-2 mt-2">
                <span class="text-muted-foreground block text-xs">Observações</span>
                <p class="mt-1 text-sm whitespace-pre-wrap">{{ detalhes.observacoes }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- MODO EDIÇÃO -->
        <div v-else class="space-y-6">
          <!-- Seção: Informações Pessoais -->
          <div class="border rounded-lg p-4 space-y-4">
            <h3 class="font-medium flex items-center gap-2">
              <User class="w-4 h-4" />
              Informações Pessoais
            </h3>
            
            <Input id="edit-nome" label="Nome Completo *" v-model="form.nome_completo" placeholder="Nome do contato" required />
            
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-email" label="E-mail" v-model="form.email" type="email" placeholder="email@exemplo.com" />
              <Input id="edit-cpf" label="CPF" v-model="form.cpf" v-maska="'###.###.###-##'" placeholder="000.000.000-00" />
            </div>
            
            <div class="grid grid-cols-3 gap-4">
              <Input id="edit-rg" label="RG" v-model="form.rg" placeholder="RG" />
              <Input id="edit-cnh" label="CNH" v-model="form.cnh" placeholder="CNH" />
              <Select
                label="Gênero"
                v-model="form.genero"
                :options="[
                  { value: 'masculino', label: 'Masculino' },
                  { value: 'feminino', label: 'Feminino' },
                  { value: 'outro', label: 'Outro' },
                  { value: 'prefiro_nao_informar', label: 'Prefiro não informar' }
                ]"
              />
            </div>
            
            <Input id="edit-data-nascimento" label="Data de Nascimento" v-model="form.data_nascimento" type="date" />
          </div>

          <!-- Seção: Profissional -->
          <div class="border rounded-lg p-4 space-y-4">
            <h3 class="font-medium flex items-center gap-2">
              <Briefcase class="w-4 h-4" />
              Informações Profissionais
            </h3>
            
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-profissao" label="Profissão" v-model="form.profissao" placeholder="Ex: Engenheiro" />
              <Input id="edit-empresa" label="Empresa" v-model="form.empresa" placeholder="Nome da empresa" />
            </div>
            
            <Input 
              id="edit-renda" 
              label="Renda Mensal" 
              v-model="form.renda_mensal" 
              type="number" 
              step="0.01" 
              min="0"
              placeholder="0,00"
            />
          </div>

          <!-- Seção: Contatos -->
          <div class="border rounded-lg p-4 space-y-4">
            <h3 class="font-medium flex items-center gap-2">
              <Phone class="w-4 h-4" />
              Contatos
            </h3>
            
            <div v-for="(contato, idx) in form.contatos" :key="idx" class="flex gap-2 mb-2 items-start">
              <Input 
                :id="`edit-contato-${idx}`" 
                v-model="contato.numero" 
                v-maska="['(##) ####-####', '(##) #####-####']"
                placeholder="(11) 99999-9999" 
                class="flex-1"
              />
              <Select v-model="contato.tipo" class="w-32">
                <option value="WhatsApp">WhatsApp</option>
                <option value="Celular">Celular</option>
                <option value="Telefone">Telefone</option>
                <option value="Comercial">Comercial</option>
              </Select>
              <div class="flex items-center gap-1">
                <input 
                  type="checkbox" 
                  :id="`principal-${idx}`"
                  v-model="contato.principal"
                  class="rounded"
                />
                <label :for="`principal-${idx}`" class="text-sm">Principal</label>
              </div>
              <Button 
                type="button" 
                variant="ghost" 
                size="sm"
                @click="form.contatos.splice(idx, 1)"
                v-if="form.contatos.length > 1"
              >
                <X class="w-4 h-4" />
              </Button>
            </div>
            
            <Button 
              type="button" 
              variant="outline" 
              size="sm" 
              @click="form.contatos.push({ numero: '', tipo: 'WhatsApp', principal: false })"
            >
              + Adicionar contato
            </Button>
          </div>

          <!-- Seção: Endereço -->
          <div class="border rounded-lg p-4 space-y-4">
            <h3 class="font-medium flex items-center gap-2">
              <MapPin class="w-4 h-4" />
              Endereço
            </h3>
            
            <div class="grid grid-cols-3 gap-4">
              <Input id="edit-cep" label="CEP" v-model="form.cep" v-maska="'#####-###'" placeholder="00000-000" class="col-span-1" />
              <Input id="edit-rua" label="Rua" v-model="form.rua" placeholder="Rua..." class="col-span-2" />
            </div>
            
            <div class="grid grid-cols-4 gap-4">
              <Input id="edit-numero" label="Número" v-model="form.numero" placeholder="123" class="col-span-1" />
              <Input id="edit-bairro" label="Bairro" v-model="form.bairro" placeholder="Bairro" class="col-span-3" />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-cidade" label="Cidade" v-model="form.cidade" placeholder="Cidade" />
              <Input id="edit-estado" label="Estado" v-model="form.estado" placeholder="SP" maxlength="2" />
            </div>
            
            <Input id="edit-complemento" label="Complemento" v-model="form.complemento" placeholder="Apt 101, bloco A..." />
          </div>

          <!-- Seção: Dados Bancários -->
          <div class="border rounded-lg p-4 space-y-4">
            <h3 class="font-medium flex items-center gap-2">
              <CreditCard class="w-4 h-4" />
              Dados Bancários <span class="text-xs text-muted-foreground">(Opcional)</span>
            </h3>
            
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-banco-nome" label="Nome do Banco" v-model="form.banco_nome" placeholder="Banco do Brasil" />
              <Input id="edit-banco-codigo" label="Código do Banco" v-model="form.banco_codigo" placeholder="001" />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-agencia" label="Agência" v-model="form.agencia" placeholder="0000" />
              <Input id="edit-conta" label="Conta" v-model="form.conta" placeholder="00000-0" />
            </div>
            
            <Select
              label="Tipo de Conta"
              v-model="form.conta_tipo"
              :options="[
                { value: '', label: 'Selecione...' },
                { value: 'corrente', label: 'Corrente' },
                { value: 'poupanca', label: 'Poupança' }
              ]"
            />
            
            <div class="border-t pt-4">
              <h4 class="text-sm font-medium mb-3">PIX</h4>
              <div class="grid grid-cols-2 gap-4">
                <Input id="edit-pix" label="Chave PIX" v-model="form.pix" placeholder="Chave PIX" />
                <Select
                  label="Tipo de Chave"
                  v-model="form.pix_tipo"
                  :options="[
                    { value: '', label: 'Selecione...' },
                    { value: 'cpf', label: 'CPF' },
                    { value: 'celular', label: 'Celular' },
                    { value: 'email', label: 'E-mail' },
                    { value: 'aleatorio', label: 'Chave aleatória' }
                  ]"
                />
              </div>
            </div>
          </div>

          <!-- Seção: Gerenciamento -->
          <div class="border rounded-lg p-4 space-y-4">
            <h3 class="font-medium flex items-center gap-2">
              <Settings class="w-4 h-4" />
              Gerenciamento
            </h3>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Corretor Responsável</label>
                <Select v-model="form.corretor_id" class="w-full">
                  <option :value="null">Selecione um corretor...</option>
                  <option v-for="corretor in corretores" :key="corretor.id" :value="corretor.id">
                    {{ corretor.nome }}
                  </option>
                </Select>
              </div>
              
              <div>
                <Select
                    :label="'Status'"
                    :tooltip="'Imóvel que o lead demonstrou interesse'"
                    v-model="form.status_id"
                    :error="errors?.status"
                    name="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :options="[
                        { value: '', label: 'Selecione um status' },
                        ...(status || []).map(status => ({ 
                            value: status.id,
                            label: `${status.nome}`
                        }))
                    ]"
                />
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Tipo de Relação</label>
                <Select v-model="form.tipo_relacao" class="w-full">
                  <option value="cliente">Cliente</option>
                  <option value="investidor">Investidor</option>
                  <option value="parceiro">Parceiro</option>
                  <option value="indicador">Indicador</option>
                  <option value="proprietario">Proprietário</option>
                </Select>
              </div>
              
              <div>
                <label class="block text-sm font-medium mb-1">Nível de Interesse</label>
                <Select v-model="form.nivel_interesse" class="w-full">
                  <option :value="1">1 - Muito Baixo</option>
                  <option :value="2">2 - Baixo</option>
                  <option :value="3">3 - Médio</option>
                  <option :value="4">4 - Alto</option>
                  <option :value="5">5 - Muito Alto</option>
                </Select>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-1">Observações</label>
              <textarea 
                v-model="form.observacoes" 
                rows="4"
                class="w-full rounded-md border border-[var(--border)] bg-[var(--background)] px-3 py-2 text-sm"
                placeholder="Observações sobre o contato..."
              ></textarea>
            </div>
          </div>

          <!-- Erro ao salvar -->
          <div v-if="error" class="text-sm text-destructive bg-destructive/10 p-3 rounded">{{ error }}</div>
        </div>
      </div>

      <DialogFooter class="mt-6">
        <div class="flex w-full justify-end gap-2">
          <Button variant="outline" @click="handleClose">
            <X class="w-4 h-4 mr-1" />
            Fechar
          </Button>
          
          <template v-if="!isEditing">
            <Button variant="secondary" @click="isEditing = true" :disabled="loadingDetails">
              Editar
            </Button>
          </template>
          <template v-else>
            <Button variant="ghost" @click="isEditing = false" :disabled="saving">
              Cancelar
            </Button>
            <Button @click="saveChanges" :disabled="saving">
              <Loader2 v-if="saving" class="w-4 h-4 mr-1 animate-spin" />
              <Save v-else class="w-4 h-4 mr-1" />
              Salvar
            </Button>
          </template>
        </div>
      </DialogFooter>
    </DialogScrollContent>
  </Dialog>
</template>