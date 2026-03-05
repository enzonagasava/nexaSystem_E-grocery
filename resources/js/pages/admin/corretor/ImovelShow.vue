<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import AuthLayout from '@/layouts/AuthLayout.vue'
import Button from '@/components/ui/button/Button.vue';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue'
import ImovelMediaModal from '@/components/ImovelMediaModal.vue'
import ImovelUploadZone from '@/components/imovel/ImovelUploadZone.vue'
import ImovelDetailsModal from '@/components/imovel/ImovelDetailsModal.vue'
import { useMediaUploadStore } from '@/stores/mediaUploadStore'
import { useToast } from '@/composables/useToast'
import { MEDIA_TYPE_MAP } from '@/constants/mediaTypes'
import { swal } from '@/plugins/swal'


// Swiper
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/thumbs'
import { Navigation, Thumbs } from 'swiper/modules'
import { Swiper, SwiperSlide } from 'swiper/vue'

import {
  ArrowLeft,
  Edit,
  MapPin,
  Home,
  BedDouble,
  Bath,
  Car,
  Maximize,
  Calendar,
  Check,
  ChevronDown,
  Image as ImageIcon,
  Play,
  FileText,
  Video,
  Upload,
  X,
  Trash2,
  GripVertical,
} from 'lucide-vue-next'

const props = defineProps<{ imovel: any }>()
const page = usePage()

// Store
const mediaStore = useMediaUploadStore()
const { showToast } = useToast()

// Swiper thumbs
const thumbsSwiper = ref<any>(null)
const setThumbsSwiper = (swiper: any) => { thumbsSwiper.value = swiper }

// Estado de UI
const imovelData = ref(props.imovel)
const activeTab = ref<'imagens' | 'videos' | 'plantas'>('imagens')
const selectedMediaIndex = ref<number | null>(null)
const mediaModalOpen = ref(false)
const detailsModalOpen = ref(false)
const detailsStartEditing = ref(false)
const uploadError = ref<string | null>(null)
const draggedId = ref<string | null>(null)
const dragOverId = ref<string | null>(null)

const expandedSections = ref({
  galeria: true,
  descricao: true,
  caracteristicas: false,
  localizacao: false,
  proprietario: false,
})

// Edit mode for ordering media and quick add
const editingOrder = ref(false)
const showQuickUpload = ref(false)
const galleryRef = ref<HTMLElement | null>(null)
const isSavingOrder = ref(false)
const isDeleting = ref(false)
const originalOrders = ref({ imagens: [] as any[], videos: [] as any[], plantas: [] as any[] })
// Items marked for deletion while in edit mode (only applied to server when user saves)
const pendingDeletes = ref({ imagens: [] as number[], videos: [] as number[], plantas: [] as number[] })

// Computed
const imagens = computed(() => imovelData.value?.imagens || [])
const videos = computed(() => imovelData.value?.videos || [])
const plantas = computed(() => imovelData.value?.plantas || [])
const autorizacoes = computed(() => imovelData.value?.autorizacoes || [])

const imagemPrincipal = computed(() => {
  // Prefer pending image preview if available, else use first existing
  const pendingImgs = mediaStore.pendingByType['imagens']
  if (pendingImgs && pendingImgs.length > 0 && pendingImgs[0].preview) return pendingImgs[0].preview
  if (imagens.value.length > 0) return imagens.value[0].url
  return null
})

const pendingImagens = computed(() => mediaStore.pendingByType['imagens'] || [])
const pendingVideos = computed(() => mediaStore.pendingByType['videos'] || [])
const pendingPlantas = computed(() => mediaStore.pendingByType['plantas'] || [])

// Combined lists to allow mixing existing and pending items for ordering UI
const combinedImagens = computed(() => {
  const ex = imagens.value.map((i: any) => ({ key: `ex-${i.id}`, type: 'existing', data: i }))
  const p = pendingImagens.value.map((pnd: any) => ({ key: String(pnd.id), type: 'pending', data: pnd }))
  return [...ex, ...p]
})

const combinedVideos = computed(() => {
  const ex = videos.value.map((i: any) => ({ key: `ex-${i.id}`, type: 'existing', data: i }))
  const p = pendingVideos.value.map((pnd: any) => ({ key: String(pnd.id), type: 'pending', data: pnd }))
  return [...ex, ...p]
})

const combinedPlantas = computed(() => {
  const ex = plantas.value.map((i: any) => ({ key: `ex-${i.id}`, type: 'existing', data: i }))
  const p = pendingPlantas.value.map((pnd: any) => ({ key: String(pnd.id), type: 'pending', data: pnd }))
  return [...ex, ...p]
})

const hasPendingChanges = computed(() => mediaStore.pendingCount > 0)

const selectedCount = computed(() => {
  try {
    return selectedItems.value[activeTab.value]?.length || 0
  } catch (e) { return 0 }
})

// Selection mode for multi-delete
const selectionMode = ref(false)
const selectedItems = ref({ imagens: [] as string[], videos: [] as string[], plantas: [] as string[] })

function isItemSelected(type: 'imagens' | 'videos' | 'plantas', key: string) {
  return selectedItems.value[type].includes(String(key))
}

function toggleSelectItem(type: 'imagens' | 'videos' | 'plantas', key: string, value?: boolean) {
  const arr = selectedItems.value[type]
  const strKey = String(key)
  if (typeof value === 'boolean') {
    const exists = arr.includes(strKey)
    if (value && !exists) arr.push(strKey)
    if (!value && exists) arr.splice(arr.indexOf(strKey), 1)
    return
  }
  const idx = arr.indexOf(strKey)
  if (idx === -1) arr.push(strKey)
  else arr.splice(idx, 1)
}

function clearSelection() {
  selectedItems.value.imagens = []
  selectedItems.value.videos = []
  selectedItems.value.plantas = []
  selectionMode.value = false
}

const allSelected = computed<boolean>({
  get: () => {
    const type = activeTab.value
    const combined = type === 'imagens' ? combinedImagens.value : type === 'videos' ? combinedVideos.value : combinedPlantas.value
    return combined.length > 0 && selectedItems.value[type].length === combined.length
  },
  set: (v: boolean) => {
    const type = activeTab.value
    const combined = type === 'imagens' ? combinedImagens.value : type === 'videos' ? combinedVideos.value : combinedPlantas.value
    if (v) selectedItems.value[type] = combined.map((item: any) => String(item.key))
    else selectedItems.value[type] = []
  },
})

function toggleSelectAll() {
  const type = activeTab.value
  const combined = type === 'imagens' ? combinedImagens.value : type === 'videos' ? combinedVideos.value : combinedPlantas.value
  if (allSelected.value) selectedItems.value[type] = []
  else selectedItems.value[type] = combined.map((item: any) => String(item.key))
}

async function deleteSelected() {
  const type = activeTab.value
  const keys = [...selectedItems.value[type]]
  if (!keys.length) return

  const result = await swal.fire({
    title: 'Excluir mídia',
    text: `Deseja excluir ${keys.length} item(s)? Esta ação não poderá ser desfeita.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, excluir',
    cancelButtonText: 'Cancelar'
  })
  if (!result.isConfirmed) return
  // If we're in edit-order mode, mark existing items for deletion locally and remove pending files immediately
  if (editingOrder.value) {
    const pendingKeys: string[] = []
    for (const k of keys) {
      if (String(k).startsWith('ex-')) {
        const id = Number(String(k).replace('ex-', ''))
        // add to pendingDeletes for current type
        const arr = pendingDeletes.value[type as keyof typeof pendingDeletes.value]
        if (!arr.includes(id)) arr.push(id)
        // remove visually from imovelData
        if (type === 'imagens') imovelData.value.imagens = (imovelData.value.imagens || []).filter((i: any) => i.id !== id)
        else if (type === 'videos') imovelData.value.videos = (imovelData.value.videos || []).filter((v: any) => v.id !== id)
        else if (type === 'plantas') imovelData.value.plantas = (imovelData.value.plantas || []).filter((p: any) => p.id !== id)
        // Note: do NOT remove from originalOrders here so Cancel can restore visually-deleted items
      } else {
        pendingKeys.push(String(k))
      }
    }

    for (const pk of pendingKeys) {
      try { mediaStore.removePendingFile(pk) } catch (e) { console.error(e) }
    }

    clearSelection()
    showToast?.({ title: 'Marcado', description: `${keys.length} item(s) marcados para exclusão`, type: 'info' })
    return
  }

  // Otherwise perform immediate deletion (non-edit mode)
  let showedLoading = false
  if (keys.length > 1) {
    swal.loading('Excluindo mídias...', false)
    showedLoading = true
  }

  isDeleting.value = true

  try {
    // Process pending removals locally first
    const pendingKeys: string[] = []
    const existingIds: number[] = []

    for (const k of keys) {
      if (String(k).startsWith('ex-')) {
        existingIds.push(Number(String(k).replace('ex-', '')))
      } else {
        pendingKeys.push(String(k))
      }
    }

    // remove pending files locally
    for (const pk of pendingKeys) {
      try { mediaStore.removePendingFile(pk) } catch (e) { console.error(e) }
    }

    // delete existing items via direct DELETE requests (no Inertia navigation)
    if (existingIds.length) {
      const typeKey = MEDIA_TYPE_MAP[type === 'imagens' ? 'imagens' : type === 'videos' ? 'videos' : 'plantas' as keyof typeof MEDIA_TYPE_MAP] || type
      const failed: number[] = []
      // Read CSRF token from meta tag (works with Laravel default setup)
      let csrfToken: string | null = null
      try {
        const meta = document?.querySelector('meta[name="csrf-token"]')
        if (meta && meta.getAttribute) csrfToken = meta.getAttribute('content')
      } catch (e) { csrfToken = null }

      // Use fetch with XSRF token from cookie to perform multiple deletes reliably
      function getCookie(name: string) {
        try {
          const match = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]+)'))
          return match ? match[2] : null
        } catch (e) { return null }
      }

      const xsrfCookie = getCookie('XSRF-TOKEN')
      const xsrfToken = xsrfCookie ? decodeURIComponent(xsrfCookie) : null

      for (const id of existingIds) {
        try {
          const headers: any = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
          if (xsrfToken) headers['X-XSRF-TOKEN'] = xsrfToken
          else {
            // Fallback to meta csrf token if cookie not present
            const meta = document?.querySelector('meta[name="csrf-token"]')
            if (meta && meta.getAttribute) headers['X-CSRF-TOKEN'] = meta.getAttribute('content')
          }

          const res = await fetch(`/admin/corretor/imoveis/${props.imovel.id}/${typeKey}/${id}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers
          })

          if (!res.ok) {
            failed.push(id)
            console.error('delete failed', id, res.status)
          } else {
            // locally remove from imovelData
            if (type === 'imagens') imovelData.value.imagens = (imovelData.value.imagens || []).filter((i: any) => i.id !== id)
            else if (type === 'videos') imovelData.value.videos = (imovelData.value.videos || []).filter((v: any) => v.id !== id)
            else if (type === 'plantas') imovelData.value.plantas = (imovelData.value.plantas || []).filter((p: any) => p.id !== id)

            // Sync originalOrders so Cancel won't restore deleted items
            if (type === 'imagens') originalOrders.value.imagens = originalOrders.value.imagens.filter((i: any) => i.id !== id)
            else if (type === 'videos') originalOrders.value.videos = originalOrders.value.videos.filter((v: any) => v.id !== id)
            else if (type === 'plantas') originalOrders.value.plantas = originalOrders.value.plantas.filter((p: any) => p.id !== id)
          }
        } catch (err) {
          failed.push(id)
          console.error('Erro ao deletar item:', err)
        }
      }

      if (failed.length) {
        showToast?.({ title: 'Aviso', description: `${failed.length} item(s) não puderam ser removidos`, type: 'warning' })
      }
    }

    clearSelection()
    showToast?.({ title: 'Sucesso', description: 'Itens removidos', type: 'success' })
  } finally {
    isDeleting.value = false
    if (showedLoading) Swal.close()
  }
}

const enderecoFormatado = computed(() => {
  const end = imovelData.value?.endereco
  if (!end) return null
  const partes = [end.endereco, end.numero, end.bairro, end.cidade, end.estado].filter(Boolean)
  return partes.length ? partes.join(', ') : null
})

const mediasForModal = computed(() => {
  if (activeTab.value === 'imagens') {
    return imagens.value.map((img: any) => ({
      id: img.id, url: img.url, type: 'image', name: img.original_name
    }))
  }
  if (activeTab.value === 'videos') {
    return videos.value.map((vid: any) => ({
      id: vid.id, url: vid.url, type: 'video', name: vid.original_name
    }))
  }
  return plantas.value.map((p: any) => ({
    id: p.id, url: p.url, type: p.mime_type?.includes('pdf') ? 'pdf' : 'image', name: p.original_name
  }))
})

const selectedMedia = computed(() => {
  if (selectedMediaIndex.value === null) return null
  const medias = mediasForModal.value
  if (selectedMediaIndex.value >= 0 && selectedMediaIndex.value < medias.length) {
    const m = medias[selectedMediaIndex.value]
    return { url: m.url, type: m.type, name: m.name }
  }
  return null
})

// Helpers
function formatCurrency(value: number | string | null | undefined): string {
  if (!value) return '—'
  const num = typeof value === 'string' ? parseFloat(value) : value
  if (isNaN(num)) return '—'
  return num.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

function formatDate(value: string | null | undefined): string {
  if (!value) return '—'
  try {
    const date = new Date(value)
    return date.toLocaleString('pt-BR', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })
  } catch { return '—' }
}

function toggleSection(section: string) {
  // Prevent collapsing the gallery while in edit-order mode
  if (section === 'galeria' && editingOrder.value && expandedSections.value.galeria) return

  expandedSections.value[section as keyof typeof expandedSections.value] =
    !expandedSections.value[section as keyof typeof expandedSections.value]
}

function openMediaModal(index: number | string) {
  selectedMediaIndex.value = Number(index)
  mediaModalOpen.value = true
}

function closeMediaModal() {
  mediaModalOpen.value = false
  selectedMediaIndex.value = null
}

function openDetailsModal() { detailsStartEditing.value = true; detailsModalOpen.value = true }
function closeDetailsModal() { detailsModalOpen.value = false; detailsStartEditing.value = false }

function handleImovelUpdated(updatedImovel: any) {
  imovelData.value = Object.assign({}, imovelData.value, updatedImovel)
}

// Gallery file handling
function handleFilesSelected(data: { files: File[]; type: string }) {
  for (const file of data.files) {
    mediaStore.addPendingFile(data.type, file)
  }
}

function handleRemovePending(id: string) {
  mediaStore.removePendingFile(id)
}

async function handleDeleteMedia(type: string, id: number) {
  const result = await swal.fire({
    title: 'Excluir mídia',
    text: 'Tem certeza que deseja excluir esta mídia? Esta ação não poderá ser desfeita.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, excluir',
    cancelButtonText: 'Cancelar'
  })
  if (!result.isConfirmed) return
  // If we're in edit mode, stage the deletion locally and don't call the server yet
  if (editingOrder.value) {
    const arr = pendingDeletes.value[type as keyof typeof pendingDeletes.value]
    if (!arr.includes(id)) arr.push(id)

    if (type === 'imagens') imovelData.value.imagens = (imovelData.value.imagens || []).filter((i: any) => i.id !== id)
    else if (type === 'videos') imovelData.value.videos = (imovelData.value.videos || []).filter((v: any) => v.id !== id)
    else if (type === 'plantas') imovelData.value.plantas = (imovelData.value.plantas || []).filter((p: any) => p.id !== id)

    // Do not remove from originalOrders here so Cancel can restore the item
    showToast?.({ title: 'Marcado', description: 'Mídia marcada para exclusão (será removida ao salvar)', type: 'info' })
    return
  }

  // Not editing: perform immediate delete
  handleMediaDeleted({ type, id })
}

// Drag and drop
function onDragStart(e: DragEvent, id: string) {
  // ensure the drag has transferable data for broader browser compatibility
  try {
    if (e.dataTransfer) {
      e.dataTransfer.effectAllowed = 'move'
      // store id as plain text so drop can read from event if needed
      e.dataTransfer.setData('text/plain', String(id))
    }
  } catch (err) {
    // ignore dataTransfer errors in some browsers
  }
  draggedId.value = id
}

function onDragOver(e: DragEvent, targetId: string) {
  e.preventDefault()
  if (e.dataTransfer) e.dataTransfer.dropEffect = 'move'
  dragOverId.value = targetId
}

function onDragEnter(e: DragEvent, targetId: string) {
  e.preventDefault()
  dragOverId.value = targetId
}

function onDragLeave() {
  dragOverId.value = null
}

function onDrop(e: DragEvent, targetId: string) {
  e.preventDefault()
  let sourceId = draggedId.value
  if (!sourceId && e.dataTransfer) {
    try {
      const dt = e.dataTransfer.getData('text/plain')
      if (dt) sourceId = dt
    } catch (err) {}
  }

  if (!sourceId || sourceId === targetId) {
    draggedId.value = null
    dragOverId.value = null
    return
  }

  // Helper to reorder combined arrays and sync back to existing + pending
  function reorderCombined(combined: any[]) {
    const fromIdx = combined.findIndex((it: any) => String(it.key) === String(sourceId))
    const toIdx = combined.findIndex((it: any) => String(it.key) === String(targetId))
    if (fromIdx === -1 || toIdx === -1) return false
    const [moved] = combined.splice(fromIdx, 1)
    combined.splice(toIdx, 0, moved)

    // sync back
    const existing = combined.filter((it: any) => it.type === 'existing').map((it: any) => it.data)
    const pendingOrder = combined.filter((it: any) => it.type === 'pending').map((it: any) => it.data.id)
    return { existing, pendingOrder }
  }

  try {
    if (activeTab.value === 'imagens') {
      const combined = combinedImagens.value.map((c: any) => ({ ...c }))
      const res = reorderCombined(combined)
      if (res) {
        imovelData.value.imagens = res.existing
        mediaStore.reorderPendingFiles(res.pendingOrder)
      }
    } else if (activeTab.value === 'videos') {
      const combined = combinedVideos.value.map((c: any) => ({ ...c }))
      const res = reorderCombined(combined)
      if (res) {
        imovelData.value.videos = res.existing
        mediaStore.reorderPendingFiles(res.pendingOrder)
      }
    } else if (activeTab.value === 'plantas') {
      const combined = combinedPlantas.value.map((c: any) => ({ ...c }))
      const res = reorderCombined(combined)
      if (res) {
        imovelData.value.plantas = res.existing
        mediaStore.reorderPendingFiles(res.pendingOrder)
      }
    }
  } catch (err) {}

  draggedId.value = null
  dragOverId.value = null
}

function onDragEnd() {
  draggedId.value = null
  dragOverId.value = null
}

function handleToggleEditOrder() {
  editingOrder.value = !editingOrder.value
  // hide quick upload when entering edit mode
  if (editingOrder.value === false) showQuickUpload.value = false
  // focus gallery when entering edit mode
  if (editingOrder.value) {
    // store original order so cancel can revert
    originalOrders.value.imagens = (imovelData.value.imagens || []).map((i: any) => ({ ...i }))
    originalOrders.value.videos = (imovelData.value.videos || []).map((i: any) => ({ ...i }))
    originalOrders.value.plantas = (imovelData.value.plantas || []).map((i: any) => ({ ...i }))
    setTimeout(() => {
      try { galleryRef.value?.focus(); galleryRef.value?.scrollIntoView({ behavior: 'smooth', block: 'center' }) } catch (e) {}
    }, 50)
  } else {
    // leaving edit mode without saving: clear originals
    originalOrders.value.imagens = []
    originalOrders.value.videos = []
    originalOrders.value.plantas = []
  }
}

async function handleSaveOrder() {
  // Confirm before saving
  const confirm = await swal.fire({
    title: 'Salvar alterações',
    text: 'Deseja salvar as alterações realizadas?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sim, salvar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true,
  })
  if (!confirm.isConfirmed) return

  // Attempt to persist new order to server (best-effort). Builds array of ids for active tab.
  const type = activeTab.value
  let ids: Array<number | string> = []
  if (type === 'imagens') ids = (imovelData.value.imagens || []).map((i: any) => i.id)
  else if (type === 'videos') ids = (imovelData.value.videos || []).map((i: any) => i.id)
  else if (type === 'plantas') ids = (imovelData.value.plantas || []).map((i: any) => i.id)

  // If there are pending deletes for this type, perform them first on server
  const deletesForType = pendingDeletes.value[type as keyof typeof pendingDeletes.value] || []
  if (deletesForType.length) {
    swal.loading('Aplicando exclusões...', false)
    const failed = await performServerDeletes(type as any, deletesForType)
    Swal.close()
    if (failed.length) {
      showToast?.({ title: 'Aviso', description: `${failed.length} item(s) não puderam ser removidos`, type: 'warning' })
    }
    // clear pendingDeletes for this type regardless
    pendingDeletes.value[type as keyof typeof pendingDeletes.value] = []
  }

  // Exit edit mode IMMEDIATELY after user confirmed — before Inertia visit
  // (Inertia preserveState can overwrite state set inside callbacks)
  editingOrder.value = false
  showQuickUpload.value = false
  clearSelection()
  originalOrders.value.imagens = []
  originalOrders.value.videos = []
  originalOrders.value.plantas = []
  pendingDeletes.value.imagens = []
  pendingDeletes.value.videos = []
  pendingDeletes.value.plantas = []

  router.post(
    route('admin.corretor.imoveis.update.order', props.imovel.id),
    { type, order: ids },
    {
      preserveScroll: true,
      preserveState: true,
      onStart: () => { isSavingOrder.value = true },
      onSuccess: (visitPage: any) => {
        const newImovel = (visitPage.props as any).imovel
        if (newImovel) imovelData.value = newImovel
        showToast?.({ title: 'Sucesso', description: 'Ordem salva', type: 'success' })
      },
      onError: (errors: any) => {
        showToast?.({ title: 'Erro', description: 'Erro ao salvar ordem', type: 'error' })
      },
      onFinish: () => {
        isSavingOrder.value = false
      }
    }
  )
}

// Helper to perform server-side deletes for items marked locally
async function performServerDeletes(type: 'imagens' | 'videos' | 'plantas', ids: number[]) {
  if (!ids || !ids.length) return []
  const typeKey = MEDIA_TYPE_MAP[type as keyof typeof MEDIA_TYPE_MAP] || type
  const failed: number[] = []

  // Read CSRF token
  function getCookie(name: string) {
    try {
      const match = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]+)'))
      return match ? match[2] : null
    } catch (e) { return null }
  }

  const xsrfCookie = getCookie('XSRF-TOKEN')
  const xsrfToken = xsrfCookie ? decodeURIComponent(xsrfCookie) : null

  for (const id of ids) {
    try {
      const headers: any = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      if (xsrfToken) headers['X-XSRF-TOKEN'] = xsrfToken
      else {
        const meta = document?.querySelector('meta[name="csrf-token"]')
        if (meta && meta.getAttribute) headers['X-CSRF-TOKEN'] = meta.getAttribute('content')
      }

      const res = await fetch(`/admin/corretor/imoveis/${props.imovel.id}/${typeKey}/${id}`, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers
      })

      if (!res.ok) {
        failed.push(id)
        console.error('delete failed', id, res.status)
      }
    } catch (err) {
      failed.push(id)
      console.error('Erro ao deletar item:', err)
    }
  }

  return failed
}

async function handleCancelEditOrder() {
  // If there are any unsaved changes (pending uploads or reordering), confirm before discarding
  function arraysDiffer(orig: any[], current: any[]) {
    if (!orig || orig.length === 0) return false
    const origIds = orig.map((i: any) => i.id)
    const currIds = (current || []).map((i: any) => i.id)
    return JSON.stringify(origIds) !== JSON.stringify(currIds)
  }

  const hasOrderChanges = arraysDiffer(originalOrders.value.imagens, imovelData.value.imagens) ||
    arraysDiffer(originalOrders.value.videos, imovelData.value.videos) ||
    arraysDiffer(originalOrders.value.plantas, imovelData.value.plantas)

  const hasPending = mediaStore.pendingCount > 0

  if (hasPending || hasOrderChanges) {
    const result = await swal.fire({
      title: 'Alterações não salvas',
      text: hasPending
        ? `Existem ${mediaStore.pendingCount} arquivo(s) pendente(s). Deseja descartar os arquivos pendentes e sair do modo de edição?`
        : 'Existem alterações na ordem dos itens. Deseja descartar as alterações e sair do modo de edição?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Descartar e sair',
      cancelButtonText: 'Continuar editando',
      reverseButtons: true,
    })

    if (!result.isConfirmed) return

    // User confirmed: discard pending uploads and/or order changes
    try { if (hasPending) mediaStore.clearPending() } catch (e) {}
    uploadError.value = null
    if (hasPending) showToast?.({ title: 'Descartado', description: 'Arquivos pendentes descartados', type: 'info' })
    // clear any locally marked deletes as user chose to discard changes
    pendingDeletes.value.imagens = []
    pendingDeletes.value.videos = []
    pendingDeletes.value.plantas = []
  }

  // Reset any local reorder changes by restoring stored originals
  try {
    if (originalOrders.value.imagens.length) imovelData.value.imagens = originalOrders.value.imagens
    if (originalOrders.value.videos.length) imovelData.value.videos = originalOrders.value.videos
    if (originalOrders.value.plantas.length) imovelData.value.plantas = originalOrders.value.plantas
  } catch (e) {}
  originalOrders.value.imagens = []
  originalOrders.value.videos = []
  originalOrders.value.plantas = []
  clearSelection()
  editingOrder.value = false
}

// Media upload handlers
async function handleMediaSave() {
  if (mediaStore.pendingCount === 0) return

  // Confirm before saving
  const confirm = await swal.fire({
    title: 'Salvar alterações',
    text: `Deseja salvar ${mediaStore.pendingCount} arquivo(s) pendente(s)?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sim, salvar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true,
  })
  if (!confirm.isConfirmed) return

  uploadError.value = null
  mediaStore.isUploading = true

  // If there are pending deletes from edit mode across any type, apply them first
  const types: Array<'imagens' | 'videos' | 'plantas'> = ['imagens', 'videos', 'plantas']
  for (const t of types) {
    const toDelete = pendingDeletes.value[t]
    if (toDelete && toDelete.length) {
      swal.loading('Aplicando exclusões...', false)
      const failed = await performServerDeletes(t, toDelete)
      Swal.close()
      if (failed.length) showToast?.({ title: 'Aviso', description: `${failed.length} item(s) não puderam ser removidos`, type: 'warning' })
      pendingDeletes.value[t] = []
    }
  }

  // Exit edit mode IMMEDIATELY after user confirmed — before Inertia visit
  // (Inertia preserveState can overwrite state set inside callbacks)
  editingOrder.value = false
  showQuickUpload.value = false
  clearSelection()
  originalOrders.value.imagens = []
  originalOrders.value.videos = []
  originalOrders.value.plantas = []
  pendingDeletes.value.imagens = []
  pendingDeletes.value.videos = []
  pendingDeletes.value.plantas = []

  const formData = mediaStore.getPendingAsFormData()

  router.post(
    route('admin.corretor.imoveis.update.post', props.imovel.id),
    formData,
    {
      forceFormData: true,
      preserveScroll: true,
      preserveState: true,
      onProgress: (progress) => {
        if (progress?.percentage) {
          mediaStore.uploadProgress = progress.percentage
        }
      },
      onSuccess: (visitPage) => {
        const newImovel = (visitPage.props as any).imovel
        if (newImovel) {
          imovelData.value = newImovel
        }
        mediaStore.clearPending()
        showToast?.({ title: 'Sucesso', description: 'Mídias salvas com sucesso', type: 'success' })
      },
      onError: (errors) => {
        const errorMsg = Object.values(errors).flat().join(', ') || 'Erro ao fazer upload'
        uploadError.value = errorMsg
        showToast?.({ title: 'Erro', description: errorMsg, type: 'error' })
      },
      onFinish: () => {
        mediaStore.isUploading = false
        mediaStore.uploadProgress = 0
      }
    }
  )
}

async function handleMediaCancel() {
  mediaStore.clearPending()
  uploadError.value = null
}

async function confirmCancelUploads() {
  if (mediaStore.pendingCount === 0) return
  const result = await swal.fire({
    title: 'Alterações não salvas',
    text: `Existem ${mediaStore.pendingCount} arquivo(s) pendente(s). O cliente ainda não salvou as alterações. Deseja descartar os arquivos pendentes e continuar?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Descartar alterações',
    cancelButtonText: 'Continuar upload',
    reverseButtons: true,
  })

  if (result.isConfirmed) {
    mediaStore.clearPending()
    uploadError.value = null
    showToast?.({ title: 'Cancelado', description: 'Arquivos pendentes descartados', type: 'info' })
  }
}

async function handleMediaDeleted(data: { type: string; id: number }) {
  try {
    const typeKey = MEDIA_TYPE_MAP[data.type as keyof typeof MEDIA_TYPE_MAP] || data.type

    // Use fetch + SweetAlert loading so we don't trigger global loading overlay
    swal.loading('Excluindo mídia...', false)
    isDeleting.value = true

    function getCookie(name: string) {
      try {
        const match = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]+)'))
        return match ? match[2] : null
      } catch (e) { return null }
    }

    const xsrfCookie = getCookie('XSRF-TOKEN')
    const xsrfToken = xsrfCookie ? decodeURIComponent(xsrfCookie) : null

    try {
      const headers: any = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      if (xsrfToken) headers['X-XSRF-TOKEN'] = xsrfToken
      else {
        const meta = document?.querySelector('meta[name="csrf-token"]')
        if (meta && meta.getAttribute) headers['X-CSRF-TOKEN'] = meta.getAttribute('content')
      }

      const res = await fetch(`/admin/corretor/imoveis/${props.imovel.id}/${typeKey}/${data.id}`, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers
      })

      Swal.close()

      if (!res.ok) {
        console.error('delete failed', data.id, res.status)
        showToast?.({ title: 'Erro', description: 'Erro ao deletar mídia', type: 'error' })
      } else {
        // update local state (fallback if server doesn't return full imovel)
        if (data.type === 'imagens') imovelData.value.imagens = imagens.value.filter((i: any) => i.id !== data.id)
        else if (data.type === 'videos') imovelData.value.videos = videos.value.filter((v: any) => v.id !== data.id)
        else if (data.type === 'plantas') imovelData.value.plantas = plantas.value.filter((p: any) => p.id !== data.id)
        else if (data.type === 'autorizacoes') imovelData.value.autorizacoes = autorizacoes.value.filter((a: any) => a.id !== data.id)

        showToast?.({ title: 'Sucesso', description: 'Mídia removida com sucesso', type: 'success' })
      }
    } catch (err) {
      Swal.close()
      console.error('Erro ao deletar mídia:', err)
      showToast?.({ title: 'Erro', description: 'Erro ao deletar mídia', type: 'error' })
    } finally {
      isDeleting.value = false
    }
  } catch (error: any) {
    console.error('Erro ao deletar mídia:', error)
  }
}

watch(() => props.imovel, (newVal: any) => { if (newVal) imovelData.value = newVal }, { deep: false })
</script>

<template>
  <Head>
    <title>{{ imovelData.nome }} — Detalhes do Imóvel</title>
  </Head>

  <AuthLayout :modulo="String(page.props.modulo ?? '')" :hide-global-loading="true">
    <div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
      <div class="max-w-6xl mx-auto">

        <!-- Header -->
        <div class="mb-6">
          <Link :href="route('admin.corretor.imoveis.index')" class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-4">
            <ArrowLeft class="w-4 h-4 mr-1" />
            Voltar para Imóveis
          </Link>

          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold text-foreground">{{ imovelData.nome }}</h1>
                <span v-if="imovelData.status"
                  class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-primary text-primary-foreground">
                  {{ imovelData.status }}
                </span>
                <span v-if="imovelData.categoria"
                  class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-muted text-muted-foreground">
                  {{ imovelData.categoria }}
                </span>
              </div>
              <p v-if="imovelData.codigo" class="text-sm text-muted-foreground mt-1">Código: {{ imovelData.codigo }}</p>
              <p v-if="enderecoFormatado" class="text-sm text-muted-foreground flex items-center gap-1 mt-1">
                <MapPin class="w-3 h-3" />
                {{ enderecoFormatado }}
              </p>
            </div>

            <div class="flex items-center gap-3">
              <Button variant="primary" @click="openDetailsModal">
                <Edit class="w-4 h-4" />
                Editar
              </Button>
            </div>
          </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

          <!-- Coluna Principal (2/3) -->
          <div class="lg:col-span-2 space-y-6">

            <!-- Carrossel de Imagens + Vídeo -->
            <div v-if="imagens.length || videos.length" class="imovel-carousel">
              <!-- Swiper Principal -->
              <Swiper
                :modules="[Navigation, Thumbs]"
                :thumbs="{ swiper: thumbsSwiper && !thumbsSwiper.destroyed ? thumbsSwiper : undefined }"
                :navigation="{
                  prevEl: '.carousel-prev',
                  nextEl: '.carousel-next',
                }"
                :loop="false"
                :space-between="0"
                class="carousel-main rounded-xl overflow-hidden bg-muted aspect-video relative"
              >
                <SwiperSlide v-for="(img, idx) in imagens" :key="'img-' + img.id">
                  <img
                    :src="img.url"
                    :alt="img.original_name || imovelData.nome"
                    class="w-full h-full object-cover cursor-pointer"
                    @click="openMediaModal(idx)"
                  />
                </SwiperSlide>

                <SwiperSlide v-for="(vid, idx) in videos" :key="'vid-' + vid.id">
                  <div class="relative w-full h-full bg-black flex items-center justify-center">
                    <video
                      :src="vid.url"
                      class="w-full h-full object-contain"
                      controls
                      preload="metadata"
                    />
                  </div>
                </SwiperSlide>

                <!-- Navegação -->
                <template #container-end>
                  <Button class="carousel-prev carousel-nav-btn" aria-label="Anterior">
                    <i class="fa-solid fa-angle-left"></i>
                  </button>
                  <Button class="carousel-next carousel-nav-btn" aria-label="Próximo">
                    <i class="fa-solid fa-angle-right"></i>
                  </button>
                </template>
              </Swiper>

              <!-- Swiper Thumbs -->
              <Swiper
                :modules="[Thumbs]"
                :slides-per-view="6"
                :space-between="8"
                :watch-slides-progress="true"
                :free-mode="true"
                @swiper="setThumbsSwiper"
                class="carousel-thumbs mt-3"
                :breakpoints="{
                  0: { slidesPerView: 4, spaceBetween: 6 },
                  640: { slidesPerView: 5, spaceBetween: 8 },
                  1024: { slidesPerView: 6, spaceBetween: 8 },
                }"
              >
                <SwiperSlide v-for="(img, idx) in imagens" :key="'thumb-img-' + img.id">
                  <img
                    :src="img.url"
                    :alt="img.original_name || 'Miniatura'"
                    class="w-full h-full object-cover rounded-md cursor-pointer border-2 border-transparent transition-all"
                  />
                </SwiperSlide>

                <SwiperSlide v-for="(vid, idx) in videos" :key="'thumb-vid-' + vid.id">
                  <div class="relative w-full h-full rounded-md overflow-hidden cursor-pointer border-2 border-transparent transition-all bg-black">
                    <video :src="vid.url" class="w-full h-full object-cover" muted preload="metadata" />
                    <div class="absolute inset-0 flex items-center justify-center bg-black/40">
                      <Play class="w-5 h-5 text-white" />
                    </div>
                  </div>
                </SwiperSlide>
              </Swiper>
            </div>

            <!-- Sem mídia -->
            <div v-else class="rounded-xl overflow-hidden bg-muted aspect-video flex items-center justify-center text-muted-foreground">
              <ImageIcon class="w-16 h-16" />
            </div>

            <!-- Galeria de Mídia com gerenciamento inline -->
            <div :class="editingOrder ? 'bg-card rounded-xl border p-4 transition-all duration-300 transform scale-105 shadow-lg' : 'bg-card rounded-xl border p-4 transition-all duration-300'">
              <div class="w-full flex items-start justify-between gap-3">
                <Button variant="sectionAlt" @click="toggleSection('galeria')">
                  <h2 class="text-lg font-semibold">Galeria de Mídia</h2>
                  <ChevronDown v-if="!editingOrder" class="w-5 h-5 text-muted-foreground transition-transform" :class="{ 'rotate-180': expandedSections.galeria }" />
                </Button>

                <div class="flex items-center gap-2">
                  <template v-if="!editingOrder">
                    <Button size="sm" variant="outline" @click="handleToggleEditOrder" :title="editingOrder ? 'Sair do modo ordenar' : 'Editar ordem'">
                      <Edit class="w-4 h-4 mr-1" />
                      <span class="text-xs">{{ editingOrder ? 'Fechar' : 'Editar' }}</span>
                    </Button>
                  </template>
                  <!-- When editing, show selection controls and cancel/save depending on state -->
                  <template v-else-if="editingOrder && !hasPendingChanges">
                    <div class="flex items-center gap-2">
                      <template v-if="!selectionMode">
                        <Button size="sm" variant="ghost" @click="selectionMode = true">
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l3 3L22 4" /></svg>
                          <span class="text-xs">Selecionar</span>
                        </Button>
                      </template>
                      <template v-else>
                        <Checkbox v-model="allSelected" variant="solid" aria-label="Selecionar todos" />
                        <Button size="sm" variant="destructive" :disabled="selectedCount === 0 || isDeleting" @click="deleteSelected">
                          <template v-if="isDeleting">
                            <svg class="animate-spin w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-xs">Excluindo...</span>
                          </template>
                          <template v-else>
                            <Trash2 class="w-4 h-4 mr-1" />
                            <span class="text-xs">Excluir ({{ selectedCount }})</span>
                          </template>
                        </Button>
                        <Button size="sm" variant="outline" @click="clearSelection">Cancelar seleção</Button>
                      </template>

                      <Button size="sm" :disabled="isSavingOrder" @click="handleSaveOrder">
                        <Check class="w-4 h-4 mr-1" />
                        <span class="text-xs">Salvar</span>
                      </Button>
                      <Button size="sm" variant="outline" :disabled="isSavingOrder" @click="handleCancelEditOrder">
                        <X class="w-4 h-4 mr-1" />
                        <span class="text-xs">Cancelar</span>
                      </Button>
                    </div>
                  </template>
                  <template v-else-if="editingOrder && hasPendingChanges">
                    <div class="flex items-center gap-2">
                      <span class="text-sm font-medium">{{ mediaStore.pendingCount }} arquivo(s) pendente(s)</span>
                      <Button size="sm" :disabled="mediaStore.isUploading" @click="handleMediaSave">
                        <Check class="w-4 h-4 mr-1" />
                        <span class="text-xs">Salvar</span>
                      </Button>
                      <Button size="sm" variant="outline" :disabled="isSavingOrder" @click="handleCancelEditOrder">
                        <X class="w-4 h-4 mr-1" />
                        <span class="text-xs">Cancelar</span>
                      </Button>
                    </div>
                  </template>
                </div>
              </div>

              <div v-show="expandedSections.galeria" ref="galleryRef" tabindex="-1" :class="editingOrder ? 'relative z-50 mt-4 transition-all duration-300 rounded-lg p-3 bg-card' : 'mt-4 transition-all duration-300'">
                <!-- Overlay for rest of page when editing (no blur) -->
                <div v-if="editingOrder" class="fixed inset-0 bg-black/10 z-40 pointer-events-none"></div>
                <!-- Quick upload shown when user clicks + -->
                <div v-if="showQuickUpload" class="mb-3">
                  <div class="flex items-center justify-between mb-2">
                    <div class="text-sm font-medium">Adicionar ({{ activeTab }})</div>
                    <div class="flex gap-2">
                      <Button size="sm" variant="outline" @click="showQuickUpload = false">Fechar</Button>
                    </div>
                  </div>
                  <ImovelUploadZone :media-type="activeTab === 'imagens' ? 'image' : activeTab === 'videos' ? 'video' : 'planta'" :multiple="true" @files-selected="handleFilesSelected" />
                </div>
                <!-- Tabs -->
                <div class="flex border-b mb-4">
                  <Button
                    variant="tab"
                    :class="activeTab === 'imagens' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    @click="activeTab = 'imagens'"
                  >
                    <ImageIcon class="w-4 h-4 inline-block mr-1" />
                    Imagens ({{ imagens.length }}<span v-if="pendingImagens.length" class="text-warning"> +{{ pendingImagens.length }}</span>)
                  </Button>
                  <Button
                    variant="tab"
                    :class="activeTab === 'videos' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    @click="activeTab = 'videos'"
                  >
                    <Video class="w-4 h-4 inline-block mr-1" />
                    Vídeos ({{ videos.length }}<span v-if="pendingVideos.length" class="text-warning"> +{{ pendingVideos.length }}</span>)
                  </Button>
                  <Button
                    variant="tab"
                    :class="activeTab === 'plantas' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    @click="activeTab = 'plantas'"
                  >
                    <FileText class="w-4 h-4 inline-block mr-1" />
                    Plantas ({{ plantas.length }}<span v-if="pendingPlantas.length" class="text-warning"> +{{ pendingPlantas.length }}</span>)
                  </Button>
                </div>

                <!-- Upload error -->
                <div v-if="uploadError || mediaStore.uploadError" class="mb-4 p-3 bg-destructive/10 border border-destructive/30 rounded-lg text-sm text-destructive">
                  {{ uploadError || mediaStore.uploadError }}
                </div>

                <!-- Save / Cancel bar -->
                <div v-if="hasPendingChanges" class="mb-4 flex items-center justify-between p-3 bg-primary/10 border border-primary/20 rounded-lg">
                  <span class="text-sm font-medium">{{ mediaStore.pendingCount }} arquivo(s) pendente(s)</span>
                  <div class="flex gap-2">
                    <Button variant="outline" size="sm" :disabled="mediaStore.isUploading" @click="confirmCancelUploads">
                      <X class="w-4 h-4 mr-1" /> Cancelar
                    </Button>
                    <Button size="sm" :disabled="mediaStore.isUploading" @click="handleMediaSave">
                      <template v-if="mediaStore.isUploading">
                        <span class="mr-2">{{ mediaStore.uploadProgress }}%</span> Enviando...
                      </template>
                      <template v-else>
                        <Check class="w-4 h-4 mr-1" /> Salvar
                      </template>
                    </Button>
                    <!-- selection controls removed from this bar; only Save/Cancel remain -->
                  </div>
                </div>

                <!-- ============ TAB IMAGENS ============ -->
                <div v-if="activeTab === 'imagens'">
                  <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 relative" :class="editingOrder ? 'ring-2 ring-primary/50 rounded-lg p-1 bg-card' : ''">
                    <div
                      v-for="(item, idx) in combinedImagens"
                      :key="item.key"
                      :draggable="editingOrder && !selectionMode"
                      @dragstart="editingOrder && !selectionMode && onDragStart($event, item.key)"
                      @dragenter="editingOrder && !selectionMode && onDragEnter($event, item.key)"
                      @dragover="editingOrder && !selectionMode && onDragOver($event, item.key)"
                      @dragleave="editingOrder && !selectionMode && onDragLeave"
                      @drop="editingOrder && !selectionMode && onDrop($event, item.key)"
                      @dragend="editingOrder && !selectionMode && onDragEnd"
                      :class="[
                        'relative group aspect-square rounded-lg overflow-hidden bg-muted',
                        editingOrder && !selectionMode ? 'cursor-grab' : (selectionMode ? 'cursor-pointer' : '') ,
                        draggedId === String(item.key) ? 'opacity-40 scale-95' : '',
                        dragOverId === String(item.key) && draggedId !== String(item.key) ? 'ring-primary scale-105' : ''
                      ]"
                    >
                      <template v-if="item.type === 'existing'">
                          <img
                            :src="item.data.url || ''"
                            :alt="item.data.original_name || 'Imagem'"
                            class="w-full h-full object-cover cursor-pointer"
                            @click="selectionMode ? toggleSelectItem('imagens', item.key) : openMediaModal(imagens.findIndex((i: any) => i.id === item.data.id))"
                          />
                          <div class="absolute top-1 left-1 bg-black/70 text-white text-[10px] px-1.5 py-0.5 rounded">{{ Number(idx) + 1 }}</div>
                          <div v-if="selectionMode" class="absolute top-1 right-1 z-30">
                          <Checkbox :model-value="isItemSelected('imagens', item.key)" @update:modelValue="(val: boolean) => toggleSelectItem('imagens', item.key, val)" variant="solid" aria-label="Selecionar imagem" />
                        </div>
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100 pointer-events-none group-hover:pointer-events-auto">
                          <div class="flex items-center gap-2">
                            <Button class="p-1.5 bg-destructive text-white rounded-full hover:bg-destructive/90 transition-colors" title="Deletar" @click.stop="handleDeleteMedia('imagens', item.data.id)"><Trash2 class="w-3.5 h-3.5" /></button>
                            <GripVertical v-if="editingOrder" class="w-4 h-4 text-white/60" />
                          </div>
                        </div>
                      </template>
                      <template v-else>
                        <img
                          v-if="item.data.preview"
                          :src="item.data.preview"
                          class="w-full h-full object-cover"
                          @click="selectionMode ? toggleSelectItem('imagens', item.key) : null"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground text-xs"><Upload class="w-6 h-6 animate-pulse" /></div>
                        <div class="absolute top-1 left-1 bg-warning text-warning-foreground text-[10px] font-bold px-1.5 py-0.5 rounded">{{ Number(idx) + 1 }}</div>
                        <div class="absolute bottom-1 right-1 text-[10px] bg-black/60 text-white px-1.5 py-0.5 rounded">{{ mediaStore.formatFileSize(item.data.file.size) }}</div>
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 pointer-events-none group-hover:pointer-events-auto">
                            <Button class="p-1.5 bg-destructive text-white rounded-full hover:bg-destructive/90" @click.stop="handleRemovePending(item.data.id)"><X class="w-3.5 h-3.5" /></button>
                            <div v-if="selectionMode" class="absolute top-1 right-1 z-30">
                              <Checkbox :model-value="isItemSelected('imagens', item.key)" @update:modelValue="(val: boolean) => toggleSelectItem('imagens', item.key, val)" variant="solid" aria-label="Selecionar imagem" />
                            </div>
                          <GripVertical class="w-4 h-4 text-white/60" />
                        </div>
                      </template>
                    </div>

                    <!-- Add button -->
                    <div class="aspect-square rounded-lg border-2 border-dashed border-muted-foreground/30 hover:border-primary/50 transition-colors flex items-center justify-center">
                      <ImovelUploadZone media-type="image" :multiple="true" @files-selected="handleFilesSelected" />
                    </div>
                  </div>

                  <div v-if="combinedImagens.length === 0" class="text-center py-6 text-muted-foreground text-sm">Nenhuma imagem cadastrada. Adicione usando o botão acima.</div>
                </div>

                <!-- ============ TAB VÍDEOS ============ -->
                <div v-if="activeTab === 'videos'">
                  <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div
                      v-for="(item, idx) in combinedVideos"
                      :key="item.key"
                      :draggable="editingOrder && !selectionMode"
                      @dragstart="editingOrder && !selectionMode && onDragStart($event, item.key)"
                      @dragenter="editingOrder && !selectionMode && onDragEnter($event, item.key)"
                      @dragover="editingOrder && !selectionMode && onDragOver($event, item.key)"
                      @dragleave="editingOrder && !selectionMode && onDragLeave"
                      @drop="editingOrder && !selectionMode && onDrop($event, item.key)"
                      @dragend="editingOrder && !selectionMode && onDragEnd"
                      :class="[
                        'relative group aspect-video rounded-lg overflow-hidden bg-muted',
                        editingOrder && !selectionMode ? 'cursor-grab' : (selectionMode ? 'cursor-pointer' : '') ,
                        draggedId === String(item.key) ? 'opacity-40 scale-95' : '',
                        dragOverId === String(item.key) && draggedId !== String(item.key) ? 'ring-primary scale-105' : ''
                      ]"
                    >
                      <template v-if="item.type === 'existing'">
                        <video :src="item.data.url" class="w-full h-full object-cover" />
                        <div
                          :class="[
                            'absolute inset-0 bg-black/40 flex items-center justify-center group-hover:bg-black/50 transition-colors',
                            selectionMode ? 'pointer-events-auto' : 'pointer-events-none group-hover:pointer-events-auto'
                          ]"
                          @click="selectionMode ? toggleSelectItem('videos', item.key) : (!editingOrder && openMediaModal(videos.findIndex((i: any) => i.id === item.data.id)))"
                        >
                          <Video class="w-8 h-8 text-white" />
                        </div>
                        <div v-if="selectionMode" class="absolute top-1 right-1 z-30">
                          <Checkbox :model-value="isItemSelected('videos', item.key)" @update:modelValue="(val: boolean) => toggleSelectItem('videos', item.key, val)" variant="solid" aria-label="Selecionar vídeo" />
                        </div>
                        <p class="absolute bottom-0 left-0 right-0 p-1.5 text-[11px] text-white bg-gradient-to-t from-black/60 truncate">{{ item.data.original_name }}</p>
                        <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                          <Button class="p-1 bg-destructive text-white rounded-full" @click.stop="handleDeleteMedia('videos', item.data.id)"><Trash2 class="w-3 h-3" /></button>
                          <GripVertical v-if="editingOrder" class="w-4 h-4 text-white/60" />
                        </div>
                      </template>
                      <template v-else>
                        <img
                          v-if="item.data.preview"
                          :src="item.data.preview"
                          class="w-full h-full object-cover"
                          @click="selectionMode ? toggleSelectItem('videos', item.key) : null"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center bg-black/60"><Video class="w-8 h-8 text-white/50" /></div>
                        <div class="absolute bottom-0 left-0 right-0 p-1.5 bg-gradient-to-t from-black/70">
                          <p class="text-[11px] text-white truncate">{{ item.data.file.name }}</p>
                          <p class="text-[10px] text-white/60">{{ mediaStore.formatFileSize(item.data.file.size) }}</p>
                        </div>
                        <Button class="absolute top-1 right-1 p-1 bg-destructive text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity" @click.stop="handleRemovePending(item.data.id)"><X class="w-3 h-3" /></button>
                        <div v-if="selectionMode" class="absolute top-1 left-1 z-30">
                          <Checkbox :model-value="isItemSelected('videos', item.key)" @update:modelValue="(val: boolean) => toggleSelectItem('videos', item.key, val)" variant="solid" aria-label="Selecionar vídeo" />
                        </div>
                      </template>
                    </div>

                    <div class="aspect-video rounded-lg border-2 border-dashed border-muted-foreground/30 hover:border-primary/50 transition-colors flex items-center justify-center">
                      <ImovelUploadZone media-type="video" :multiple="true" @files-selected="handleFilesSelected" />
                    </div>
                  </div>

                  <div v-if="combinedVideos.length === 0" class="text-center py-6 text-muted-foreground text-sm">Nenhum vídeo cadastrado. Adicione usando o botão acima.</div>
                </div>

                <!-- ============ TAB PLANTAS ============ -->
                <div v-if="activeTab === 'plantas'">
                  <div class="space-y-2">
                    <div
                      v-for="(item, idx) in combinedPlantas"
                      :key="item.key"
                      :draggable="editingOrder && !selectionMode"
                      @dragstart="editingOrder && !selectionMode && onDragStart($event, item.key)"
                      @dragenter="editingOrder && !selectionMode && onDragEnter($event, item.key)"
                      @dragover="editingOrder && !selectionMode && onDragOver($event, item.key)"
                      @dragleave="editingOrder && !selectionMode && onDragLeave"
                      @drop="editingOrder && !selectionMode && onDrop($event, item.key)"
                      @dragend="editingOrder && !selectionMode && onDragEnd"
                      :class="[
                        'flex items-center gap-3 p-3 rounded-lg border hover:bg-muted/50 transition-colors group',
                        editingOrder && !selectionMode ? 'cursor-grab' : (selectionMode ? 'cursor-pointer' : '') ,
                        draggedId === String(item.key) ? 'opacity-40' : '',
                        dragOverId === String(item.key) && draggedId !== String(item.key) ? 'ring-2 ring-primary' : ''
                      ]"
                    >
                      <template v-if="item.type === 'existing'">
                        <FileText
                          class="w-7 h-7 text-muted-foreground flex-shrink-0 cursor-pointer"
                          @click="selectionMode ? toggleSelectItem('plantas', item.key) : (!editingOrder && openMediaModal(plantas.findIndex((i: any) => i.id === item.data.id)))"
                        />
                        <div v-if="selectionMode" class="absolute top-1 right-1 z-30">
                          <Checkbox :model-value="isItemSelected('plantas', item.key)" @update:modelValue="(val: boolean) => toggleSelectItem('plantas', item.key, val)" variant="solid" aria-label="Selecionar planta" />
                        </div>
                          <div class="flex-1 min-w-0 cursor-pointer" @click="!editingOrder && openMediaModal(plantas.findIndex((i: any) => i.id === item.data.id))">
                          <p class="font-medium text-sm truncate">{{ item.data.original_name || 'Planta ' + item.data.id }}</p>
                          <p class="text-xs text-muted-foreground">{{ item.data.mime_type || item.data.type }}</p>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                          <Button class="p-1.5 text-destructive hover:bg-destructive/10 rounded-full opacity-0 group-hover:opacity-100 transition-opacity" @click="handleDeleteMedia('plantas', item.data.id)"><Trash2 class="w-4 h-4" /></button>
                          <GripVertical v-if="editingOrder" class="w-4 h-4 text-muted-foreground opacity-0 group-hover:opacity-60 transition-opacity" />
                        </div>
                      </template>
                      <template v-else>
                        <FileText class="w-7 h-7 text-warning flex-shrink-0" />
                        <div class="flex-1 min-w-0">
                          <p class="font-medium text-sm truncate">{{ item.data.file.name }}</p>
                          <p class="text-xs text-muted-foreground">{{ mediaStore.formatFileSize(item.data.file.size) }}</p>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                          <Button class="p-1.5 text-destructive hover:bg-destructive/10 rounded-full opacity-0 group-hover:opacity-100 transition-opacity" @click="handleRemovePending(item.data.id)"><X class="w-4 h-4" /></button>
                          <GripVertical class="w-4 h-4 text-muted-foreground opacity-0 group-hover:opacity-60 transition-opacity" />
                        </div>
                      </template>
                    </div>

                    <div v-if="combinedPlantas.length === 0" class="text-center py-6 text-muted-foreground text-sm">Nenhuma planta cadastrada.</div>
                  </div>

                  <!-- Upload zone for plantas -->
                  <div class="mt-3 rounded-lg border-2 border-dashed border-muted-foreground/30 hover:border-primary/50 transition-colors">
                    <ImovelUploadZone media-type="planta" :multiple="true" @files-selected="handleFilesSelected" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Descrição -->
            <div v-if="imovelData.descricao" class="bg-card rounded-xl border p-4">
              <h2 class="text-lg font-semibold mb-3">Descrição</h2>
              <p class="text-muted-foreground whitespace-pre-line">{{ imovelData.descricao }}</p>
            </div>

            <!-- Características -->
            <div class="bg-card rounded-xl border p-4">
              <Button variant="sectionAlt" @click="toggleSection('caracteristicas')">
                <h2 class="text-lg font-semibold">Características</h2>
                <ChevronDown class="w-5 h-5 text-muted-foreground transition-transform" :class="{ 'rotate-180': expandedSections.caracteristicas }" />
              </Button>

              <div v-show="expandedSections.caracteristicas" class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div v-if="imovelData.caracteristicas?.quartos" class="flex items-center gap-2">
                  <BedDouble class="w-5 h-5 text-muted-foreground" />
                  <span>{{ imovelData.caracteristicas.quartos }} quarto(s)</span>
                </div>
                <div v-if="imovelData.caracteristicas?.suites" class="flex items-center gap-2">
                  <BedDouble class="w-5 h-5 text-muted-foreground" />
                  <span>{{ imovelData.caracteristicas.suites }} suíte(s)</span>
                </div>
                <div v-if="imovelData.caracteristicas?.banheiros" class="flex items-center gap-2">
                  <Bath class="w-5 h-5 text-muted-foreground" />
                  <span>{{ imovelData.caracteristicas.banheiros }} banheiro(s)</span>
                </div>
                <div v-if="imovelData.caracteristicas?.vagas" class="flex items-center gap-2">
                  <Car class="w-5 h-5 text-muted-foreground" />
                  <span>{{ imovelData.caracteristicas.vagas }} vaga(s)</span>
                </div>
                <div v-if="imovelData.caracteristicas?.area_total" class="flex items-center gap-2">
                  <Maximize class="w-5 h-5 text-muted-foreground" />
                  <span>{{ imovelData.caracteristicas.area_total }} m² total</span>
                </div>
                <div v-if="imovelData.caracteristicas?.area_construida" class="flex items-center gap-2">
                  <Home class="w-5 h-5 text-muted-foreground" />
                  <span>{{ imovelData.caracteristicas.area_construida }} m² construída</span>
                </div>
                <div v-if="imovelData.caracteristicas?.ano_construcao" class="flex items-center gap-2">
                  <Calendar class="w-5 h-5 text-muted-foreground" />
                  <span>Ano {{ imovelData.caracteristicas.ano_construcao }}</span>
                </div>
                <div v-if="imovelData.caracteristicas?.mobilia" class="flex items-center gap-2">
                  <Home class="w-5 h-5 text-muted-foreground" />
                  <span>{{ imovelData.caracteristicas.mobilia }}</span>
                </div>
                <div v-if="imovelData.caracteristicas?.varanda" class="flex items-center gap-2">
                  <Check class="w-5 h-5 text-green-600" />
                  <span>Com varanda</span>
                </div>
              </div>

              <!-- Itens -->
              <div v-if="imovelData.caracteristicas?.itens?.length" class="mt-4">
                <h3 class="text-sm font-medium mb-2">Itens do Imóvel</h3>
                <div class="flex flex-wrap gap-2">
                  <span v-for="item in imovelData.caracteristicas.itens" :key="item" class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium">{{ item }}</span>
                </div>
              </div>

              <!-- Áreas de Lazer -->
              <div v-if="imovelData.caracteristicas?.areas_lazer" class="mt-4">
                <h3 class="text-sm font-medium mb-2">Áreas de Lazer</h3>
                <p class="text-muted-foreground">{{ imovelData.caracteristicas.areas_lazer }}</p>
              </div>
            </div>

            <!-- Localização -->
            <div class="bg-card rounded-xl border p-4">
              <Button variant="sectionAlt" @click="toggleSection('localizacao')">
                <h2 class="text-lg font-semibold">Localização</h2>
                <ChevronDown class="w-5 h-5 text-muted-foreground transition-transform" :class="{ 'rotate-180': expandedSections.localizacao }" />
              </Button>

              <div v-show="expandedSections.localizacao" class="mt-4 grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-muted-foreground">CEP</span>
                  <p class="font-medium">{{ imovelData.endereco?.cep || '—' }}</p>
                </div>
                <div>
                  <span class="text-muted-foreground">Endereço</span>
                  <p class="font-medium">{{ imovelData.endereco?.endereco || '—' }}</p>
                </div>
                <div>
                  <span class="text-muted-foreground">Bairro</span>
                  <p class="font-medium">{{ imovelData.endereco?.bairro || '—' }}</p>
                </div>
                <div>
                  <span class="text-muted-foreground">Cidade / Estado</span>
                  <p class="font-medium">{{ imovelData.endereco?.cidade || '—' }} / {{ imovelData.endereco?.estado || '—' }}</p>
                </div>
                <div v-if="imovelData.endereco?.complemento">
                  <span class="text-muted-foreground">Complemento</span>
                  <p class="font-medium">{{ imovelData.endereco.complemento }}</p>
                </div>
                <div v-if="imovelData.endereco?.referencia">
                  <span class="text-muted-foreground">Referência</span>
                  <p class="font-medium">{{ imovelData.endereco.referencia }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar (1/3) -->
          <div class="space-y-6">

            <!-- Card de Valores -->
            <div class="bg-card rounded-xl border p-4">
              <h2 class="text-lg font-semibold mb-4">Valores</h2>
              <div class="space-y-3">
                <div v-if="imovelData.valores?.valor_venda" class="flex justify-between items-center">
                  <span class="text-muted-foreground">Venda</span>
                  <span class="text-xl font-bold text-primary">{{ formatCurrency(imovelData.valores.valor_venda) }}</span>
                </div>
                <div v-if="imovelData.valores?.valor_locacao" class="flex justify-between items-center">
                  <span class="text-muted-foreground">Locação</span>
                  <span class="text-xl font-bold text-primary">{{ formatCurrency(imovelData.valores.valor_locacao) }}/mês</span>
                </div>
                <hr v-if="imovelData.valores?.valor_venda || imovelData.valores?.valor_locacao" class="my-3" />
                <div v-if="imovelData.valores?.valor_condominio" class="flex justify-between text-sm">
                  <span class="text-muted-foreground">Condomínio</span>
                  <span>{{ formatCurrency(imovelData.valores.valor_condominio) }}/mês</span>
                </div>
                <div v-if="imovelData.valores?.valor_iptu" class="flex justify-between text-sm">
                  <span class="text-muted-foreground">IPTU</span>
                  <span>{{ formatCurrency(imovelData.valores.valor_iptu) }}/ano</span>
                </div>
                <div class="flex gap-2 mt-4 flex-wrap">
                  <span v-if="imovelData.valores?.aceita_financiamento" class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium text-green-600 border-green-600">
                    <Check class="w-3 h-3 mr-1" /> Aceita Financiamento
                  </span>
                  <span v-if="imovelData.valores?.aceita_permuta" class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium text-blue-600 border-blue-600">
                    <Check class="w-3 h-3 mr-1" /> Aceita Permuta
                  </span>
                </div>
              </div>
            </div>

            <!-- Card de Informações -->
            <div class="bg-card rounded-xl border p-4">
              <h2 class="text-lg font-semibold mb-4">Informações</h2>
              <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Status</span>
                  <span class="inline-flex items-center rounded-full bg-primary text-primary-foreground px-2.5 py-0.5 text-xs font-medium">{{ imovelData.status || 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Categoria</span>
                  <span>{{ imovelData.categoria || 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Condição</span>
                  <span>{{ imovelData.condicao || 'N/A' }}</span>
                </div>
                <div v-if="imovelData.finalidade" class="flex justify-between">
                  <span class="text-muted-foreground">Finalidade</span>
                  <span>{{ imovelData.finalidade }}</span>
                </div>
                <div v-if="imovelData.exclusividade" class="flex justify-between">
                  <span class="text-muted-foreground">Exclusividade</span>
                  <span class="inline-flex items-center rounded-full bg-muted text-muted-foreground px-2.5 py-0.5 text-xs font-medium">Sim</span>
                </div>
                <div v-if="imovelData.created_at" class="flex justify-between">
                  <span class="text-muted-foreground">Cadastrado em</span>
                  <span>{{ imovelData.created_at }}</span>
                </div>
              </div>
            </div>

            <!-- Card de Proprietário (colapsável) -->
            <div class="bg-card rounded-xl border p-4">
              <Button variant="sectionAlt" @click="toggleSection('proprietario')">
                <h2 class="text-lg font-semibold">Proprietário</h2>
                <ChevronDown class="w-5 h-5 text-muted-foreground transition-transform" :class="{ 'rotate-180': expandedSections.proprietario }" />
              </Button>

              <div v-show="expandedSections.proprietario" class="mt-4 space-y-2 text-sm">
                <div v-if="imovelData.proprietario?.nome" class="flex justify-between">
                  <span class="text-muted-foreground">Nome</span>
                  <span>{{ imovelData.proprietario.nome }}</span>
                </div>
                <div v-if="imovelData.proprietario?.telefone" class="flex justify-between">
                  <span class="text-muted-foreground">Telefone</span>
                  <span>{{ imovelData.proprietario.telefone }}</span>
                </div>
                <div v-if="imovelData.proprietario?.email" class="flex justify-between">
                  <span class="text-muted-foreground">Email</span>
                  <span class="truncate ml-2">{{ imovelData.proprietario.email }}</span>
                </div>
                <div v-if="imovelData.proprietario?.documento" class="flex justify-between">
                  <span class="text-muted-foreground">Documento</span>
                  <span>{{ imovelData.proprietario.documento }}</span>
                </div>
                <div v-if="!imovelData.proprietario?.nome && !imovelData.proprietario?.telefone && !imovelData.proprietario?.email" class="text-muted-foreground">
                  Dados do proprietário não informados
                </div>
              </div>
            </div>

            <!-- Anúncios vinculados -->
            <div v-if="imovelData.listings?.length" class="bg-card rounded-xl border p-4">
              <h2 class="text-lg font-semibold mb-4">Anúncios</h2>
              <div class="space-y-2">
                <Link
                  v-for="listing in imovelData.listings"
                  :key="listing.id"
                  :href="route('admin.corretor.listings.show', listing.id)"
                  class="flex items-center justify-between p-2 rounded-lg hover:bg-muted/50 transition-colors"
                >
                  <span class="text-sm font-medium">Anúncio #{{ listing.id }}</span>
                  <span
                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="listing.anuncio_ativo ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'"
                  >
                    {{ listing.anuncio_ativo ? 'Ativo' : 'Inativo' }}
                  </span>
                </Link>
              </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-card rounded-xl border p-4">
              <h2 class="text-lg font-semibold mb-4">Mídia</h2>
              <div class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                  <span class="flex items-center gap-2 text-muted-foreground"><ImageIcon class="w-4 h-4" /> Imagens</span>
                  <span class="font-medium">{{ imagens.length }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="flex items-center gap-2 text-muted-foreground"><Video class="w-4 h-4" /> Vídeos</span>
                  <span class="font-medium">{{ videos.length }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="flex items-center gap-2 text-muted-foreground"><FileText class="w-4 h-4" /> Plantas</span>
                  <span class="font-medium">{{ plantas.length }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="flex items-center gap-2 text-muted-foreground"><FileText class="w-4 h-4" /> Autorizações</span>
                  <span class="font-medium">{{ autorizacoes.length }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modais -->
    <ImovelMediaModal
      :show="mediaModalOpen && selectedMedia !== null"
      :media="selectedMedia"
      @close="closeMediaModal"
    />
    <ImovelDetailsModal
      :open="detailsModalOpen"
      :imovel="imovelData"
      :start-editing="detailsStartEditing"
      @close="closeDetailsModal"
      @updated="handleImovelUpdated"
    />
  </AuthLayout>
</template>

<style scoped>
/* ===== Carrossel Principal ===== */
.carousel-main {
  width: 100%;
  position: relative;
}

.carousel-main :deep(.swiper-slide) {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Botões de navegação */
.carousel-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0, 0, 0, 0.45);
  border: none;
  border-radius: 50%;
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background-color 0.2s ease;
  z-index: 10;
  font-size: 1.25rem;
  color: #fff;
  backdrop-filter: blur(4px);
}

.carousel-nav-btn:hover {
  background: rgba(0, 0, 0, 0.7);
}

.carousel-prev {
  left: 0.75rem;
}

.carousel-next {
  right: 0.75rem;
}

/* ===== Thumbnails ===== */
.carousel-thumbs {
  width: 100%;
}

.carousel-thumbs :deep(.swiper-slide) {
  aspect-ratio: 1;
  opacity: 0.5;
  transition: opacity 0.2s ease;
  border-radius: 0.5rem;
  overflow: hidden;
}

.carousel-thumbs :deep(.swiper-slide-thumb-active) {
  opacity: 1;
}

.carousel-thumbs :deep(.swiper-slide-thumb-active img),
.carousel-thumbs :deep(.swiper-slide-thumb-active > div) {
  border-color: hsl(var(--primary)) !important;
}

/* Responsive */
@media (max-width: 768px) {
  .carousel-nav-btn {
    width: 2rem;
    height: 2rem;
    font-size: 1rem;
  }

  .carousel-prev {
    left: 0.5rem;
  }

  .carousel-next {
    right: 0.5rem;
  }
}
</style>
