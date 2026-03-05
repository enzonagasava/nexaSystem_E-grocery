<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import { getListingStatusText } from '@/constants/listingStatus'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import ManageListingsModal from './ManageListingsModal.vue'

interface Listing {
  id: number
  anuncio_ativo: boolean
  anuncio_status?: string | null
  created_at?: string
  updated_at?: string
}

interface Props {
  listings: Listing[]
  imovelId: number
  imovelNome: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'toggle-listing', listingId: number, active: boolean): void
}>()

const loading = ref<number | null>(null)
const showModal = ref(false)

async function handleToggle(listing: Listing, checked: boolean) {
  if (loading.value) return

  loading.value = listing.id

  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    await axios.put(
      `/admin/corretor/listings/${listing.id}`,
      { anuncio_ativo: checked },
      {
        headers: {
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
        withCredentials: true,
      }
    )

    listing.anuncio_ativo = checked
    emit('toggle-listing', listing.id, checked)
  } catch (err) {
    console.error('Falha ao atualizar anúncio', err)
  } finally {
    loading.value = null
  }
}
</script>

<template>
  <!-- Sem anúncios -->
  <div v-if="!listings || listings.length === 0" class="inline-flex items-center gap-2">
    <span class="text-sm text-muted-foreground">Anúncio:</span>
    <Link
      :href="route('admin.corretor.listings.create', { imovel_id: imovelId })"
      class="text-sm text-primary hover:text-primary/80 transition font-medium"
    >
      Criar anúncio
    </Link>
  </div>

  <!-- Um único anúncio - mostrar toggle simples -->
  <div v-else-if="listings.length === 1" class="inline-flex items-center gap-3 text-sm">
    <span class="text-sm text-muted-foreground">Anúncio:</span>
    <Switch
      :model-value="listings[0].anuncio_ativo === true"
      :loading="loading !== null"
      @update:model-value="(val) => handleToggle(listings[0], val)"
    />
  </div>

  <!-- Múltiplos anúncios - mostrar botão para abrir modal -->
  <div v-else class="inline-flex items-center gap-3 text-sm">
    <span class="text-sm text-muted-foreground">Anúncios:</span>
    <Button
      variant="secondary"
      size="sm"
      @click="showModal = true"
      class="font-semibold"
    >
      {{ listings.length }} anúncio{{ listings.length !== 1 ? 's' : '' }}
    </Button>
  </div>

  <!-- Modal de gerenciamento -->
  <ManageListingsModal
    :open="showModal"
    :listings="listings"
    :imovel-id="imovelId"
    :imovel-nome="imovelNome"
    @close="showModal = false"
    @toggle-listing="(id, active) => emit('toggle-listing', id, active)"
    @updated="$emit('toggle-listing', listings[0].id, listings[0].anuncio_ativo)"
  />
</template>
