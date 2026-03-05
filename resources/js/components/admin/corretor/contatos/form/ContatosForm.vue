<script setup lang="ts">
/**
 * Página de criação de contato a partir de um lead.
 * Permite selecionar um lead existente ou criar um contato manualmente.
 */
import { ref, computed, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import { Button } from '@/components/ui/button'
import Input from '@/components/ui/input/Input.vue'
import Select from '@/components/ui/select/Select.vue'
import { Label } from '@/components/ui/label'
import {
  ArrowLeft,
  Save,
  Loader2,
  ChevronDown,
  Search,
  User,
  Plus,
  Users,
  X,
  Mail,
  Phone,
  AlertCircle,
} from 'lucide-vue-next'
import { useToastStore } from '@/stores/toast'
import Toast from '@/components/ui/toast/Toast.vue'

// Interfaces
interface LeadResumo {
  id: number
  nome_completo: string
  email: string | null
  contatos?: Array<{
    numero: string
    tipo: string
    principal: boolean
  }>
  cidade: string | null
  estado: string | null
  status: string
  created_at: string
}

interface CorretorResumo {
  id: number
  nome: string
}

const props = defineProps<{
  leads: LeadResumo[]
  corretores: CorretorResumo[]
  status: any
}>()

const page = usePage()
const toast = useToastStore()

// Estado principal
const mode = ref<'from-lead' | 'manual'>('from-lead')
const saving = ref(false)
const error = ref<string | null>(null)
const validationErrors = ref<Record<string, string[]>>({})
const status = props.status

// Modo lead existente: seleção de lead
const searchQuery = ref('')
const selectedLeadId = ref<number | null>(null)

// Seções colapsáveis (modo manual)
const expandedSections = ref({
  basico: true,
  profissional: false,
  relacao: false,
  endereco: false,
  configuracao: true,
})

// Dados do contato (para ambos os modos)
const contatoForm = ref({
  // Dados básicos
  lead_id: null as number | null,
  nome_completo: '',
  email: '',
  
  // Dados pessoais
  genero: '',
  data_nascimento: '',
  cpf: '',
  rg: '',
  cnh: '',
  
  // Endereço
  cep: '',
  rua: '',
  bairro: '',
  cidade: '',
  estado: '',
  complemento: '',
  numero: '',
  
  // Profissional
  profissao: '',
  empresa: '',
  renda_mensal: null as number | null,
  
  // Relacionamento
  status_cliente: 'contato', //definindo o status do cliente para relacionar no quadro kanban como contato
  status_id: null as number | null,
  tipo_relacao: '',
  nivel_interesse: '',
  observacoes: '',
  
  // Atribuição
  corretor_id: null as number | null,

    // NOVOS CAMPOS:
  renda_familiar: null as number | null,
  origem: '',
  como_conheceu: '',
  
  // Preferências de imóvel
  preferencias_imovel_tipos: [] as string[],
  preferencias_modalidade: '',
  preferencias_preco_min: null as number | null,
  preferencias_preco_max: null as number | null,
  preferencias_localizacao: '',
  preferencias_caracteristicas: '',
})



const selectedLead = computed(() => {
  if (!selectedLeadId.value) return null
  return props.leads.find((lead: LeadResumo) => lead.id === selectedLeadId.value) || null
})

console.log(props.leads)

const canSubmit = computed(() => {
  if (mode.value === 'from-lead') {
    return selectedLeadId.value !== null
  }
  // Modo manual: campos obrigatórios
  return contatoForm.value.nome_completo.trim() !== ''
})

// Métodos
function toggleSection(section: keyof typeof expandedSections.value) {
  expandedSections.value[section] = !expandedSections.value[section]
}

function selectLead(id: number) {
  selectedLeadId.value = id
  const lead = props.leads.find((l: LeadResumo) => l.id === id)
  if (lead) {
    // Preencher formulário com dados do lead
    contatoForm.value.lead_id = lead.id
    contatoForm.value.nome_completo = lead.nome_completo
    contatoForm.value.email = lead.email || ''
    
    // Se o lead tiver contato principal, usar para dados adicionais
    if (lead.contatos && lead.contatos.length > 0) {
      const principal = lead.contatos.find((c: { numero: string; tipo: string; principal: boolean }) => c.principal) || lead.contatos[0]
      // Pode usar para preencher telefone se houver campo específico
    }
    
    if (lead.cidade) contatoForm.value.cidade = lead.cidade
    if (lead.estado) contatoForm.value.estado = lead.estado
  }
}

function clearSelection() {
  selectedLeadId.value = null
  searchQuery.value = ''
  contatoForm.value.lead_id = null
  contatoForm.value.nome_completo = ''
  contatoForm.value.email = ''
  contatoForm.value.cidade = ''
  contatoForm.value.estado = ''
}

function formatDate(date: string | null | undefined): string {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('pt-BR')
}

function getFieldError(field: string): string | null {
  const errs = validationErrors.value[field]
  return errs && errs.length > 0 ? errs[0] : null
}

async function buscarCep() {
  const cep = contatoForm.value.cep.replace(/\D/g, '')
  if (cep.length !== 8) return

  try {
    const resp = await fetch(`https://viacep.com.br/ws/${cep}/json/`)
    const data = await resp.json()

    if (!data.erro) {
      contatoForm.value.rua = data.logradouro || ''
      contatoForm.value.bairro = data.bairro || ''
      contatoForm.value.cidade = data.localidade || ''
      contatoForm.value.estado = data.uf || ''
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
  contatoForm.value.cep = value
}

async function submitForm() {
  saving.value = true
  error.value = null
  validationErrors.value = {}

  try {
    let payload: Record<string, any> = {
      mode: mode.value,
      ...contatoForm.value
    }

    if (mode.value === 'from-lead') {
      payload.lead_id = selectedLeadId.value
    }
    
    const url = payload.lead_id ? `/admin/corretor/contatos/${payload.lead_id}` 
    : '/admin/corretor/contatos';

    const method = payload.lead_id ? 'PUT' : 'POST'

    //As vezes precisa passar o decodeURIComponente
    const xsrfToken = decodeURIComponent(getCookie('XSRF-TOKEN') || '')

    const response = await fetch(url, {
      method: method,
      headers: {
        'X-XSRF-TOKEN': xsrfToken, 
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify(payload)
    })

    if(response.ok){
      toast.show('Contato criado com sucesso!', 'success')
      
      // Redirecionar para a lista de contatos
      setTimeout(() => {
        window.location.href = '/admin/corretor/contatos'
      }, 1500)
    }else{
      toast.show('Erro ao criar o contato', 'error')
    }

    
  } catch (e: any) {
    console.error('Erro ao criar contato', e)

    if (e.response?.status === 422 && e.response?.data?.errors) {
      validationErrors.value = e.response.data.errors
      error.value = 'Corrija os erros no formulário.'
      
      // Mostrar toasts para cada erro
      Object.values(e.response.data.errors).forEach((err: any) => {
        toast.show(err[0], 'error')
      })
    } else {
      error.value = e.response?.data?.error || e.response?.data?.message || 'Não foi possível criar o contato. Tente novamente.'
      toast.show(error.value, 'error')
    }
  } finally {
    saving.value = false
  }
}

// Função auxiliar para pegar cookie
function getCookie(name: string): string | null {
  const value = `; ${document.cookie}`
  const parts = value.split(`; ${name}=`)
  if (parts.length === 2) return parts.pop()?.split(';').shift() || null
  return null
}

// Limpar erros ao trocar de modo
watch(mode, () => {
  error.value = null
  validationErrors.value = {}
  clearSelection()
})
</script>

<template>
    <Toast />
    <div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
          <Link :href="route('admin.corretor.contatos.index')" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-4">
            <ArrowLeft class="w-4 h-4 mr-1" />
            Voltar para Contatos
          </Link>

          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h1 class="text-2xl font-bold text-foreground">Criar Contato</h1>
              <p class="text-sm text-muted-foreground mt-1">
                Converta um lead em contato ou cadastre um novo contato manualmente
              </p>
            </div>

            <Button
              variant="primary"
              :disabled="saving || !canSubmit"
              @click="submitForm"
            >
              <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
              <Save v-else class="w-4 h-4 mr-2" />
              Criar Contato
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
              :class="mode === 'from-lead'
                ? 'border-primary bg-primary/5'
                : 'border-transparent bg-card hover:border-muted'"
              @click="mode = 'from-lead'"
            >
              <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="mode === 'from-lead' ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'">
                <Users class="w-5 h-5" />
              </div>
              <div>
                <p class="font-semibold text-foreground">Converter Lead</p>
                <p class="text-xs text-muted-foreground">Selecione um lead para converter em contato</p>
              </div>
            </button>

            <button
              type="button"
              class="flex items-center gap-3 p-4 rounded-xl border-2 transition-all text-left"
              :class="mode === 'manual'
                ? 'border-primary bg-primary/5'
                : 'border-transparent bg-card hover:border-muted'"
              @click="mode = 'manual'"
            >
              <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="mode === 'manual' ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'">
                <User class="w-5 h-5" />
              </div>
              <div>
                <p class="font-semibold text-foreground">Cadastro Manual</p>
                <p class="text-xs text-muted-foreground">Crie um contato do zero</p>
              </div>
            </button>
          </div>
        </div>

        <div class="space-y-6">
          <!-- ========================================= -->
          <!-- MODO CONVERTER LEAD: Seleção de Lead -->
          <!-- ========================================= -->
          <template v-if="mode === 'from-lead'">
            <!-- Lead selecionado -->
            <div v-if="selectedLead" class="bg-card rounded-xl border border-primary p-4">
              <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-foreground">Lead Selecionado</h2>
                <button
                  type="button"
                  class="text-muted-foreground hover:text-destructive transition-colors"
                  @click="clearSelection"
                >
                  <X class="w-5 h-5" />
                </button>
              </div>

              <div class="flex gap-4">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                  <User class="w-8 h-8 text-primary" />
                </div>
                <div class="flex-1 min-w-0">
                  <h3 class="font-semibold text-foreground text-lg">{{ selectedLead.nome_completo }}</h3>
                  <div class="flex flex-wrap items-center gap-3 mt-1">
                    <span v-if="selectedLead.email" class="text-sm text-muted-foreground flex items-center gap-1">
                      <Mail class="w-3 h-3" />
                      {{ selectedLead.email }}
                    </span>
                    <span v-if="selectedLead.cidade" class="text-sm text-muted-foreground">
                      {{ selectedLead.cidade }}{{ selectedLead.estado ? `, ${selectedLead.estado}` : '' }}
                    </span>
                    <span class="text-xs bg-muted text-muted-foreground rounded-full px-2 py-0.5">
                      Lead desde {{ formatDate(selectedLead.created_at) }}
                    </span>
                  </div>
                  
                  <!-- Contatos do lead -->
                  <div v-if="selectedLead.contatos && selectedLead.contatos.length" class="mt-2 flex flex-wrap gap-2">
                    <span
                      v-for="(contato, idx) in selectedLead.contatos"
                      :key="idx"
                      class="inline-flex items-center gap-1 text-xs bg-muted/50 text-muted-foreground rounded-full px-2 py-0.5"
                    >
                      <Phone class="w-3 h-3" />
                      {{ contato.numero }}
                      <span v-if="contato.principal" class="text-primary text-[10px] font-medium">(Principal)</span>
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="mt-4 p-3 bg-muted/30 rounded-lg">
                <p class="text-sm text-muted-foreground">
                  <span class="font-medium text-foreground">Ao converter:</span> Os dados básicos do lead serão copiados para o contato. Você poderá complementar as informações após a conversão.
                </p>
              </div>
            </div>

            <!-- Busca e lista de leads -->
            <div v-else class="bg-card rounded-xl border p-4">
              <h2 class="text-lg font-semibold mb-4">Selecione um Lead para Converter</h2>

              <!-- Campo de busca -->
              <div class="relative mb-4">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Buscar por nome, email, cidade..."
                  class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-[var(--border)] bg-[var(--card)] text-sm shadow-xs focus:border-[var(--ring)] focus:ring-[var(--ring)]/50 focus:ring-[3px] outline-none"
                />
              </div>

              <!-- Lista de leads -->
              <div class="space-y-2 max-h-[400px] overflow-y-auto">
                <button
                  v-for="lead in props.leads"
                  :key="lead.id"
                  type="button"
                  class="w-full flex items-center gap-3 p-3 rounded-lg border border-transparent hover:border-primary hover:bg-primary/5 transition-all text-left"
                  @click="selectLead(lead.id)"
                >
                  <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                    <User class="w-5 h-5 text-primary" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                      <h3 class="font-medium text-foreground">{{ lead.nome_completo }}</h3>
                      <span class="text-xs bg-muted text-muted-foreground rounded-full px-2 py-0.5">{{ lead.status.nome }}</span>
                    </div>
                    <p v-if="lead.email" class="text-xs text-muted-foreground">{{ lead.email }}</p>
                    <div class="flex items-center gap-2 mt-0.5">
                      <span v-if="lead.cidade" class="text-xs text-muted-foreground">{{ lead.cidade }}{{ lead.estado ? `, ${lead.estado}` : '' }}</span>
                      <span class="text-xs text-muted-foreground">{{ formatDate(lead.created_at) }}</span>
                    </div>
                  </div>
                </button>

                <div v-if="props.leads === 0" class="text-center py-8 text-muted-foreground">
                  <Users class="w-10 h-10 mx-auto mb-2 opacity-50" />
                  <p v-if="searchQuery">Nenhum lead encontrado para "{{ searchQuery }}"</p>
                  <p v-else>Nenhum lead disponível para conversão</p>
                  <button
                    type="button"
                    class="mt-2 text-sm text-primary hover:underline"
                    @click="mode = 'manual'"
                  >
                    Criar contato manualmente
                  </button>
                </div>
              </div>
            </div>
          </template>

          <!-- ========================================= -->
          <!-- MODO MANUAL: Formulário de Contato -->
          <!-- ========================================= -->
          <template v-if="mode === 'manual'">
            <!-- Seção: Dados Básicos (obrigatório) -->
            <div class="bg-card rounded-xl border" :class="getFieldError('nome_completo') ? 'border-destructive' : ''">
              <button
                class="w-full flex items-center justify-between p-4 text-left"
                type="button"
                @click="toggleSection('basico')"
              >
                <h2 class="text-lg font-semibold">
                  Dados Básicos
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
                    <Label for="nome_completo">Nome Completo *</Label>
                    <Input
                      id="nome_completo"
                      v-model="contatoForm.nome_completo"
                      placeholder="Nome completo do contato"
                      class="mt-1"
                      :class="getFieldError('nome_completo') ? 'border-destructive' : ''"
                    />
                    <p v-if="getFieldError('nome_completo')" class="text-xs text-destructive mt-1">
                      {{ getFieldError('nome_completo') }}
                    </p>
                  </div>
                  <div>
                    <Label for="email">E-mail</Label>
                    <Input
                      id="email"
                      v-model="contatoForm.email"
                      type="email"
                      placeholder="email@exemplo.com"
                      class="mt-1"
                    />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div>
                    <Label for="genero">Gênero</Label>
                    <Select id="genero" v-model="contatoForm.genero" class="mt-1">
                      <option value="">Selecione...</option>
                      <option value="masculino">Masculino</option>
                      <option value="feminino">Feminino</option>
                      <option value="outro">Outro</option>
                      <option value="prefiro_nao_informar">Prefiro não informar</option>
                    </Select>
                  </div>
                  <div>
                    <Input
                      label="Data Nascimento"
                      id="data_nascimento"
                      v-model="contatoForm.data_nascimento"
                      type="date"
                    />
                  </div>
                  <div>
                    <Label for="cpf">CPF</Label>
                    <Input
                      id="cpf"
                      v-model="contatoForm.cpf"
                      placeholder="000.000.000-00"
                      class="mt-1"
                    />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <Label for="rg">RG</Label>
                    <Input id="rg" v-model="contatoForm.rg" placeholder="RG" class="mt-1" />
                  </div>
                  <div>
                    <Label for="cnh">CNH</Label>
                    <Input id="cnh" v-model="contatoForm.cnh" placeholder="CNH" class="mt-1" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Seção: Dados Profissionais -->
            <div class="bg-card rounded-xl border">
              <button
                class="w-full flex items-center justify-between p-4 text-left"
                type="button"
                @click="toggleSection('profissional')"
              >
                <h2 class="text-lg font-semibold">Dados Profissionais</h2>
                <ChevronDown
                  class="w-5 h-5 text-muted-foreground transition-transform"
                  :class="{ 'rotate-180': expandedSections.profissional }"
                />
              </button>

              <div v-show="expandedSections.profissional" class="px-4 pb-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <Label for="profissao">Profissão</Label>
                    <Input id="profissao" v-model="contatoForm.profissao" placeholder="Ex: Engenheiro" class="mt-1" />
                  </div>
                  <div>
                    <Label for="empresa">Empresa</Label>
                    <Input id="empresa" v-model="contatoForm.empresa" placeholder="Onde trabalha" class="mt-1" />
                  </div>
                </div>
                <div>
                  <Label for="renda_mensal">Renda Mensal (R$)</Label>
                  <Input
                    id="renda_mensal"
                    v-model="contatoForm.renda_mensal"
                    type="number"
                    step="0.01"
                    placeholder="0,00"
                    class="mt-1"
                  />
                </div>
              </div>
            </div>

            <!-- Seção: Relacionamento -->
            <div class="bg-card rounded-xl border">
              <button
                class="w-full flex items-center justify-between p-4 text-left"
                type="button"
                @click="toggleSection('relacao')"
              >
                <h2 class="text-lg font-semibold">Relacionamento</h2>
                <ChevronDown
                  class="w-5 h-5 text-muted-foreground transition-transform"
                  :class="{ 'rotate-180': expandedSections.relacao }"
                />
              </button>

              <div v-show="expandedSections.relacao" class="px-4 pb-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <Label for="status">Status</Label>
                    <Select id="status" v-model="contatoForm.status" class="mt-1">
                      <option value="ativo">Ativo</option>
                      <option value="inativo">Inativo</option>
                      <option value="potencial">Potencial</option>
                      <option value="perdido">Perdido</option>
                    </Select>
                  </div>
                  <div>
                    <Label for="tipo_relacao">Tipo de Relação</Label>
                    <Select id="tipo_relacao" v-model="contatoForm.tipo_relacao" class="mt-1">
                      <option value="">Selecione...</option>
                      <option value="proprietario">Proprietário</option>
                      <option value="inquilino">Inquilino</option>
                      <option value="comprador">Comprador</option>
                      <option value="vendedor">Vendedor</option>
                      <option value="fiador">Fiador</option>
                      <option value="outro">Outro</option>
                    </Select>
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <Label for="nivel_interesse">Nível de Interesse</Label>
                    <Select id="nivel_interesse" v-model="contatoForm.nivel_interesse" class="mt-1">
                      <option value="">Selecione...</option>
                      <option value="baixo">Baixo</option>
                      <option value="medio">Médio</option>
                      <option value="alto">Alto</option>
                      <option value="urgente">Urgente</option>
                    </Select>
                  </div>
                  <div>
                    <Label for="ultimo_contato">Último Contato</Label>
                    <Input
                      id="ultimo_contato"
                      v-model="contatoForm.ultimo_contato"
                      type="date"
                      class="mt-1"
                    />
                  </div>
                </div>

                <div>
                  <Label for="observacoes">Observações</Label>
                  <textarea
                    id="observacoes"
                    v-model="contatoForm.observacoes"
                    placeholder="Observações adicionais sobre o contato..."
                    class="mt-1 w-full rounded-md border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm shadow-xs focus:border-[var(--ring)] focus:ring-[var(--ring)]/50 focus:ring-[3px] outline-none"
                    rows="3"
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
                      :value="contatoForm.cep"
                      placeholder="00000-000"
                      class="mt-1"
                      maxlength="9"
                      @input="formatCep"
                      @blur="buscarCep"
                    />
                  </div>
                  <div>
                    <Label for="estado">Estado</Label>
                    <Input id="estado" v-model="contatoForm.estado" placeholder="UF" maxlength="2" class="mt-1" />
                  </div>
                  <div>
                    <Label for="cidade">Cidade</Label>
                    <Input id="cidade" v-model="contatoForm.cidade" placeholder="Cidade" class="mt-1" />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <Label for="bairro">Bairro</Label>
                    <Input id="bairro" v-model="contatoForm.bairro" placeholder="Bairro" class="mt-1" />
                  </div>
                  <div>
                    <Label for="rua">Endereço</Label>
                    <Input id="rua" v-model="contatoForm.rua" placeholder="Rua/Avenida" class="mt-1" />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div>
                    <Label for="numero">Número</Label>
                    <Input id="numero" v-model="contatoForm.numero" placeholder="Nº" class="mt-1" />
                  </div>
                  <div>
                    <Label for="complemento">Complemento</Label>
                    <Input id="complemento" v-model="contatoForm.complemento" placeholder="Apto, Bloco..." class="mt-1" />
                  </div>
                </div>
              </div>
            </div>
          </template>

        <!-- ========================================= -->
        <!-- SEÇÃO COMUM: Configuração do Contato (para ambos modos) -->
        <!-- ========================================= -->
        <div class="bg-card rounded-xl border">
        <button
            class="w-full flex items-center justify-between p-4 text-left"
            type="button"
            @click="toggleSection('configuracao')"
        >
            <h2 class="text-lg font-semibold">Configuração do Contato</h2>
            <ChevronDown
            class="w-5 h-5 text-muted-foreground transition-transform"
            :class="{ 'rotate-180': expandedSections.configuracao }"
            />
        </button>

        <div v-show="expandedSections.configuracao" class="px-4 pb-4 space-y-4">
            <!-- Linha 1: Corretor e Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Corretor Responsável -->
            <div>
                <Label for="corretor_id">Corretor Responsável</Label>
                <Select id="corretor_id" v-model="contatoForm.corretor_id" class="mt-1">
                <option :value="null">Selecione um corretor...</option>
                <option v-for="corretor in corretores" :key="corretor.id" :value="corretor.id">
                    {{ corretor.nome }}
                </option>
                </Select>
                <p v-if="getFieldError('corretor_id')" class="text-xs text-destructive mt-1">
                {{ getFieldError('corretor_id') }}
                </p>
            </div>

            <!-- Status do Contato -->
            <div>
                <Select
                    :label="'Status'"
                    :tooltip="'Imóvel que o lead demonstrou interesse'"
                    v-model="contatoForm.status_id"
                    :error="errors?.status_id"
                    name="status"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :options="[
                      { value: '', label: 'Selecione um status' },
                      ...(status || []).map((s: any) => ({ 
                        value: s.id,
                        label: `${s.nome}`
                      }))
                    ]"
                />
                <p v-if="getFieldError('status')" class="text-xs text-destructive mt-1">
                {{ getFieldError('status') }}
                </p>
            </div>
            </div>

            <!-- Linha 2: Tipo de Relação e Nível de Interesse -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Tipo de Relação -->
            <div>
                <Label for="tipo_relacao">Tipo de Relação</Label>
                <Select id="tipo_relacao" v-model="contatoForm.tipo_relacao" class="mt-1">
                <option value="">Selecione...</option>
                <option value="proprietario">Proprietário</option>
                <option value="inquilino">Inquilino</option>
                <option value="comprador">Comprador</option>
                <option value="vendedor">Vendedor</option>
                <option value="fiador">Fiador</option>
                <option value="investidor">Investidor</option>
                <option value="parceiro">Parceiro</option>
                <option value="outro">Outro</option>
                </Select>
            </div>

            <!-- Nível de Interesse -->
            <div>
                <Label for="nivel_interesse">Nível de Interesse</Label>
                <Select id="nivel_interesse" v-model="contatoForm.nivel_interesse" class="mt-1">
                <option value="">Selecione...</option>
                <option value="1">Baixo</option>
                <option value="2">Médio</option>
                <option value="3">Alto</option>
                <option value="4">Urgente</option>
                </Select>
            </div>
            </div>

            <!-- Linha 3: Renda Mensal e Renda Familiar -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Renda Mensal -->
            <div>
                <Label for="renda_mensal">Renda Mensal (R$)</Label>
                <div class="relative mt-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">R$</span>
                <Input
                    id="renda_mensal"
                    v-model="contatoForm.renda_mensal"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0,00"
                    class="pl-10"
                />
                </div>
                <p v-if="getFieldError('renda_mensal')" class="text-xs text-destructive mt-1">
                {{ getFieldError('renda_mensal') }}
                </p>
            </div>

            <!-- Renda Familiar -->
            <div>
                <Label for="renda_familiar">Renda Familiar (R$)</Label>
                <div class="relative mt-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">R$</span>
                <Input
                    id="renda_familiar"
                    v-model="contatoForm.renda_familiar"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0,00"
                    class="pl-10"
                />
                </div>
            </div>
            </div>

            <!-- Linha 4: Data Último Contato e Origem -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <!-- Origem do Contato -->
            <div>
                <Label for="origem">Origem do Contato</Label>
                <Select id="origem" v-model="contatoForm.origem" class="mt-1">
                <option value="">Selecione...</option>
                <option value="site">Site</option>
                <option value="portal">Portal (OLX, Zap, etc)</option>
                <option value="indicacao">Indicação</option>
                <option value="rede_social">Rede Social</option>
                <option value="whatsapp">WhatsApp</option>
                <option value="telefone">Telefone</option>
                <option value="email">E-mail</option>
                <option value="presencial">Presencial</option>
                <option value="campanha">Campanha</option>
                <option value="outro">Outro</option>
                </Select>
            </div>
            </div>

            <!-- Linha 5: Preferências de Imóvel -->
            <div>
            <Label for="preferencias_imovel">Preferências de Imóvel</Label>
            <div class="mt-1 space-y-3">
                <!-- Tipo de imóvel preferido -->
                <div class="flex flex-wrap gap-3">
                <label class="flex items-center gap-2 text-sm">
                    <input
                    type="checkbox"
                    v-model="contatoForm.preferencias_imovel_tipos"
                    value="apartamento"
                    class="rounded border-gray-300"
                    />
                    Apartamento
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input
                    type="checkbox"
                    v-model="contatoForm.preferencias_imovel_tipos"
                    value="casa"
                    class="rounded border-gray-300"
                    />
                    Casa
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input
                    type="checkbox"
                    v-model="contatoForm.preferencias_imovel_tipos"
                    value="terreno"
                    class="rounded border-gray-300"
                    />
                    Terreno
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input
                    type="checkbox"
                    v-model="contatoForm.preferencias_imovel_tipos"
                    value="comercial"
                    class="rounded border-gray-300"
                    />
                    Comercial
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input
                    type="checkbox"
                    v-model="contatoForm.preferencias_imovel_tipos"
                    value="rural"
                    class="rounded border-gray-300"
                    />
                    Rural
                </label>
                </div>

                <!-- Modalidade preferida -->
                <div class="flex flex-wrap gap-3">
                <label class="flex items-center gap-2 text-sm">
                    <input
                    type="radio"
                    v-model="contatoForm.preferencias_modalidade"
                    value="compra"
                    class="border-gray-300"
                    />
                    Compra
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input
                    type="radio"
                    v-model="contatoForm.preferencias_modalidade"
                    value="locacao"
                    class="border-gray-300"
                    />
                    Locação
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input
                    type="radio"
                    v-model="contatoForm.preferencias_modalidade"
                    value="ambos"
                    class="border-gray-300"
                    />
                    Ambos
                </label>
                </div>

                <!-- Faixa de preço -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <Label for="preco_min" class="text-xs">Preço mínimo (R$)</Label>
                    <Input
                    id="preco_min"
                    v-model="contatoForm.preferencias_preco_min"
                    type="number"
                    step="0.01"
                    placeholder="Mínimo"
                    class="mt-1"
                    />
                </div>
                <div>
                    <Label for="preco_max" class="text-xs">Preço máximo (R$)</Label>
                    <Input
                    id="preco_max"
                    v-model="contatoForm.preferencias_preco_max"
                    type="number"
                    step="0.01"
                    placeholder="Máximo"
                    class="mt-1"
                    />
                </div>
                </div>

                <!-- Localização preferida -->
                <div>
                <Label for="preferencias_localizacao" class="text-xs">Localização preferida</Label>
                <Input
                    id="preferencias_localizacao"
                    v-model="contatoForm.preferencias_localizacao"
                    placeholder="Ex: Zona Sul, Centro, Bairro X..."
                    class="mt-1"
                />
                </div>

                <!-- Características desejadas -->
                <div>
                <Label for="preferencias_caracteristicas" class="text-xs">Características desejadas</Label>
                <textarea
                    id="preferencias_caracteristicas"
                    v-model="contatoForm.preferencias_caracteristicas"
                    placeholder="Ex: 3 quartos, suíte, vaga de garagem, área de lazer..."
                    class="mt-1 w-full rounded-md border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm shadow-xs focus:border-[var(--ring)] focus:ring-[var(--ring)]/50 focus:ring-[3px] outline-none"
                    rows="2"
                />
                </div>
            </div>
            </div>

            <!-- Linha 6: Observações -->
            <div>
            <Label for="observacoes">Observações Gerais</Label>
            <textarea
                id="observacoes"
                v-model="contatoForm.observacoes"
                placeholder="Observações adicionais sobre o contato, histórico de conversas, preferências especiais..."
                class="mt-1 w-full rounded-md border border-[var(--border)] bg-[var(--card)] px-3 py-2 text-sm shadow-xs focus:border-[var(--ring)] focus:ring-[var(--ring)]/50 focus:ring-[3px] outline-none"
                rows="4"
            />
            <p v-if="getFieldError('observacoes')" class="text-xs text-destructive mt-1">
                {{ getFieldError('observacoes') }}
            </p>
            </div>

            <!-- Linha 8: Como conheceu a imobiliária -->
            <div>
            <Label for="como_conheceu">Como conheceu a imobiliária?</Label>
            <Input
                id="como_conheceu"
                v-model="contatoForm.como_conheceu"
                placeholder="Ex: Indicação, Google, Facebook, Outdoor..."
                class="mt-1"
            />
            </div>
        </div>
        </div>

          <!-- Botões de Ação -->
          <div class="flex justify-end gap-4 pt-4">
            <Link :href="route('admin.corretor.contatos.index')">
              <Button variant="outline">Cancelar</Button>
            </Link>
            <Button
              variant="primary"
              :disabled="saving || !canSubmit"
              @click="submitForm"
            >
              <Loader2 v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
              <Save v-else class="w-4 h-4 mr-2" />
              Criar Contato
            </Button>
          </div>
        </div>
      </div>
    </div>
</template>