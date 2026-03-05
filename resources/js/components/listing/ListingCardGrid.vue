<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Eye, Edit, MapPin } from 'lucide-vue-next'
import Card from '@/components/ui/Card.vue'

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
  <Card :ariaLabel="listing.imovel?.nome || 'Anúncio #' + listing.id" class="hover:shadow-md">
    <template #image>
      <div @click="handleOpenDetails" class="block cursor-pointer" :aria-label="'Ver detalhes de ' + (listing.imovel?.nome || 'Anúncio')">
        <div class="relative h-48 w-full flex items-center justify-center overflow-hidden">
          <img 
            v-if="listing.imovel?.imageUrl" 
            :src="listing.imovel.imageUrl" 
            :alt="listing.imovel.nome || 'Imagem do imóvel'" 
            loading="lazy" 
            class="object-cover w-full h-full transition-transform hover:scale-105" 
          />
          <div v-else class="text-muted">Sem imagem</div>

          <div class="absolute top-2 right-2">
            <span 
              class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
              :class="listing.anuncio_ativo ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'"
            >
              {{ listing.anuncio_ativo ? 'Ativo' : 'Inativo' }}
            </span>
          </div>
        </div>
      </div>

      <div class="p-4" @click="handleOpenDetails">
        <h2 class="font-semibold text-lg truncate cursor-pointer hover:text-primary transition-colors">
          {{ listing.imovel?.nome || 'Anúncio #' + listing.id }}
        </h2>
        
        <p v-if="listing.imovel?.codigo" class="text-xs text-muted-foreground mt-1">
          Código: {{ listing.imovel.codigo }}
        </p>
        
        <p v-if="listing.imovel?.cidade || listing.imovel?.bairro" class="text-sm text-muted-foreground flex items-center gap-1 mt-2">
          <MapPin class="w-3 h-3" />
          {{ [listing.imovel?.bairro, listing.imovel?.cidade].filter(Boolean).join(', ') }}
        </p>
        
        <div v-if="listing.imovel?.valor_venda || listing.imovel?.valor_locacao" class="mt-3 text-primary font-semibold">
          <span v-if="listing.imovel?.valor_venda">{{ formatCurrency(listing.imovel.valor_venda) }}</span>
          <span v-else-if="listing.imovel?.valor_locacao">{{ formatCurrency(listing.imovel.valor_locacao) }}/mês</span>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="flex items-center justify-between border-t pt-3 px-4 pb-3">
        <div class="flex items-center gap-2">
          <Link :href="`/admin/corretor/listings/${listing.id}`" class="text-sm text-muted-foreground hover:text-foreground flex items-center gap-1 transition-colors">
            <Eye class="w-4 h-4" />
            <span>Detalhes</span>
          </Link>
          <Link :href="`/admin/corretor/listings/${listing.id}/edit`" class="text-sm text-primary hover:underline flex items-center gap-1 transition-colors">
            <Edit class="w-4 h-4" />
            <span>Editar</span>
          </Link>
        </div>
        <span class="text-xs text-muted-foreground">{{ new Date(listing.created_at).toLocaleDateString('pt-BR') }}</span>
      </div>
    </template>
  </Card>
</template>
