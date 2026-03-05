<script setup lang="ts">
/**
 * Gerenciador unificado de todas as mídias do imóvel
 * Permite visualizar, reordenar, deletar e adicionar imagens, vídeos, plantas e autorizações
 * Com sistema de buffer de arquivos pendentes antes do upload
 */
import { ref, computed } from 'vue'
import { ChevronDown, Trash2, GripVertical, Plus, X, Check, Play } from 'lucide-vue-next'
import ImovelUploadZone from './ImovelUploadZone.vue'
import Button from '@/components/ui/button/Button.vue'
import { useMediaUploadStore } from '@/stores/mediaUploadStore'
import { MEDIA_TYPE_MAP } from '@/constants/mediaTypes'

interface Media {
  id: number
  url?: string | null
  path?: string | null
  type?: string
  mime?: string
  original_name?: string
  size?: number
  ordem?: number
  uploadedAt?: string
  name?: string
}

interface Props {
  imovel: any
}

interface Emits {
  (e: 'media-added', data: { type: string; files: File[] }): void
  (e: 'media-deleted', data: { type: string; id: number }): void
  (e: 'media-reordered', data: { type: string; ordem: number[]; ids: number[] }): void
  (e: 'media-save'): void
  (e: 'media-cancel'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Store
const mediaStore = useMediaUploadStore()

// Estado
const expandedSections = ref<Record<string, boolean>>({
  imagens: true,
  videos: false,
  plantas: false,
  autorizacoes: false
})
const uploadingType = ref<string | null>(null)
const uploadProgress = ref(0)
const draggedItemId = ref<string | null>(null)
const dragOverType = ref<string | null>(null)

// Computed
const imagens = computed(() => props.imovel?.imagens || [])
const videos = computed(() => props.imovel?.videos || [])
const plantas = computed(() => props.imovel?.plantas || [])
const autorizacoes = computed(() => props.imovel?.autorizacoes || [])

// Métodos
function toggleSection(section: string) {
  expandedSections.value[section] = !expandedSections.value[section]
}

function isImage(url: string | null | undefined): boolean {
  if (!url) return false
  return ['.png', '.jpg', '.jpeg', '.gif', '.webp', '.svg'].some(ext =>
    url.toLowerCase().endsWith(ext)
  )
}

function isPdf(url: string | null | undefined): boolean {
  if (!url) return false
  return url.toLowerCase().endsWith('.pdf')
}

function formatFileSize(bytes: number | undefined): string {
  if (!bytes) return '—'
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

function formatDate(dateString: string | undefined): string {
  if (!dateString) return '—'
  try {
    const date = new Date(dateString)
    return date.toLocaleString('pt-BR', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return '—'
  }
}

function handleFilesSelected(data: { files: File[]; type: string }) {
  // Add files to pending buffer instead of uploading immediately
  let addedCount = 0
  for (const file of data.files) {
    if (mediaStore.addPendingFile(data.type, file)) {
      addedCount++
    }
  }

  // Show error if any file failed validation
  if (mediaStore.uploadError) {
    // Error message is in mediaStore.uploadError
    // Component can display it
  }
}

function handleDeleteMedia(type: string, id: number) {
  if (!confirm('Tem certeza que deseja deletar esta mídia?')) return
  emit('media-deleted', { type, id })
}

function handleRemovePending(id: string) {
  mediaStore.removePendingFile(id)
}

function handleDragStart(id: string, type: string) {
  draggedItemId.value = id
  dragOverType.value = type
}

function handleDragEnd() {
  draggedItemId.value = null
  dragOverType.value = null
}

function handleDragOver(event: DragEvent) {
  event.preventDefault()
  event.dataTransfer!.dropEffect = 'move'
}

function handleDrop(event: DragEvent, type: string) {
  event.preventDefault()
  
  if (draggedItemId.value && dragOverType.value === type) {
    // Get pending files of this type
    const pendingOfType = mediaStore.pendingByType[type] || []
    const draggedIndex = pendingOfType.findIndex(f => f.id === draggedItemId.value)
    const targetIndex = pendingOfType.findIndex(f => f.id === (event.currentTarget as HTMLElement).getAttribute('data-file-id'))
    
    if (draggedIndex !== -1 && targetIndex !== -1) {
      // Create new array with reordered items
      const allPending = [...mediaStore.pendingFiles]
      const draggedItem = allPending.find(f => f.id === draggedItemId.value)
      const targetItem = allPending.find(f => f.id === (event.currentTarget as HTMLElement).getAttribute('data-file-id'))
      
      if (draggedItem && targetItem) {
        const draggedIdx = allPending.indexOf(draggedItem)
        const targetIdx = allPending.indexOf(targetItem)
        
        if (draggedIdx !== -1 && targetIdx !== -1) {
          // Swap items
          [allPending[draggedIdx], allPending[targetIdx]] = [allPending[targetIdx], allPending[draggedIdx]]
          
          // Update store with new order
          mediaStore.reorderPendingFiles(allPending.map(f => f.id))
        }
      }
    }
  }
  
  draggedItemId.value = null
  dragOverType.value = null
}

function handleSaveMedia() {
  emit('media-save')
}

function handleCancelMedia() {
  mediaStore.clearPending()
  emit('media-cancel')
}

function openModal(url: string | null, type: string, name?: string) {
  // Emitir para parent abrir modal
  emit('media-added', { type: 'modal-open', files: [] })
}
</script>

<template>
  <div class="space-y-4">
    <!-- ERROR MESSAGE -->
    <div v-if="mediaStore.uploadError" class="p-3 bg-destructive/10 border border-destructive/30 rounded-lg text-sm text-destructive">
      {{ mediaStore.uploadError }}
    </div>

    <!-- PENDING MEDIA ACTIONS (sticky) -->
    <div v-if="mediaStore.pendingCount > 0" class="flex items-center justify-between p-4 bg-primary/10 border border-primary/20 rounded-lg">
      <div class="flex items-center gap-2">
        <span class="text-sm font-medium">{{ mediaStore.pendingCount }} arquivo(s) aguardando envio</span>
      </div>
      <div class="flex gap-2">
        <Button
          variant="outline"
          size="sm"
          @click="handleCancelMedia"
          :disabled="mediaStore.isUploading"
        >
          <X class="w-4 h-4 mr-1" />
          Cancelar
        </Button>
        <Button
          size="sm"
          @click="handleSaveMedia"
          :loading="mediaStore.isUploading"
          loading-position="start"
        >
          <Check class="w-4 h-4 mr-1" />
          Salvar
        </Button>
      </div>
    </div>

    <!-- IMAGENS -->
    <div class="border rounded-lg overflow-hidden">
      <button
        @click="toggleSection('imagens')"
        class="w-full flex items-center justify-between p-4 hover:bg-secondary/50 bg-card border-b transition-colors"
      >
        <div class="flex items-center gap-2">
          <span class="font-medium">Imagens</span>
          <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded-full">{{ imagens.length }}</span>
          <span v-if="mediaStore.pendingCountByType['imagens']" class="text-xs bg-warning/10 text-warning px-2 py-1 rounded-full">
            +{{ mediaStore.pendingCountByType['imagens'] }} pendente
          </span>
        </div>
        <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.imagens ? 'rotate-180' : '']" />
      </button>

      <div v-if="expandedSections.imagens" class="p-4 space-y-4 bg-muted/20">
        <!-- PENDING IMAGES PREVIEW -->
        <div v-if="mediaStore.pendingByType['imagens'] && mediaStore.pendingByType['imagens'].length > 0">
          <p class="text-sm font-medium mb-3 text-warning flex items-center gap-2">
            <span>Imagens Pendentes (Arraste para reordenar)</span>
            <span class="text-xs bg-warning/20 text-warning px-2 py-1 rounded">{{ mediaStore.pendingByType['imagens'].length }}</span>
          </p>
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 mb-4 pb-4 border-b">
            <div
              v-for="(pending, idx) in mediaStore.pendingByType['imagens']"
              :key="pending.id"
              :data-file-id="pending.id"
              draggable="true"
              @dragstart="handleDragStart(pending.id, 'imagens')"
              @dragend="handleDragEnd"
              @dragover="handleDragOver"
              @drop="handleDrop($event, 'imagens')"
              :class="[
                'relative group h-32 bg-muted rounded-lg overflow-hidden cursor-grab active:cursor-grabbing transition-all',
                draggedItemId === pending.id ? 'opacity-50 ring-2 ring-primary' : '',
                dragOverType === 'imagens' ? 'ring-2 ring-primary/50' : ''
              ]"
            >
              <img v-if="pending.preview" :src="pending.preview" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground text-xs">
                Carregando...
              </div>

              <!-- Overlay com ações -->
              <div class="absolute inset-0 bg-black/0 group-hover:bg-black/50 transition-colors flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                <button
                  @click="handleRemovePending(pending.id)"
                  class="p-2 bg-destructive text-white rounded-full hover:bg-destructive/90 transition-colors"
                  title="Remover imagem"
                >
                  <X class="w-4 h-4" />
                </button>
                <GripVertical class="w-4 h-4 text-white opacity-60" />
              </div>

              <!-- Order Badge -->
              <div class="absolute top-1 left-1 bg-primary text-white text-xs font-medium px-2 py-1 rounded">
                {{ idx + 1 }}
              </div>

              <!-- Size Badge -->
              <div class="absolute bottom-1 right-1 text-xs bg-black/70 text-white px-2 py-1 rounded">
                {{ mediaStore.formatFileSize(pending.file.size) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Preview grid - existing images -->
        <div v-if="imagens.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
          <div
            v-for="(img, idx) in imagens"
            :key="img.id"
            class="relative group h-32 bg-muted rounded-lg overflow-hidden cursor-pointer hover:ring-2 ring-primary transition-all"
          >
            <img v-if="img.url" :src="img.url" class="w-full h-full object-cover" />
            <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground text-xs">
              Sem imagem
            </div>

            <!-- Overlay com ações -->
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/50 transition-colors flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
              <button
                @click="handleDeleteMedia('imagens', img.id)"
                class="p-2 bg-destructive text-white rounded-full hover:bg-destructive/90"
                title="Deletar imagem"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>

            <!-- Badge ordem -->
            <div class="absolute top-1 left-1 bg-black/70 text-white text-xs px-2 py-1 rounded">
              {{ Number(idx) + 1 }}
            </div>
          </div>
        </div>

        <!-- Upload -->
        <div class="border-t pt-4">
          <p class="text-sm font-medium mb-3">Adicionar imagens</p>
          <ImovelUploadZone
            media-type="image"
            :multiple="true"
            @files-selected="handleFilesSelected"
          />
        </div>
      </div>
    </div>

    <!-- VÍDEOS -->
    <div class="border rounded-lg overflow-hidden">
      <button
        @click="toggleSection('videos')"
        class="w-full flex items-center justify-between p-4 hover:bg-secondary/50 bg-card border-b transition-colors"
      >
        <div class="flex items-center gap-2">
          <span class="font-medium">Vídeos</span>
          <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded-full">{{ videos.length }}</span>
          <span v-if="mediaStore.pendingCountByType['videos']" class="text-xs bg-warning/10 text-warning px-2 py-1 rounded-full">
            +{{ mediaStore.pendingCountByType['videos'] }} pendente
          </span>
        </div>
        <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.videos ? 'rotate-180' : '']" />
      </button>

      <div v-if="expandedSections.videos" class="p-4 space-y-4 bg-muted/20">
        <!-- PENDING VIDEOS PREVIEW -->
        <div v-if="mediaStore.pendingByType['videos'] && mediaStore.pendingByType['videos'].length > 0">
          <p class="text-sm font-medium mb-3 text-warning flex items-center gap-2">
            <span>Vídeos Pendentes (Arraste para reordenar)</span>
            <span class="text-xs bg-warning/20 text-warning px-2 py-1 rounded">{{ mediaStore.pendingByType['videos'].length }}</span>
          </p>
          <div class="space-y-3 mb-4 pb-4 border-b">
            <div
              v-for="(pending, idx) in mediaStore.pendingByType['videos']"
              :key="pending.id"
              :data-file-id="pending.id"
              draggable="true"
              @dragstart="handleDragStart(pending.id, 'videos')"
              @dragend="handleDragEnd"
              @dragover="handleDragOver"
              @drop="handleDrop($event, 'videos')"
              :class="[
                'flex items-start gap-3 p-3 bg-muted rounded-lg cursor-grab active:cursor-grabbing transition-all',
                draggedItemId === pending.id ? 'opacity-50 ring-2 ring-primary' : '',
                dragOverType === 'videos' ? 'ring-2 ring-primary/50' : ''
              ]"
            >
              <!-- Video Thumbnail Preview -->
              <div class="relative flex-shrink-0 w-24 h-16 bg-black rounded overflow-hidden">
                <img
                  v-if="pending.preview"
                  :src="pending.preview"
                  class="w-full h-full object-cover"
                  alt="Video thumbnail"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-white/50">
                  <span class="text-2xl">🎬</span>
                </div>
                <!-- Play icon overlay -->
                <div class="absolute inset-0 flex items-center justify-center">
                  <div class="w-8 h-8 bg-white/80 rounded-full flex items-center justify-center">
                    <Play class="w-4 h-4 text-black ml-0.5" />
                  </div>
                </div>
                <span class="absolute bottom-1 right-1 text-xs font-medium bg-primary text-white px-1.5 py-0.5 rounded">{{ idx + 1 }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ pending.file.name }}</p>
                <p class="text-xs text-muted-foreground">{{ mediaStore.formatFileSize(pending.file.size) }}</p>
              </div>
              <div class="flex gap-2 flex-shrink-0">
                <button
                  @click="handleRemovePending(pending.id)"
                  class="p-2 hover:bg-destructive/10 text-destructive rounded transition-colors"
                  title="Remover vídeo"
                >
                  <X class="w-4 h-4" />
                </button>
                <GripVertical class="w-4 h-4 text-muted-foreground mt-2" />
              </div>
            </div>
          </div>
        </div>

        <!-- Lista de vídeos - existing -->
        <div v-if="videos.length > 0" class="space-y-3">
          <div
            v-for="video in videos"
            :key="video.id"
            class="bg-card border rounded-lg hover:border-primary transition-colors overflow-hidden"
          >
            <!-- Video Player -->
            <div v-if="video.url" class="relative bg-black">
              <video
                :src="video.url"
                controls
                preload="metadata"
                class="w-full max-h-64 object-contain"
                @loadedmetadata="(e) => (e.target as HTMLVideoElement).currentTime = 0.1"
              >
                Seu navegador não suporta o elemento de vídeo.
              </video>
            </div>
            <!-- Video Info -->
            <div class="flex items-center gap-3 p-3">
              <div class="text-xl flex-shrink-0">🎬</div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ video.name || `Vídeo ${video.id}` }}</p>
                <p class="text-xs text-muted-foreground">{{ formatFileSize(video.size) }}</p>
              </div>
              <button
                @click="handleDeleteMedia('videos', video.id)"
                class="p-2 hover:bg-destructive/10 text-destructive rounded transition-colors flex-shrink-0"
                title="Deletar vídeo"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
        <div v-else class="text-sm text-muted-foreground py-4 text-center">
          Nenhum vídeo adicionado
        </div>

        <!-- Upload -->
        <div class="border-t pt-4">
          <p class="text-sm font-medium mb-3">Adicionar vídeo</p>
          <ImovelUploadZone media-type="video" @files-selected="handleFilesSelected" />
        </div>
      </div>
    </div>

    <!-- PLANTAS -->
    <div class="border rounded-lg overflow-hidden">
      <button
        @click="toggleSection('plantas')"
        class="w-full flex items-center justify-between p-4 hover:bg-secondary/50 bg-card border-b transition-colors"
      >
        <div class="flex items-center gap-2">
          <span class="font-medium">Plantas</span>
          <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded-full">{{ plantas.length }}</span>
          <span v-if="mediaStore.pendingCountByType['plantas']" class="text-xs bg-warning/10 text-warning px-2 py-1 rounded-full">
            +{{ mediaStore.pendingCountByType['plantas'] }} pendente
          </span>
        </div>
        <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.plantas ? 'rotate-180' : '']" />
      </button>

      <div v-if="expandedSections.plantas" class="p-4 space-y-4 bg-muted/20">
        <!-- PENDING PLANTAS PREVIEW -->
        <div v-if="mediaStore.pendingByType['plantas'] && mediaStore.pendingByType['plantas'].length > 0">
          <p class="text-sm font-medium mb-3 text-warning flex items-center gap-2">
            <span>Plantas Pendentes (Arraste para reordenar)</span>
            <span class="text-xs bg-warning/20 text-warning px-2 py-1 rounded">{{ mediaStore.pendingByType['plantas'].length }}</span>
          </p>
          <div class="space-y-3 mb-4 pb-4 border-b">
            <div
              v-for="(pending, idx) in mediaStore.pendingByType['plantas']"
              :key="pending.id"
              :data-file-id="pending.id"
              draggable="true"
              @dragstart="handleDragStart(pending.id, 'plantas')"
              @dragend="handleDragEnd"
              @dragover="handleDragOver"
              @drop="handleDrop($event, 'plantas')"
              :class="[
                'flex items-start gap-3 p-3 bg-muted rounded-lg cursor-grab active:cursor-grabbing transition-all',
                draggedItemId === pending.id ? 'opacity-50 ring-2 ring-primary' : '',
                dragOverType === 'plantas' ? 'ring-2 ring-primary/50' : ''
              ]"
            >
              <div class="flex flex-col items-center justify-center flex-shrink-0 gap-1">
                <div class="text-2xl"></div>
                <span class="text-xs font-medium bg-primary text-white px-2 py-1 rounded">{{ idx + 1 }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ pending.file.name }}</p>
                <p class="text-xs text-muted-foreground">{{ mediaStore.formatFileSize(pending.file.size) }}</p>
              </div>
              <div class="flex gap-2 flex-shrink-0">
                <button
                  @click="handleRemovePending(pending.id)"
                  class="p-2 hover:bg-destructive/10 text-destructive rounded transition-colors"
                  title="Remover planta"
                >
                  <X class="w-4 h-4" />
                </button>
                <GripVertical class="w-4 h-4 text-muted-foreground mt-2" />
              </div>
            </div>
          </div>
        </div>

        <!-- Lista de plantas - existing -->
        <div v-if="plantas.length > 0" class="space-y-3">
          <div
            v-for="planta in plantas"
            :key="planta.id"
            class="flex items-start gap-3 p-3 bg-card border rounded-lg hover:border-primary transition-colors"
          >
            <div class="text-2xl flex-shrink-0">
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium truncate">{{ planta.name || `Planta ${planta.id}` }}</p>
              <p class="text-xs text-muted-foreground">{{ formatDate(planta.uploadedAt) }}</p>
            </div>
            <button
              @click="handleDeleteMedia('plantas', planta.id)"
              class="p-2 hover:bg-destructive/10 text-destructive rounded transition-colors flex-shrink-0"
              title="Deletar planta"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
        <div v-else class="text-sm text-muted-foreground py-4 text-center">
          Nenhuma planta adicionada
        </div>

        <!-- Upload -->
        <div class="border-t pt-4">
          <p class="text-sm font-medium mb-3">Adicionar planta</p>
          <ImovelUploadZone media-type="planta" @files-selected="handleFilesSelected" />
        </div>
      </div>
    </div>

    <!-- AUTORIZAÇÕES -->
    <div class="border rounded-lg overflow-hidden">
      <button
        @click="toggleSection('autorizacoes')"
        class="w-full flex items-center justify-between p-4 hover:bg-secondary/50 bg-card border-b transition-colors"
      >
        <div class="flex items-center gap-2">
          <span class="font-medium">Autorizações</span>
          <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded-full">{{ autorizacoes.length }}</span>
          <span v-if="mediaStore.pendingCountByType['autorizacoes']" class="text-xs bg-warning/10 text-warning px-2 py-1 rounded-full">
            +{{ mediaStore.pendingCountByType['autorizacoes'] }} pendente
          </span>
        </div>
        <ChevronDown :class="['w-4 h-4 transition-transform', expandedSections.autorizacoes ? 'rotate-180' : '']" />
      </button>

      <div v-if="expandedSections.autorizacoes" class="p-4 space-y-4 bg-muted/20">
        <!-- PENDING AUTORIZAÇÕES PREVIEW -->
        <div v-if="mediaStore.pendingByType['autorizacoes'] && mediaStore.pendingByType['autorizacoes'].length > 0">
          <p class="text-sm font-medium mb-3 text-warning flex items-center gap-2">
            <span>Autorizações Pendentes (Arraste para reordenar)</span>
            <span class="text-xs bg-warning/20 text-warning px-2 py-1 rounded">{{ mediaStore.pendingByType['autorizacoes'].length }}</span>
          </p>
          <div class="space-y-3 mb-4 pb-4 border-b">
            <div
              v-for="(pending, idx) in mediaStore.pendingByType['autorizacoes']"
              :key="pending.id"
              :data-file-id="pending.id"
              draggable="true"
              @dragstart="handleDragStart(pending.id, 'autorizacoes')"
              @dragend="handleDragEnd"
              @dragover="handleDragOver"
              @drop="handleDrop($event, 'autorizacoes')"
              :class="[
                'flex items-start gap-3 p-3 bg-muted rounded-lg cursor-grab active:cursor-grabbing transition-all',
                draggedItemId === pending.id ? 'opacity-50 ring-2 ring-primary' : '',
                dragOverType === 'autorizacoes' ? 'ring-2 ring-primary/50' : ''
              ]"
            >
              <div class="flex flex-col items-center justify-center flex-shrink-0 gap-1">
                <div class="text-2xl"></div>
                <span class="text-xs font-medium bg-primary text-white px-2 py-1 rounded">{{ idx + 1 }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ pending.file.name }}</p>
                <p class="text-xs text-muted-foreground">{{ mediaStore.formatFileSize(pending.file.size) }}</p>
              </div>
              <div class="flex gap-2 flex-shrink-0">
                <button
                  @click="handleRemovePending(pending.id)"
                  class="p-2 hover:bg-destructive/10 text-destructive rounded transition-colors"
                  title="Remover autorização"
                >
                  <X class="w-4 h-4" />
                </button>
                <GripVertical class="w-4 h-4 text-muted-foreground mt-2" />
              </div>
            </div>
          </div>
        </div>

        <!-- Lista de autorizações - existing -->
        <div v-if="autorizacoes.length > 0" class="space-y-3">
          <div
            v-for="auth in autorizacoes"
            :key="auth.id"
            class="flex items-start gap-3 p-3 bg-card border rounded-lg hover:border-primary transition-colors"
          >
            <div class="text-2xl flex-shrink-0"></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium truncate">{{ auth.original_name || `Autorização ${auth.id}` }}</p>
              <p class="text-xs text-muted-foreground">{{ formatFileSize(auth.size) }}</p>
            </div>
            <button
              @click="handleDeleteMedia('autorizacoes', auth.id)"
              class="p-2 hover:bg-destructive/10 text-destructive rounded transition-colors flex-shrink-0"
              title="Deletar autorização"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
        <div v-else class="text-sm text-muted-foreground py-4 text-center">
          Nenhuma autorização adicionada
        </div>

        <!-- Upload -->
        <div class="border-t pt-4">
          <p class="text-sm font-medium mb-3">Adicionar autorização</p>
          <ImovelUploadZone media-type="autorizacao" @files-selected="handleFilesSelected" />
        </div>
      </div>
    </div>
  </div>
</template>
