<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'

let datepickerIdCounter = 0
const instanceId = ++datepickerIdCounter

const props = withDefaults(defineProps<{
  modelValue?: string | null
  placeholder?: string
  mode?: 'date' | 'datetime' | 'time'
}>(), {
  modelValue: null,
  placeholder: 'Selecione uma data',
  mode: 'date',
})

const emit = defineEmits<{
  (e: 'update:modelValue', v: string | null): void
}>()

const open = ref(false)
const view = ref(new Date())

function parseModelValue(v?: string | null): Date | null {
  if (!v) return null
  // if value is YYYY-MM-DD treat as local date (avoid new Date(str) UTC parsing)
  if (/^\d{4}-\d{2}-\d{2}$/.test(v)) {
    const [y, m, d] = v.split('-').map(Number)
    return new Date(y, m - 1, d)
  }
  const parsed = new Date(v)
  if (isNaN(parsed.getTime())) return null
  return parsed
}

const selected = ref<Date | null>(parseModelValue(props.modelValue))

watch(() => props.modelValue, (v: string | null) => {
  selected.value = parseModelValue(v)
})

function formatDate(d: Date | null) {
  if (!d) return ''
  return d.toLocaleDateString('pt-BR')
}

function formatDisplay() {
  // date only
  if (props.mode === 'date') {
    return formatDate(selected.value)
  }

  // time only (modelValue like HH:MM) - show as-is
  if (props.mode === 'time') {
    if (props.modelValue && /^\d{2}:\d{2}$/.test(props.modelValue)) return props.modelValue
    return ''
  }

  return ''
}

function toISO(d: Date) {
  return d.toISOString().split('T')[0]
}

function startOfCalendar(d: Date) {
  const first = new Date(d.getFullYear(), d.getMonth(), 1)
  const day = first.getDay()
  const start = new Date(first)
  start.setDate(first.getDate() - day)
  return start
}

function daysForMonth(d: Date) {
  const start = startOfCalendar(d)
  const days: Date[] = []
  for (let i = 0; i < 42; i++) {
    const cd = new Date(start)
    cd.setDate(start.getDate() + i)
    days.push(cd)
  }
  return days
}

const monthDays = computed(() => daysForMonth(view.value))
const monthTitle = computed(() => new Intl.DateTimeFormat('pt-BR', { month: 'long', year: 'numeric' }).format(view.value))

function prev() {
  const d = new Date(view.value)
  d.setMonth(d.getMonth() - 1)
  view.value = d
}

function next() {
  const d = new Date(view.value)
  d.setMonth(d.getMonth() + 1)
  view.value = d
}

function isSameDay(a: Date, b: Date) {
  return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate()
}

function choose(day: Date) {
  selected.value = new Date(day.getFullYear(), day.getMonth(), day.getDate())
  if (props.mode === 'date') {
    emit('update:modelValue', toISO(selected.value))
    open.value = false
  }
}

function applyDateTime() {
  if (!selected.value) return
  if (props.mode === 'date') {
    emit('update:modelValue', toISO(selected.value))
    open.value = false
  }
}

// close on outside click - improved with instance awareness
function handleDocClick(e: MouseEvent) {
  if (!open.value) return
  const target = e.target as HTMLElement | null
  if (!target) return
  
  // if click inside the root input area, ignore
  if (rootEl.value && rootEl.value.contains(target)) return
  
  // if click inside the popup (teleported to body), ignore
  const allPopups = document.querySelectorAll('[data-datepicker-popup]')
  for (const popup of allPopups) {
    if (popup.contains(target)) {
      return
    }
  }
  
  // click is truly outside - close this datepicker
  open.value = false
}

const rootEl = ref<HTMLElement | null>(null)
const popupEl = ref<HTMLElement | null>(null)
const popupStyle = ref<{ [k: string]: string }>({ position: 'fixed', top: '0px', left: '0px', minWidth: '220px', zIndex: '99999', pointerEvents: 'auto' })
let resizeObserver: ResizeObserver | null = null

function pickToday() {
  const now = new Date()
  view.value = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  selected.value = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  if (props.mode === 'date') {
    emit('update:modelValue', toISO(selected.value))
    open.value = false
  }
}

function positionPopup() {
  if (!rootEl.value || !popupEl.value) return
  const input = rootEl.value.querySelector('input') as HTMLElement | null
  const rect = input ? input.getBoundingClientRect() : rootEl.value.getBoundingClientRect()
  const popup = popupEl.value
  const width = popup.offsetWidth || 288
  const height = popup.offsetHeight || 300

  // viewport-based fixed positioning
  let left = rect.left
  let top = rect.bottom + 6

  // horizontal clamp
  if (left + width > window.innerWidth - 8) {
    left = Math.max(8, window.innerWidth - width - 8)
  }
  if (left < 8) left = 8

  // if popup taller than viewport -> clamp & enable internal scroll
  if (height > window.innerHeight - 16) {
    popupStyle.value.maxHeight = `${window.innerHeight - 16}px`
    popupStyle.value.overflowY = 'auto'
    top = 8
  } else {
    // remove any previous constraints
    delete popupStyle.value.maxHeight
    delete popupStyle.value.overflowY

    // if it would overflow bottom, try opening above
    if (top + height > window.innerHeight - 8) {
      const aboveTop = rect.top - height - 6
      if (aboveTop >= 8) {
        top = aboveTop
      } else {
        // fallback: clamp so it fits
        top = Math.max(8, window.innerHeight - height - 8)
      }
    }
  }

  popupStyle.value.left = `${left}px`
  popupStyle.value.top = `${top}px`
  popupStyle.value.minWidth = `${rect.width}px`
}

onMounted(() => {
  document.addEventListener('click', handleDocClick)
  window.addEventListener('resize', positionPopup)
  window.addEventListener('scroll', positionPopup, true)
})

onUnmounted(() => {
  document.removeEventListener('click', handleDocClick)
  window.removeEventListener('resize', positionPopup)
  window.removeEventListener('scroll', positionPopup, true)
  if (resizeObserver) {
    resizeObserver.disconnect()
    resizeObserver = null
  }
})

watch(open, async (v: boolean) => {
  if (v) {
    await nextTick()
    positionPopup()

    // observe popup size changes (footer/timepicker may change height)
    if (popupEl.value && typeof window.ResizeObserver !== 'undefined') {
      resizeObserver = new ResizeObserver(() => positionPopup())
      resizeObserver.observe(popupEl.value)
    }
  } else {
    if (resizeObserver) {
      resizeObserver.disconnect()
      resizeObserver = null
    }
  }
})

// expose keyboard: Esc closes
function onKey(e: KeyboardEvent) {
  if (e.key === 'Escape' && open.value) {
    open.value = false
    e.stopPropagation()
  }
}
onMounted(() => document.addEventListener('keydown', onKey))
onUnmounted(() => document.removeEventListener('keydown', onKey))

</script>

<template>
  <div ref="rootEl" class="relative inline-block">
    <div class="flex items-center gap-2" @click="open = !open">
        <Input
          :placeholder="props.placeholder"
          :modelValue="formatDisplay()"
          readonly
          :class="[
            'px-3 py-2 rounded-md border border-[var(--border)] bg-[var(--card)] focus:outline-none cursor-pointer',
            'text-center',
            (props.mode === 'datetime' || props.mode === 'time') ? 'min-w-[160px]' : ''
          ]"
        />
      </div>

    <teleport to="body">
      <div v-if="open" ref="popupEl" :style="popupStyle" :data-datepicker-popup="instanceId" class="rounded shadow-lg bg-[var(--card)] border border-[var(--border)] origin-top-left" @click="(e) => e.stopPropagation()" @pointerdown="(e) => e.stopPropagation()">
      <div class="flex items-center justify-between p-2">
        <div class="flex items-center gap-1">
          <Button size="sm" variant="ghost" @click="prev">‹</Button>
          <Button size="sm" variant="ghost" @click="next">›</Button>
        </div>
        <div class="text-sm font-medium text-[var(--text-foreground)]">{{ monthTitle }}</div>
        <div class="text-sm">
          <Button size="sm" variant="ghost" @click="pickToday">Hoje</Button>
        </div>
      </div>

      <div class="grid grid-cols-7 gap-1 px-2 text-xs text-[var(--text-foreground)]">
        <div class="py-1 text-center">dom.</div>
        <div class="py-1 text-center">seg.</div>
        <div class="py-1 text-center">ter.</div>
        <div class="py-1 text-center">qua.</div>
        <div class="py-1 text-center">qui.</div>
        <div class="py-1 text-center">sex.</div>
        <div class="py-1 text-center">sáb.</div>
      </div>

      <div class="grid grid-cols-7 gap-1 p-2">
        <template v-for="(day, i) in monthDays" :key="i">
          <Button
            @click="choose(day)"
            variant="ghost"
            class="w-full min-h-[40px] text-left p-2 rounded transition-colors"
            :class="[
              (day.getMonth() !== view.getMonth()) ? 'opacity-40 cursor-default' : 'cursor-pointer',
              (selected && isSameDay(day, selected)) ? 'bg-[var(--primary)] text-[var(--primary-foreground)] font-semibold' : 'hover:bg-[var(--primary)]/20 hover:border-[var(--primary)] border border-transparent'
            ]"
          >
            <div class="text-sm">{{ day.getDate() }}</div>
          </Button>
        </template>
      </div>

      <!-- footer time controls removed - use Timepicker component instead -->
      </div>
    </teleport>
  </div>
</template>