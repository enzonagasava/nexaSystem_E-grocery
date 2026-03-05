<script setup lang="ts">
/**
 * Modal expandido de detalhes e edição completa de lead.
 * Permite visualizar e editar todos os campos organizados em seções colapsáveis.
 */
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
import { Maximize2, Save, X, Loader2, ChevronDown, Phone, Mail, User, MapPin, CreditCard, Settings } from 'lucide-vue-next'
import { useToastStore } from '@/stores/toast'
import { validateLead } from '@/utils/validateLead'
import type { LeadForm } from '@/types/forms/lead-form'


const props = defineProps<{
  open: boolean,
  lead: LeadForm | null,
  corretores?: Array<{ id: number; nome: string }>,
  imoveis?: Array<{ id: number; nome: string; codigo?: string }>,
  status: Array 
}>()


const emit = defineEmits<{
  (e: 'close'): void
  (e: 'updated', lead: LeadForm): void
}>()

const toast = useToastStore()

// Estado
const loadingDetails = ref(false)
const saving = ref(false)
const error = ref<string | null>(null)
const detalhes = ref<LeadForm | null>(null)
const corretores = ref<Array>(null)
const status = ref<Array>(null)
const isEditing = ref(false)

// Seções expandidas
const expandedSections = ref<Record<string, boolean>>({
  basic: true,
  contacts: false,
  location: false,
  bank: false,
  management: false,
  interests: false,
})

const form = reactive<LeadForm>({
    nome_completo: props.lead?.nome_completo || '',
    email: props.lead?.email || '',
    contatos: props.lead?.contatos || [{ 
        numero: '', 
        tipo: 'WhatsApp', 
        principal: true 
    }],
    genero: props.lead?.genero || '',
    data_nascimento: props.lead?.data_nascimento || '',
    redes_sociais: props.lead?.redes_sociais || [],
    cpf: props.lead?.cpf || '',
    rg: props.lead?.rg || '',
    cep: props.lead?.cep || '',
    rua: props.lead?.rua || '',
    bairro: props.lead?.bairro || '',
    cidade: props.lead?.cidade || '',
    estado: props.lead?.estado || '',
    complemento: props.lead?.complemento || '',
    numero: props.lead?.numero || '',
    banco_nome: props.lead?.banco_nome || '',
    banco_codigo: props.lead?.banco_codigo || '',
    agencia: props.lead?.agencia || '',
    conta: props.lead?.conta || '',
    conta_tipo: props.lead?.conta_tipo || '',
    pix: props.lead?.pix || '',
    pix_tipo: props.lead?.pix_tipo || '',
    corretor_id: props.lead?.corretor_id || '',
    adicionar_rodizio: props.lead?.adicionar_rodizio || false,
    status_id: props.lead?.status.id || '',
    imovel: props.imovel || []
});

// Watchers
watch(() => props.open, async (isOpen) => {
  if (isOpen && props.lead) {
    error.value = null
    isEditing.value = false
    expandedSections.value = {
      basic: true,
      contacts: false,
      location: false,
      bank: false,
      management: false,
      interests: false,
    }
    await fetchDetails(props.lead.id)
  }
})

// Funções
async function fetchDetails(id: number) {
  loadingDetails.value = true
  error.value = null
  try {
    const xsrfToken = getCookie('XSRF-TOKEN');

    const resp = await fetch(`/admin/corretor/leads/${id}/show`, {
        method: 'GET',    
        headers: {
            'X-XSRF-TOKEN': xsrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
    })
    const data = await resp.json()
    const leadData = data.lead

    detalhes.value = leadData
    corretores.value = data.corretores

    //Lista de status
    status.value = data.status
    // Preenche formulário com valores atuais
    Object.assign(form, {
      nome_completo: leadData.nome_completo || '',
      email: leadData.email || '',
      contatos: Array.isArray(leadData.contatos) ? leadData.contatos : [{ numero: '', tipo: 'WhatsApp', principal: true }],
      genero: leadData.genero || '',
      data_nascimento: leadData.data_nascimento || '',
      redes_sociais: leadData.redes_sociais || [],
      cpf: leadData.cpf || '',
      rg: leadData.rg || '',
      cep: leadData.cep || '',
      rua: leadData.rua || '',
      bairro: leadData.bairro || '',
      cidade: leadData.cidade || '',
      estado: leadData.estado || '',
      complemento: leadData.complemento || '',
      numero: leadData.numero || '',
      banco_nome: leadData.banco_nome || '',
      banco_codigo: leadData.banco_codigo || '',
      agencia: leadData.agencia || '',
      conta: leadData.conta || '',
      conta_tipo: leadData.conta_tipo || '',
      pix: leadData.pix || '',
      pix_tipo: leadData.pix_tipo || '',
      corretor_id: leadData.corretor_id || '',
      adicionar_rodizio: leadData.adicionar_rodizio || false,
      //Status atual do lead
      status_id: leadData.status_id || '',
      imovel: leadData.imovel_interesse || [],
    })
  } catch (e: any) {
    console.error('Erro ao buscar detalhes do lead', e)
    error.value = 'Não foi possível carregar os detalhes do lead.'
  } finally {
    loadingDetails.value = false
  }
}

async function saveChanges() {
  if (!props.lead) return
  
  saving.value = true
  error.value = null
  
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    
    const resp = await axios.put(`/admin/corretor/leads/${props.lead.id}`, form, {
      headers: {
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
      withCredentials: true,
    })
    
    const updatedData = resp.data?.data || resp.data

    emit('updated', updatedData)

    // Update local details
    detalhes.value = Object.assign({}, detalhes.value || {}, updatedData)
    toast.show('Lead atualizado com sucesso!', 'success')
    isEditing.value = false
  } catch (e: any) {
    console.error('Erro ao salvar lead', e)
    error.value = e.response?.data?.message || 'Não foi possível salvar as alterações.'
    toast.show(error.value, 'error')
  } finally {
    saving.value = false
  }
}

function openFullScreen() {
  if (!props.lead) return
  emit('close')
  router.visit(route('admin.corretor.leads.show', { id: props.lead.id }))
}

function handleClose() {
  emit('close')
}

function toggleSection(section: string) {
  expandedSections.value[section] = !expandedSections.value[section]
}

function formatDate(value: string | null | undefined): string {
  if (!value) return '—'
  try {
    const date = new Date(value)
    return date.toLocaleDateString('pt-BR')
  } catch {
    return '—'
  }
}

function formatPhone(phone: string | null | undefined): string {
  if (!phone) return '—'
  // Simple formatting, adjust as needed
  return phone
}

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
  const contatos = detalhes.value?.contatos
  if (!contatos || contatos.length === 0) return null
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

const generoLabel = computed(() => {
  const map: Record<string, string> = {
    'masculino': 'Masculino',
    'feminino': 'Feminino',
    'outro': 'Outro',
    'prefiro_nao_informar': 'Prefiro não informar'
  }
  return detalhes.value?.genero ? map[detalhes.value.genero] || detalhes.value.genero : '—'
})
</script>

<template>
  <Dialog :open="open" @update:open="(val: boolean) => !val && handleClose()">
    <DialogScrollContent class="max-w-3xl">
      <DialogHeader class="flex flex-row items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
          <DialogTitle class="text-xl font-semibold truncate flex items-center gap-2">
            <User class="w-5 h-5" />
            {{ isEditing ? 'Editar Lead' : (detalhes?.nome_completo || lead?.nome_completo || 'Detalhes do Lead') }}
          </DialogTitle>
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
        <Button variant="outline" @click="lead && fetchDetails(lead.id)">Tentar novamente</Button>
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
            <div v-if="expandedSections.basic" class="border-t px-4 py-3 bg-secondary/20 space-y-2 text-sm">
              <div class="grid grid-cols-2 gap-4">
                <div><span class="text-muted-foreground">Nome:</span> <span class="ml-2 font-medium">{{ detalhes?.nome_completo || '—' }}</span></div>
                <div><span class="text-muted-foreground">Email:</span> <span class="ml-2 font-medium">{{ detalhes?.email || '—' }}</span></div>
                <div><span class="text-muted-foreground">CPF:</span> <span class="ml-2 font-medium">{{ detalhes?.cpf || '—' }}</span></div>
                <div><span class="text-muted-foreground">RG:</span> <span class="ml-2 font-medium">{{ detalhes?.rg || '—' }}</span></div>
                <div><span class="text-muted-foreground">Gênero:</span> <span class="ml-2 font-medium">{{ generoLabel }}</span></div>
                <div><span class="text-muted-foreground">Nascimento:</span> <span class="ml-2 font-medium">{{ formatDate(detalhes?.data_nascimento) }} <span v-if="idade" class="text-muted-foreground text-xs">({{ idade }} anos)</span></span></div>
                <div><span class="text-muted-foreground">Cadastro:</span> <span class="ml-2 font-medium">{{ formatDate(detalhes?.created_at) }}</span></div>
                <div><span class="text-muted-foreground">Status:</span> 
                  <span class="ml-2">
                    <span v-if="detalhes?.status.nome === 'ativo'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      Ativo
                    </span>
                    <span v-else-if="detalhes?.status.nome === 'inativo'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                      Inativo
                    </span>
                    <span v-else>{{ detalhes?.status.nome || '—' }}</span>
                  </span>
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
                  <span class="font-medium">{{ formatPhone(contato.numero) }}</span>
                  <span v-if="contato.principal" class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded">Principal</span>
                </div>
              </div>
              
              <div v-if="detalhes.redes_sociais?.length" class="mt-2 pt-2 border-t">
                <span class="text-muted-foreground block mb-1">Redes Sociais:</span>
                <div v-for="(rede, idx) in detalhes.redes_sociais" :key="idx" class="text-sm">
                  {{ rede.nome }}: 
                  <a :href="rede.link" target="_blank" class="text-primary hover:underline">{{ rede.link }}</a>
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
            <div v-if="expandedSections.location" class="border-t px-4 py-3 bg-secondary/20 space-y-2 text-sm">
              <div class="grid grid-cols-2 gap-4">
                <div><span class="text-muted-foreground">CEP:</span> <span class="ml-2 font-medium">{{ detalhes.cep || '—' }}</span></div>
                <div><span class="text-muted-foreground">Rua:</span> <span class="ml-2 font-medium">{{ detalhes.rua || '—' }}</span></div>
                <div><span class="text-muted-foreground">Número:</span> <span class="ml-2 font-medium">{{ detalhes.numero || '—' }}</span></div>
                <div><span class="text-muted-foreground">Bairro:</span> <span class="ml-2 font-medium">{{ detalhes.bairro || '—' }}</span></div>
                <div><span class="text-muted-foreground">Cidade:</span> <span class="ml-2 font-medium">{{ detalhes.cidade || '—' }}</span></div>
                <div><span class="text-muted-foreground">Estado:</span> <span class="ml-2 font-medium">{{ detalhes.estado || '—' }}</span></div>
                <div class="col-span-2"><span class="text-muted-foreground">Complemento:</span> <span class="ml-2 font-medium">{{ detalhes.complemento || '—' }}</span></div>
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
            <div v-if="expandedSections.bank" class="border-t px-4 py-3 bg-secondary/20 space-y-2 text-sm">
              <div class="grid grid-cols-2 gap-4">
                <div><span class="text-muted-foreground">Banco:</span> <span class="ml-2 font-medium">{{ detalhes.banco_nome || '—' }}</span></div>
                <div><span class="text-muted-foreground">Código:</span> <span class="ml-2 font-medium">{{ detalhes.banco_codigo || '—' }}</span></div>
                <div><span class="text-muted-foreground">Agência:</span> <span class="ml-2 font-medium">{{ detalhes.agencia || '—' }}</span></div>
                <div><span class="text-muted-foreground">Conta:</span> <span class="ml-2 font-medium">{{ detalhes.conta || '—' }} <span v-if="detalhes.conta_tipo" class="text-xs text-muted-foreground">({{ detalhes.conta_tipo }})</span></span></div>
              </div>
              <div v-if="detalhes.pix" class="border-t pt-2 mt-2">
                <span class="text-muted-foreground">PIX:</span>
                <div class="mt-1 font-medium">{{ detalhes.pix }} <span v-if="detalhes.pix_tipo" class="text-xs text-muted-foreground ml-1">({{ detalhes.pix_tipo }})</span></div>
              </div>
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
            <div v-if="expandedSections.management" class="border-t px-4 py-3 bg-secondary/20 space-y-2 text-sm">
              <div class="grid grid-cols-2 gap-4">
                <div><span class="text-muted-foreground">Corretor:</span> 
                  <span class="ml-2 font-medium">{{ detalhes.corretor?.name || 'Não atribuído' }}</span>
                </div>
                <div><span class="text-muted-foreground">Rodízio:</span> 
                  <span class="ml-2 font-medium">{{ detalhes?.adicionar_rodizio ? 'Sim' : 'Não' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Seção: Imóveis de Interesse -->
          <div v-if="detalhes?.imovel_interesse?.length" class="border rounded-lg">
            <button @click="toggleSection('interests')" class="w-full flex items-center justify-between p-4 hover:bg-secondary/50 rounded-t-lg">
              <span class="font-medium">Imóveis de Interesse</span>
              <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.interests ? 'rotate-180' : '']" />
            </button>
            <div v-if="expandedSections.interests" class="border-t px-4 py-3 bg-secondary/20 space-y-2 text-sm">
              <div v-for="imovel in detalhes.imovel_interesse" :key="imovel.id" class="flex items-center justify-between py-1 border-b last:border-0">
                <span>{{ imovel.nome }}</span>
                <span v-if="imovel.codigo" class="text-xs text-muted-foreground">Cód: {{ imovel.codigo }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- MODO EDIÇÃO -->
        <div v-else class="space-y-6">
          <!-- Seção: Informações Pessoais (EDIÇÃO) -->
          <div class="border rounded-lg p-4 space-y-4">
            <h3 class="font-medium flex items-center gap-2">
              <User class="w-4 h-4" />
              Informações Pessoais
            </h3>
            
            <Input id="edit-nome" label="Nome Completo *" v-model="form.nome_completo" placeholder="Nome do lead" required />
            
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-email" label="E-mail" v-model="form.email" type="email" placeholder="email@exemplo.com" />
              <Input id="edit-cpf" label="CPF" v-model="form.cpf" v-maska="'###.###.###-##'" placeholder="000.000.000-00" />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-rg" label="RG" v-model="form.rg" placeholder="RG" />
              <div>
                <Select
                :label="'Gênero'"
                v-model="form.genero"
                name="genero"
                title="Gênero do lead. Selecione uma opção para personalizar a comunicação. Opções: Masculino, Feminino, Outro ou Prefiro não informar."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :options="[
                    { value: 'masculino', label: 'Masculino' },
                    { value: 'feminino', label: 'Feminino' },
                    { value: 'outro', label: 'Outro' },
                    { value: 'prefiro_nao_informar', label: 'Prefiro não informar' } 
                ]"
                />
              </div>
            </div>
            
            <Input id="edit-data-nascimento" label="Data de Nascimento" v-model="form.data_nascimento" type="date" />
            
            <!-- Contatos -->
            <div class="border-t pt-4">
              <h4 class="text-sm font-medium mb-3">Contatos</h4>
              <div v-for="(contato, idx) in form.contatos" :key="idx" class="flex gap-2 mb-2">
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
                </Select>
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
          </div>

          <!-- Seção: Endereço (EDIÇÃO) -->
          <div class="border rounded-lg p-4 space-y-4">
            <h3 class="font-medium flex items-center gap-2">
              <MapPin class="w-4 h-4" />
              Endereço
            </h3>
            
            <Input id="edit-cep" label="CEP" v-model="form.cep" v-maska="'#####-###'" placeholder="00000-000" />
            <Input id="edit-rua" label="Rua" v-model="form.rua" placeholder="Rua..." />
            
            <div class="grid grid-cols-3 gap-4">
              <Input id="edit-numero" label="Número" v-model="form.numero" placeholder="123" />
              <Input id="edit-bairro" label="Bairro" v-model="form.bairro" placeholder="Bairro" class="col-span-2" />
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <Input id="edit-cidade" label="Cidade" v-model="form.cidade" placeholder="Cidade" />
              <Input id="edit-estado" label="Estado" v-model="form.estado" placeholder="SP" maxlength="2" />
            </div>
            
            <Input id="edit-complemento" label="Complemento" v-model="form.complemento" placeholder="Apt 101, bloco A..." />
          </div>

          <!-- Seção: Dados Bancários (EDIÇÃO) -->
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
            
            <div>
              <label class="block text-sm font-medium text-[var(--text-primary)] mb-1">Tipo de Conta</label>
              <Select v-model="form.conta_tipo" class="w-full">
                <option value="">Selecione...</option>
                <option value="corrente">Corrente</option>
                <option value="poupanca">Poupança</option>
              </Select>
            </div>
            
            <div class="border-t pt-4">
              <h4 class="text-sm font-medium mb-3">PIX</h4>
              <div class="grid grid-cols-2 gap-4">
                <Input 
                  id="edit-pix" 
                  label="Chave PIX" 
                  v-model="form.pix" 
                  :placeholder="placeholderPix"
                />
                <div>
                  <label class="block text-sm font-medium text-[var(--text-primary)] mb-1">Tipo de Chave</label>
                  <Select v-model="form.pix_tipo" class="w-full">
                    <option value="">Selecione...</option>
                    <option value="cpf">CPF</option>
                    <option value="celular">Celular</option>
                    <option value="email">E-mail</option>
                    <option value="aleatorio">Chave aleatória</option>
                  </Select>
                </div>
              </div>
            </div>
          </div>

          <!-- Seção: Gerenciamento (EDIÇÃO) -->
          <div class="border rounded-lg p-4 space-y-4">
            <h3 class="font-medium flex items-center gap-2">
              <Settings class="w-4 h-4" />
              Gerenciamento
            </h3>
                      
          <div>
            <label class="block text-sm font-medium text-[var(--text-primary)] mb-1">Corretor Responsável</label>
            <Select
              v-model="form.corretor_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              :options="[
                { value: '', label: 'Selecione um corretor...' },
                ...(corretores || []).map(corretor => ({ 
                  value: corretor.id,
                  label: corretor.name 
                }))
              ]"
            />
          </div>
            
            <div>
               <Select
                    :label="'Status'"
                    :tooltip="'Imóvel que o lead demonstrou interesse'"
                    v-model="form.status_id"
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
            
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.adicionar_rodizio" class="rounded" />
              <span class="text-sm">Adicionar ao rodízio de leads</span>
            </label>
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
            <Button @click="saveChanges" :disabled="saving" loadingPosition="start" variant="primary">
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