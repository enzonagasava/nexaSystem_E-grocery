<script setup lang="ts">
import { ref, watch } from 'vue'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import Select from '../select/Select.vue';

const props = withDefaults(defineProps<{
  modelValue?: string | null
  placeholder?: string
}>(), {
  modelValue: null,
  placeholder: 'HH:MM',
})

const emit = defineEmits<{
  (e: 'update:modelValue', v: string | null): void
}>()

function parseTime(v?: string | null): { hour: number; minute: number } {
  if (!v) return { hour: 0, minute: 0 }
  
  // Try ISO datetime format first
  const isoMatch = v.match(/T(\d{2}):(\d{2})/)
  if (isoMatch) {
    return { hour: parseInt(isoMatch[1], 10), minute: parseInt(isoMatch[2], 10) }
  }
  const timeMatch = v.match(/^(\d{2}):(\d{2})$/)
  if (timeMatch) {
    return { hour: parseInt(timeMatch[1], 10), minute: parseInt(timeMatch[2], 10) }
  }
  
  return { hour: 0, minute: 0 }
}

const parsed = parseTime(props.modelValue)
const hour = ref<number>(parsed.hour)
const minute = ref<number>(parsed.minute)

watch(() => props.modelValue, (v: string | null | undefined) => {
  const p = parseTime(v)
  hour.value = p.hour
  minute.value = p.minute
})

function formatDisplay() {
  const hh = String(hour.value).padStart(2, '0')
  const mm = String(minute.value).padStart(2, '0')
  return `${hh}:${mm}`
}

function emitTime() {
  const hh = String(hour.value).padStart(2, '0')
  const mm = String(minute.value).padStart(2, '0')
  emit('update:modelValue', `${hh}:${mm}`)
}
</script>

<template>
  <div class="flex items-center gap-2 p-2 rounded-md border border-[var(--border)] bg-[var(--card)]">
    <Select v-model.number="hour" @change="emitTime" class="rounded px-2 py-1 bg-[var(--card)] border border-[var(--border)] text-[var(--text-foreground)]">
      <option v-for="h in 24" :key="h" :value="h-1">{{ String(h-1).padStart(2,'0') }}</option>
    </Select>
    <span class="text-[var(--text-foreground)]">:</span>
    <Select v-model.number="minute" @change="emitTime" class="rounded px-2 py-1 bg-[var(--card)] border border-[var(--border)] text-[var(--text-foreground)]">
      <option v-for="m in 60" :key="m" :value="m-1">{{ String(m-1).padStart(2,'0') }}</option>
    </Select>
  </div>
</template>
