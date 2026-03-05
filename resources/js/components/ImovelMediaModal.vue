<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { ZoomIn, ZoomOut, Download, RotateCw, Maximize2, X } from 'lucide-vue-next'

const props = defineProps<{ media: { url: string | null, type: string, name?: string } | null, show: boolean }>()
const emit = defineEmits<{ (e: 'close'): void }>()

const scale = ref(1)
const rotation = ref(0)
const pos = ref({ x: 0, y: 0 })
const dragging = ref(false)
const dragStart = ref({ x: 0, y: 0 })
const container = ref<HTMLElement | null>(null)
const imgElement = ref<HTMLImageElement | null>(null)
const pinchStartDistance = ref<number | null>(null)
const pinchInitialScale = ref(1)

const canZoomIn = computed(() => scale.value < 6)
const canZoomOut = computed(() => scale.value > 1)

function close() { 
  setScale(1)
  rotation.value = 0
  emit('close') 
}

function zoomIn() {
  if (canZoomIn.value) setScale(Math.min(6, scale.value + 0.25))
}

function zoomOut() {
  if (canZoomOut.value) setScale(Math.max(1, scale.value - 0.25))
}

function resetZoom() {
  setScale(1)
  rotation.value = 0
}

function setScale(newScale: number) {
  const clamped = Math.max(1, Math.min(6, +newScale.toFixed(2)))
  scale.value = clamped
  if (Math.abs(clamped - 1) < 0.001) {
    pos.value = { x: 0, y: 0 }
    dragging.value = false
  }
}

function rotateLeft() {
  rotation.value = (rotation.value - 90 + 360) % 360
}

function rotateRight() {
  rotation.value = (rotation.value + 90) % 360
}

function downloadMedia() {
  if (!props.media?.url) return
  const link = document.createElement('a')
  link.href = props.media.url
  link.download = props.media.name || 'media'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function onWheel(e: WheelEvent) {
  if (!props.media || props.media.type !== 'image') return
  e.preventDefault()
  const delta = e.deltaY > 0 ? -0.1 : 0.1
  setScale(scale.value + delta)
}

function onPointerDown(e: PointerEvent) {
  if (scale.value <= 1) return
  dragging.value = true
  dragStart.value = { x: e.clientX - pos.value.x, y: e.clientY - pos.value.y }
  try { (e.target as Element).setPointerCapture(e.pointerId) } catch (err) { /* ignore */ }
}

function onPointerMove(e: PointerEvent) {
  if (!dragging.value) return
  pos.value = { x: e.clientX - dragStart.value.x, y: e.clientY - dragStart.value.y }
}

function onPointerUp(_e: PointerEvent) { dragging.value = false }

function onTouchStart(e: TouchEvent) {
  if (!props.media || props.media.type !== 'image') return
  if (e.touches && e.touches.length === 2) {
    const t0 = e.touches[0]
    const t1 = e.touches[1]
    pinchStartDistance.value = Math.hypot(t0.clientX - t1.clientX, t0.clientY - t1.clientY)
    pinchInitialScale.value = scale.value
    dragging.value = false
  }
}

function onTouchMove(e: TouchEvent) {
  if (!props.media || props.media.type !== 'image') return
  if (e.touches && e.touches.length === 2 && pinchStartDistance.value) {
    e.preventDefault()
    const t0 = e.touches[0]
    const t1 = e.touches[1]
    const dist = Math.hypot(t0.clientX - t1.clientX, t0.clientY - t1.clientY)
    const factor = dist / pinchStartDistance.value
    setScale(pinchInitialScale.value * factor)
  }
}

function onTouchEnd(_e: TouchEvent) {
  pinchStartDistance.value = null
  pinchInitialScale.value = scale.value
}

function toggleFullscreen() {
  if (!container.value) return
  // @ts-ignore
  if (!document.fullscreenElement) container.value.requestFullscreen?.()
  else document.exitFullscreen?.()
}

onMounted(() => { 
  document.addEventListener('wheel', onWheel, { passive: false })
})

onBeforeUnmount(() => { 
  document.removeEventListener('wheel', onWheel)
})
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 backdrop-blur-sm">
    <div ref="container" class="relative w-11/12 max-w-6xl max-h-[90vh] bg-card rounded-lg shadow-2xl overflow-hidden flex flex-col">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b bg-card">
        <div>
          <h3 class="font-semibold">{{ props.media?.name ?? 'Mídia' }}</h3>
          <p class="text-xs text-muted-foreground">{{ props.media?.type?.toUpperCase() || 'UNKNOWN' }}</p>
        </div>
        <div class="flex items-center gap-2">
          <!-- Zoom Controls (apenas para imagens) -->
          <template v-if="props.media?.type === 'image'">
            <button
              @click="zoomOut"
              :disabled="!canZoomOut"
              class="p-2 hover:bg-secondary disabled:opacity-50 rounded transition-colors"
              title="Zoom out"
            >
              <ZoomOut class="w-4 h-4" />
            </button>
            <span class="text-xs font-medium w-12 text-center">{{ Math.round(scale * 100) }}%</span>
            <button
              @click="zoomIn"
              :disabled="!canZoomIn"
              class="p-2 hover:bg-secondary disabled:opacity-50 rounded transition-colors"
              title="Zoom in"
            >
              <ZoomIn class="w-4 h-4" />
            </button>
            <button
              @click="resetZoom"
              class="p-2 hover:bg-secondary rounded transition-colors"
              title="Reset zoom"
            >
              <RotateCw class="w-4 h-4" />
            </button>
            <div class="w-px h-4 bg-border mx-1"></div>
            <button
              @click="rotateLeft"
              class="p-2 hover:bg-secondary rounded transition-colors"
              title="Rotate left"
            >
              ↺
            </button>
            <button
              @click="rotateRight"
              class="p-2 hover:bg-secondary rounded transition-colors"
              title="Rotate right"
            >
              ↻
            </button>
          </template>

          <!-- Common Controls -->
          <div class="w-px h-4 bg-border mx-1" v-if="props.media?.type === 'image'"></div>
          <button
            @click="downloadMedia"
            class="p-2 hover:bg-secondary rounded transition-colors"
            title="Baixar"
          >
            <Download class="w-4 h-4" />
          </button>
          <button
            @click="toggleFullscreen"
            class="p-2 hover:bg-secondary rounded transition-colors"
            title="Tela cheia"
          >
            <Maximize2 class="w-4 h-4" />
          </button>
          <button
            @click="close"
            class="p-2 hover:bg-destructive/10 text-destructive rounded transition-colors"
            title="Fechar"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 overflow-hidden flex items-center justify-center bg-muted/50 p-4">
        <template v-if="props.media && props.media.type === 'image'">
          <div class="touch-pan-y flex items-center justify-center w-full h-full">
            <img
              ref="imgElement"
              :src="props.media.url ?? undefined"
              :style="{ 
                transform: `translate(${pos.x}px, ${pos.y}px) scale(${scale}) rotate(${rotation}deg)`, 
                cursor: scale > 1 ? 'grab' : 'auto',
                transition: dragging ? 'none' : 'transform 0.2s'
              }"
              draggable="false"
              @pointerdown="onPointerDown"
              @pointermove="onPointerMove"
              @pointerup="onPointerUp"
              @touchstart="onTouchStart"
              @touchmove="onTouchMove"
              @touchend="onTouchEnd"
              @touchcancel="onTouchEnd"
              @pointercancel="onPointerUp"
              class="select-none max-w-full max-h-full object-contain"
            />
          </div>
        </template>

        <template v-else-if="props.media && props.media.type === 'pdf'">
          <iframe :src="props.media.url ?? undefined" class="w-full h-full border-0 rounded" />
        </template>

        <template v-else-if="props.media && props.media.type === 'video'">
          <video 
            controls 
            class="w-full h-full bg-black rounded max-w-full max-h-full object-contain" 
            :src="props.media.url ?? undefined"
          ></video>
        </template>

        <template v-else>
          <div class="text-muted-foreground text-center">
            <p class="text-sm">Nenhuma mídia selecionada</p>
          </div>
        </template>
      </div>

      <!-- Footer Info -->
      <div v-if="props.media" class="p-3 border-t bg-card text-xs text-muted-foreground flex items-center justify-between">
        <span>{{ props.media.url ? 'Modo: ' + (scale > 1 ? 'Zoom ativado' : 'Zoom normal') : '' }}</span>
        <span v-if="props.media.type === 'image'">Scroll para zoom • Arraste para mover (com zoom)</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.touch-pan-y { 
  touch-action: pan-y; 
}
</style>
