<script setup lang="ts">
import { CONDICAO_IMOVEL } from '@/constants/condicaoImovel'
import { IMOVEL_STATUS } from '@/constants/imovelStatus'
import Card from '@/components/ui/Card.vue'
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
  const complemento = end.complemento || p.complemento || ''
  const referencia = end.referencia || p.referencia || ''
  const bairro = end.bairro || p.bairro || ''
  const cep = end.cep || p.cep || ''
  const cidade = end.cidade || p.cidade || ''
  const estado = end.estado || p.estado || ''

  const firstParts: string[] = []
  if (logradouro) firstParts.push(logradouro)
  if (numero) firstParts.push(numero)
  let line1 = firstParts.join(', ')
  if (complemento) line1 = line1 ? `${line1}, ${complemento}` : complemento
  if (referencia) line1 = line1 ? `${line1} - ${referencia}` : referencia

  const secondParts: string[] = []
  if (bairro) secondParts.push(bairro)
  if (cep) secondParts.push(cep)
  let line2 = secondParts.join(', ')
  const cidadeEstado = cidade ? `${cidade}${estado ? '/' + estado : ''}` : (estado ? `/${estado}` : '')
  if (cidadeEstado) line2 = line2 ? `${line2} - ${cidadeEstado}` : cidadeEstado

  const combined = [line1, line2].filter(Boolean).join('\n')
  if (combined) return combined

  return p.cidade || p.descricao || '—'
}

function handleOpenDetails() {
  emit('open-details', props.imovel)
}

function handleDelete() {
  emit('delete', props.imovel)
}
</script>

<template>
  <Card :ariaLabel="imovel.nome" class="hover:shadow-md" :class="{ 'ring-2 ring-primary': selectionMode && selected }">
    <template #image>
      <div @click="selectionMode ? emit('select', imovel, !selected) : handleOpenDetails()" class="block cursor-pointer" :aria-label="'Ver detalhes de ' + imovel.nome">
        <div class="relative h-32 md:h-48 w-full flex items-center justify-center">
          <img v-if="imovel.imageUrl" :src="imovel.imageUrl" :alt="imovel.nome" loading="lazy" class="object-cover w-full h-full" />
          <div v-else class="text-muted">Sem imagem</div>

          <div v-if="selectionMode" class="absolute top-2 left-2 z-10" @click.stop>
            <Checkbox :model-value="selected" @update:modelValue="(val: boolean) => emit('select', imovel, val)" variant="solid" aria-label="Selecionar imóvel" />
          </div>
          <div v-if="imovel.condicao" class="absolute top-2 right-2">
            <span :style="{ backgroundColor: getCondicaoMeta(imovel.condicao).color }" class="text-inverse text-xs px-2 py-1 rounded">{{ getCondicaoMeta(imovel.condicao).label }}</span>
          </div>
        </div>
      </div>
      <div class="p-2 md:p-4">
        <h2 class="font-semibold text-base md:text-lg truncate">{{ imovel.nome }}</h2>
        <div class="mt-2 text-sm text-muted space-y-1">
          <div class="flex items-center justify-between">
            <span class="text-xs text-muted-foreground">Código</span>
            <span class="ml-2 text-sm font-medium">{{ imovel.codigo || '—' }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-xs text-muted-foreground">Status</span>
            <span class="ml-2 text-sm font-medium">{{ getStatusLabel(imovel.status) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-xs text-muted-foreground">Venda</span>
            <span class="ml-2 text-sm font-medium">{{ formatCurrency(imovel.valores?.valor_venda) }}</span>
          </div>
          <div class="text-xs text-muted-foreground">{{ formatAddress(imovel) }}</div>
        </div>
      </div>
    </template>
    <template #footer>
      <div class="flex items-center justify-between p-2">
          <ListingToggleButton
            :listings="imovel.listings || []"
            :imovel-id="imovel.id"
            :imovel-nome="imovel.nome"
            @toggle-listing="() => emit('toggle-listing', imovel)"
          />

          <div class="flex items-center gap-2">
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
  </Card>
</template>
