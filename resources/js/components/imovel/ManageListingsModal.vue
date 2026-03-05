<script setup lang="ts">
import { ref, computed } from 'vue'
import axios from 'axios'
import { getListingStatusText } from '@/constants/listingStatus'
import { X, Plus, Trash2, Pencil } from 'lucide-vue-next'
import DialogScrollContent from '@/components/ui/dialog/DialogScrollContent.vue'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import {
  DialogRoot,
  DialogTrigger,
} from 'reka-ui'
import { Link } from '@inertiajs/vue3'

interface Listing {
  id: number
  anuncio_ativo: boolean
  anuncio_status?: string | null
  created_at?: string
  updated_at?: string
}

interface Props {
  open: boolean
  listings: Listing[]
  imovelId: number
  imovelNome: string
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'toggle-listing', listingId: number, active: boolean): void
  (e: 'updated'): void
}>()

const loading = ref<number | null>(null)
const deleting = ref<number | null>(null)

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

async function handleDelete(listingId: number) {
  if (!confirm('Tem certeza que deseja deletar este anúncio?')) return
  if (deleting.value) return

  deleting.value = listingId

  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    await axios.delete(
      `/admin/corretor/listings/${listingId}`,
      {
        headers: {
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
        withCredentials: true,
      }
    )

    emit('updated')
  } catch (err) {
    console.error('Falha ao deletar anúncio', err)
  } finally {
    deleting.value = null
  }
}

function formatDate(date?: string): string {
  if (!date) return '—'
  try {
    return new Date(date).toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return '—'
  }
}
</script>

<template>
  <DialogRoot :open="open" @update:open="(val) => !val && emit('close')">
    <DialogScrollContent class="max-w-2xl">
      <!-- Header -->
      <div class="flex items-start justify-between gap-4 mb-6">
        <div>
          <h2 class="text-lg font-semibold">Gerenciar Anúncios</h2>
          <p class="text-sm text-muted-foreground mt-1">{{ imovelNome }}</p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!listings || listings.length === 0" class="py-8 text-center">
        <p class="text-muted-foreground mb-4">Nenhum anúncio vinculado a este imóvel</p>
        <Link :href="route('admin.corretor.listings.create', { imovel_id: imovelId })">
          <Button variant="primary" size="sm">
            <Plus class="w-4 h-4 mr-2" />
            Criar Anúncio
          </Button>
        </Link>
      </div>

      <!-- Listings List -->
      <div v-else class="space-y-3">
        <Link
          v-for="(listing, index) in listings"
          :key="listing.id"
          :href="route('admin.corretor.listings.show', listing.id)"
          class="block border border-border rounded-lg p-4 hover:bg-[var(--list-row-hover)] hover:border-primary hover:shadow-md transition"
        >
          <!-- Header com número e status -->
          <div class="flex items-start justify-between mb-3">
            <div>
              <h3 class="font-semibold text-sm">Anúncio #{{ index + 1 }}</h3>
              <div v-if="listing.anuncio_status" class="text-xs text-muted-foreground mt-1">
                Status: {{ getListingStatusText(listing.anuncio_status) }}
              </div>
            </div>
            <div class="flex items-center gap-2">
              <!-- Status Badge -->
              <div
                class="px-2 py-1 rounded text-xs font-medium transition-colors"
                :class="listing.anuncio_ativo ? 'bg-primary/20 text-primary' : 'bg-muted text-muted-foreground'"
              >
                {{ listing.anuncio_ativo ? 'Ativo' : 'Inativo' }}
              </div>
            </div>
          </div>

          <!-- Info -->
          <div class="space-y-2 mb-4 text-xs text-muted-foreground">
            <div v-if="listing.created_at">
              <span>Criado em: {{ formatDate(listing.created_at) }}</span>
            </div>
            <div v-if="listing.updated_at">
              <span>Atualizado em: {{ formatDate(listing.updated_at) }}</span>
            </div>
          </div>

          <!-- Toggle ativo/inativo -->
          <div class="flex items-center justify-between">
            <div @click.stop>
              <Switch
                :model-value="listing.anuncio_ativo === true"
                :loading="loading === listing.id"
                :disabled="loading !== null"
                @update:model-value="(val) => handleToggle(listing, val)"
              />
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
              <Link
                :href="route('admin.corretor.listings.edit', listing.id)"
                class="inline-flex items-center justify-center w-8 h-8 rounded-md text-muted-foreground hover:text-white hover:bg-primary/10 transition"
                title="Editar anúncio"
                @click.stop
              >
                <Pencil class="w-4 h-4" />
              </Link>
              <button
                @click.stop
                @click="handleDelete(listing.id)"
                :disabled="deleting !== null"
                class="inline-flex items-center justify-center w-8 h-8 rounded-md text-muted-foreground hover:text-destructive hover:bg-destructive/10 transition disabled:opacity-50"
                title="Deletar anúncio"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </Link>
      </div>

      <!-- Footer -->
      <div v-if="listings && listings.length > 0" class="flex gap-2 mt-6 pt-4 border-t border-border">
        <Link :href="route('admin.corretor.listings.create', { imovel_id: imovelId })" class="flex-1">
          <Button variant="secondary" size="sm" class="w-full">
            <Plus class="w-4 h-4 mr-2" />
            Novo Anúncio
          </Button>
        </Link>
        <Button variant="ghost" size="sm" @click="emit('close')">
          Fechar
        </Button>
      </div>
    </DialogScrollContent>
  </DialogRoot>
</template>

<style scoped>
.dark .border-border.rounded-lg.p-4:hover {
  background: var(--list-row-hover-dark);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}
</style>
