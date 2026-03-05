<script setup lang="ts">
import { CONDICAO_IMOVEL } from '@/constants/condicaoImovel'
import { IMOVEL_STATUS } from '@/constants/imovelStatus'
import Checkbox from '@/components/ui/checkbox/Checkbox.vue'
import { Link } from '@inertiajs/vue3'
import { Eye, Trash2 } from 'lucide-vue-next'
import ListingToggleButton from './ListingToggleButton.vue'

interface Listing {
  id: number
  anuncio_ativo: boolean
  anuncio_status?: string | null
  created_at?: string
  updated_at?: string
}

interface ImovelListagem {
  id: number
  nome: string
  status?: string
  descricao?: string | null
  cidade?: string | null
  imageUrl?: string | null
  codigo?: string | null
  valores?: { valor_venda?: number | null; valor_locacao?: number | null } | null
  endereco?: any
  condicao?: string | null
  listing?: any
  listings?: Listing[]
  created_at?: string | null
}

interface Props {
  imovel: ImovelListagem
  selectionMode?: boolean
  selected?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  selectionMode: false,
  selected: false,
})

const emit = defineEmits<{
  (e: 'open-details', imovel: ImovelListagem): void
  (e: 'toggle-listing', imovel: ImovelListagem): void
  (e: 'delete', imovel: ImovelListagem): void
  (e: 'select', imovel: ImovelListagem, value: boolean): void
}>()

const getCondicaoMeta = (value: string) => {
  const found = CONDICAO_IMOVEL.find((c) => c.value === value)
  if (found) {
    const color = (found as any).color || ''
    if (/^(#|var\(|rgb|hsl)/.test(color)) {
      return { label: found.label, color }
    }
    return { label: found.label, color: 'var(--primary)' }
  }
  return { label: value || '', color: 'var(--muted)' }
}

function getStatusLabel(status?: string): string {
  const found = IMOVEL_STATUS.find((item: any) => item.value === status)
  return found?.label || status || '—'
}

function formatCurrency(value: number | string | null | undefined): string {
  if (value === null || value === undefined || value === '') return '—'
  const num = typeof value === 'string' ? parseFloat(value.replace(/,/g, '.')) : value
  if (isNaN(Number(num))) return '—'
  return Number(num).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

function formatAddress(p: any): string {
  const end = p.endereco || {}
  const logradouro = end.endereco || p.endereco || ''
  const numero = end.numero || p.numero || ''
  const cidade = end.cidade || p.cidade || ''
  const estado = end.estado || p.estado || ''

  const partes = [logradouro, numero].filter(Boolean).join(', ')
  const cidadeEstado = cidade ? `${cidade}${estado ? '/' + estado : ''}` : (estado ? `/${estado}` : '')

  return [partes, cidadeEstado].filter(Boolean).join(' - ') || p.cidade || p.descricao || '—'
}

function handleOpenDetails() {
  emit('open-details', props.imovel)
}

function handleDelete() {
  emit('delete', props.imovel)
}
</script>

<template>
  <div class="nexa-list-row" :class="{ 'ring-2 ring-primary': selectionMode && selected }">
    <!-- Checkbox de seleção -->
    <div v-if="selectionMode" class="flex items-center justify-center flex-shrink-0">
      <Checkbox :model-value="selected" @update:modelValue="(val: boolean) => emit('select', imovel, val)" variant="solid" aria-label="Selecionar imóvel" />
    </div>

    <!-- Imagem pequena -->
    <div class="nexa-list-row-image" @click="selectionMode ? emit('select', imovel, !selected) : undefined">
      <img v-if="imovel.imageUrl" :src="imovel.imageUrl" :alt="imovel.nome" loading="lazy" class="object-cover" />
      <div v-else class="flex items-center justify-center bg-muted text-muted-foreground text-xs">Sem imagem</div>
    </div>

    <!-- Conteúdo principal - clicável para abrir detalhes -->
    <div class="nexa-list-row-content" @click="handleOpenDetails" role="button" tabindex="0" @keydown.enter="handleOpenDetails" @keydown.space="handleOpenDetails">
      <div class="nexa-list-row-header">
        <h3 class="nexa-list-row-title">{{ imovel.nome }}</h3>
        <div v-if="imovel.condicao" class="nexa-list-row-condicao">
          <span :style="{ backgroundColor: getCondicaoMeta(imovel.condicao).color }" class="text-inverse text-xs px-2 py-1 rounded">
            {{ getCondicaoMeta(imovel.condicao).label }}
          </span>
        </div>
      </div>

      <!-- Informações em grid responsivo -->
      <div class="nexa-list-row-grid">
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">Código</span>
          <span class="nexa-list-row-value">{{ imovel.codigo || '—' }}</span>
        </div>
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">Status</span>
          <span class="nexa-list-row-value">{{ getStatusLabel(imovel.status) }}</span>
        </div>
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">Venda</span>
          <span class="nexa-list-row-value font-semibold">{{ formatCurrency(imovel.valores?.valor_venda) }}</span>
        </div>
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">Locação</span>
          <span class="nexa-list-row-value">{{ formatCurrency(imovel.valores?.valor_locacao) }}</span>
        </div>
      </div>

      <!-- Endereço na parte inferior -->
      <div class="nexa-list-row-address">{{ formatAddress(imovel) }}</div>
    </div>

    <!-- Toggle anúncio ativo -->
    <div class="flex items-center justify-start p-2">
        <ListingToggleButton
          :listings="imovel.listings || []"
          :imovel-id="imovel.id"
          :imovel-nome="imovel.nome"
          @toggle-listing="() => emit('toggle-listing', imovel)"
        />
      </div>

      <div class="nexa-list-row-actions">
        <Link
          :href="route('admin.corretor.imoveis.show', imovel.id)"
          class="inline-flex items-center justify-center w-8 h-8 rounded-md text-muted hover:bg-primary transition"
          aria-label="Ver detalhes do imóvel"
          title="Ver detalhes"
        >
          <Eye class="w-4 h-4" />
        </Link>
        <button
          @click="handleDelete"
          class="inline-flex items-center justify-center w-8 h-8 rounded-md text-red-500 hover:bg-red-50 dark:hover:bg-red-950 transition"
          aria-label="Excluir imóvel"
          title="Excluir"
          type="button"
        >
          <Trash2 class="w-4 h-4" />
        </button>
      </div>
  </div>
</template>

<style scoped>
/* Estilos de linha da lista - usar CSS variables */
.nexa-list-row {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  background: var(--card);
  transition: all 0.2s ease;
  align-items: center;
}

.nexa-list-row:hover {
  background: var(--list-row-hover);
  border-color: var(--primary);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.dark .nexa-list-row:hover {
  background: var(--list-row-hover-dark);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.nexa-list-row-image {
  flex-shrink: 0;
  width: 80px;
  height: 80px;
  border-radius: var(--radius);
  overflow: hidden;
  background: var(--muted);
  display: flex;
  align-items: center;
  justify-content: center;
}

.nexa-list-row-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.nexa-list-row-content {
  flex: 1;
  min-width: 0;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.nexa-list-row-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  justify-content: space-between;
  flex-wrap: wrap;
}

.nexa-list-row-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
  flex-shrink: 0;
  word-break: break-word;
}

.nexa-list-row-condicao {
  flex-shrink: 0;
}

.nexa-list-row-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
  font-size: 0.875rem;
}

.nexa-list-row-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.nexa-list-row-label {
  font-size: 0.75rem;
  color: var(--text-muted);
  text-transform: uppercase;
  font-weight: 500;
  letter-spacing: 0.5px;
}

.nexa-list-row-value {
  color: var(--text-primary);
  font-weight: 500;
}

.nexa-list-row-address {
  font-size: 0.8125rem;
  color: var(--text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  width: 100%;
}

.nexa-list-row-actions {
  flex-shrink: 0;
  display: flex;
  align-items: center;
}

.nexa-list-row-actions label {
  position: relative;
}

/* Responsivo */
@media (max-width: 640px) {
  .nexa-list-row {
    flex-direction: column;
    padding: 0.75rem;
  }

  .nexa-list-row-image {
    width: 100%;
    height: 150px;
  }

  .nexa-list-row-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
  }

  .nexa-list-row-header {
    width: 100%;
  }

  .nexa-list-row-address {
    width: 100%;
  }
}
</style>
