<script setup lang="ts">
import { computed } from 'vue'
import { TrendingUp, BarChart } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

interface Props {
  modelValue: 'line' | 'bar'
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: 'line',
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: 'line' | 'bar'): void
}>()

const isLine = computed(() => props.modelValue === 'line')

function toggleView(mode: 'line' | 'bar') {
  if (props.modelValue !== mode) {
    emit('update:modelValue', mode)
  }
}
</script>

<template>
  <div :class="cn('flex items-center gap-1 p-1 rounded-lg border border-[var(--border)]', props.class)" role="group" aria-label="Alternar tipo de gráfico">
    <button
      @click="toggleView('line')"
      :class="cn(
        'inline-flex items-center justify-center px-3 py-2 rounded-md transition-colors duration-200 cursor-pointer hover:bg-muted-foreground/10',
        isLine ? 'bg-primary text-white hover:bg-[var(--primary)]/80' : 'text-muted-foreground hover:text-foreground'
      )"
      :aria-pressed="isLine"
      title="Linha"
    >
      <TrendingUp class="w-4 h-4" />
    </button>

    <button
      @click="toggleView('bar')"
      :class="cn(
        'inline-flex items-center justify-center px-3 py-2 rounded-md transition-colors duration-200 cursor-pointer hover:bg-muted-foreground/10',
        !isLine ? 'bg-primary text-white hover:bg-[var(--primary)]/80' : 'text-muted-foreground hover:text-foreground'
      )"
      :aria-pressed="!isLine"
      title="Barras"
    >
      <BarChart class="w-4 h-4" />
    </button>
  </div>
</template>
