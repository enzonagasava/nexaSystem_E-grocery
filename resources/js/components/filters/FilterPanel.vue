<script setup lang="ts">
import { computed, ref } from 'vue'
import { Sheet, SheetContent } from '@/components/ui/sheet'
import SheetHeader from '@/components/ui/sheet/SheetHeader.vue'
import SheetTitle from '@/components/ui/sheet/SheetTitle.vue'
import SheetDescription from '@/components/ui/sheet/SheetDescription.vue'
import SheetFooter from '@/components/ui/sheet/SheetFooter.vue'
import Input from '@/components/ui/input/Input.vue'
import Select from '@/components/ui/select/Select.vue'
import Button from '@/components/ui/button/Button.vue'
import Autocomplete from '@/components/ui/autocomplete/Autocomplete.vue'
import { useCidadeAutocomplete } from '@/composables/useCidadeAutocomplete'
import { useCategoriaAutocomplete } from '@/composables/useCategoriaAutocomplete'
import { CONDICAO_IMOVEL } from '@/constants/condicaoImovel'
import { IMOVEL_STATUS } from '@/constants/imovelStatus'
import { X, Check, Star, Save } from 'lucide-vue-next'
import type { ImovelFilters } from '@/composables/useFilters'

interface Props {
  open: boolean
  filters: ImovelFilters
  isLoading?: boolean
  hasActiveFilters?: boolean
  filterPresets?: Record<string, Partial<ImovelFilters>>
  favoriteFilters?: Record<string, Record<string, any>>
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
  hasActiveFilters: false,
  filterPresets: () => ({}),
  favoriteFilters: () => ({}),
})

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'apply'): void
  (e: 'clear'): void
  (e: 'update:filter', key: keyof ImovelFilters, value: any): void
  
  (e: 'save-favorite', name: string): void
  (e: 'apply-favorite', name: string): void
  (e: 'remove-favorite', name: string): void
}>()

const showSaveFavorite = ref(false)
const favoriteName = ref('')

// Autocomplete de cidade
const { filteredCidades, updateSearch: updateCidadeSearch } = useCidadeAutocomplete()

// Autocomplete de categoria
const { filteredCategorias, updateSearch: updateCategoriaSearch } = useCategoriaAutocomplete()

// Opções para has_listing
const listingOptions = [
  { value: '', label: 'Todos' },
  { value: 'true', label: 'Com anúncio' },
  { value: 'false', label: 'Sem anúncio' },
]

// Opções de ordenação
const sortOptions = [
  { value: '', label: 'Padrão (mais recentes)' },
  { value: 'nome', label: 'Nome' },
  { value: 'valor_venda', label: 'Valor de venda' },
  { value: 'valor_locacao', label: 'Valor de locação' },
  { value: 'created_at', label: 'Data de criação' },
]

const orderOptions = [
  { value: 'desc', label: 'Decrescente' },
  { value: 'asc', label: 'Crescente' },
]

// Computed para converter has_listing boolean/null em string para o select
const hasListingValue = computed({
  get: () => {
    if (props.filters.has_listing === true) return 'true'
    if (props.filters.has_listing === false) return 'false'
    return ''
  },
  set: (value: string) => {
    if (value === 'true') emit('update:filter', 'has_listing', true)
    else if (value === 'false') emit('update:filter', 'has_listing', false)
    else emit('update:filter', 'has_listing', null)
  },
})

function handleApply() {
  emit('apply')
  emit('update:open', false)
}

function handleClear() {
  emit('clear')
}

function updateFilter(key: keyof ImovelFilters, value: any) {
  emit('update:filter', key, value)
}

function handleSaveFavorite() {
  if (favoriteName.value.trim()) {
    emit('save-favorite', favoriteName.value.trim())
    favoriteName.value = ''
    showSaveFavorite.value = false
  }
}

// presets removed

function handleApplyFavorite(name: string) {
  emit('apply-favorite', name)
  emit('update:open', false)
}

function handleRemoveFavorite(name: string) {
  emit('remove-favorite', name)
}

</script>

<template>
  <Sheet :open="open" @update:open="(val: boolean) => emit('update:open', val)">
    <SheetContent side="right" class="w-full sm:max-w-md overflow-y-auto">
      <SheetHeader>
        <SheetTitle>Filtrar Imóveis</SheetTitle>
        <SheetDescription>
          Refine sua busca usando os filtros abaixo
        </SheetDescription>
      </SheetHeader>

      <div class="space-y-3 border-b border-border">
      </div>

      <div class="space-y-6">
        <!-- Busca livre -->
        <div>
          <Input
            id="filter-q"
            v-model="filters.q"
            @update:modelValue="(val: string | number) => updateFilter('q', val)"
            label="Busca"
            placeholder="Nome, código ou descrição..."
          />
        </div>

        <!-- Cidade com Autocomplete -->
        <div>
          <Autocomplete
            id="filter-cidade"
            :model-value="filters.cidade"
            :suggestions="filteredCidades"
            @update:modelValue="(val: string | number) => updateFilter('cidade', val)"
            @search="updateCidadeSearch"
            label="Cidade"
            placeholder="Ex: São Paulo"
          />
        </div>

        <!-- Categoria -->
        <div>
          <Autocomplete
            id="filter-categoria"
            :model-value="filters.categoria"
            :suggestions="filteredCategorias"
            @update:modelValue="(val: string | number) => updateFilter('categoria', val)"
            @search="updateCategoriaSearch"
            label="Categoria"
            placeholder="Ex: Apartamento"
          />
        </div>

        <!-- Condição -->
        <div>
          <Select
            id="filter-condicao"
            :model-value="filters.condicao"
            @update:modelValue="(val: string | number) => updateFilter('condicao', val)"
            label="Condição"
            :options="[
              { value: '', label: 'Todas' },
              ...CONDICAO_IMOVEL.map((c: any) => ({ value: c.value, label: c.label }))
            ]"
          />
        </div>

        <!-- Status -->
        <div>
          <Select
            id="filter-status"
            :model-value="filters.status"
            @update:modelValue="(val: string | number) => updateFilter('status', val)"
            label="Status"
            :options="[
              { value: '', label: 'Todos' },
              ...IMOVEL_STATUS.map((s: any) => ({ value: s.value, label: s.label }))
            ]"
          />
        </div>

        <!-- Faixa de Preço -->
        <div class="space-y-3">
          <label class="block text-sm font-medium text-[var(--text-primary)]">Faixa de Preço (venda)</label>
          <div class="grid grid-cols-2 gap-3">
            <Input
              id="filter-min-valor"
              :model-value="filters.min_valor"
              @update:modelValue="(val: string | number) => updateFilter('min_valor', val ? Number(val) : null)"
              type="number"
              placeholder="Mínimo"
            />
            <Input
              id="filter-max-valor"
              :model-value="filters.max_valor"
              @update:modelValue="(val: string | number) => updateFilter('max_valor', val ? Number(val) : null)"
              type="number"
              placeholder="Máximo"
            />
          </div>
        </div>

        <!-- Anúncio -->
        <div>
          <Select
            id="filter-has-listing"
            v-model="hasListingValue"
            label="Status do Anúncio"
            :options="listingOptions"
          />
        </div>

        <!-- Mínimo de anúncios (aparece quando 'Com anúncio' está selecionado) -->
        <div v-if="filters.has_listing">
          <Input
            id="filter-min-listings"
            :model-value="filters.min_listings"
            @update:modelValue="(val: string | number) => updateFilter('min_listings', val ? Number(val) : null)"
            label="Quantidade de anúncios"
            type="number"
            min="1"
            placeholder="1"
          />
        </div>

        <!-- Características mínimas -->
        <div class="space-y-3">
          <label class="block text-sm font-medium text-[var(--text-primary)]">Características Mínimas</label>
          <div class="grid grid-cols-2 gap-3">
            <Input
              id="filter-min-quartos"
              :model-value="filters.min_quartos"
              @update:modelValue="(val: string | number) => updateFilter('min_quartos', val ? Number(val) : null)"
              type="number"
              placeholder="Quartos"
              min="0"
            />
            <Input
              id="filter-min-vagas"
              :model-value="filters.min_vagas"
              @update:modelValue="(val: string | number) => updateFilter('min_vagas', val ? Number(val) : null)"
              type="number"
              placeholder="Vagas"
              min="0"
            />
          </div>
        </div>

        <!-- Ordenação -->
        <div class="space-y-3">
          <Select
            id="filter-sort"
            :model-value="filters.sort"
            @update:modelValue="(val: string | number) => updateFilter('sort', val)"
            label="Ordenar por"
            :options="sortOptions"
          />
          <Select
            v-if="filters.sort"
            id="filter-order"
            :model-value="filters.order"
            @update:modelValue="(val: string | number) => updateFilter('order', val as 'asc' | 'desc')"
            label="Direção"
            :options="orderOptions"
          />
        </div>
      </div>

      <SheetFooter class="flex flex-col sm:flex-row gap-2 justify-end">
        <Button
          variant="outline"
          :disabled="!hasActiveFilters || isLoading"
          @click="handleClear"
        >
          <X/>
          Limpar
        </Button>
        <Button
          variant="primary"
          :disabled="isLoading"
          @click="handleApply"
        >
          <Check/>
          {{ isLoading ? 'Aplicando...' : 'Aplicar Filtros' }}
        </Button>
      </SheetFooter>
    </SheetContent>
  </Sheet>
</template>
