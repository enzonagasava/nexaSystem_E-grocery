<script setup lang="ts">
import { computed } from 'vue'
import { Grid3X3, List, Kanban } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

interface Props {
  modelValue: 'grid' | 'list'
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: 'grid',
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: 'grid' | 'list'): void
}>()

const isGrid = computed(() => props.modelValue === 'grid')
const isList = computed(() => props.modelValue === 'list')

function toggleView(mode: 'grid' | 'list') {
  if (props.modelValue !== mode) {
    emit('update:modelValue', mode)
  }
}
</script>

<template>
  <div :class="cn('flex items-center gap-1 p-1 rounded-lg bg-muted', props.class)" role="group" aria-label="Alternar visualização">
    <!-- Grid View Button -->
    <button
      @click="toggleView('grid')"
      :class="cn(
        'inline-flex items-center justify-center px-3 py-2 rounded-md transition-colors duration-200 cursor-pointer hover:bg-muted-foreground/10',
        isGrid
          ? 'bg-primary text-white hover:bg-[var(--primary)]/80'
          : 'text-muted-foreground hover:text-foreground'
      )"
      :aria-pressed="isGrid"
      :aria-label="'Visualizar em grade'"
      title="Visualização em grade"
    >
      <Grid3X3 class="w-4 h-4" />
    </button>

    <!-- List View Button -->
    <button
      @click="toggleView('list')"
      :class="cn(
        'inline-flex items-center justify-center px-3 py-2 rounded-md transition-colors duration-200 cursor-pointer hover:bg-muted-foreground/10',
        isList
          ? 'bg-primary text-white hover:bg-[var(--primary)]/80'
          : 'text-muted-foreground hover:text-foreground'
      )"
      :aria-pressed="isList"
      :aria-label="'Visualizar em lista'"
      title="Visualização em lista"
    >
      <List class="w-4 h-4" />
    </button>
  </div>
</template>
