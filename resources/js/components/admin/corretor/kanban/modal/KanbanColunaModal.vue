<script setup lang="ts">

const props = defineProps<{
  showColumnModal: boolean,
  columnModalMode: 'create' | 'edit',
  columnForm: {
    id: number | null,
    status: {
      id: number
      nome: string
    }
    cor: string,
    descricao: string,
  },
  onClose: () => void,
  onSave: () => void,
  onUpdateForm: (field: string, value: any) => void
}>()
</script>

<template>
  <div v-if="props.showColumnModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="props.onClose">
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-md p-6">
      <h3 class="text-lg font-semibold mb-4">{{ props.columnModalMode === 'create' ? 'Nova Coluna' : 'Editar Coluna' }}</h3>
      
      <div class="space-y-4">
        <!-- Campo de título -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título da Coluna</label>
          <input 
            :value="props.columnForm.status.nome"
            @input="props.onUpdateForm('status.nome', ($event.target as HTMLInputElement).value)"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            placeholder="Ex: Em negociação"
          />
        </div>
        
        <!-- Seletor de Cor -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cor</label>
          <input 
            :value="props.columnForm.cor"
            @input="props.onUpdateForm('cor', ($event.target as HTMLInputElement).value)"
            type="color"
            class="w-full h-10 px-1 py-1 border border-gray-300 dark:border-gray-700 rounded-md"
          />
        </div>
        
        <!-- Descrição -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição (opcional)</label>
          <textarea 
            :value="props.columnForm.descricao"
            @input="props.onUpdateForm('descricao', ($event.target as HTMLTextAreaElement).value)"
            rows="2"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
            placeholder="Descrição da coluna"
          ></textarea>
        </div>
      </div>

      <div class="flex justify-end gap-2 mt-6">
        <button 
          @click="props.onClose"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition"
        >
          Cancelar
        </button>
        <button 
          @click="props.onSave"
          class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md transition"
        >
          {{ props.columnModalMode === 'create' ? 'Criar' : 'Salvar' }}
        </button>
      </div>
    </div>
  </div>
</template>