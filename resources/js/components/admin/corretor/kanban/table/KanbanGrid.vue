<script setup lang="ts">
import { ref, computed, watch, onMounted, toRaw } from 'vue'
import { VueDraggable } from 'vue-draggable-plus'
import { GripVertical, Eye, RefreshCw, Settings, Plus, UserPlus, Trash} from 'lucide-vue-next'
import type { LeadListagem } from '@/types/forms/lead-form'
import { useToastStore } from '@/stores/toast';
import Toast from '@/components/ui/toast/Toast.vue';
import LeadDetailsModal from '@/components/admin/corretor/leads/modal/LeadDetailsModal.vue';
import DropdownButtonKanban from '@/components/ui/dropdown-button/DropdownButtonKanban.vue';
import KanbanColunaModal from '../modal/KanbanColunaModal.vue';
import KanbanConfigColunaModal from '../modal/KanbanConfigColunaModal.vue';

const props = defineProps<{
  leads: LeadListagem[],
  colunas?: any[],
  cardsPorColuna?: Record<string, any>,
  quadro: any,
  todosQuadros?: any[]
}>()

const colunasLocais = ref(props.colunas)
const selectedLead = ref<LeadListagem | null>(null);
const showDetailsModal = ref(false);
const isUpdating = ref(false)
const toast = useToastStore();
const leadsLocal = ref(props.leads);
const leadsFlatList = ref<LeadListagem[]>([])
console.log(colunasLocais)

const emit = defineEmits<{
  (e: 'open-details', lead: LeadListagem): void
  (e: 'update-status', lead: LeadListagem, newStatus: string): void
  (e: 'change-quadro', quadro: any): void
}>()

// Definição das colunas (vem das props ou usa fallback)
const colunasExibidas = computed(() => {
  // Se temos colunas locais (após carregar um quadro), usa elas
  if (colunasLocais.value.length > 0) {
    return colunasLocais.value
  }
  
  // Senão, usa as props ou fallback
  if (props.colunas && props.colunas.length > 0) {
    return props.colunas
  }
  
  // Fallback para colunas padrão
  return [
    { id: 1, titulo: 'Novo', cor: 'blue', cor_fundo: '#EFF6FF', icone: 'UserPlus' },
    { id: 2, titulo: 'Simulação', cor: 'purple', cor_fundo: '#EFF6FF', icone: 'Calculator' },
    { id: 3, titulo: 'Visita', cor: 'purple', cor_fundo: '#F3E8FF', icone: 'Home' },
    { id: 4, titulo: 'Negociação', cor: 'green', cor_fundo: '#D1FAE5', icone: 'MessageCircle' },
  ];
})


// Estado local para os leads agrupados
const localColumnsData = ref<Record<string, LeadListagem[]>>({})
const isLoading = ref(false)

// Watch para inicializar dados
watch([() => props.leads, () => props.cardsPorColuna], ([newLeads, cardsPorColuna]) => {
  if (cardsPorColuna && Object.keys(cardsPorColuna).length > 0) {
    const groups: Record<string, LeadListagem[]> = {};
    Object.values(cardsPorColuna).forEach((item: any) => {
      if (item.coluna && item.leads) {
        groups[item.coluna.id] = item.leads;
      }
    });
    localColumnsData.value = groups;
  } else if (newLeads && newLeads.length) {
    const groups: Record<string, LeadListagem[]> = {};
    colunasExibidas.value.forEach(col => {
      groups[col.id] = [];
    });
    
    newLeads.forEach(lead => {
      const status = lead.status || 'lead';
      const coluna = colunasExibidas.value.find(c => 
        c.titulo.toLowerCase().includes(status.toLowerCase()) ||
        status.toLowerCase().includes(c.titulo.toLowerCase())
      );
      
      if (coluna && groups[coluna.id]) {
        groups[coluna.id].push(lead);
      } else if (groups[1]) {
        groups[1].push(lead);
      }
    });
    
    localColumnsData.value = groups;
  }
}, { immediate: true, deep: true });

// Computed para expor os dados agrupados
const columnsData = computed(() => localColumnsData.value)



async function handleDragEnd(event: any) {
  console.log('handleDragEnd DISPARADO!', event)
  
  try {
    const { newIndex, oldIndex, from, to } = event
    console.log('De:', from.dataset.status, 'oldIndex:', oldIndex)
    console.log('Para:', to.dataset.status, 'newIndex:', newIndex)

    if (to.dataset.colunaEspecial === 'true') {
      return
    }

    const movedLead = localColumnsData.value[to.dataset.status]?.[newIndex]
    
    if (!movedLead) {
      console.error('Lead não encontrado no destino')
      return
    }

    console.log('Lead movido:', movedLead)
    
    // Pega status antigo (do estado atual ou de um backup)
    const oldStatus = movedLead.status
    
    // Encontra coluna destino
    const colunaDestino = colunasExibidas.value.find(c => c.id.toString() === to.dataset.status)
    const newStatusId = colunaDestino?.status?.id || parseInt(to.dataset.status)
    
    console.log('Atualizando status de', oldStatus, 'para', newStatusId)
    
    // Atualiza localmente
    movedLead.status = newStatusId
    
    isUpdating.value = true
    
    try {
      const response = await fetch(`/admin/corretor/kanban/${movedLead.id}/status`, {
        method: 'PUT',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
        },
        body: JSON.stringify({ 
          status_id: newStatusId,
          coluna_id: parseInt(to.dataset.status)
        })
      })
      
      if (!response.ok) {
        throw new Error('Erro ao atualizar status')
      }
      
      toast.show('Lead movido com sucesso!', 'success')
      
    } catch (error) {
      console.error('Erro no backend:', error)
      
      // Reverte a mudança LOCAL
      movedLead.status = oldStatus
      localColumnsData.value = { ...localColumnsData.value }
      
      toast.show('Erro ao mover lead. Tente novamente.', 'error')
      
    } finally {
      isUpdating.value = false
    }
    
  } catch (error) {
    console.error('Erro ao mover lead:', error)
    toast.show('Erro ao mover lead', 'error')
  }
}

// Funções auxiliares
function getPrincipalContact(lead: LeadListagem) {
  if (!lead.contatos || !lead.contatos.length) return null
  const principal = lead.contatos.find(c => c.principal)
  return principal || lead.contatos[0]
}

function getInitials(name: string) {
  if (!name) return '??'
  return name
    .split(' ')
    .map(n => n[0])
    .filter(c => c && c !== '')
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

function formatCurrency(value?: number | string) {
  if (!value) return null
  const numValue = typeof value === 'string' ? parseFloat(value) : value
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(numValue)
}


function handleLeadUpdated(updatedLead: LeadListagem) {
  // Encontra em qual coluna o lead está atualmente
  let found = false
  
  // Percorre todas as colunas
  Object.keys(localColumnsData.value).forEach(colunaId => {
    const column = localColumnsData.value[colunaId]
    const index = column.findIndex(l => l.id === updatedLead.id)
    
    if (index !== -1) {
      // Atualiza o lead na coluna atual
      column[index] = { ...column[index], ...updatedLead }
      found = true
    }
  })
  
  if (found) {
    localColumnsData.value = { ...localColumnsData.value }
    
    //Filtra novo status    
    const novoStatus = updatedLead.status.id

    //Procura o status nas colunas existentes
    const colunaDestino = colunasExibidas.value.find(c => 
      c.status?.id === novoStatus || c.id === novoStatus
    )

    if (colunaDestino) {
      // Encontra a coluna atual do lead
      for (const colunaId in localColumnsData.value) {
        const index = localColumnsData.value[colunaId].findIndex(l => l.id === updatedLead.id)
        if (index !== -1 && colunaId !== colunaDestino.id.toString()) {
          // Precisa mover para outra coluna
          const [movedLead] = localColumnsData.value[colunaId].splice(index, 1)
          
          if (!localColumnsData.value[colunaDestino.id]) {
            localColumnsData.value[colunaDestino.id] = []
          }
          
          localColumnsData.value[colunaDestino.id].push(movedLead)
          localColumnsData.value = { ...localColumnsData.value }
          break
        }
      }
    }
  }
}

function handleOpenDetails(lead: LeadListagem) {
  selectedLead.value = lead;
  showDetailsModal.value = true;
}

function closeDetailsModal() {
  showDetailsModal.value = false;
  selectedLead.value = null;
}

const showColumnModal = ref(false)
const showConfigColumnModal = ref(false)
const columnModalMode = ref<'create' | 'edit'>('create')
const columnForm = ref({
  id: null as number | null,
  status:{
    id: null as number | null,
    nome: '',
  },
  cor: '',
  descricao: ''
})
const editingColumn = ref<any>(null)

// Funções para gerenciar colunas
function openCreateColumnModal() {
  columnModalMode.value = 'create'
  showColumnModal.value = true

  columnForm.value = {
    id: '',
    status: {
      id: null as number | null,
      nome: ''
    },
    cor: 'purple',
    descricao: ''
  }
}

// Funções para gerenciar colunas
function openConfigColumnModal() {
  showConfigColumnModal.value = true
  columnModalMode.value = 'edit'
}

function openEditColumnModal(coluna: any) {
  columnModalMode.value = 'edit'
  editingColumn.value = coluna
  columnForm.value = {
    id: coluna.id,
    status: {
      id: coluna.status.id, 
      nome: coluna.status.nome
    },
    cor: coluna.cor || 'blue',
    descricao: coluna.descricao || ''
  }
  showColumnModal.value = true
}

function closeColumnModal() {
  showColumnModal.value = false
  editingColumn.value = null
}

function closeConfigColumnModal() {
  showConfigColumnModal.value = false
}

async function saveColumn() {
  if (!columnForm.value.status.nome) {
    toast.show('Título é obrigatório', 'error')
    return
  }
  try {
    if (columnModalMode.value === 'create') {
      // Criar nova coluna
      const response = await fetch('/admin/corretor/kanban/colunas', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
        },
        body: JSON.stringify({
          status: columnForm.value.status.nome,
          cor: columnForm.value.cor,
          descricao: columnForm.value.descricao,
          kanban_quadro_id: quadroAtual.value.id
        })
      })
      
      if (response.ok) {
        const data = await response.json()

        // Adiciona a nova coluna à lista
        colunasLocais.value = data.coluna

        // Inicializa o array para a nova coluna
        localColumnsData.value[data.coluna.id] = []
        toast.show('Coluna criada com sucesso!', 'success')
        closeColumnModal()
      }
    } else {
      // Editar coluna existente
        console.log('atualizando column', colunasLocais)

      const response = await fetch(`/admin/corretor/kanban/colunas/${columnForm.value.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
        },
        body: JSON.stringify({
          status: columnForm.value.status.nome,
          status_id: columnForm.value.status.id,
          cor: columnForm.value.cor,
          descricao: columnForm.value.descricao,
          kanban_quadro_id: quadroAtual.value.id
        })
      })
      if (response.ok) {
        const data = await response.json()
        console.log(data)
        // Atualiza a coluna na lista
        const index = colunasLocais.value.findIndex(c => c.id === editingColumn.value.id)
        if (index !== -1) {
          colunasLocais.value[index] = data.coluna 
        }
        toast.show('Coluna atualizada com sucesso!', 'success')
        closeColumnModal()
      }
    }
  } catch (error) {
    console.error('Erro ao salvar coluna:', error)
    toast.show('Erro ao salvar coluna', 'error')
  }
}


async function confirmDeleteColumn(coluna: any) {
  if (!confirm(`Tem certeza que deseja excluir a coluna "${coluna.status.nome}"? Os leads serão movidos para a primeira coluna.`)) {
    return
  }
  
  try {
    const response = await fetch(`/admin/corretor/kanban/colunas/${coluna.id}`, {
      method: 'DELETE',
      headers: {
        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
      }
    })
    
    if (response.ok) {
      // Move os leads para a primeira coluna
      const leadsDaColuna = localColumnsData.value[coluna.id] || []
      const primeiraColuna = colunasLocais.value[0]
      
      if (leadsDaColuna.length > 0 && primeiraColuna) {
        localColumnsData.value[primeiraColuna.id] = [
          ...(localColumnsData.value[primeiraColuna.id] || []),
          ...leadsDaColuna
        ]
      }
      
      // Remove a coluna
      delete localColumnsData.value[coluna.id]
      colunasLocais.value = colunasLocais.value.filter(c => c.id !== coluna.id)
      
      toast.show('Coluna excluída com sucesso!', 'success')
    }
  } catch (error) {
    console.error('Erro ao excluir coluna:', error)
    toast.show('Erro ao excluir coluna', 'error')
  }
}

// Estado
const quadroAtual = ref(props.quadro)
const quadrosList = ref<any[]>(props.quadros || [])
const showQuadroModal = ref(false)
const novoQuadroForm = ref({
  nome: '',
  tipo: 'leads',
  descricao: ''
})

// Funções
async function selectQuadro(quadro: any) {
  console.log('Selecionando quadro:', quadro)
  
  // Emite evento para o componente pai
  emit('change-quadro', quadro)
  
  // Atualiza o quadro atual localmente
  quadroAtual.value = quadro
  
  // Aqui você pode adicionar lógica para carregar os dados do novo quadro
  await carregarDadosQuadro(quadro.id)

  toast.show(`Quadro "${quadro.nome}" carregado!`, 'success')
}

async function carregarDadosQuadro(quadroId: number) {
  isLoading.value = true
  
  try {
    const xsrfToken = getCookie('XSRF-TOKEN')
    const response = await fetch(`/admin/corretor/kanban/quadros/${quadroId}/dados`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-XSRF-TOKEN': xsrfToken,
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      console.log('Dados recebidos:', data)
      
      if (data.success) {
        // Atualiza o quadro atual
        quadroAtual.value = data.quadro
        
        // ATUALIZA AS COLUNAS LOCAIS com os dados do backend
        if (data.colunas) {
          colunasLocais.value = data.colunas
        }
        
        // Processa cardsPorColuna - leads organizados por coluna
        if (data.cardsPorColuna) {
          const cardsFormatados: Record<string, LeadListagem[]> = {}
          Object.entries(data.cardsPorColuna).forEach(([colunaId, colunaData]: [string, any]) => {
            // Formata cada lead da coluna
            cardsFormatados[colunaId] = (colunaData.leads || []).map((item: any) => ({
              id: item.id,
              nome_completo: item.nome_completo,
              email: item.email,
              status: item.status,
              cidade: item.cidade,
              estado: item.estado,
              valor_desejado: item.valor_desejado,
              adicionar_rodizio: item.adicionar_rodizio,
              contatos: item.contatos,
              corretor_id: item.corretor_id,
              corretor: item.corretor,
              lead_id: item.lead_id,
              lead: item.lead,
              tipo: item.tipo,
              created_at: item.created_at,
              updated_at: item.updated_at
            }))
          })
          
          // Atribui os leads organizados por coluna
          localColumnsData.value = cardsFormatados
        }
        
        // Processa a lista completa de leads (items)
        if (data.items) {
          leadsFlatList.value = data.items.map((item: any) => ({
            id: item.id,
            nome_completo: item.nome_completo,
            email: item.email,
            status: item.status,
            cidade: item.cidade,
            estado: item.estado,
            valor_desejado: item.valor_desejado,
            adicionar_rodizio: item.adicionar_rodizio,
            contatos: item.contatos,
            corretor_id: item.corretor_id,
            corretor: item.corretor,
            lead_id: item.lead_id,
            lead: item.lead,
            tipo: item.tipo,
            created_at: item.created_at,
            updated_at: item.updated_at
          }))
        }
      }
    }
  } catch (error) {
    console.error('Erro ao carregar dados do quadro:', error)
  } finally {
    isLoading.value = false
  }
}
function openCreateQuadroModal() {
  showQuadroModal.value = true
}

function closeQuadroModal() {
  showQuadroModal.value = false
  novoQuadroForm.value = {
    nome: '',
    tipo: 'leads',
    descricao: ''
  }
}

const quadrosFiltrados = computed(() => {
  if (!props.todosQuadros) return []
  return props.todosQuadros.filter(q => q.id !== quadroAtual.value.id)
})

const colunaContatoLeads = ref<LeadListagem[]>([])

async function handleContactDrop(event: any) {
  console.log('Drop na coluna de contato:', event)
  
  try {
    // Pega o lead movido de diferentes formas possíveis
    let movedLead = null
    
    movedLead = event.data;

    console.log(movedLead)

    
    console.log('Lead a ser convertido:', movedLead)
    
    // Verifica se já é um contato
    if (movedLead.tipo === 'contato') {
      toast.show('Este item já é um contato', 'warning')
      colunaContatoLeads.value = []
      return
    }
    
    // Confirma com o usuário
    if (!confirm(`Deseja converter "${movedLead.nome_completo || 'Lead'}" para contato?`)) {
      colunaContatoLeads.value = []
      return
    }
    
    isUpdating.value = true
    
    // Faz a chamada para converter lead em contato
    const xsrfToken = getCookie('XSRF-TOKEN')
    const response = await fetch(`/admin/corretor/leads/${movedLead.id}/converter`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': xsrfToken,
      },
      body: JSON.stringify({
        converter: true
      })
    })
    
    if (response.ok) {
      const data = await response.json()
      
      // 🔥 Remove o lead de todas as colunas (agora é contato)
      let leadRemovido = false
      Object.keys(localColumnsData.value).forEach(colunaId => {
        const index = localColumnsData.value[colunaId].findIndex(l => l.id === movedLead.id)
        if (index !== -1) {
          localColumnsData.value[colunaId].splice(index, 1)
          leadRemovido = true
        }
      })
      
      // Limpa a coluna de contato
      colunaContatoLeads.value = []
      
      // Força atualização se removeu algo
      if (leadRemovido) {
        localColumnsData.value = { ...localColumnsData.value }
      }
      
      toast.show(`${movedLead.nome_completo || 'Lead'} convertido para contato com sucesso!`, 'success')
      
      // Se o quadro atual for de contatos, recarrega os dados
      if (quadroAtual.value?.tipo === 'contatos') {
        await carregarDadosQuadro(quadroAtual.value.id)
      }
      
    } else {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Erro ao converter lead')
    }
  } catch (error) {
    console.error('Erro ao converter lead:', error)
    toast.show(error.message || 'Erro ao converter lead para contato', 'error')
  } finally {
    // Limpa a coluna de contato e finaliza
    colunaContatoLeads.value = []
    isUpdating.value = false
  }
}
onMounted(() => {
  const quadroId = props.quadro?.id || 1 // Ajuste conforme necessário
  carregarDadosQuadro(quadroId)
})

// Watch para debug
watch(localColumnsData, (newVal) => {
  console.log('localColumnsData atualizado:', Object.keys(newVal).map(key => 
    `${key}: ${newVal[key]?.length} leads`
  ))
}, { deep: true })


function updateColumnForm(field: string, value: any) {
  if (field.includes('.')) {
    // Para campos aninhados
    const [parent, child] = field.split('.')
    columnForm.value = {
      ...columnForm.value,
      [parent]: {
        ...(columnForm.value[parent as keyof typeof columnForm.value] as object || {}),
        [child]: value
      }
    }
  } else {
    // Para campos simples
    columnForm.value = {
      ...columnForm.value,
      [field]: value
    }
  }
}

function atualizarColunas(novasColunas) {
  colunasLocais.value = novasColunas
}

// Função para determinar se usa texto claro ou escuro baseado na cor de fundo
const ajustarContraste = (hexColor) => {
  // Remove o # se existir
  const hex = hexColor.replace('#', '');
  
  // Converte para RGB
  const r = parseInt(hex.substring(0, 2), 16);
  const g = parseInt(hex.substring(2, 4), 16);
  const b = parseInt(hex.substring(4, 6), 16);
  
  // Calcula luminância (fórmula WCAG)
  const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
  
  // Retorna texto escuro para fundos claros, texto claro para fundos escuros
  return luminance > 0.7 ? '#1f2937' : '#f9fafb'; // gray-800 ou gray-50
}
</script>

<template>
  <Toast/>
  <div class="kanban-container">
    <!-- Loading overlay -->
    <div v-if="isLoading" class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 flex items-center justify-center z-10">
      <RefreshCw class="w-8 h-8 animate-spin text-primary-600" />
    </div>

    <!-- Cabeçalho do Kanban com opções -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-2">
         <DropdownButtonKanban :label="quadroAtual.nome" :label2="quadroAtual?.total_leads" class="relative">

        <!-- Conteúdo do Dropdown - Lista de Quadros -->
        <div class="w-72 py-1">
          <!-- Lista de Quadros -->
          <div 
            v-for="quadroItem in quadrosFiltrados" 
            :key="quadroItem.id"
            @click="selectQuadro(quadroItem)"
            class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition flex items-center justify-between"
            :class="{ 'bg-primary-50 dark:bg-primary-900/20': quadroAtual?.id === quadroItem.id }"
          >
            <div class="flex items-center gap-2 min-w-0">
              <!-- Ícone do quadro baseado no tipo -->
              <div 
                class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0"
                :class="quadroItem.tipo === 'leads' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600'"
              >
                <component 
                  :is="quadroItem.tipo === 'leads' ? 'Users' : 'UserCheck'"
                  class="w-3 h-3"
                />
              </div>
              
              <!-- Nome e tipo do quadro -->
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ quadroItem.nome }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ quadroItem.tipo === 'leads' ? 'Funil de Leads' : 'Processo de Contatos' }}
                </p>
              </div>
            </div>
            
            <!-- Badge com contagem -->
            <div class="flex items-center gap-2 flex-shrink-0 ml-2">
              <span class="text-xs font-medium px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-full">
                {{ quadroItem.total_leads || 0 }} leads
              </span>
              
            </div>
          </div>
          
          <!-- Rodapé do Dropdown - Criar novo quadro -->
          <div class="px-3 py-2 border-t border-gray-100 dark:border-gray-800 mt-1">
            <button 
              @click="openCreateQuadroModal"
              class="flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 w-full"
            >
              <Plus class="w-4 h-4" />
              <span>Criar novo quadro</span>
            </button>
          </div>
        </div>
      </DropdownButtonKanban>
      </div>
      
      <!-- Botões de ação -->
      <div class="flex items-center gap-2">
        <!-- Botão para criar nova coluna -->
        <button 
          @click="openCreateColumnModal"
          class="p-2 text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/30 transition flex items-center gap-1"
          title="Criar nova coluna"
        >
          <Plus class="w-4 h-4" />
          <span class="text-sm hidden sm:inline">Nova Coluna</span>
        </button>
        
        <button 
          @click="openConfigColumnModal"
          class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
          <Settings class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Colunas do Kanban -->
    <div class="flex gap-4 overflow-x-auto pb-4 min-h-[70vh]">
    <div 
      v-for="coluna in colunasLocais" 
      :key="coluna.id"
      class="flex-shrink-0 w-80 rounded-lg border flex flex-col h-full"
      :style="{ 
        backgroundColor: coluna.cor ? coluna.cor + '20' : '#f9fafb',
        borderColor: coluna.cor || '#e5e7eb'
      }"
    >
        <!-- Cabeçalho da Coluna com menu de opções -->
        <div class="p-3 border-b flex justify-between items-center" :class="coluna.cor ? `border-${coluna.cor}-200` : 'border-gray-200'">
          <div class="flex items-center gap-2">
            <h3 
              class="font-semibold text-gray-700 dark:text-gray-300 p-1 rounded-md" 
              :style="{ 
                backgroundColor: coluna.cor ? coluna.cor : '#f3f4f6', // 25 = ~15% opacidade
                color: coluna.cor ? ajustarContraste(coluna.cor) : '#374151'
              }"
            > 
              {{ coluna.status?.nome }}
            </h3>
            <span 
              class="text-xs px-2 py-0.5 rounded-full bg-white dark:bg-gray-800 shadow-sm text-gray-600 dark:text-gray-400"
            >
              {{ columnsData[coluna.id]?.length || 0 }}
            </span>
          </div>

          
          <!-- Menu da coluna -->
          <div class="flex items-center gap-1">
            <button 
              @click.stop="openEditColumnModal(coluna)"
              class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded hover:bg-gray-100 dark:hover:bg-gray-800"
              title="Editar coluna"
            >
              <Settings class="w-3 h-3" />
            </button>
            <button 
              @click.stop="confirmDeleteColumn(coluna)"
              class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded hover:bg-gray-100 dark:hover:bg-gray-800"
              title="Excluir coluna"
              v-if="colunas.length > 1"
            >
              <Trash class="w-3 h-3" />
            </button>
          </div>
        </div>

        <VueDraggable
          v-model="localColumnsData[coluna.id]"
          group="leads"
          :animation="250"
          :scroll="true"
          ghost-class="kanban-ghost"
          drag-class="kanban-drag"
          chosen-class="kanban-chosen"
          :data-status="coluna.id"
          @end="handleDragEnd"
          class="flex-1 p-2 space-y-2 overflow-y-auto min-h-[200px]"
          :class="{ 'opacity-50': isLoading }"
          :key="'draggable-' + coluna.id + '-' + (localColumnsData[coluna.id]?.length || 0) + '-' + Date.now()"
        >
          <div
            v-for="lead in columnsData[coluna.id]"
            :key="lead.id"
            class="kanban-card bg-white dark:bg-gray-900 rounded-lg shadow-sm hover:shadow-md transition-all border border-gray-200 dark:border-gray-700 group cursor-pointer"
            @click="handleOpenDetails(lead)"
          >
            <!-- Handle para arrastar -->
            <div class="flex items-start p-3">
              <div 
                class="flex-shrink-0 mr-2 cursor-move opacity-30 group-hover:opacity-100 transition-opacity"
                @click.stop
              >
                <GripVertical class="w-4 h-4 text-gray-500" />
              </div>
              
              <!-- Conteúdo do Card -->
              <div class="flex-1 min-w-0">
                <!-- Header: Nome e Avatar -->
                <div class="flex items-start justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-700 dark:text-primary-300 text-xs font-semibold">
                      {{ getInitials(lead.nome_completo) }}
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="font-medium text-gray-900 dark:text-white text-sm truncate" :title="lead.nome_completo">
                        {{ lead.nome_completo }}
                      </h4>
                      <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ lead.email || 'Sem email' }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Contato principal -->
                <div v-if="getPrincipalContact(lead)" class="mb-2">
                  <div class="flex items-center gap-1 text-xs">
                    <span class="text-gray-600 dark:text-gray-300">
                      {{ getPrincipalContact(lead)?.numero }}
                    </span>
                    <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded-full text-gray-600 dark:text-gray-300 text-[10px] uppercase">
                      {{ getPrincipalContact(lead)?.tipo }}
                    </span>
                  </div>
                </div>

                <!-- Localização (se tiver) -->
                <div v-if="lead.cidade || lead.estado" class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                  📍 {{ lead.cidade || '' }}{{ lead.estado ? `, ${lead.estado}` : '' }}
                </div>

                <!-- Valor do imóvel (se tiver) -->
                <div v-if="lead.valor_desejado" class="mb-2 text-xs font-medium text-gray-700 dark:text-gray-300">
                  💰 {{ formatCurrency(lead.valor_desejado) }}
                </div>

                <!-- Footer: ID e Ações -->
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                  <span class="text-xs font-mono text-gray-400">
                    #{{ lead.id?.toString().padStart(4, '0') }}
                  </span>
                  <div class="flex items-center gap-1">
                    <!-- Badge de Rodízio -->
                    <span 
                      v-if="lead.adicionar_rodizio"
                      class="text-[10px] px-1.5 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full"
                    >
                      Rodízio
                    </span>
                    <!-- Botão Ver Detalhes -->
                    <button 
                      @click.stop="handleOpenDetails(lead)"
                      class="p-1 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                      title="Ver detalhes"
                    >
                      <Eye class="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </VueDraggable>
        
        <!-- Rodapé da Coluna -->
        <div class="p-2 border-t" :class="coluna.cor ? `border-${coluna.cor}-200` : 'border-gray-200'">
          <div class="text-xs text-center text-gray-500 dark:text-gray-700">
            {{ columnsData[coluna.id]?.length || 0 }} {{ columnsData[coluna.id]?.length === 1 ? 'lead' : 'leads' }}
          </div>
        </div>
    </div>

    
     
    <div v-if="quadroAtual.tipo == 'leads'" class="flex-shrink-0 w-80 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700 flex flex-col h-full bg-gray-50 dark:bg-purple-900/20">
      <!-- Cabeçalho da Coluna de Contato -->
      <div class="p-4 border-b border-gray-200 dark:border-gray-800 text-center">
        <div class="flex items-center justify-center gap-2 mb-2">
          <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center">
            <UserPlus class="w-5 h-5 text-white" />
          </div>
        </div>
        <h3 class="font-bold text-gray-700 dark:text-gray-300 text-lg">Gerar Contato</h3>
        <p class="text-xs text-gray-600 dark:text-gray-700mt-1">
          Arraste um lead aqui para convertê-lo em contato
        </p>
      </div>
      
      <!-- Área de Drop especial -->
      <VueDraggable
        v-model="colunaContatoLeads"
        group="leads"
        :animation="250"
        ghost-class="kanban-ghost-contact"
        drag-class="kanban-drag-contact"
        chosen-class="kanban-chosen-contact"
        :data-status="'contato'"
        :data-coluna-especial="true"
        @change="handleContactDrop"
        class="flex-1 p-4 min-h-[200px] flex items-center justify-center"
        :class="{ 'opacity-50': isUpdating }"
      >
        <!-- Placeholder quando vazio -->
        <div v-if="colunaContatoLeads.length === 0" class="text-center">
          <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-gray-900/30 flex items-center justify-center">
            <ArrowDown class="w-8 h-8 text-gray-500" />
          </div>
          <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">
            Solte aqui para converter
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
            O lead será transformado em contato
          </p>
        </div>
        
        <!-- Cards temporários (só aparecem durante o drag) -->
        <div v-for="lead in colunaContatoLeads" :key="lead.id" class="hidden">
          <!-- Invisível, só para manter a referência -->
        </div>
      </VueDraggable>
      
      <!-- Rodapé informativo -->
      <div class="p-3 border-t border-gray-200 dark:border-gray-800 text-center">
        <span class="text-xs text-gray-600 dark:text-gray-400">
          ⚡ Conversão instantânea
        </span>
      </div>
    </div>
    </div>
      <KanbanColunaModal 
        :showColumnModal="showColumnModal"
        :columnModalMode="columnModalMode"
        :columnForm="columnForm"
        :onClose="closeColumnModal"
        :onSave="saveColumn"
        :onUpdateForm="updateColumnForm"
      />

      <KanbanConfigColunaModal
      :showConfigColumnModal="showConfigColumnModal"
      :columnModalMode="columnModalMode"
      :colunasLocais="colunasLocais"
      :quadroAtual="quadroAtual"
      :toast="toast"
      :onSave="saveOrdemColumn"
      :onClose= "closeConfigColumnModal"
      @update:colunas-locais="atualizarColunas"
    />
  </div>
  
  <LeadDetailsModal
    :open="showDetailsModal"
    :lead="selectedLead"
    @close="closeDetailsModal"
    @updated="handleLeadUpdated"
  />
</template>
<style scoped>
/* Seus estilos existentes permanecem iguais */
</style>