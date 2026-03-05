import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { MEDIA_TYPE_MAP, MEDIA_LIMITS, normalizeMediaType } from '@/constants/mediaTypes'

export interface PendingFile {
  id: string
  type: string
  file: File
  preview?: string
  status: 'pending' | 'uploading' | 'success' | 'error'
  error?: string
  progress?: number
}

export const useMediaUploadStore = defineStore('mediaUpload', () => {
  const pendingFiles = ref<PendingFile[]>([])
  const isUploading = ref(false)
  const uploadProgress = ref(0)
  const uploadError = ref<string | null>(null)

  // Group pending files by type
  const pendingByType = computed(() => {
    const grouped: Record<string, PendingFile[]> = {}
    pendingFiles.value.forEach(file => {
      if (!grouped[file.type]) {
        grouped[file.type] = []
      }
      grouped[file.type].push(file)
    })
    return grouped
  })

  // Count pending files
  const pendingCount = computed(() => pendingFiles.value.length)

  // Count pending files by type
  const pendingCountByType = computed(() => {
    const counts: Record<string, number> = {}
    Object.entries(pendingByType.value).forEach(([type, files]) => {
      counts[type] = files.length
    })
    return counts
  })

  // Generate unique ID for pending file
  function generateId(): string {
    return `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
  }

  // Add pending file (validates before adding)
  function addPendingFile(type: string, file: File): boolean {
    try {
      // Normalize input type (e.g., 'image' -> 'imagens')
      const normalizedType = normalizeMediaType(type)
      if (!normalizedType) {
        uploadError.value = `Tipo de mídia inválido: ${type}`
        return false
      }

      // Get limits using normalized type (lowercase singular for MEDIA_LIMITS)
      const limitKey = normalizedType === 'imagens' ? 'image' :
                       normalizedType === 'videos' ? 'video' :
                       normalizedType === 'plantas' ? 'planta' :
                       normalizedType === 'autorizacoes' ? 'autorizacao' : null

      if (!limitKey) {
        uploadError.value = `Chave de limite não encontrada para tipo: ${normalizedType}`
        return false
      }

      const config = MEDIA_LIMITS[limitKey as keyof typeof MEDIA_LIMITS]
      if (!config) {
        uploadError.value = `Configuração não encontrada para tipo: ${limitKey}`
        return false
      }

      // Validate file size
      if (file.size > config.maxSize) {
        uploadError.value = `${file.name} excede o tamanho máximo de ${formatFileSize(config.maxSize)}`
        return false
      }

      // Validate MIME type
      const allowedMimes = config.mimes.split(',').map(m => {
        // Convert mimes like 'jpg' to 'image/jpeg'
        const ext = m.trim()
        const mimeMap: Record<string, string> = {
          'png': 'image/png',
          'jpg': 'image/jpeg',
          'jpeg': 'image/jpeg',
          'webp': 'image/webp',
          'gif': 'image/gif',
          'mp4': 'video/mp4',
          'webm': 'video/webm',
          'mov': 'video/quicktime',
          'avi': 'video/x-msvideo',
          'pdf': 'application/pdf'
        }
        return mimeMap[ext] || `application/${ext}`
      })

      if (!allowedMimes.includes(file.type)) {
        uploadError.value = `${file.name} tem tipo de arquivo não permitido. Tipos aceitos: ${config.mimes}`
        return false
      }

      // Generate preview for images
      let preview: string | undefined
      if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (e) => {
          const index = pendingFiles.value.findIndex(p => p.file === file)
          if (index !== -1) {
            pendingFiles.value[index].preview = e.target?.result as string
          }
        }
        reader.readAsDataURL(file)
      }

      // Generate thumbnail preview for videos using canvas capture
      if (file.type.startsWith('video/')) {
        generateVideoThumbnail(file).then((thumbnail) => {
          const index = pendingFiles.value.findIndex(p => p.file === file)
          if (index !== -1) {
            pendingFiles.value[index].preview = thumbnail
          }
        }).catch((err) => {
          console.warn('Failed to generate video thumbnail:', err)
        })
      }

      // Add to pending (store normalized type)
      pendingFiles.value.push({
        id: generateId(),
        type: normalizedType,  // Store normalized type
        file,
        preview,
        status: 'pending',
        error: undefined,
        progress: 0
      })

      // Clear error after successful add
      uploadError.value = null
      return true
    } catch (error) {
      uploadError.value = error instanceof Error ? error.message : 'Erro ao adicionar arquivo'
      return false
    }
  }

  // Remove specific pending file
  function removePendingFile(id: string): void {
    const index = pendingFiles.value.findIndex(f => f.id === id)
    if (index !== -1) {
      pendingFiles.value.splice(index, 1)
    }
  }

  // Remove all pending files of a type
  function removePendingByType(type: string): void {
    pendingFiles.value = pendingFiles.value.filter(f => f.type !== type)
  }

  // Generate video thumbnail by capturing a frame at 1 second
  async function generateVideoThumbnail(file: File): Promise<string> {
    return new Promise((resolve, reject) => {
      const video = document.createElement('video')
      const canvas = document.createElement('canvas')
      const ctx = canvas.getContext('2d')

      video.preload = 'metadata'
      video.muted = true
      video.playsInline = true

      video.onloadedmetadata = () => {
        // Seek to 1 second or 10% of video duration (whichever is smaller)
        video.currentTime = Math.min(1, video.duration * 0.1)
      }

      video.onseeked = () => {
        // Set canvas size to video dimensions (max 320px width for thumbnail)
        const scale = Math.min(1, 320 / video.videoWidth)
        canvas.width = video.videoWidth * scale
        canvas.height = video.videoHeight * scale

        if (ctx) {
          ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
          const thumbnail = canvas.toDataURL('image/jpeg', 0.7)
          URL.revokeObjectURL(video.src)
          resolve(thumbnail)
        } else {
          reject(new Error('Canvas context not available'))
        }
      }

      video.onerror = () => {
        URL.revokeObjectURL(video.src)
        reject(new Error('Failed to load video'))
      }

      video.src = URL.createObjectURL(file)
    })
  }

  // Clear all pending files
  function clearPending(): void {
    pendingFiles.value = []
    uploadError.value = null
    uploadProgress.value = 0
  }

  // Get pending files as FormData for upload
  function getPendingAsFormData(): FormData {
    const formData = new FormData()

    pendingFiles.value.forEach((pending) => {
      const typeKey = MEDIA_TYPE_MAP[pending.type as keyof typeof MEDIA_TYPE_MAP]
      if (!typeKey) return

      // Map API keys back to form field names (based on controller expectations)
      // Backend expects: imagens[], videos[], planta, autorizacao
      // CRITICAL: Use [] notation for array fields so PHP parses them as arrays
      const fieldNameMap: Record<string, string> = {
        'images': 'imagens[]',      // Array notation for multiple files
        'videos': 'videos[]',       // Array notation for multiple files
        'plants': 'planta[]',       // Array notation for multiple files
        'authorizations': 'autorizacao' // Singular (only one file)
      }

      const fieldName = fieldNameMap[typeKey] || typeKey

      if (fieldName) {
        formData.append(fieldName, pending.file, pending.file.name)
      }
    })

    return formData
  }

  // Update file status and progress
  function updateFileProgress(id: string, progress: number, status?: PendingFile['status']): void {
    const file = pendingFiles.value.find(f => f.id === id)
    if (file) {
      file.progress = progress
      if (status) file.status = status
    }
  }

  // Mark file as error
  function markFileError(id: string, error: string): void {
    const file = pendingFiles.value.find(f => f.id === id)
    if (file) {
      file.status = 'error'
      file.error = error
    }
  }

  // Mark file as success
  function markFileSuccess(id: string): void {
    const file = pendingFiles.value.find(f => f.id === id)
    if (file) {
      file.status = 'success'
      file.error = undefined
    }
  }

  // Reorder pending files
  function reorderPendingFiles(orderedIds: string[]): void {
    const newOrder: PendingFile[] = []
    orderedIds.forEach(id => {
      const file = pendingFiles.value.find(f => f.id === id)
      if (file) {
        newOrder.push(file)
      }
    })
    pendingFiles.value = newOrder
  }

  // Format file size for display
  function formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
  }

  return {
    pendingFiles,
    isUploading,
    uploadProgress,
    uploadError,
    pendingByType,
    pendingCount,
    pendingCountByType,
    addPendingFile,
    removePendingFile,
    removePendingByType,
    clearPending,
    getPendingAsFormData,
    updateFileProgress,
    markFileError,
    markFileSuccess,
    reorderPendingFiles,
    formatFileSize
  }
})
