
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
</script>

<template>
  <AuthLayout>
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
