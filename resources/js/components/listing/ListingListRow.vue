<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Eye, Edit, MapPin } from 'lucide-vue-next'

interface ListingItem {
  id: number
  anuncio_ativo: boolean
  anuncio_status?: string | null
  created_at: string
  imovel?: {
    id: number
    nome?: string
    codigo?: string
    categoria?: string
    cidade?: string
    bairro?: string
    valor_venda?: number | string | null
    valor_locacao?: number | string | null
    imageUrl?: string | null
  } | null
}

interface Props {
  listing: ListingItem
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'open-details', listing: ListingItem): void
}>()

function formatCurrency(value: number | string | null | undefined): string {
  if (value === null || value === undefined || value === '') return '—'
  const num = typeof value === 'string' ? parseFloat(value.replace(/,/g, '.')) : value
  if (isNaN(Number(num))) return '—'
  return Number(num).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

function handleOpenDetails() {
  emit('open-details', props.listing)
}
</script>

<template>
  <div class="nexa-list-row">
    <!-- Imagem pequena -->
    <div class="nexa-list-row-image">
      <img 
        v-if="listing.imovel?.imageUrl" 
        :src="listing.imovel.imageUrl" 
        :alt="listing.imovel.nome || 'Imagem do imóvel'" 
        loading="lazy" 
        class="object-cover" 
      />
      <div v-else class="flex items-center justify-center bg-muted text-muted-foreground text-xs">Sem imagem</div>
    </div>

    <!-- Conteúdo principal - clicável para abrir detalhes -->
    <div 
      class="nexa-list-row-content" 
      @click="handleOpenDetails" 
      role="button" 
      tabindex="0" 
      @keydown.enter="handleOpenDetails" 
      @keydown.space="handleOpenDetails"
    >
      <div class="nexa-list-row-header">
        <h3 class="nexa-list-row-title">{{ listing.imovel?.nome || 'Anúncio #' + listing.id }}</h3>
        <div class="nexa-list-row-status">
          <span 
            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
            :class="listing.anuncio_ativo ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'"
          >
            {{ listing.anuncio_ativo ? 'Ativo' : 'Inativo' }}
          </span>
        </div>
      </div>

      <!-- Informações em grid responsivo -->
      <div class="nexa-list-row-grid">
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">Código</span>
          <span class="nexa-list-row-value">{{ listing.imovel?.codigo || '—' }}</span>
        </div>
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">Categoria</span>
          <span class="nexa-list-row-value">{{ listing.imovel?.categoria || '—' }}</span>
        </div>
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">Venda</span>
          <span class="nexa-list-row-value font-semibold text-primary">{{ formatCurrency(listing.imovel?.valor_venda) }}</span>
        </div>
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">Locação</span>
          <span class="nexa-list-row-value">{{ formatCurrency(listing.imovel?.valor_locacao) }}</span>
        </div>
      </div>

      <!-- Endereço -->
      <div v-if="listing.imovel?.cidade || listing.imovel?.bairro" class="nexa-list-row-address flex items-center gap-1">
        <MapPin class="w-3 h-3 flex-shrink-0" />
        <span>{{ [listing.imovel?.bairro, listing.imovel?.cidade].filter(Boolean).join(', ') }}</span>
      </div>
    </div>

    <!-- Ações -->
    <div class="nexa-list-row-actions">
      <div class="flex items-center gap-3">
        <span class="text-xs text-muted-foreground whitespace-nowrap">{{ new Date(listing.created_at).toLocaleDateString('pt-BR') }}</span>
        <Link :href="`/admin/corretor/listings/${listing.id}`" class="text-sm text-muted-foreground hover:text-foreground flex items-center gap-1 transition-colors">
          <Eye class="w-4 h-4" />
          <span class="hidden sm:inline">Ver</span>
        </Link>
        <Link :href="`/admin/corretor/listings/${listing.id}/edit`" class="text-sm text-primary hover:underline flex items-center gap-1 transition-colors">
          <Edit class="w-4 h-4" />
          <span class="hidden sm:inline">Editar</span>
        </Link>
      </div>
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
  transition: color 0.2s ease;
}

.nexa-list-row-content:hover .nexa-list-row-title {
  color: var(--primary);
}

.nexa-list-row-status {
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

  .nexa-list-row-actions {
    width: 100%;
    justify-content: space-between;
  }
}
</style>
