<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { VueDraggable } from 'vue-draggable-plus'

interface Column {
  id: number
  titulo: string
  descricao?: string
  cor: string
  ordem: number
  status?: {
    nome: string
  }
}

const props = defineProps<{
    showConfigColumnModal: boolean,
    colunasLocais: any[],
    onSave: () => void,
    onClose: () => void,
    columnModalMode: 'edit',
    quadroAtual: any,
    toast: any
}>()

const emit = defineEmits(['update:colunasLocais', 'save'])

// Criar uma cópia local do array que pode ser modificada
const colunas = ref<any[]>([])

// Estado para controlar qual item está sendo editado
const editingItemId = ref<number | null>(null)
const editingItemValue = ref('')

// Watch para quando o modal abre
watch(() => props.showConfigColumnModal, (novoValor) => {
  if (novoValor) {
    // Recarregar dados quando o modal abre
    colunas.value = JSON.parse(JSON.stringify(props.colunasLocais))
    editingItemId.value = null // Reset do estado de edição
  }
})

// Função executada quando a ordem muda (durante o arrasto)
const handleOrderChange = (event: any) => {
  console.log('Ordem alterada:', event)
  console.log('Nova ordem:', colunas.value)
  
  // Atualizar ordens quando a ordem muda
  atualizarOrdens()
}

// Atualizar os valores de ordem baseado na posição no array
const atualizarOrdens = () => {
  colunas.value.forEach((item: any, index: number) => {
    item.ordem = (index + 1) * 10
  })
  console.log('Ordens atualizadas:', colunas.value)
}

// Funções para adicionar nova coluna
const addColumnToBacklog = () => {
  const novaColuna = {
    id: null,
    tempId: Math.random(), 
    titulo: 'NOVO STATUS',
    cor: '#6B7280',
    status: {
      id: null,
      nome: 'NOVO STATUS'
    }
  }
  
  colunas.value.push(novaColuna)
  atualizarOrdens()
}

// Remover coluna
const removeColumn = (index: number) => {
  colunas.value.splice(index, 1)
  atualizarOrdens()
}

// Iniciar edição do nome
const startEdit = (item: any, index: number) => {
  editingItemId.value = item.tempId
  editingItemValue.value = item.status?.nome || item.titulo || ''
}

// Salvar edição do nome
const saveEdit = (item: any) => {
  if (editingItemValue.value.trim()) {
    if (item.status) {
      item.status.nome = editingItemValue.value
    } else {
      item.titulo = editingItemValue.value
    }
  }
  cancelEdit()
}

// Cancelar edição
const cancelEdit = () => {
  editingItemId.value = null
  editingItemValue.value = ''
}

// Editar coluna (agora apenas inicia a edição inline)
const editColumn = (item: any, index: number) => {
  startEdit(item, index)
}

// Tecla Enter para salvar, Esc para cancelar
const handleKeyDown = (event: KeyboardEvent, item: any) => {
  if (event.key === 'Enter') {
    saveEdit(item)
  } else if (event.key === 'Escape') {
    cancelEdit()
  }
}

async function handleSave() {
  try {
    console.log('Enviando dados:', colunas.value)

    const response = await fetch('/admin/corretor/kanban/colunas/ordem', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
      },
      body: JSON.stringify({
        colunas: colunas.value,
        kanban_quadro_id: props.quadroAtual.id
      })
    })
    
    if (response.ok) {
      const data = await response.json()
      props.toast.show('Ordem salva com sucesso!', 'success')
      
      // Atualiza as colunas locais com os dados retornados
      if (data.colunas) {
        emit('update:colunasLocais', data.colunas)
      }
      
      props.showConfigColumnModal = false
    } else {
      const errorData = await response.json()
      console.error('Erro da API:', errorData)
      props.toast.show('Erro ao salvar ordem', 'error')
    }
    
  } catch (error) {
    console.error('Erro ao salvar ordem:', error)
    props.toast.show('Erro ao salvar ordem', 'error')
  }
}
</script>

<template>
  <div 
    v-if="props.showConfigColumnModal" 
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
    @click.self="props.onClose"
  >
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-3xl p-6 max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
          Configurações das Colunas/Status
        </h3>
        <button 
          @click="props.onClose"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Colunas/Status Lists -->
      <div class="space-y-6">
        <!-- Backlog Section -->
        <div class="border dark:border-gray-700 rounded-lg p-4">
          <div class="flex items-center gap-2 mb-3">
            <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
            <h5 class="font-medium text-gray-900 dark:text-gray-100">Ordem</h5>
          </div>
          
          <div class="space-y-2">
            <VueDraggable
              :handle= "'.handle'"
              v-model="colunas"
              class="flex flex-col gap-2 w-full bg-gray-500/5 rounded"
              :scroll="true"
              :animation="150"
              @end="handleOrderChange"
              
            >

                <li
                  v-for="(item, index) in colunas"
                  :key="item.id"
                  class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-800 rounded group"
                >
                  <div class="handle cursor-move text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                  </div>
                  <div 
                    class="w-3 h-3 rounded-full"
                    :style="{ backgroundColor: item.cor }">
                  </div>
                  
                  <!-- Nome editável -->
                  <div class="flex-1">
                    <div v-if="editingItemId === item.tempId" class="flex items-center gap-2">
                      <input
                        v-model="editingItemValue"
                        @keydown="handleKeyDown($event, item)"
                        @blur="saveEdit(item)"
                        type="text"
                        class="w-full px-2 py-1 text-sm border border-primary-500 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-100"
                        :placeholder="item.status?.nome || item.titulo"
                        ref="inputRef"
                      />
                    </div>
                    <span v-else class="block">
                      {{ item.status?.nome || item.titulo }}
                    </span>
                  </div>

                  <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                    <button 
                      @click="editColumn(item, index)"
                      class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded"
                      :disabled="editingItemId !== null"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button 
                      @click="removeColumn(index)"
                      class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded text-red-500"
                      :disabled="editingItemId !== null"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </li>
            </VueDraggable>
            
            <!-- Add Status Button -->
            <button 
              @click="addColumnToBacklog"
              class="w-full flex items-center gap-2 p-2 text-sm text-gray-500 hover:text-primary-600 hover:bg-gray-50 dark:hover:bg-gray-800 rounded transition"
              :disabled="editingItemId !== null"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Add status
            </button>
          </div>
        </div>
      </div>

      <!-- Footer Links -->
      <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button class="text-sm text-primary-600 hover:text-primary-700">
          Saiba mais sobre os status
        </button>
        
        <div class="flex gap-3">
          <button 
            @click="props.onClose"
            class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded"
          >
            Cancelar
          </button>
          <button 
            @click="handleSave"
            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md"
          >
            Aplicar alterações
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.fade-move,
.fade-enter-active,
.fade-leave-active {
  transition: all 0.5s cubic-bezier(0.55, 0, 0.1, 1);
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: scaleY(0.01) translate(30px, 0);
}

.fade-leave-active {
  position: absolute;
}
.sort-target {
  padding: 0 1rem;
}
</style>