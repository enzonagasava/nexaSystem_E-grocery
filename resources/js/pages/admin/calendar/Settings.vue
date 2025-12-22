<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue'
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const pp = page.props as any

// use explicit any/typed refs so TS/volar won't complain about page.props shape
const settings = ref<any>(pp.settings || {})
const calendars = ref<any[]>(pp.google?.calendars || [])

const timezone = ref<string>(settings.value.timezone || 'America/Sao_Paulo')
const calendarId = ref<string>(settings.value.calendar_id || '')
const locale = ref<string>(settings.value.locale || 'pt_BR')

const saving = ref(false)

async function save() {
  saving.value = true
  try {
    await axios.put(route('admin.calendar.settings.update'), {
      calendar_id: calendarId.value,
      timezone: timezone.value,
      locale: locale.value,
    })
    window.location.reload()
  } catch (e) {
    console.error('Failed to save settings', e)
    alert('Erro ao salvar configurações')
  } finally { saving.value = false }
}
</script>

<template>
  <AuthLayout>
    <div class="p-6 max-w-3xl">
      <h2 class="text-xl font-semibold mb-4">Configurações do Calendário</h2>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Agenda padrão</label>
        <select v-model="calendarId" class="mt-1 block w-full rounded border px-2 py-1">
          <option value="">(Nenhuma selecionada)</option>
          <option v-for="c in calendars" :key="c.id" :value="c.id">{{ c.summary }} — {{ c.id }}</option>
        </select>
        <div v-if="!pp.google?.connected" class="text-sm text-yellow-600 mt-1">Google não conectado — conecte para listar agendas.</div>
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Fuso horário</label>
        <select v-model="timezone" class="mt-1 block w-full rounded border px-2 py-1">
          <option value="America/Sao_Paulo">Brasil (America/Sao_Paulo)</option>
          <option value="UTC">UTC</option>
          <option value="Europe/Lisbon">Europe/Lisbon</option>
          <option value="America/New_York">America/New_York</option>
        </select>
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Locale</label>
        <select v-model="locale" class="mt-1 block w-full rounded border px-2 py-1">
          <option value="pt_BR">pt_BR</option>
          <option value="en_US">en_US</option>
        </select>
      </div>

      <div class="flex justify-end gap-2">
        <button @click="save" :disabled="saving" class="rounded bg-blue-600 px-3 py-1 text-white">Salvar</button>
      </div>
    </div>
  </AuthLayout>
</template>
