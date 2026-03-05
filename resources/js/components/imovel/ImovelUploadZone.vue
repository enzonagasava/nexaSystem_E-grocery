<script setup lang="ts">
/**
 * Componente de upload com drag-drop para diferentes tipos de mídia
 * Suporta: imagens, vídeos, PDFs, plantas e autorizações
 * Integrado com validação de tipo e tamanho
 */
import { ref, computed } from 'vue'
import { Cloud, Check, AlertCircle } from 'lucide-vue-next'

interface Props {
  mediaType: 'image' | 'video' | 'pdf' | 'planta' | 'autorizacao'
  maxSize?: number // bytes
  multiple?: boolean
  accept?: string
  label?: string
}

interface UploadEvent {
  files: File[]
  type: Props['mediaType']
}

const props = withDefaults(defineProps<Props>(), {
  maxSize: 5 * 1024 * 1024, // 5MB padrão
  multiple: false,
  accept: '*/*'
})

const emit = defineEmits<{
  (e: 'files-selected', data: UploadEvent): void
}>()

// Estado
const isDragging = ref(false)
const selectedFiles = ref<File[]>([])
const errors = ref<string[]>([])
const isProcessing = ref(false)

// Config de tipos
const typeConfig = {
  image: {
    label: 'Imagem',
    accept: 'image/png,image/jpeg,image/jpg,image/webp',
    maxSize: 5 * 1024 * 1024,
    icon: ''
  },
  video: {
    label: 'Vídeo',
    accept: 'video/mp4,video/webm,video/quicktime,video/x-msvideo',
    maxSize: 1 * 1024 * 1024 * 1024, // 1GB
    icon: ''
  },
  pdf: {
    label: 'PDF',
    accept: 'application/pdf',
    maxSize: 10 * 1024 * 1024,
    icon: ''
  },
  planta: {
    label: 'Planta',
    accept: 'application/pdf,image/png,image/jpeg,image/jpg',
    maxSize: 10 * 1024 * 1024,
    icon: ''
  },
  autorizacao: {
    label: 'Autorização',
    accept: 'application/pdf,image/png,image/jpeg,image/jpg',
    maxSize: 5 * 1024 * 1024,
    icon: ''
  }
}

const currentConfig = computed(() => typeConfig[props.mediaType])
const displayLabel = computed(() => props.label || currentConfig.value.label)

// Métodos
function validateFiles(files: FileList | File[]): boolean {
  errors.value = []
  const fileArray = Array.from(files)
  
  if (!props.multiple && fileArray.length > 1) {
    errors.value.push(`Apenas um arquivo é permitido para ${displayLabel.value.toLowerCase()}`)
    return false
  }

  for (const file of fileArray) {
    // Validar tamanho usando currentConfig.value.maxSize (respeita limites por tipo)
    if (file.size > currentConfig.value.maxSize) {
      const sizeMB = (currentConfig.value.maxSize / (1024 * 1024)).toFixed(0)
      errors.value.push(`${file.name}: arquivo muito grande (máx ${sizeMB}MB)`)
      continue
    }

    // Validar tipo
    const config = currentConfig.value
    const validTypes = config.accept.split(',').map(t => t.trim())
    const isValidType = validTypes.some(type => {
      if (type.endsWith('/*')) {
        return file.type.startsWith(type.slice(0, -2))
      }
      return file.type === type || file.name.toLowerCase().endsWith(type.split('/')[1])
    })

    if (!isValidType) {
      errors.value.push(`${file.name}: tipo de arquivo não permitido`)
    }
  }

  return errors.value.length === 0
}

function handleDragOver(e: DragEvent) {
  e.preventDefault()
  isDragging.value = true
}

function handleDragLeave() {
  isDragging.value = false
}

function handleDrop(e: DragEvent) {
  e.preventDefault()
  isDragging.value = false
  
  if (e.dataTransfer?.files) {
    handleFiles(e.dataTransfer.files)
  }
}

function handleInputChange(e: Event) {
  const input = e.target as HTMLInputElement
  if (input.files) {
    handleFiles(input.files)
  }
}

function handleFiles(files: FileList) {
  if (!validateFiles(files)) return
  
  selectedFiles.value = Array.from(files)
  isProcessing.value = true
  
  setTimeout(() => {
    emit('files-selected', {
      files: selectedFiles.value,
      type: props.mediaType
    })
    selectedFiles.value = []
    isProcessing.value = false
  }, 500)
}

function clearErrors() {
  errors.value = []
}
</script>

<template>
  <div class="space-y-2">
    <!-- Upload Zone -->
    <div
      @dragover="handleDragOver"
      @dragleave="handleDragLeave"
      @drop="handleDrop"
      :class="[
        'relative border-2 border-dashed rounded-lg p-6 transition-colors',
        isDragging ? 'border-primary bg-primary/5' : 'border-muted bg-muted/20',
        isProcessing && 'opacity-50 pointer-events-none'
      ]"
    >
      <input
        type="file"
        :accept="currentConfig.accept"
        :multiple="multiple"
        @change="handleInputChange"
        class="sr-only"
        :id="`upload-${mediaType}`"
      />
      
      <label
        :for="`upload-${mediaType}`"
        class="flex flex-col items-center justify-center cursor-pointer"
      >
        <Cloud v-if="!isProcessing" class="w-8 h-8 text-muted-foreground mb-2" />
        <div v-else class="w-8 h-8 border-2 border-primary border-t-transparent rounded-full animate-spin mb-2" />
        
        <p class="text-sm font-medium text-text-primary">
          {{ isProcessing ? 'Processando...' : `Arraste ${displayLabel.toLowerCase()} aqui` }}
        </p>
        <p class="text-xs text-muted-foreground mt-1">
          ou clique para selecionar
        </p>
      </label>
    </div>

    <!-- Arquivos selecionados -->
    <div v-if="selectedFiles.length > 0" class="space-y-2">
      <div v-for="(file, idx) in selectedFiles" :key="idx" class="flex items-center gap-2 p-2 bg-green-50 dark:bg-green-900/20 rounded border border-green-200 dark:border-green-800">
        <Check class="w-4 h-4 text-green-600 flex-shrink-0" />
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium truncate">{{ file.name }}</p>
          <p class="text-xs text-muted-foreground">{{ (file.size / 1024).toFixed(0) }} KB</p>
        </div>
      </div>
    </div>

    <!-- Erros -->
    <div v-if="errors.length > 0" class="space-y-2">
      <div v-for="(error, idx) in errors" :key="idx" class="flex items-start gap-2 p-2 bg-destructive/10 rounded border border-destructive/20">
        <AlertCircle class="w-4 h-4 text-destructive flex-shrink-0 mt-0.5" />
        <p class="text-sm text-destructive">{{ error }}</p>
      </div>
      <button
        @click="clearErrors"
        type="button"
        class="text-xs text-muted-foreground hover:text-text-primary underline"
      >
        Limpar erros
      </button>
    </div>

    <!-- Info tamanho máximo -->
    <p class="text-xs text-muted-foreground">
      Tamanho máximo: {{ (currentConfig.maxSize / (1024 * 1024)).toFixed(0) }} MB
      {{ multiple ? '• Múltiplos arquivos permitidos' : '• Um arquivo por vez' }}
    </p>
  </div>
</template>
