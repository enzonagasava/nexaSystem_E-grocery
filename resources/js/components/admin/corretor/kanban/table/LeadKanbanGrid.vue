<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { VueDraggable } from 'vue-draggable-plus'
import { GripVertical, Eye, RefreshCw } from 'lucide-vue-next'
import type { LeadListagem } from '@/types/forms/lead-form'
import { useToastStore } from '@/stores/toast';
import Toast from '@/components/ui/toast/Toast.vue';
import LeadDetailsModal from '@/components/admin/corretor/leads/modal/LeadDetailsModal.vue';


const props = defineProps<{
  leads: LeadListagem[]
}>()

const selectedLead = ref<LeadListagem | null>(null);
const showDetailsModal = ref(false);
const isUpdating = ref(false)
const toast = useToastStore();

const emit = defineEmits<{
  (e: 'open-details', lead: LeadListagem): void
  (e: 'update-status', lead: LeadListagem, newStatus: string): void
}>()

// Estado local para os leads agrupados (mutável para o drag and drop)
const localColumnsData = ref<Record<string, LeadListagem[]>>({})
const isLoading = ref(false)

// Definição das colunas do Kanban
const statusColumns = [
  { value: 'lead', label: 'Lead', color: 'green', bgColor: 'bg-green-50 dark:bg-gray-800/50', borderColor: 'border-green-200 dark:border-gray-700', textColor: 'text-green-700 dark:text-gray-300' },
  { value: 'simulacao', label: 'Simulação do Financiamento', color: 'blue', bgColor: 'bg-gray-50 dark:bg-gray-800/50', borderColor: 'border-gray-200 dark:border-gray-700', textColor: 'text-gray-700 dark:text-gray-300' },
  { value: 'visita', label: 'Visita', color: 'purple', bgColor: 'bg-purple-50 dark:bg-gray-800/50', borderColor: 'border-purple-200 dark:border-gray-700', textColor: 'text-purple-700 dark:text-gray-300' },
  { value: 'contato', label: 'Negociação', color: 'red', bgColor: 'bg-red-50 dark:bg-gray-800/50', borderColor: 'border-red-200 dark:border-gray-700', textColor: 'text-red-700 dark:text-gray-300' }
]

// Função para reagrupar os leads
function regroupLeads(leads: LeadListagem[]) {
  const groups: Record<string, LeadListagem[]> = {}
  
  // Inicializa todas as colunas
  statusColumns.forEach(col => {
    groups[col.value] = []
  })

  // Distribui os leads
  leads.forEach(lead => {
    const status = lead.status || 'lead' 
    if (groups[status]) {
      groups[status].push(lead)
    }
  })
  
  return groups
}

// Inicializa o estado local quando os props mudam
watch(() => props.leads, (newLeads) => {
  if (newLeads && newLeads.length) {
    localColumnsData.value = regroupLeads(newLeads)
  } else if (newLeads && !newLeads.length) {
    // Se não houver leads, inicializa colunas vazias
    const emptyGroups: Record<string, LeadListagem[]> = {}
    statusColumns.forEach(col => {
      emptyGroups[col.value] = []
    })
    localColumnsData.value = emptyGroups
  }
}, { immediate: true, deep: true })

// Computed para expor os dados agrupados (para o template)
const columnsData = computed(() => localColumnsData.value)

async function handleDragEnd(event: any) {
  try {
    const { newIndex, from, to } = event

    // Se moveu para outra coluna
    if (from.dataset.status !== to.dataset.status) {

      // Pega o lead que foi movido
      const movedLead = localColumnsData.value[to.dataset.status]?.[newIndex]

      if (movedLead) {

        // Salva o status anterior para caso de erro
        const oldStatus = movedLead.status
        
        // Atualiza o status do lead localmente (otimista)
        movedLead.status = to.dataset.status
        
        // Faz a chamada para o backend
        isUpdating.value = true
        
        try {
          const url = `/admin/corretor/kanban/${movedLead.id}/status`
          const xsrfToken = getCookie('XSRF-TOKEN');

          const headers: Record<string, string> = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          }

          if (xsrfToken) {
            headers['X-XSRF-TOKEN'] = xsrfToken
          }

          const fetchOptions: RequestInit = {
            method: 'PUT',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
              'X-XSRF-TOKEN': xsrfToken,
            },
            body: JSON.stringify({ status: to.dataset.status }) // Envia apenas o status
          };
          
          const response = await fetch(url, fetchOptions);
          
          if (response.ok) {
            toast.show('Status atualizado com sucesso!', 'success')
            // Emite evento para o componente pai saber da mudança
            emit('update-status', movedLead, to.dataset.status)
          } else {
            throw new Error('Erro ao atualizar status')
          }
                    
        } catch (error) {
          // Se der erro, reverte a mudança local
          movedLead.status = oldStatus
          
          console.error('Erro ao atualizar status:', error)
          toast.show('Erro ao atualizar status. Tente novamente.', 'error')
          
          // Reverte o drag (recria os grupos)
          localColumnsData.value = regroupLeads(props.leads)
        }
      }
    }
  } catch (error) {
    console.error('Erro ao mover lead:', error)
    toast.show('Erro ao mover lead', 'error')
  } finally {
    isUpdating.value = false
  }
}

// Formatar contato principal
function getPrincipalContact(lead: LeadListagem) {
  if (!lead.contatos || !lead.contatos.length) return null
  
  const principal = lead.contatos.find(c => c.principal)
  return principal || lead.contatos[0]
}

// Iniciais para avatar
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

// Formatar valor (se tiver)
function formatCurrency(value?: number | string) {
  if (!value) return null
  
  const numValue = typeof value === 'string' ? parseFloat(value) : value
  
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(numValue)
}

// Handlers
function handleOpenDetails(lead: LeadListagem) {
  console.log('abre modal')
  selectedLead.value = lead;
  showDetailsModal.value = true;
}

function closeDetailsModal() {
  showDetailsModal.value = false;
  selectedLead.value = null;
}

function handleLeadUpdated(updatedLead: LeadListagem) {
  console.log('Lead atualizado no Kanban:', updatedLead);
  
  // Encontra a coluna atual e o índice
  let sourceColumn = '';
  let sourceIndex = -1;
  
  // Cria um mapa para busca mais eficiente (opcional para muitos leads)
  for (const [status, leads] of Object.entries(localColumnsData.value)) {
    const index = leads.findIndex(l => l.id === updatedLead.id);
    if (index !== -1) {
      sourceColumn = status;
      sourceIndex = index;
      break;
    }
  }
  
  if (sourceColumn === '' || sourceIndex === -1) {
    console.warn('Lead não encontrado nas colunas');
    return;
  }
  
  const oldStatus = localColumnsData.value[sourceColumn][sourceIndex].status;
  const newStatus = updatedLead.status || oldStatus;
  
  // Prepara o lead atualizado
  const updatedLeadData = {
    ...localColumnsData.value[sourceColumn][sourceIndex],
    ...updatedLead,
    contatos: updatedLead.contatos ?? localColumnsData.value[sourceColumn][sourceIndex].contatos,
    corretor: updatedLead.corretor ?? localColumnsData.value[sourceColumn][sourceIndex].corretor,
  };
  
  // Cria novo estado baseado na mudança
  const newColumnsData = { ...localColumnsData.value };
  
  if (oldStatus !== newStatus) {
    // MOVER PARA OUTRA COLUNA
    // Remove da coluna de origem (criando novo array)
    newColumnsData[sourceColumn] = [
      ...localColumnsData.value[sourceColumn].slice(0, sourceIndex),
      ...localColumnsData.value[sourceColumn].slice(sourceIndex + 1)
    ];
    
    // Adiciona na coluna de destino (criando novo array)
    newColumnsData[newStatus] = [
      ...(localColumnsData.value[newStatus] || []),
      updatedLeadData
    ];
  } else {
    // MESMA COLUNA - apenas atualiza o item
    newColumnsData[sourceColumn] = [
      ...localColumnsData.value[sourceColumn].slice(0, sourceIndex),
      updatedLeadData,
      ...localColumnsData.value[sourceColumn].slice(sourceIndex + 1)
    ];
  }
  
  // Atualiza o estado - isso força o VueDraggable a re-renderizar
  // porque todos os arrays são novos
  localColumnsData.value = newColumnsData;
  
  toast.show('Lead atualizado com sucesso!', 'success');
  
  // Atualiza o lead selecionado se necessário
  if (selectedLead.value?.id === updatedLead.id) {
    selectedLead.value = updatedLeadData;
  }
}


// 👈 FUNÇÃO AUXILIAR PARA ATUALIZAR VIA PROPS (caso necessário)
function refreshFromProps() {
  if (props.leads) {
    localColumnsData.value = regroupLeads(props.leads);
  }
}

// Watch para mudanças nas props (já existe, mas vamos garantir)
watch(() => props.leads, (newLeads) => {
  if (newLeads) {
    localColumnsData.value = regroupLeads(newLeads);
  }
}, { deep: true });

</script>

<template>
  <Toast/>
  <div class="kanban-container">
    <!-- Loading overlay (opcional) -->
    <div v-if="isLoading" class="absolute inset-0 bg-white/50 dark:bg-gray-900/50 flex items-center justify-center z-10">
      <RefreshCw class="w-8 h-8 animate-spin text-primary-600" />
    </div>

    <!-- Colunas do Kanban -->
    <div class="flex gap-4 overflow-x-auto pb-4 min-h-[70vh]">
      <div 
        v-for="column in statusColumns" 
        :key="column.value"
        class="flex-shrink-0 w-80 rounded-lg border flex flex-col h-full"
        :class="[column.bgColor, column.borderColor]"
      >
        <!-- Cabeçalho da Coluna -->
        <div class="p-3 border-b" :class="column.borderColor">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span 
                class="w-2.5 h-2.5 rounded-full" 
                :class="`bg-${column.color}-500`"
              ></span>
              <h3 class="font-semibold" :class="column.textColor">
                {{ column.label }}
              </h3>
              <span 
                class="text-xs px-2 py-0.5 rounded-full bg-white dark:bg-gray-800 shadow-sm"
                :class="column.textColor"
              >
                {{ columnsData[column.value]?.length || 0 }}
              </span>
            </div>
          </div>
        </div>

        <!-- Área de Cards com Drag & Drop -->
        <VueDraggable
          v-model="localColumnsData[column.value]"
          group="leads"
          :animation="250"
          :scroll="true"
          ghost-class="kanban-ghost"
          drag-class="kanban-drag"
          chosen-class="kanban-chosen"
          :data-status="column.value"
          @end="handleDragEnd"
          class="flex-1 p-2 space-y-2 overflow-y-auto min-h-[200px]"
          :class="{ 'opacity-50': isLoading }"
          >
          <div
           @click="handleOpenDetails(lead)"
            v-for="lead in columnsData[column.value]"
            :key="lead.id"
            class="kanban-card bg-white dark:bg-gray-900 rounded-lg shadow-sm hover:shadow-md transition-all border border-gray-200 dark:border-gray-700 group cursor-pointer"
          >
            <!-- Handle para arrastar -->
            <div class="flex items-start p-3">
              <div class="flex-shrink-0 mr-2 cursor-move opacity-30 group-hover:opacity-100 transition-opacity">
                <GripVertical class="w-4 h-4 text-gray-500" />
              </div>
              
              <!-- Conteúdo do Card -->
              <div class="flex-1 min-w-0 w-100">
                <!-- Header: Nome e Avatar -->
                <div class="flex items-start justify-between mb-2">
                  <div class="flex items-center gap-2 w-100">
                    <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-700 dark:text-primary-300 text-xs font-semibold">
                      {{ getInitials(lead.nome_completo) }}
                    </div>
                    <div class="justify-between flex w-[100%]">
                      <div class="flex-1 min-w-0">
                        <h4 class="font-medium text-gray-900 dark:text-white text-sm truncate" :title="lead.nome_completo">
                          {{ lead.nome_completo }}
                        </h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                          {{ lead.email || 'Sem email' }}
                        </p>
                      </div>
                      <span class="text-xs font-mono text-gray-400">
                        #{{ lead.id?.toString().padStart(4, '0') }}
                      </span>
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

                <!-- Footer: ID e Ações -->
                <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                  <div class="flex items-center gap-1">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </VueDraggable>
        
        <!-- Rodapé da Coluna (opcional) -->
        <div class="p-2 border-t" :class="column.borderColor">
          <div class="text-xs text-center" :class="column.textColor">
            {{ columnsData[column.value]?.length || 0 }} {{ columnsData[column.value]?.length === 1 ? 'lead' : 'leads' }}
          </div>
        </div>
      </div>
    </div>
  </div>

    <LeadDetailsModal
      :open="showDetailsModal"
      :lead="selectedLead"
      @close="closeDetailsModal"
      @updated="handleLeadUpdated"
    />
</template>

<style scoped>
/* Animações do Kanban */
.kanban-fade-move,
.kanban-fade-enter-active,
.kanban-fade-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.kanban-fade-enter-from,
.kanban-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}

.kanban-fade-leave-active {
  position: absolute;
  width: calc(100% - 1rem);
}

/* Estilos para drag and drop */
.kanban-ghost {
  opacity: 0.4;
  background: #e2e8f0 !important;
  border: 2px dashed #94a3b8 !important;
  box-shadow: none !important;
  transform: rotate(0deg) !important;
}

.dark .kanban-ghost {
  background: #1e293b !important;
  border-color: #475569 !important;
}

.kanban-drag {
  cursor: grabbing !important;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
  transform: scale(1.02) rotate(0.5deg) !important;
  transition: all 0.2s !important;
}

.kanban-chosen {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
}

/* Scrollbar personalizada */
.kanban-container {
  width: 100%;
  position: relative;
}

.overflow-x-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f1f5f9;
  overflow-y: hidden;
  overflow-x: auto;
}

.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

.dark .overflow-x-auto::-webkit-scrollbar-track {
  background: #1e293b;
}

.dark .overflow-x-auto::-webkit-scrollbar-thumb {
  background: #475569;
}

.dark .overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}

/* Altura mínima para as colunas */

</style>  