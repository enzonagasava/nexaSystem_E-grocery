<script setup lang="ts">
import { computed } from 'vue'
import { X } from 'lucide-vue-next'
import Button from '@/components/ui/button/Button.vue'
import type { ImovelFilters } from '@/composables/useFilters'

interface Props {
  filters: ImovelFilters
  activeFiltersCount: number
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'remove-filter', key: keyof ImovelFilters): void
  (e: 'clear-all'): void
}>()

const activeFiltersDisplay = computed(() => {
  const display: Array<{ key: keyof ImovelFilters; label: string; value: string }> = []

  if (props.filters.q) {
    display.push({ key: 'q', label: 'Busca', value: props.filters.q })
  }
  if (props.filters.cidade) {
    display.push({ key: 'cidade', label: 'Cidade', value: props.filters.cidade })
  }
  if (props.filters.categoria) {
    display.push({ key: 'categoria', label: 'Categoria', value: props.filters.categoria })
  }
  if (props.filters.condicao) {
    display.push({ key: 'condicao', label: 'Condição', value: props.filters.condicao })
  }
  if (props.filters.status) {
    display.push({ key: 'status', label: 'Status', value: props.filters.status })
  }
  if (props.filters.min_valor !== null) {
    display.push({ key: 'min_valor', label: 'Valor mín', value: `R$ ${props.filters.min_valor}` })
  }
  if (props.filters.max_valor !== null) {
    display.push({ key: 'max_valor', label: 'Valor máx', value: `R$ ${props.filters.max_valor}` })
  }
  if (props.filters.has_listing !== null) {
    display.push({
      key: 'has_listing',
      label: 'Anúncio',
      value: props.filters.has_listing ? 'Com anúncio' : 'Sem anúncio',
    })
  }
  if (props.filters.min_quartos !== null) {
    display.push({ key: 'min_quartos', label: 'Quartos', value: `${props.filters.min_quartos}+` })
  }
  if (props.filters.min_vagas !== null) {
    display.push({ key: 'min_vagas', label: 'Vagas', value: `${props.filters.min_vagas}+` })
  }

  return display
})

function removeFilter(key: keyof ImovelFilters) {
  emit('remove-filter', key)
}

function clearAll() {
  emit('clear-all')
}
</script>

<template>
  <div v-if="activeFiltersCount > 0" class="mb-4">
    <div class="flex items-center gap-2 flex-wrap bg-muted/50 p-3 rounded-lg">
      <span class="text-sm font-medium text-muted-foreground">Filtros ativos:</span>
      
      <div
        v-for="filter in activeFiltersDisplay"
        :key="filter.key"
        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-primary/10 border border-primary/20 rounded-md text-xs"
      >
        <span class="font-medium">{{ filter.label }}:</span>
        <span class="text-muted-foreground">{{ filter.value }}</span>
        <button
          type="button"
          class="ml-1 hover:bg-destructive/20 rounded p-0.5 transition-colors"
          @click="removeFilter(filter.key)"
          :aria-label="`Remover filtro ${filter.label}`"
        >
          <X class="w-3 h-3" />
        </button>
      </div>

      <Button
        size="sm"
        variant="ghost"
        class="ml-auto text-xs"
        @click="clearAll"
      >
        <X class="w-3 h-3 mr-1" />
        Limpar todos
      </Button>
    </div>
  </div>
</template>
