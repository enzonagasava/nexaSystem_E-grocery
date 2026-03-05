<script setup lang="ts">
import { ref } from 'vue'
import ImovelUploadZone from '@/components/imovel/ImovelUploadZone.vue'
import {
  Image as ImageIcon,
  Video,
  FileText,
  X,
  Trash2,
  Upload,
} from 'lucide-vue-next'

const props = defineProps<{
  formRef: any
  errors?: any
}>()

const emit = defineEmits<{
  (e: 'update:files', data: { imagens: File[]; videos: File[]; plantas: File[] }): void
}>()

// ---- state ----
const activeTab = ref<'imagens' | 'videos' | 'plantas'>('imagens')

interface PendingMedia {
  id: string
  file: File
  preview?: string
}

const pendingImagens = ref<PendingMedia[]>([])
const pendingVideos = ref<PendingMedia[]>([])
const pendingPlantas = ref<PendingMedia[]>([])
const videoError = ref<string | null>(null)

// ---- helpers ----
function generateId(): string {
  return `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
}

function formatFileSize(bytes: number): string {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

function emitFiles() {
  emit('update:files', {
    imagens: pendingImagens.value.map((p: PendingMedia) => p.file),
    videos: pendingVideos.value.map((p: PendingMedia) => p.file),
    plantas: pendingPlantas.value.map((p: PendingMedia) => p.file),
  })
}

// ---- file handling ----
function handleFilesSelected(data: { files: File[]; type: string }) {
  // If adding videos, enforce a maximum of 2
  if (data.type === 'video' || data.type === 'videos') {
    const allowed = 1 - pendingVideos.value.length
    if (allowed <= 0) {
      videoError.value = 'Máximo de 1 vídeo permitido.'
      setTimeout(() => (videoError.value = null), 4000)
      return
    }

    const filesToAdd = data.files.slice(0, allowed)
    for (const file of filesToAdd) {
      const entry: PendingMedia = { id: generateId(), file }
      const isVideo = file.type.startsWith('video/')
      if (isVideo) {
        generateVideoThumbnail(file)
          .then((thumb) => {
            entry.preview = thumb
          })
          .catch(() => {})
      }
      pendingVideos.value.push(entry)
    }

    if (data.files.length > allowed) {
      videoError.value = `Apenas ${allowed} arquivo(s) foram adicionados. Limite de 1 vídeo.`
      setTimeout(() => (videoError.value = null), 4000)
    }

    emitFiles()
    return
  }

  // Non-video handling (images / plantas)
  for (const file of data.files) {
    const entry: PendingMedia = { id: generateId(), file }

    const isImage = file.type.startsWith('image/')

    // Generate preview for images
    if (isImage) {
      const reader = new FileReader()
      reader.onload = (ev) => {
        entry.preview = ev.target?.result as string
      }
      reader.readAsDataURL(file)
    }

    if (data.type === 'image' || data.type === 'imagens') {
      pendingImagens.value.push(entry)
    } else {
      // planta
      if (isImage) {
        const reader = new FileReader()
        reader.onload = (ev) => {
          entry.preview = ev.target?.result as string
        }
        reader.readAsDataURL(file)
      }
      pendingPlantas.value.push(entry)
    }
  }
  emitFiles()
}

function removeFile(type: 'imagens' | 'videos' | 'plantas', id: string) {
  if (type === 'imagens') pendingImagens.value = pendingImagens.value.filter((f: PendingMedia) => f.id !== id)
  else if (type === 'videos') pendingVideos.value = pendingVideos.value.filter((f: PendingMedia) => f.id !== id)
  else pendingPlantas.value = pendingPlantas.value.filter((f: PendingMedia) => f.id !== id)
  emitFiles()
}

async function generateVideoThumbnail(file: File): Promise<string> {
  return new Promise((resolve, reject) => {
    const video = document.createElement('video')
    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')
    video.preload = 'metadata'
    video.muted = true
    video.playsInline = true
    video.onloadedmetadata = () => {
      video.currentTime = Math.min(1, video.duration * 0.1)
    }
    video.onseeked = () => {
      const scale = Math.min(1, 320 / video.videoWidth)
      canvas.width = video.videoWidth * scale
      canvas.height = video.videoHeight * scale
      if (ctx) {
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
        resolve(canvas.toDataURL('image/jpeg', 0.7))
      } else {
        reject(new Error('No canvas context'))
      }
      URL.revokeObjectURL(video.src)
    }
    video.onerror = () => {
      URL.revokeObjectURL(video.src)
      reject(new Error('Video load error'))
    }
    video.src = URL.createObjectURL(file)
  })
}

// Expose for parent access
defineExpose({
  getFiles: () => ({
    imagens: pendingImagens.value.map((p: PendingMedia) => p.file),
    videos: pendingVideos.value.map((p: PendingMedia) => p.file),
    plantas: pendingPlantas.value.map((p: PendingMedia) => p.file),
  }),
})
</script>

<template>
  <div class="mt-4 border p-4 rounded">
    <h2 class="font-semibold mb-4">Mídia</h2>

    <!-- Tabs -->
    <div class="flex border-b mb-4">
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
        :class="activeTab === 'imagens' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
        @click="activeTab = 'imagens'"
      >
        <ImageIcon class="w-4 h-4 inline-block mr-1" />
        Imagens ({{ pendingImagens.length }})
      </button>
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
        :class="activeTab === 'videos' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
        @click="activeTab = 'videos'"
      >
        <Video class="w-4 h-4 inline-block mr-1" />
        Vídeos ({{ pendingVideos.length }})
      </button>
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
        :class="activeTab === 'plantas' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
        @click="activeTab = 'plantas'"
      >
        <FileText class="w-4 h-4 inline-block mr-1" />
        Plantas ({{ pendingPlantas.length }})
      </button>
    </div>

    <!-- Erros -->
    <p v-if="errors?.imagens && activeTab === 'imagens'" class="text-destructive text-xs mb-2">{{ errors.imagens }}</p>
    <p v-if="errors?.video && activeTab === 'videos'" class="text-destructive text-xs mb-2">{{ errors.video }}</p>
    <p v-if="videoError && activeTab === 'videos'" class="text-destructive text-xs mb-2">{{ videoError }}</p>
    <p v-if="errors?.planta && activeTab === 'plantas'" class="text-destructive text-xs mb-2">{{ errors.planta }}</p>

    <!-- ============ TAB IMAGENS ============ -->
    <div v-if="activeTab === 'imagens'">
      <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
        <div
          v-for="(item, idx) in pendingImagens"
          :key="item.id"
          class="relative group aspect-square rounded-lg overflow-hidden bg-muted"
        >
          <img
            v-if="item.preview"
            :src="item.preview"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground">
            <Upload class="w-6 h-6 animate-pulse" />
          </div>
          <div class="absolute top-1 left-1 bg-black/70 text-white text-[10px] px-1.5 py-0.5 rounded">{{ Number(idx) + 1 }}</div>
          <div class="absolute bottom-1 right-1 text-[10px] bg-black/60 text-white px-1.5 py-0.5 rounded">{{ formatFileSize(item.file.size) }}</div>
          <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100 pointer-events-none group-hover:pointer-events-auto">
            <button type="button" class="p-1.5 bg-destructive text-white rounded-full hover:bg-destructive/90 transition-colors" title="Remover" @click.stop="removeFile('imagens', item.id)">
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>

        <!-- Upload Zone -->
        <div class="aspect-square rounded-lg border-2 border-dashed border-muted-foreground/30 hover:border-primary/50 transition-colors flex items-center justify-center">
          <ImovelUploadZone media-type="image" :multiple="true" @files-selected="handleFilesSelected" />
        </div>
      </div>

      <div v-if="pendingImagens.length === 0" class="text-center py-4 text-muted-foreground text-sm">
        Nenhuma imagem adicionada. Arraste ou clique para adicionar.
      </div>
    </div>

    <!-- ============ TAB VÍDEOS ============ -->
    <div v-if="activeTab === 'videos'">
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <div
          v-for="(item, idx) in pendingVideos"
          :key="item.id"
          class="relative group aspect-video rounded-lg overflow-hidden bg-muted"
        >
          <img v-if="item.preview" :src="item.preview" class="w-full h-full object-cover" />
          <div v-else class="w-full h-full flex items-center justify-center bg-black/60">
            <Video class="w-8 h-8 text-white/50" />
          </div>
          <div class="absolute bottom-0 left-0 right-0 p-1.5 bg-gradient-to-t from-black/70">
            <p class="text-[11px] text-white truncate">{{ item.file.name }}</p>
            <p class="text-[10px] text-white/60">{{ formatFileSize(item.file.size) }}</p>
          </div>
          <button type="button" class="absolute top-1 right-1 p-1 bg-destructive text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity" @click.stop="removeFile('videos', item.id)">
            <X class="w-3 h-3" />
          </button>
        </div>

        <!-- Upload Zone -->
        <div class="aspect-video rounded-lg border-2 border-dashed border-muted-foreground/30 hover:border-primary/50 transition-colors flex items-center justify-center">
          <ImovelUploadZone media-type="video" :multiple="true" @files-selected="handleFilesSelected" />
        </div>
      </div>

      <div v-if="pendingVideos.length === 0" class="text-center py-4 text-muted-foreground text-sm">
        Nenhum vídeo adicionado. Arraste ou clique para adicionar.
      </div>
    </div>

    <!-- ============ TAB PLANTAS ============ -->
    <div v-if="activeTab === 'plantas'">
      <div class="space-y-2">
        <div
          v-for="(item, idx) in pendingPlantas"
          :key="item.id"
          class="flex items-center gap-3 p-3 rounded-lg border hover:bg-muted/50 transition-colors group"
        >
          <!-- Thumbnail or icon -->
          <template v-if="item.preview">
            <img :src="item.preview" class="w-10 h-10 rounded object-cover flex-shrink-0" />
          </template>
          <template v-else>
            <FileText class="w-7 h-7 text-muted-foreground flex-shrink-0" />
          </template>

          <div class="flex-1 min-w-0">
            <p class="font-medium text-sm truncate">{{ item.file.name }}</p>
            <p class="text-xs text-muted-foreground">{{ formatFileSize(item.file.size) }}</p>
          </div>
          <button type="button" class="p-1.5 text-destructive hover:bg-destructive/10 rounded-full opacity-0 group-hover:opacity-100 transition-opacity" @click="removeFile('plantas', item.id)">
            <Trash2 class="w-4 h-4" />
          </button>
        </div>

        <div v-if="pendingPlantas.length === 0" class="text-center py-4 text-muted-foreground text-sm">
          Nenhuma planta adicionada.
        </div>
      </div>

      <!-- Upload zone for plantas -->
      <div class="mt-3 rounded-lg border-2 border-dashed border-muted-foreground/30 hover:border-primary/50 transition-colors">
        <ImovelUploadZone media-type="planta" :multiple="true" @files-selected="handleFilesSelected" />
      </div>
    </div>
  </div>
</template>
