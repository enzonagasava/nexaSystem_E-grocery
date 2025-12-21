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
  start: string
  end: string
  description?: string
  allDay?: boolean
  calendar?: string
}

interface GoogleCalendar {
  id: string
  summary: string
}

interface CalendarSettings {
  calendar_id: string
  timezone: string
  locale: string
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
</script>

<template>
  <AuthLayout>
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
