<<<<<<< HEAD

<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue'
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import Modal from '@/components/ui/modals/Modal.vue'
import axios from 'axios'
import { usePage } from '@inertiajs/vue3'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import Checkbox from '@/components/ui/checkbox/Checkbox.vue'
import Datepicker from '@/components/ui/picker/Datepicker.vue'
import Timepicker from '@/components/ui/picker/Timepicker.vue'


interface EventItem {
  id: string;
  title: string;
  start: string;
  end?: string | null;
  allDay?: boolean;
}

const page = usePage()
const pp = page.props as any

const calendarRouteBase = computed(() => {
  const m = (page.props as { modulo?: string }).modulo
  if (m === 'clinica') return 'admin.clinica.calendar'
  if (m === 'corretor') return 'admin.corretor.calendar'
  return 'admin.ecommerce.calendar'
})

const today = new Date()
const current = ref(new Date(today.getFullYear(), today.getMonth(), 1))
const currentView = ref<'month' | 'week' | 'day'>('month')

function setView(v: 'month' | 'week' | 'day') {
  currentView.value = v
}

function goToToday() {
  const t = new Date()
  if (currentView.value === 'month') {
    current.value = new Date(t.getFullYear(), t.getMonth(), 1)
  } else if (currentView.value === 'week') {
    // set reference to today (weekDays will compute week start)
    current.value = new Date(t.getFullYear(), t.getMonth(), t.getDate())
  } else {
    current.value = new Date(t.getFullYear(), t.getMonth(), t.getDate())
  }
}

function isTodayActive() {
  const t = new Date()
  if (currentView.value === 'month') {
    return current.value.getFullYear() === t.getFullYear() && current.value.getMonth() === t.getMonth()
  }
  if (currentView.value === 'week') {
    const start = new Date(current.value)
    const weekStart = new Date(start)
    weekStart.setDate(start.getDate() - start.getDay())
    const weekEnd = new Date(weekStart)
    weekEnd.setDate(weekStart.getDate() + 6)
    const todayTime = new Date(t.getFullYear(), t.getMonth(), t.getDate()).getTime()
    const sTime = new Date(weekStart.getFullYear(), weekStart.getMonth(), weekStart.getDate()).getTime()
    const eTime = new Date(weekEnd.getFullYear(), weekEnd.getMonth(), weekEnd.getDate()).getTime()
    return todayTime >= sTime && todayTime <= eTime
  }
  if (currentView.value === 'day') {
    return isSameDay(current.value, t)
  }
  return false
}
const events = ref<EventItem[]>([])
const loading = ref(false)

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

const monthDays = computed(() => daysForMonth(current.value))

function weekDays(d: Date) {
  const start = new Date(d)
  const weekStart = new Date(start)
  weekStart.setDate(start.getDate() - start.getDay())
  const days: Date[] = []
  for (let i = 0; i < 7; i++) {
    const cd = new Date(weekStart)
    cd.setDate(weekStart.getDate() + i)
    days.push(cd)
  }
  return days
}

const displayedDays = computed(() => {
  if (currentView.value === 'month') return daysForMonth(current.value)
  if (currentView.value === 'week') return weekDays(current.value)
  return [new Date(current.value.getFullYear(), current.value.getMonth(), current.value.getDate())]
})

const monthTitle = computed(() => {
  const t = new Date()
  if (currentView.value === 'month') return new Intl.DateTimeFormat('pt-BR', { month: 'long', year: 'numeric' }).format(current.value)
  if (currentView.value === 'week') {
    const w = weekDays(current.value)
    const first = w[0]
    const last = w[w.length - 1]
    const fmt = new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: 'short' })
    return `${fmt.format(first)} — ${fmt.format(last)}`
  }
  // day
  return new Intl.DateTimeFormat('pt-BR', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' }).format(current.value)
})

const gridCols = computed(() => (currentView.value === 'month' || currentView.value === 'week') ? 'grid-cols-7' : 'grid-cols-1')

const headerLabels = computed(() => {
  const fmt = new Intl.DateTimeFormat('pt-BR', { weekday: 'short' })
  if (currentView.value === 'month') {
    const w = weekDays(current.value)
    return w.map((d: Date) => fmt.format(d))
  }
  return displayedDays.value.map((d: Date) => fmt.format(d))
})

const hours = computed(() => Array.from({ length: 24 }, (_, i) => i))

function formatHour(h: number) {
  return String(h).padStart(2, '0') + ':00'
}

function dayCellClasses(day: Date) {
  const inRange = isDateInRange(day)
  const notCurrentMonth = currentView.value === 'month' && day.getMonth() !== current.value.getMonth()
  const todayFlag = isSameDay(day, new Date())

  const baseMin = currentView.value === 'month' ? 'min-h-[8rem]' : 'min-h-screen'

  return [
    `p-2 ${baseMin} rounded user-select-none transition-colors day-cell`,
    inRange ? 'in-range' : '',
    notCurrentMonth ? 'not-current-month' : '',
    todayFlag ? 'today' : '',
    isDragging.value && inRange ? 'cursor-grabbing' : 'cursor-grab',
  ]
}

async function loadEvents() {
  loading.value = true
  try {
    const res = await axios.get<EventItem[]>(route(`${calendarRouteBase.value}.events`))
    events.value = res.data.map((e: any) => ({ id: e.id, title: e.summary, start: e.start, end: e.end ?? null, allDay: !!e.allDay, label: e.label ?? null, labelColor: e.labelColor ?? null }))
  } catch (err) {
    console.error('Failed to load events', err)
    events.value = []
  } finally {
    loading.value = false
  }
}

function prevMonth() {
  if (currentView.value === 'month') {
    const d = new Date(current.value)
    d.setMonth(d.getMonth() - 1)
    current.value = new Date(d.getFullYear(), d.getMonth(), 1)
  } else if (currentView.value === 'week') {
    const d = new Date(current.value)
    d.setDate(d.getDate() - 7)
    current.value = new Date(d.getFullYear(), d.getMonth(), d.getDate())
  } else {
    const d = new Date(current.value)
    d.setDate(d.getDate() - 1)
    current.value = new Date(d.getFullYear(), d.getMonth(), d.getDate())
  }
}

function nextMonth() {
  if (currentView.value === 'month') {
    const d = new Date(current.value)
    d.setMonth(d.getMonth() + 1)
    current.value = new Date(d.getFullYear(), d.getMonth(), 1)
  } else if (currentView.value === 'week') {
    const d = new Date(current.value)
    d.setDate(d.getDate() + 7)
    current.value = new Date(d.getFullYear(), d.getMonth(), d.getDate())
  } else {
    const d = new Date(current.value)
    d.setDate(d.getDate() + 1)
    current.value = new Date(d.getFullYear(), d.getMonth(), d.getDate())
  }
}

function isSameDay(a: Date, b: Date) {
  return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate()
}

function parseDateStr(v?: string | null) {
  if (!v) return null
  if (/^\d{4}-\d{2}-\d{2}$/.test(v)) {
    const [y, m, d] = v.split('-').map(Number)
    return new Date(y, m - 1, d)
  }
  const d = new Date(v as any)
  if (isNaN(d.getTime())) return null
  return d
}

function formatDateLocal(d?: Date | null) {
  if (!d) return ''
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

function ensureDateTimeString(v: string | null | undefined, defaultHour = '09', defaultMinute = '00') {
  if (!v) return null
  // already datetime like YYYY-MM-DDTHH:MM
  if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(v)) return v
  // date only YYYY-MM-DD
  if (/^\d{4}-\d{2}-\d{2}$/.test(v)) return `${v}T${defaultHour}:${defaultMinute}`
  // try parse as Date
  const d = new Date(v)
  if (isNaN(d.getTime())) return null
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}T${defaultHour}:${defaultMinute}`
}

// Helper to convert date-only to datetime, preserving existing time if available
function toDateTimePreservingTime(v: string | null | undefined, defaultHour = '09', defaultMinute = '00'): string | null {
  if (!v) return null
  // Already datetime - return as-is
  if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(v)) return v
  // Date only - add default time
  if (/^\d{4}-\d{2}-\d{2}$/.test(v)) return `${v}T${defaultHour}:${defaultMinute}`
  return null
}

function stripTimeFromDateTime(v: string | null | undefined) {
  if (!v) return null
  const m = v.match(/^(\d{4}-\d{2}-\d{2})/) 
  return m ? m[1] : v
}

// Return time part as "HH:MM" or null
function timePartFromDateTime(v: string | null | undefined): string | null {
  if (!v) return null
  const t = extractTimeFromDatetime(v)
  const hh = String(t.hour).padStart(2, '0')
  const mm = String(t.minute).padStart(2, '0')
  // if both zero and original had no time, consider null
  if (/^\d{4}-\d{2}-\d{2}$/.test(v) && !(t.hour || t.minute)) return null
  return `${hh}:${mm}`
}

function combineDateAndTime(dateStr: string | null | undefined, timeStr: string | null | undefined): string | null {
  if (!dateStr) return null
  const dateOnly = stripTimeFromDateTime(dateStr) || dateStr
  if (!timeStr) return dateOnly
  return `${dateOnly}T${timeStr}`
}

function onStartDateUpdate(v: string | null) {
  const time = timePartFromDateTime(formData.value.start)
  if (v && /^\d{4}-\d{2}-\d{2}$/.test(v)) {
    formData.value.start = time ? `${v}T${time}` : v
  } else {
    formData.value.start = v || ''
  }
}

function onEndDateUpdate(v: string | null) {
  const time = timePartFromDateTime(formData.value.end)
  if (v && /^\d{4}-\d{2}-\d{2}$/.test(v)) {
    formData.value.end = time ? `${v}T${time}` : v
  } else {
    formData.value.end = v || ''
  }
}

function onStartTimeUpdate(v: string | null) {
  if (!v) return
  const dateOnly = stripTimeFromDateTime(formData.value.start) || formatDateLocal(new Date())
  formData.value.start = `${dateOnly}T${v}`
}

function onEndTimeUpdate(v: string | null) {
  if (!v) return
  const dateOnly = stripTimeFromDateTime(formData.value.end) || stripTimeFromDateTime(formData.value.start) || formatDateLocal(new Date())
  formData.value.end = `${dateOnly}T${v}`
}

function extractTimeFromDatetime(dateStr: string | null | undefined): { hour: number; minute: number } {
  if (!dateStr) return { hour: 0, minute: 0 }
  
  // Handle ISO-like datetimes without timezone first (YYYY-MM-DDTHH:MM or with seconds)
  // This is the most common format and should always use the explicit HH:MM values
  const isoMatch = dateStr.match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(?::\d{2})?/)
  if (isoMatch) {
    const hour = parseInt(isoMatch[4], 10)
    const minute = parseInt(isoMatch[5], 10)
    console.log(`[extractTimeFromDatetime] ISO format: ${dateStr} => ${hour}:${minute}`)
    return { hour, minute }
  }

  // Try with explicit timezone (Z or +HH:MM / -HH:MM)
  try {
    if (/[Zz]|[+-]\d{2}:\d{2}$/.test(dateStr)) {
      const d = new Date(dateStr)
      if (!isNaN(d.getTime())) {
        const h = d.getHours()
        const m = d.getMinutes()
        console.log(`[extractTimeFromDatetime] With TZ: ${dateStr} => ${h}:${m}`)
        return { hour: h, minute: m }
      }
    }
  } catch (err) {
    // fallthrough
  }

  // Fallback: look for HH:MM but make sure it's in the time part (after T or space)
  // This avoids matching the day in DD/MM/YYYY format
  const timeMatch = dateStr.match(/[T\s](\d{2}):(\d{2})/)
  if (timeMatch) {
    const h = parseInt(timeMatch[1], 10)
    const m = parseInt(timeMatch[2], 10)
    console.log(`[extractTimeFromDatetime] Time after T/space: ${dateStr} => ${h}:${m}`)
    return { hour: h, minute: m }
  }

  console.log(`[extractTimeFromDatetime] No match for: ${dateStr}`)
  return { hour: 0, minute: 0 }
}

function getEventPosition(ev: EventItem, day: Date): { topPercent: number; heightPercent: number } {
  try {
    const startStr = ev.start as string | undefined
    const endStr = (ev.end as string | undefined) || startStr
    
    if (!startStr || !endStr) return { topPercent: 0, heightPercent: 0 }

    // Parse start and end times
    const startTime = extractTimeFromDatetime(startStr)
    const endTime = extractTimeFromDatetime(endStr)

    // Calculate position as percentage of 24 hours
    const startMinutes = startTime.hour * 60 + startTime.minute
    const endMinutes = endTime.hour * 60 + endTime.minute
    
    const topPercent = (startMinutes / (24 * 60)) * 100
    const durationMinutes = endMinutes - startMinutes
    const heightPercent = (durationMinutes / (24 * 60)) * 100

    return {
      topPercent: Math.max(0, topPercent),
      heightPercent: Math.max(5, heightPercent),
    }
  } catch (err) {
    return { topPercent: 0, heightPercent: 0 }
  }
}

function eventsForDay(day: Date) {
  return events.value.filter((ev: EventItem) => {
    try {
      const s = parseDateStr(ev.start as any)
      if (!s) return false
      const e = parseDateStr(ev.end as any) ?? s
      const dayTime = new Date(day.getFullYear(), day.getMonth(), day.getDate()).getTime()
      const startTime = new Date(s.getFullYear(), s.getMonth(), s.getDate()).getTime()
      const endTime = new Date(e.getFullYear(), e.getMonth(), e.getDate()).getTime()
      return dayTime >= startTime && dayTime <= endTime
    } catch (err) { return false }
  })
}

function timedEventsForDay(day: Date) {
  return eventsForDay(day)
    .filter((ev: EventItem) => !ev.allDay)
    .slice()
    .sort((a: EventItem, b: EventItem) => {
      const ta = extractTimeFromDatetime(a.start as any)
      const tb = extractTimeFromDatetime(b.start as any)
      const ma = ta.hour * 60 + ta.minute
      const mb = tb.hour * 60 + tb.minute
      return ma - mb
    })
}

// Check if two time intervals overlap
function intervalsOverlap(startA: number, endA: number, startB: number, endB: number): boolean {
  return startA < endB && startB < endA
}

// Get minutes from midnight for an event start/end
function getEventMinutes(event: EventItem): { start: number; end: number } {
  if (!event.start) return { start: 0, end: 60 }
  
  const startTime = extractTimeFromDatetime(event.start as any)
  const startMinutes = startTime.hour * 60 + startTime.minute
  
  // Calculate end time: either from event.end or default to 1 hour later
  let endMinutes = startMinutes + 60
  
  if (event.end) {
    const endTime = extractTimeFromDatetime(event.end as any)
    endMinutes = endTime.hour * 60 + endTime.minute
    
    // If end time is same as start, set to 1 hour later
    if (endMinutes <= startMinutes) {
      endMinutes = startMinutes + 60
    }
  }
  
  return { start: startMinutes, end: endMinutes }
}

// Get all events that overlap with a given event on a specific day
function getOverlappingEvents(targetEvent: EventItem, day: Date): EventItem[] {
  const dayEvents = timedEventsForDay(day)
  const targetMinutes = getEventMinutes(targetEvent)
  
  return dayEvents.filter((ev: EventItem) => {
    const evMinutes = getEventMinutes(ev)
    return intervalsOverlap(targetMinutes.start, targetMinutes.end, evMinutes.start, evMinutes.end) || isSameEvent(ev, targetEvent)
  })
}

// Check if two events are the same
function isSameEvent(a: EventItem, b: EventItem): boolean {
  return a.id === b.id
}

// Compute layout columns for events on a day
// Returns map of event ID to { column: number, totalColumns: number, left: string, width: string }
// Uses caching to avoid recalculation during same render cycle
const layoutCache = new Map<string, Map<string, { column: number; totalColumns: number; left: string; width: string }>>()

function computeEventLayout(day: Date): Map<string, { column: number; totalColumns: number; left: string; width: string }> {
  const dayCacheKey = `${day.getFullYear()}-${day.getMonth()}-${day.getDate()}`
  
  // Return cached result if available
  if (layoutCache.has(dayCacheKey)) {
    return layoutCache.get(dayCacheKey)!
  }
  
  const dayEvents = timedEventsForDay(day)
  const layout = new Map<string, { column: number; totalColumns: number; left: string; width: string }>()
  const processed = new Set<string>()
  
  for (const event of dayEvents) {
    if (processed.has(event.id)) continue
    
    // Get all events overlapping with this one
    const overlapping = getOverlappingEvents(event, day)
    const totalColumns = overlapping.length
    
    // Sort overlapping events by start time for consistent column assignment
    overlapping.sort((a: EventItem, b: EventItem) => {
      const ta = extractTimeFromDatetime(a.start as any)
      const tb = extractTimeFromDatetime(b.start as any)
      const ma = ta.hour * 60 + ta.minute
      const mb = tb.hour * 60 + tb.minute
      return ma - mb
    })
    
    // Assign columns to overlapping events
    for (let col = 0; col < overlapping.length; col++) {
      const ev = overlapping[col]
      if (!layout.has(ev.id)) {
        const width = 100 / totalColumns
        const left = (col * width)
        layout.set(ev.id, {
          column: col,
          totalColumns,
          left: `${left}%`,
          width: `${width}%`,
        })
        processed.add(ev.id)
      }
    }
  }
  
  // Cache the result
  layoutCache.set(dayCacheKey, layout)
  
  return layout
}

function formatEventTime(ev: EventItem) {
  const t = extractTimeFromDatetime(ev.start as any)
  const hh = String(t.hour).padStart(2, '0')
  const mm = String(t.minute).padStart(2, '0')
  return `${hh}:${mm}`
}

function getSegmentType(ev: EventItem, day: Date) {
  const s = parseDateStr(ev.start as any)
  if (!s) return 'single'
  const e = parseDateStr(ev.end as any) ?? s
  if (s.getTime() === e.getTime()) return 'single'
  if (isSameDay(day, s)) return 'start'
  if (isSameDay(day, e)) return 'end'
  const dayTime = new Date(day.getFullYear(), day.getMonth(), day.getDate()).getTime()
  const startTime = new Date(s.getFullYear(), s.getMonth(), s.getDate()).getTime()
  const endTime = new Date(e.getFullYear(), e.getMonth(), e.getDate()).getTime()
  if (dayTime > startTime && dayTime < endTime) return 'middle'
  return 'single'
}

// Drag selection state
const dragStart = ref<Date | null>(null)
const dragEnd = ref<Date | null>(null)
const isDragging = ref(false)

// Modal state and handlers
const modalOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const formData = ref({
  id: '',
  summary: '',
  description: '',
  start: '',
  end: '',
  label: '',
  labelColor: '',
  allDay: false,
})

watch(() => formData.value.allDay, (allDay: boolean) => {
  if (allDay) {
    formData.value.start = stripTimeFromDateTime(formData.value.start) || ''
    formData.value.end = stripTimeFromDateTime(formData.value.end) || formData.value.start || ''
  } else {
    // Only add default time if start doesn't already have time
    if (formData.value.start && !/T\d{2}:\d{2}/.test(formData.value.start)) {
      formData.value.start = toDateTimePreservingTime(formData.value.start, '09', '00') || ''
    }
    // Only add default time if end doesn't already have time
    if (formData.value.end && !/T\d{2}:\d{2}/.test(formData.value.end)) {
      formData.value.end = toDateTimePreservingTime(formData.value.end, '17', '00') || formData.value.start || ''
    }
  }
})

function isDateInRange(day: Date): boolean {
  if (!dragStart.value || !dragEnd.value) return false
  const start = dragStart.value.getTime()
  const end = dragEnd.value.getTime()
  const dayTime = day.getTime()
  const minTime = Math.min(start, end)
  const maxTime = Math.max(start, end)
  return dayTime >= minTime && dayTime <= maxTime
}

function handleDayMouseDown(day: Date) {
  dragStart.value = new Date(day)
  dragEnd.value = new Date(day)
  isDragging.value = true
}

function handleDayMouseEnter(day: Date) {
  if (!isDragging.value || !dragStart.value) return
  dragEnd.value = new Date(day)
}

function handleDayMouseUp() {
  if (!isDragging.value || !dragStart.value || !dragEnd.value) {
    isDragging.value = false
    dragStart.value = null
    dragEnd.value = null
    return
  }

  isDragging.value = false
  const start = dragStart.value.getTime() <= dragEnd.value.getTime() ? dragStart.value : dragEnd.value
  const end = dragStart.value.getTime() <= dragEnd.value.getTime() ? dragEnd.value : dragStart.value

  formData.value = {
    id: '',
    summary: '',
    description: '',
    start: formatDateLocal(start),
    end: formatDateLocal(end),
    label: '',
    labelColor: '',
    allDay: true,
  }
  modalMode.value = 'create'
  modalOpen.value = true

  dragStart.value = null
  dragEnd.value = null
}

function openEditEventModal(ev: EventItem) {
  // Normalize start/end for the form depending on allDay
  const isAllDay = !!(ev as any).allDay
  let startVal = ev.start as string
  let endVal = (ev.end as string) || startVal
  if (isAllDay) {
    startVal = stripTimeFromDateTime(startVal) || startVal
    endVal = stripTimeFromDateTime(endVal) || endVal
  } else {
    // For datetime mode, preserve existing time - don't reset to defaults
    startVal = toDateTimePreservingTime(startVal, '09', '00') || startVal
    endVal = toDateTimePreservingTime(endVal, '17', '00') || startVal
  }

  formData.value = {
    id: ev.id,
    summary: ev.title,
    description: (ev as any).description ?? '',
    start: startVal,
    end: endVal,
    label: (ev as any).label ?? '',
    labelColor: (ev as any).labelColor ?? '',
    allDay: isAllDay,
  }
  modalMode.value = 'edit'
  modalOpen.value = true
}

function closeModal() {
  modalOpen.value = false
  dragStart.value = null
  dragEnd.value = null
  layoutCache.clear()
  formData.value = { id: '', summary: '', description: '', start: '', end: '', allDay: false }
}



async function saveEvent() {
  if (!formData.value.summary.trim()) {
    alert('Por favor, preencha o título do evento')
    return
  }

  try {
    if (modalMode.value === 'create') {
      await axios.post(route(`${calendarRouteBase.value}.store`), {
        summary: formData.value.summary,
        description: formData.value.description,
        start: formData.value.start,
        end: formData.value.end,
        allDay: formData.value.allDay,
        label: formData.value.label,
        labelColor: formData.value.labelColor,
      })
    } else if (modalMode.value === 'edit' && formData.value.id) {
      await axios.put(route(`${calendarRouteBase.value}.update`, formData.value.id), {
        summary: formData.value.summary,
        description: formData.value.description,
        start: formData.value.start,
        end: formData.value.end,
        allDay: formData.value.allDay,
        label: formData.value.label,
        labelColor: formData.value.labelColor,
      })
    }
    layoutCache.clear()
    closeModal()
    await loadEvents()
  } catch (err) {
    console.error('Error saving event:', err)
    alert('Erro ao salvar evento. Tente novamente.')
  }
}

async function deleteEvent() {
  if (!formData.value.id || !confirm('Tem certeza que deseja deletar este evento?')) return

  try {
    await axios.delete(route(`${calendarRouteBase.value}.destroy`, formData.value.id))
    layoutCache.clear()
    closeModal()
    await loadEvents()
  } catch (err) {
    console.error('Error deleting event:', err)
    alert('Erro ao deletar evento. Tente novamente.')
  }
}

onMounted(() => {
  loadEvents()
  // Add global mouse up listener to handle drag end
  document.addEventListener('mouseup', handleDayMouseUp)
})

onUnmounted(() => {
  document.removeEventListener('mouseup', handleDayMouseUp)
})
=======
<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue'
import { onMounted, onUnmounted, ref, nextTick, reactive } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'

interface CalendarEvent {
  id: string
  title: string
  start: string
  end: string
  description: string
  allDay: boolean
  calendar: string
}

// API shape for events returned by backend / Google
interface ApiCalendarEvent {
  id: string
  summary: string
}

interface GoogleCalendar {
  id: string
  summary: string
}

const calendarEl = ref<HTMLElement | null>(null)
let calendarInstance: any = null
const events = ref<CalendarEvent[]>([])
// In browser environment setInterval returns a number, avoid NodeJS namespace
let refreshInterval: number | null = null
let visibilityHandler: (() => void) | null = null
const page = usePage()
const pp = page.props as any

// Settings modal state
const settingsModalVisible = ref(false)
const settingsSaving = ref(false)
const settingsCalendars = ref<GoogleCalendar[]>([])
const settingsConnected = ref(false)
const settings = reactive({ calendar_id: '', timezone: 'America/Sao_Paulo', locale: 'pt_BR' })

async function openSettingsModal() {
  try {
    const res = await axios.get(route('admin.calendar.settings.data'))
    const d = res.data
    settingsCalendars.value = d.google?.calendars || []
    settingsConnected.value = !!d.google?.connected
    const s = d.settings || {}
    settings.calendar_id = s?.calendar_id ?? ''
    settings.timezone = s?.timezone ?? 'America/Sao_Paulo'
    settings.locale = s?.locale ?? 'pt_BR'
    settingsModalVisible.value = true
  } catch (e) {
    console.error('Failed to load calendar settings', e)
    // fallback: open modal with defaults so user can still save timezone/locale
    settingsCalendars.value = []
    settingsConnected.value = false
    // try to populate from Inertia props if available
    const sp = (page.props as any).settings || {}
    settings.calendar_id = sp?.calendar_id ?? ''
    settings.timezone = sp?.timezone ?? 'America/Sao_Paulo'
    settings.locale = sp?.locale ?? 'pt_BR'
    settingsModalVisible.value = true
  }
}

async function saveSettingsModal() {
  settingsSaving.value = true
  try {
    await axios.put(route('admin.calendar.settings.update'), {
      calendar_id: settings.calendar_id,
      timezone: settings.timezone,
      locale: settings.locale,
    })
    settingsModalVisible.value = false
    // optionally reload events to reflect changes
    await loadEvents()
    // show success flash by reloading page (keeps it simple)
    window.location.reload()
  } catch (e) {
    console.error('Failed to save settings', e)
    alert('Erro ao salvar configurações')
  } finally {
    settingsSaving.value = false
  }
}

async function disconnectGoogle() {
  try {
    await router.post(route('admin.calendar.disconnect'))
  } catch (e) {
    console.error(e)
    alert('Erro ao desconectar')
  }
}

function connectGoogle() {
  window.location.href = route('admin.calendar.auth')
}

async function loadEvents() {
  const res = await axios.get<ApiCalendarEvent[]>(route('admin.calendar.events'))
  events.value = res.data.map((e: ApiCalendarEvent) => ({
    id: e.id,
    title: e.summary,
    start: e.start,
    end: e.end,
    description: e.description || '',
    allDay: !!e.allDay,
    calendar: e.calendar || ''
  }))
  // update calendarInstance if initialized — use event source for efficiency
  if (calendarInstance) {
    try {
      // remove existing event sources to avoid duplicates
      const sources = calendarInstance.getEventSources ? calendarInstance.getEventSources() : [];
      if (Array.isArray(sources)) {
        sources.forEach((s: any) => { try { s.remove(); } catch (e) { void e } })
      }
      // add new source
      calendarInstance.addEventSource(events.value)
      try { calendarInstance.render() } catch (err) { console.error('Calendar render error', err) }
    } catch (err) {
      // fallback: add events one-by-one
      console.warn('addEventSource failed, falling back to addEvent per item', err)
      try { calendarInstance.removeAllEvents() } catch(e){ void e }
      events.value.forEach(ev => {
        try { calendarInstance.addEvent(ev) } catch(e) { console.warn('addEvent fallback failed', e, ev); }
      })
      try { calendarInstance.render() } catch (err) { console.error('Calendar render error', err) }
    }
  }
}

onMounted(async () => {
  // initialize FullCalendar via global UMD `FullCalendar` (loaded from CDN)
  try {
    await nextTick()
    if (typeof window !== 'undefined' && (window as any).FullCalendar) {
      const FC = (window as any).FullCalendar
      const mountEl = calendarEl.value || document.getElementById('fc-calendar')
      if (!mountEl) {
        console.warn('Calendar mount element not found; skipping initialization')
      } else {
        const plugins: any[] = []
        if (FC.dayGridPlugin) plugins.push(FC.dayGridPlugin)
        if (FC.interactionPlugin) plugins.push(FC.interactionPlugin)

        calendarInstance = new FC.Calendar(mountEl, {
          plugins: plugins,
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek,dayGridDay'
          },
          initialView: 'dayGridMonth',
          selectable: true,
          select: function(selectInfo: any) {
            openModal('create', {
              id: null,
              summary: '',
              description: '',
              start: String(selectInfo.startStr ?? ''),

              end: String(selectInfo.endStr ?? selectInfo.startStr ?? ''),

              allDay: !!selectInfo.allDay,
              calendar: ''
            })
          },
          eventClick: function(info: any) {
            const ev = info.event
            openModal('edit', {
              id: ev.id,
              summary: ev.title,
              description: ev.extendedProps?.description ?? '',
              start: String(ev.startStr ?? ev.start ?? ''),

              end: String(ev.endStr ?? ev.end ?? ''),

              allDay: !!ev.allDay,
              calendar: ev.extendedProps?.calendar ?? ''
            })
          },
          eventDidMount: function(info: any) {
            try {
              const cal = info.event.extendedProps?.calendar
              if (cal && info.el) {
                const badge = document.createElement('span')
                badge.className = 'ml-2 text-xs px-1 rounded bg-gray-100 text-gray-700'
                badge.style.marginLeft = '6px'
                // pop() can return undefined, ensure we always set a string
                const _parts = String(cal).split('/')
                badge.innerText = String(_parts.pop() ?? '')
                const titleEl = info.el.querySelector('.fc-event-title') || info.el
                try { titleEl.appendChild(badge) } catch(e){ void e }
              }
            } catch(e){ void e }
          }
        })
        console.log('FullCalendar initialized', calendarInstance)
        try { calendarInstance.render() } catch (err) { console.error('Calendar render error', err) }
      }
    } else {
      console.warn('FullCalendar not available on window; ensure CDN script loaded')
    }
  } catch (err) {
    console.error('Error initializing FullCalendar', err)
  }

  loadEvents()

  // auto-refresh control: start only when page visible
  function startAutoRefresh() {
    if (refreshInterval) return
    try {
      refreshInterval = setInterval(() => {
        if (document.visibilityState === 'visible') {
          loadEvents().catch((e) => console.warn('Auto refresh failed', e))
        }
      }, 60000)
    } catch (e) {
      console.warn('Failed to start refresh interval', e)
    }
  }

  function stopAutoRefresh() {
    if (!refreshInterval) return
    try { clearInterval(refreshInterval) } catch (e) { void e }
    refreshInterval = null
  }

  // start only if page visible
  if (typeof document !== 'undefined' && document.visibilityState === 'visible') {
    startAutoRefresh()
  }

  // visibilitychange: start when visible, stop when hidden
  if (typeof document !== 'undefined') {
    visibilityHandler = function() {
      if (document.visibilityState === 'visible') {
        // do an immediate refresh and start interval
        loadEvents().catch(()=>{})
        startAutoRefresh()
      } else {
        stopAutoRefresh()
      }
    }
    document.addEventListener('visibilitychange', visibilityHandler)
  }

  // expose debug helper on window for dev (avoids unused local variable)
  if (typeof window !== 'undefined') {
    ;(window as any).calendarDebug = () => ({ instance: calendarInstance, events: events.value })
  }
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
    refreshInterval = null
  }
  if (visibilityHandler && typeof document !== 'undefined') {
    document.removeEventListener('visibilitychange', visibilityHandler)
    visibilityHandler = null
  }
})

// Modal state + handlers for create/update/delete
const modalVisible = ref(false)
const modalMode = ref(null as null | 'create' | 'edit')
const form = reactive({ id: null as null | string, summary: '', description: '', start: '', end: '', allDay: false, calendar: '' })

function openModal(mode: 'create' | 'edit', payload: any) {
  modalMode.value = mode
  form.id = payload.id ?? null
  form.summary = payload.summary ?? ''
  form.description = payload.description ?? ''
  // normalize incoming payload to either date-only or datetime-local depending on allDay
  form.allDay = !!payload.allDay
  if (form.allDay) {
    const s = toDateInput(payload.start ?? '')
    let e = toDateInput(payload.end ?? payload.start ?? '')
    // FullCalendar returns exclusive end for all-day selections (next day). Display same-day end to user.
    try {
      if (s && e) {
        const sd = new Date(s)
        const ed = new Date(e)
        const diff = (ed.getTime() - sd.getTime()) / (1000 * 60 * 60 * 24)
        if (diff === 1) e = s
      }
    } catch (err) { void err }
    form.start = s
    form.end = e
  } else {
    form.start = toDateTimeLocal(payload.start ?? '')
    form.end = toDateTimeLocal(payload.end ?? payload.start ?? '')
  }
  form.calendar = payload.calendar ?? ''
  modalVisible.value = true
}

function toDateInput(val: string) {
  if (!val) return ''
  // if contains T, return date part
  if (val.indexOf('T') !== -1) return val.split('T')[0]
  // strip timezone Z/offset
  return val.split('Z')[0].split('+')[0].split('-')[0] ? val.split('Z')[0] : val
}

function toDateTimeLocal(val: string) {
  if (!val) return ''
  let v = val
  // if date-only like YYYY-MM-DD, add default time
  if (!v.includes('T')) {
    return v + 'T09:00'
  }
  // remove timezone (Z or ±hh:mm)
  v = v.replace(/Z|([\+\-]\d{2}:?\d{2})$/, '')
  // remove seconds if present
  const m = v.match(/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2})/) 
  if (m) return m[1]
  return v
}

function closeModal() {
  modalVisible.value = false
  modalMode.value = null
}

async function saveEvent() {
  try {
    if (modalMode.value === 'create') {
      await axios.post(route('admin.calendar.store'), {
        summary: form.summary,
        description: form.description,
        start: form.start,
        end: form.end,
        allDay: form.allDay
      })
    } else if (modalMode.value === 'edit' && form.id) {
      await axios.put(route('admin.calendar.update', form.id), {
        summary: form.summary,
        description: form.description,
        start: form.start,
        end: form.end,
        allDay: form.allDay
      })
    }
    closeModal()
    await loadEvents()
  } catch (e) {
    console.error('Failed save event', e)
    alert('Erro ao salvar o evento')
  }
}

async function deleteEventFromModal() {
  if (!form.id) return
  if (!confirm('Confirma exclusão deste evento?')) return
  try {
    await axios.delete(route('admin.calendar.destroy', form.id))
    closeModal()
    await loadEvents()
  } catch (e) {
    console.error('Failed delete event', e)
    alert('Erro ao excluir o evento')
  }
}
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
</script>

<template>
  <AuthLayout>
<<<<<<< HEAD
    <div class="flex flex-col flex-1 min-h-0">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
          <Button @click="prevMonth" class="px-3 py-1">&lt;</Button>
          <Button @click="nextMonth" class="px-3 py-1">&gt;</Button>
          <Button variant="filter" @click="goToToday" :class="isTodayActive() ? 'bg-[var(--primary)] text-[var(--primary-foreground)]' : ''" class="px-3 py-1">Hoje</Button>
        </div>

        <div class="flex-1 text-center">
          <div class="text-lg font-semibold text-[var(--text-foreground)]">{{ monthTitle }}</div>
        </div>

        <div class="flex items-center gap-2">
          <div class="flex items-center gap-1">
            <Button variant="filter" @click="setView('month')" :class="currentView === 'month' ? 'bg-[var(--primary)] text-[var(--primary-foreground)]' : ''" class="px-3 py-1">Mês</Button>
            <Button variant="filter" @click="setView('week')" :class="currentView === 'week' ? 'bg-[var(--primary)] text-[var(--primary-foreground)]' : ''" class="px-3 py-1">Semana</Button>
            <Button variant="filter" @click="setView('day')" :class="currentView === 'day' ? 'bg-[var(--primary)] text-[var(--primary-foreground)]' : ''" class="px-3 py-1">Dia</Button>
          </div>
          <!-- <div>
            <Button v-if="pp.google?.connected" @click="() => { window.location.href = route(calendarRouteBase + '.disconnect') }" class="bg-red-600 px-3 py-1 text-white">Desconectar</Button>
            <Button v-else @click="() => { window.location.href = route(calendarRouteBase + '.auth') }" class="bg-green-600 px-3 py-1 text-white">Conectar Google</Button>
          </div> -->
        </div>
      </div>

      <!-- Flash messages -->
      <div v-if="pp.flash?.success" class="mb-4 rounded border-l-4 border-green-500 bg-green-50 p-3 text-green-800 dark:bg-green-900 dark:text-green-100">
        {{ pp.flash.success }}
      </div>
      <div v-if="pp.flash?.error" class="mb-4 rounded border-l-4 border-red-500 bg-red-50 p-3 text-red-800 dark:bg-red-900 dark:text-red-100">
        {{ pp.flash.error }}
      </div>

      <div class="shadow rounded p-2 bg-[var(--card,#0b0b0b)] flex-1 min-h-0 overflow-hidden">
        <div :class="['grid', gridCols, 'gap-1', 'text-sm', 'text-[var(--text-foreground)]']">
          <template v-for="(label, i) in headerLabels" :key="i">
            <div class="py-2 text-center font-medium">{{ label }}</div>
          </template>
        </div>

        <div :class="['grid', gridCols, 'gap-2', 'mt-2', 'h-full']">
          <template v-for="(day, idx) in displayedDays" :key="idx">
            <div 
              :class="[dayCellClasses(day), 'flex', 'flex-col']"
              @mousedown="handleDayMouseDown(day)"
              @mouseenter="handleDayMouseEnter(day)"
            >
              <div class="flex items-start justify-between">
                <div :class="['text-sm font-medium', isSameDay(day, new Date()) ? 'text-[var(--text-foreground)] font-semibold' : '']">{{ day.getDate() }}</div>
              </div>

              <template v-if="currentView !== 'month'">
                <div class="mt-2 border-t border-[var(--border)] pt-2 flex-1 relative flex flex-col">
                  <!-- All-day events -->
                  <!-- Renderiza bloco de eventos 'dia inteiro' somente se houver eventos desse tipo. -->
                  <div v-if="eventsForDay(day).some((e: EventItem) => e.allDay)" class="bg-[var(--border)] bg-opacity-5 rounded-t px-2 py-2 border-b border-[var(--border)] border-opacity-30 max-h-40 overflow-auto">
                    <div class="space-y-1">
                      <template v-for="ev in eventsForDay(day).filter((e: EventItem) => e.allDay)" :key="ev.id">
                        <div
                          @click.stop="openEditEventModal(ev)"
                          class="event-seg all-day-event px-2 py-1"
                          :title="ev.title"
                          :style="ev.labelColor ? { background: ev.labelColor, borderColor: ev.labelColor } : {}"
                        >
                          <span class="truncate text-xs font-medium">{{ ev.title }}</span>
                        </div>
                      </template>
                    </div>
                  </div>

                  <div class="flex-1 relative overflow-hidden">
                    <div class="grid h-full absolute inset-0 pointer-events-none" :style="{ gridTemplateRows: 'repeat(24, minmax(0, 1fr))' }">
                      <template v-for="h in hours" :key="h">
                        <div class="flex items-start gap-2 text-xs text-[var(--text-foreground)] px-1 border-t border-[var(--border)] border-opacity-20">
                          <div class="w-10 text-right text-muted-foreground pr-2">{{ formatHour(h) }}</div>
                          <div class="flex-1 h-full"></div>
                        </div>
                      </template>
                    </div>

                    <div class="relative h-full">
                      <template v-for="ev in timedEventsForDay(day)" :key="ev.id">
                        <div @click.stop="openEditEventModal(ev)"
                          :style="{ 
                            top: getEventPosition(ev, day).topPercent + '%', 
                            height: getEventPosition(ev, day).heightPercent + '%',
                            left: (computeEventLayout(day).get(ev.id)?.left || '0%'),
                            width: (computeEventLayout(day).get(ev.id)?.width || '100%'),
                            borderColor: ev.labelColor || 'var(--primary)',
                            backgroundColor: ev.labelColor ? `${ev.labelColor}15` : 'var(--card)'
                          }"
                          class="timed-event absolute px-1 py-0.5 flex flex-col gap-1 overflow-hidden border border-[var(--border)] bg-[var(--card)] rounded" 
                          :title="ev.title">
                          <div class="flex items-center gap-1">
                            <span :style="{ backgroundColor: ev.labelColor || 'var(--primary)' }" class="inline-block w-2 h-2 rounded-full flex-shrink-0"></span>
                            <span class="event-time text-xs font-medium" style="color: inherit">{{ formatEventTime(ev) }}</span>
                          </div>
                          <span class="event-title text-xs font-medium truncate" style="color: inherit">{{ ev.title }}</span>
                        </div>
                      </template>
                    </div>
                  </div>
                </div>
              </template>

              <template v-else>
                <div class="mt-2 space-y-1">
                  <!-- All-day events as full-width colored blocks -->
                  <template v-for="ev in eventsForDay(day).filter((e: EventItem) => e.allDay)" :key="ev.id">
                    <div
                      @click.stop="openEditEventModal(ev)"
                      :class="['event-seg', getSegmentType(ev, day)]"
                      :title="ev.title"
                      :style="ev.labelColor ? { background: ev.labelColor, borderColor: ev.labelColor } : {}"
                    >
                        <span class="truncate">{{ ev.title }}</span>
                    </div>
                  </template>

                  <!-- Timed events as dot + time + title (no background) -->
                  <template v-for="ev in timedEventsForDay(day)" :key="ev.id">
                    <div
                      @click.stop="openEditEventModal(ev)"
                      class="month-timed-event flex items-center gap-1 text-xs cursor-pointer"
                      :title="ev.title"
                    >
                      <span :style="{ backgroundColor: ev.labelColor || 'var(--primary)' }" class="inline-block w-2 h-2 rounded-full flex-shrink-0"></span>
                      <span class="text-muted-foreground">{{ formatEventTime(ev) }}</span>
                      <span class="truncate text-foreground">{{ ev.title }}</span>
                    </div>
                  </template>
                </div>
              </template>
            </div>
          </template>
        </div>
      </div>

      <!-- Event Modal (usando Modal.vue) -->
      <Modal
        v-model="modalOpen"
        :title="modalMode === 'create' ? 'Novo Evento' : 'Editar Evento'"
        size="md"
      >
        <template #default>
          <div class="space-y-3">
            <div>
              <Input v-model="formData.summary" label="Título" type="text" placeholder="Título do evento" />
            </div>

            <div>
              <label class="block text-sm font-medium text-[var(--text-foreground)] mb-1">Descrição</label>
              <textarea v-model="formData.description" placeholder="Descrição (opcional)" class="file:text-foreground placeholder:text-muted-foreground selection:bg-[var(--primary)] selection:text-[var(--primary-foreground)] border border-[var(--border)] w-full min-w-0 rounded-md bg-[var(--card)] px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm focus-visible:border-[var(--ring)] focus-visible:ring-[var(--ring)]/50 focus-visible:ring-[3px]" rows="3"></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-[var(--text-foreground)] mb-1">Etiqueta (cor)</label>
              <div class="flex items-center gap-2">
                <template v-for="c in ['#EF4444','#F59E0B','#10B981','#3B82F6','#8B5CF6','#EC4899','#6B7280']" :key="c">
                  <button type="button" @click="formData.labelColor = c" :class="['w-6 h-6 rounded-full border-2', formData.labelColor === c ? 'border-white' : 'border-transparent']" :style="{ backgroundColor: c }"></button>
                </template>
                <input v-model="formData.label" placeholder="Nome da etiqueta (opcional)" class="ml-2 px-2 py-1 rounded bg-[var(--card)] border border-[var(--border)] text-[var(--text-foreground)]" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-[var(--text-foreground)] mb-1">Início</label>
                <Datepicker :modelValue="stripTimeFromDateTime(formData.start) || formData.start" :mode="formData.allDay ? 'date' : 'date'" @update:modelValue="onStartDateUpdate" />
                <div v-if="!formData.allDay" class="mt-2">
                  <Timepicker :modelValue="timePartFromDateTime(formData.start)" @update:modelValue="onStartTimeUpdate" />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-[var(--text-foreground)] mb-1">Fim</label>
                <Datepicker :modelValue="stripTimeFromDateTime(formData.end) || formData.end" :mode="formData.allDay ? 'date' : 'date'" @update:modelValue="onEndDateUpdate" />
                <div v-if="!formData.allDay" class="mt-2">
                  <Timepicker :modelValue="timePartFromDateTime(formData.end)" @update:modelValue="onEndTimeUpdate" />
                </div>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <Checkbox v-model="formData.allDay" id="allDay" class="h-4 w-4 cursor-pointer rounded border-[rgba(255,255,255,0.2)] bg-[rgba(255,255,255,0.05)]" />
              <label for="allDay" class="cursor-pointer text-sm text-[var(--text-foreground)]">Dia inteiro</label>
            </div>
          </div>
        </template>
        <template #footer>
          <div class="mt-6 flex gap-2">
            <Button @click="closeModal" variant="outline">Cancelar</Button>
            <Button v-if="modalMode === 'edit' && formData.id" @click="deleteEvent" variant="destructive">Deletar</Button>
            <Button @click="saveEvent" variant="primary">Salvar</Button>
          </div>
        </template>
      </Modal>
    </div>
  </AuthLayout>
</template>

<style scoped>
.user-select-none {
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

/* Estilos para células do calendário */
.day-cell {
  border: 1px solid var(--border);
  background: transparent;
  color: var(--text-primary);
  transition: background .15s ease, border-color .15s ease, opacity .15s ease, transform .08s ease;
}
.day-cell.in-range {
  /* Destaque para intervalo selecionado */
  background: color-mix(in srgb, var(--primary) 18%, transparent);
  border-color: var(--primary);
}
.day-cell.not-current-month {
  opacity: 0.5;
}
.day-cell:hover {
  background: color-mix(in srgb, var(--primary) 8%, transparent);
  border-color: var(--primary);
}

/* Destaque para o dia atual */
.day-cell.today {
  background: color-mix(in srgb, var(--primary) 12%, transparent);
  border-color: var(--primary);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 6%, transparent);
}

/* Event segment styles for multi-day events */
.event-seg {
  display: flex;
  align-items: center;
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  line-height: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  background: var(--primary);
  color: var(--primary-foreground);
  cursor: pointer;
  border: 1px solid rgba(0,0,0,0.06);
  border-radius: 0.375rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.12);
  transition: box-shadow 0.15s ease, transform 0.15s ease;
}
.event-seg:hover {
  box-shadow: 0 2px 6px rgba(0,0,0,0.18);
  transform: translateY(-1px);
}
.event-seg.single {
  border-radius: 0.375rem;
}
.event-seg.start {
  border-top-left-radius: 0.375rem;
  border-bottom-left-radius: 0.375rem;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}
.event-seg.end {
  border-top-right-radius: 0.375rem;
  border-bottom-right-radius: 0.375rem;
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}
.event-seg.middle {
  border-radius: 0;
}

.all-day-event {
  color: var(--primary-foreground);
  font-weight: 500;
  font-size: 0.8125rem;
  border: 1px solid rgba(0,0,0,0.06);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
}

/* Timed events in the day/week timeline - positioned absolutely with columns */
.timed-event {
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
  overflow-y: auto;
  min-height: 2.5rem;
  z-index: 10;
}

.timed-event:hover {
  transform: translateX(2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  opacity: 1;
}

.timed-event .event-time {
  font-size: 0.7rem;
  font-weight: 600;
  line-height: 1;
}

.timed-event .event-title {
  font-size: 0.75rem;
  line-height: 1.1;
  word-break: break-word;
}

/* Month view timed events: dot + time + title, no background */
.month-timed-event {
  line-height: 1.2;
  padding: 0.125rem 0;
}
.month-timed-event:hover {
  opacity: 0.8;
}
</style>
=======
    <div class="flex flex-col">
      <!-- template changes: use `pp` instead of `page.props` to satisfy TypeScript -->
      <div class="flex items-center gap-2 justify-end gap-2 mb-4">
        <button @click="openSettingsModal" class="rounded bg-blue-600 px-3 py-1 text-white hover:bg-blue-700">Configurações</button>
        <button v-if="pp.google?.connected" @click="disconnectGoogle" class="rounded bg-red-600 px-3 py-1 text-white hover:bg-red-700">Desconectar</button>
        <button v-else @click="connectGoogle" class="rounded bg-green-600 px-3 py-1 text-white hover:bg-green-700">Conectar Google</button>
      </div>

      <div v-if="pp.flash?.success" class="mb-4 rounded border-l-4 border-green-500 bg-green-50 p-3 text-green-800">
        {{ pp.flash.success }}
      </div>
      <div v-if="pp.flash?.error" class="mb-4 rounded border-l-4 border-red-500 bg-red-50 p-3 text-red-800">
        {{ pp.flash.error }}
      </div>

      <div id="fc-calendar" ref="calendarEl" class="bg-white shadow rounded p-2 flex-1 overflow-hidden min-h-0" style="max-height:800px"></div>
      
      <!-- Modal for create / edit -->
      <div v-if="modalVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded p-4 w-full max-w-lg">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">{{ modalMode === 'create' ? 'Novo evento' : 'Editar evento' }}</h3>
            <button @click="closeModal" class="text-gray-500 hover:text-gray-700">Fechar</button>
          </div>

          <div class="space-y-2">
            <div>
              <label class="block text-sm font-medium text-gray-700">Título</label>
              <input v-model="form.summary" class="mt-1 block w-full rounded border px-2 py-1" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Descrição</label>
              <textarea v-model="form.description" class="mt-1 block w-full rounded border px-2 py-1"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-sm font-medium text-gray-700">Início</label>
                <input v-if="form.allDay" v-model="form.start" type="date" class="mt-1 block w-full rounded border px-2 py-1" />
                <input v-else v-model="form.start" type="datetime-local" class="mt-1 block w-full rounded border px-2 py-1" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Fim</label>
                <input v-if="form.allDay" v-model="form.end" type="date" class="mt-1 block w-full rounded border px-2 py-1" />
                <input v-else v-model="form.end" type="datetime-local" class="mt-1 block w-full rounded border px-2 py-1" />
              </div>
            </div>
            <div class="flex items-center gap-2">
              <input id="allDay" type="checkbox" v-model="form.allDay" @change="() => { if (form.allDay) { form.start = toDateInput(form.start); form.end = toDateInput(form.end) } else { form.start = toDateTimeLocal(form.start); form.end = toDateTimeLocal(form.end) } }" />
              <label for="allDay" class="text-sm">Dia inteiro</label>
            </div>
          </div>

          <div class="mt-4 flex justify-end gap-2">
            <button @click="closeModal" class="rounded border px-3 py-1">Cancelar</button>
            <button v-if="modalMode === 'edit'" @click="deleteEventFromModal" class="rounded bg-red-600 px-3 py-1 text-white">Excluir</button>
            <button @click="saveEvent" class="rounded bg-blue-600 px-3 py-1 text-white">Salvar</button>
          </div>
        </div>
      </div>
      <!-- Calendar settings modal -->
      <div v-if="settingsModalVisible" class="fixed inset-0 z-60 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded p-4 w-full max-w-lg">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">Configurações do Calendário</h3>
            <button @click="() => { settingsModalVisible = false }" class="text-gray-500 hover:text-gray-700">Fechar</button>
          </div>

          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700">Agenda padrão</label>
              <select v-model="settings.calendar_id" class="mt-1 block w-full rounded border px-2 py-1">
                <option value="">(Nenhuma selecionada)</option>
                <option v-for="c in settingsCalendars" :key="c.id" :value="c.id">{{ c.summary }} — {{ c.id }}</option>
              </select>
              <div v-if="!settingsConnected" class="text-sm text-yellow-600 mt-1">Google não conectado — conecte para listar agendas.</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Fuso horário</label>
              <select v-model="settings.timezone" class="mt-1 block w-full rounded border px-2 py-1">
                <option value="America/Sao_Paulo">Brasil (America/Sao_Paulo)</option>
                <option value="UTC">UTC</option>
                <option value="Europe/Lisbon">Europe/Lisbon</option>
                <option value="America/New_York">America/New_York</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Locale</label>
              <select v-model="settings.locale" class="mt-1 block w-full rounded border px-2 py-1">
                <option value="pt_BR">pt_BR</option>
                <option value="en_US">en_US</option>
              </select>
            </div>
          </div>

          <div class="mt-4 flex justify-end gap-2">
            <button @click="() => { settingsModalVisible = false }" class="rounded border px-3 py-1">Cancelar</button>
            <button @click="saveSettingsModal" :disabled="settingsSaving" class="rounded bg-blue-600 px-3 py-1 text-white">Salvar</button>
          </div>
        </div>
      </div>
    </div>
  </AuthLayout>
</template>
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
