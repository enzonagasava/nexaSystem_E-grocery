<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import api from '@/lib/axios';
import { CONDICAO_IMOVEL } from '@/constants/condicaoImovel';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
import ImovelDetailsModal from '@/components/imovel/ImovelDetailsModal.vue';
import ViewToggle from '@/components/ui/toggle/ViewToggle.vue';
import ImovelCardGrid from '@/components/imovel/ImovelCardGrid.vue';
import ImovelListRow from '@/components/imovel/ImovelListRow.vue';
import Button from '@/components/ui/button/Button.vue';
import FilterPanel from '@/components/filters/FilterPanel.vue';
import ActiveFiltersBar from '@/components/filters/ActiveFiltersBar.vue';
import { useFilters } from '@/composables/useFilters';
import { Plus, Trash2, Megaphone, Check, Filter } from 'lucide-vue-next';

// Interface para tipagem do imóvel na listagem
interface Listing {
  id: number;
  anuncio_ativo: boolean;
  anuncio_status?: string | null;
  created_at?: string;
  updated_at?: string;
}

interface ImovelListagem {
  id: number;
  nome: string;
  status?: string;
  descricao?: string | null;
  cidade?: string | null;
  imageUrl?: string | null;
  codigo?: string | null;
  valores?: { valor_venda?: number | null; valor_locacao?: number | null } | null;
  endereco?: any;
  condicao?: string | null;
  listing?: any;
  listings?: Listing[];
  created_at?: string | null;
}

const props = defineProps<{ produtos: any }>();

// Local reactive copy so we can update listing client-side without full page reload
const produtosLocal = ref(props.produtos);
watch(() => props.produtos, (v: any) => { produtosLocal.value = v });

// Estado do modal de detalhes
const showDetailsModal = ref(false);
const selectedImovel = ref<ImovelListagem | null>(null);

// Estado da visualização (grade ou lista)
const viewMode = ref<'grid' | 'list'>('grid');

// Seleção em massa
const selectionMode = ref(false)
const selectedIds = ref<number[]>([])
const bulkLoading = ref(false)

// Filtros
const showFilterPanel = ref(false)
const {
  filters,
  hasActiveFilters,
  activeFiltersCount,
  isLoading: filtersLoading,
  applyFilters,
  clearFilters,
  updateFilter,
  saveFavoriteFilters,
  getFavoriteFilters,
  applyFavoriteFilter,
  removeFavoriteFilter,
} = useFilters('admin.corretor.imoveis.index')


function handleRemoveFilter(key: keyof typeof filters.value) {
  updateFilter(key as any, key === 'has_listing' ? null : '')
  applyFilters()
}

function handleClearAllFilters() {
  clearFilters()
}

const selectedCount = computed(() => selectedIds.value.length)

const allSelected = computed<boolean>({
  get: () => {
    const data = produtosLocal.value?.data || []
    return data.length > 0 && selectedIds.value.length === data.length
  },
  set: (v: boolean) => {
    const data = produtosLocal.value?.data || []
    selectedIds.value = v ? data.map((p: ImovelListagem) => p.id) : []
  },
})

function isItemSelected(id: number) {
  return selectedIds.value.includes(id)
}

function handleSelectItem(imovel: ImovelListagem, value: boolean) {
  if (value && !selectedIds.value.includes(imovel.id)) {
    selectedIds.value.push(imovel.id)
  } else if (!value) {
    const idx = selectedIds.value.indexOf(imovel.id)
    if (idx !== -1) selectedIds.value.splice(idx, 1)
  }
}

function clearSelection() {
  selectedIds.value = []
  selectionMode.value = false
}

function toggleSelectionMode() {
  selectionMode.value = !selectionMode.value
  if (!selectionMode.value) selectedIds.value = []
}

// Recupera viewMode do localStorage ao montar o componente
onMounted(() => {
  const saved = localStorage.getItem('imoveis-view-mode')
  if (saved === 'grid' || saved === 'list') {
    viewMode.value = saved
  }
})

// Salva viewMode no localStorage quando muda
watch(viewMode, (newVal: string) => {
  localStorage.setItem('imoveis-view-mode', newVal)
})

const getCondicaoMeta = (value: string) => {
  const found = CONDICAO_IMOVEL.find((c) => c.value === value);
  if (found) {
    const color = (found as any).color || '';
    // Se a cor estiver num formato CSS direto (#, var(, rgb, hsl) usamos; caso contrário, fallback para token
    if (/^(#|var\(|rgb|hsl)/.test(color)) {
      return { label: found.label, color };
    }
    return { label: found.label, color: 'var(--primary)' };
  }
  return { label: value || '', color: 'var(--muted)' };
};

// Handlers para os componentes filhos
function handleOpenDetails(imovel: ImovelListagem) {
  selectedImovel.value = imovel;
  showDetailsModal.value = true;
}

function handleToggleListing(imovel: ImovelListagem) {
  // Just refresh the listings array with the updated state
  // The toggle is handled in the child component via API call
  // This is called after the toggle happens to update parent state if needed
  const idx = produtosLocal.value.data.findIndex((p: any) => p.id === imovel.id)
  if (idx !== -1) {
    produtosLocal.value.data.splice(idx, 1, Object.assign({}, produtosLocal.value.data[idx]))
  }
}

async function handleDeleteImovel(imovel: ImovelListagem) {
  const result = await Swal.fire({
    title: 'Excluir Imóvel?',
    text: `Tem certeza que deseja excluir permanentemente "${imovel.nome}"? O anúncio vinculado também será excluído.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Excluir',
    cancelButtonText: 'Cancelar',
  });

  if (result.isConfirmed) {
    try {
      await api.delete(route('admin.corretor.imoveis.destroy', { id: imovel.id }));
      
      // Remove do array local
      const idx = produtosLocal.value.data.findIndex((p: any) => p.id === imovel.id);
      if (idx !== -1) {
        produtosLocal.value.data.splice(idx, 1);
      }

      // Fechar modal se estava aberto
      if (selectedImovel.value && selectedImovel.value.id === imovel.id) {
        closeDetailsModal();
      }

      Swal.fire('Excluído!', 'O imóvel foi excluído com sucesso.', 'success');
    } catch (error: any) {
      console.error('Erro ao deletar imóvel:', error);
      
      // Extrair mensagem de erro do servidor ou mostrar genérica
      const errorMessage = 
        error?.response?.data?.message || 
        error?.response?.data?.error ||
        error?.message ||
        'Não foi possível excluir o imóvel. Tente novamente.';
      
      Swal.fire('Erro', errorMessage, 'error');
    }
  }
}

// Fecha o modal de detalhes
function closeDetailsModal() {
  showDetailsModal.value = false;
  selectedImovel.value = null;
}

// ── Bulk actions ── 
async function bulkCreateListings() {
  if (!selectedIds.value.length) return

  const ids = [...selectedIds.value]
  // Filtra apenas imóveis sem anuncio existente
  const data = produtosLocal.value?.data || []
  const withoutListing = ids.filter((id: number) => {
    const p = data.find((item: ImovelListagem) => item.id === id)
    return p && (!p.listings || p.listings.length === 0)
  })

  if (withoutListing.length === 0) {
    Swal.fire('Aviso', 'Todos os imóveis selecionados já possuem anúncio.', 'info')
    return
  }

  const result = await Swal.fire({
    title: 'Criar Anúncios',
    text: `Deseja criar anúncio para ${withoutListing.length} imóvel(is)?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sim, criar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true,
  })
  if (!result.isConfirmed) return

  bulkLoading.value = true
  let created = 0
  let failed = 0

  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    for (const imovelId of withoutListing) {
      try {
        await axios.post(
          route('admin.corretor.listings.store'),
          { imovel_id: imovelId, anuncio_ativo: true },
          {
            headers: {
              'X-CSRF-TOKEN': csrf,
              'X-Requested-With': 'XMLHttpRequest',
              Accept: 'application/json',
            },
            withCredentials: true,
          }
        )
        created++
      } catch (err) {
        console.error('Erro ao criar anúncio para imóvel', imovelId, err)
        failed++
      }
    }

    if (created > 0) {
      Swal.fire('Sucesso', `${created} anúncio(s) criado(s) com sucesso.${failed ? ` ${failed} falharam.` : ''}`, 'success')
        .then(() => { window.location.reload() })
    } else {
      Swal.fire('Erro', 'Não foi possível criar os anúncios.', 'error')
    }
  } finally {
    bulkLoading.value = false
    clearSelection()
  }
}

async function bulkDeleteImoveis() {
  if (!selectedIds.value.length) return

  const ids = [...selectedIds.value]
  const result = await Swal.fire({
    title: 'Excluir Imóveis',
    text: `Tem certeza que deseja excluir permanentemente ${ids.length} imóvel(is)? Os anúncios vinculados também serão excluídos.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Excluir',
    cancelButtonText: 'Cancelar',
  })
  if (!result.isConfirmed) return

  bulkLoading.value = true
  let deleted = 0
  let failed = 0

  try {
    for (const id of ids) {
      try {
        await api.delete(route('admin.corretor.imoveis.destroy', { id }))
        // Remove do array local
        const idx = produtosLocal.value.data.findIndex((p: any) => p.id === id)
        if (idx !== -1) produtosLocal.value.data.splice(idx, 1)
        deleted++
      } catch (err) {
        console.error('Erro ao deletar imóvel', id, err)
        failed++
      }
    }

    if (deleted > 0) {
      Swal.fire('Excluído!', `${deleted} imóvel(is) excluído(s) com sucesso.${failed ? ` ${failed} falharam.` : ''}`, 'success')
    } else {
      Swal.fire('Erro', 'Não foi possível excluir os imóveis.', 'error')
    }
  } finally {
    bulkLoading.value = false
    clearSelection()
  }
}

// Atualiza os dados do imóvel na listagem após edição no modal
function handleImovelUpdated(updatedImovel: any) {
  if (!produtosLocal?.value?.data) return;

  const index = produtosLocal.value.data.findIndex((p: ImovelListagem) => p.id === updatedImovel.id);
  if (index === -1) return;

  // Normalize to the card shape
  const existing = produtosLocal.value.data[index] || {};
  const cardItem: ImovelListagem = {
    id: updatedImovel.id,
    nome: updatedImovel.nome ?? existing.nome,
    status: updatedImovel.status ?? existing.status,
    descricao: updatedImovel.descricao ?? existing.descricao,
    condicao: updatedImovel.condicao ?? existing.condicao,
    cidade: (updatedImovel.endereco?.cidade ?? updatedImovel.cidade) ?? existing.cidade,
    codigo: updatedImovel.codigo ?? existing.codigo,
    valores: updatedImovel.valores ?? (updatedImovel.valor_venda || updatedImovel.valor_locacao ? { valor_venda: updatedImovel.valor_venda, valor_locacao: updatedImovel.valor_locacao } : existing.valores),
    endereco: updatedImovel.endereco ?? existing.endereco,
    imageUrl: updatedImovel.imageUrl ?? existing.imageUrl,
    listing: updatedImovel.listing ?? existing.listing ?? null,
    created_at: existing.created_at ?? null,
  };

  // Replace item to ensure reactivity
  produtosLocal.value.data.splice(index, 1, cardItem);

  // Close modal if open and the selected item matches
  if (selectedImovel.value && selectedImovel.value.id === cardItem.id) {
    selectedImovel.value = cardItem;
  }
}
</script>

<template>
  <Head>
    <title>Imóveis - Corretor</title>
  </Head>
  <AuthLayout modulo="corretor">
    <div class="p-6">
      <!-- Header com título e toggle de visualização -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-xl font-semibold">Meus Imóveis</h1>
          <p class="text-sm text-muted-foreground">Imóveis criados pelo corretor</p>
        </div>
        <div class="flex items-center gap-3">
          <Button 
            v-if="!selectionMode" 
            size="sm" 
            :variant="hasActiveFilters ? 'primary' : 'ghost'"
            @click="showFilterPanel = true"
          >
            <Filter class="w-4 h-4 mr-1" />
            <span class="text-xs">Filtrar</span>
            <span v-if="activeFiltersCount > 0" class="ml-1.5 px-2 py-0.5 text-xs font-semibold bg-white/30 dark:bg-black/30 rounded-full">
              {{ activeFiltersCount }}
            </span>
          </Button>
          
          <Button v-if="!selectionMode" size="sm" variant="ghost" @click="toggleSelectionMode">
            <Check class="w-4 h-4 mr-1" />
            <span class="text-xs">Selecionar</span>
          </Button>
          <Link :href="route('admin.corretor.imoveis.create')">
            <Button variant="primary">
              <Plus class="w-4 h-4 mr-2" />
              Criar Imóvel
            </Button>
          </Link>
          <ViewToggle v-model="viewMode" />
        </div>
      </div>

      <!-- Barra de filtros ativos -->
      <ActiveFiltersBar
        :filters="filters"
        :active-filters-count="activeFiltersCount"
        @remove-filter="handleRemoveFilter"
        @clear-all="handleClearAllFilters"
      />

      <!-- Barra de ações em massa -->
      <div v-if="selectionMode" class="mb-4 flex items-center justify-between p-3 rounded-lg">
        <div class="flex items-center gap-3">
          <Checkbox v-model="allSelected" variant="solid" aria-label="Selecionar todos" />
          <span class="text-sm font-medium">{{ selectedCount }} selecionado(s)</span>
        </div>
        <div class="flex items-center gap-2">
          <Button size="sm" :disabled="selectedCount === 0 || bulkLoading" @click="bulkCreateListings">
            <Megaphone class="w-4 h-4 mr-1" />
            <span class="text-xs">Criar Anúncio(s)</span>
          </Button>
          <Button size="sm" variant="destructive" :disabled="selectedCount === 0 || bulkLoading" @click="bulkDeleteImoveis">
            <Trash2 class="w-4 h-4 mr-1" />
            <span class="text-xs">Excluir ({{ selectedCount }})</span>
          </Button>
          <Button size="sm" variant="outline" @click="clearSelection">Cancelar</Button>
        </div>
      </div>

      <!-- Conteúdo da listagem -->
      <div v-if="produtosLocal && produtosLocal.data" class="space-y-4">
        <!-- Visualização em Grade -->
        <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 auto-rows-fr">
          <ImovelCardGrid
            v-for="p in produtosLocal.data"
            :key="p.id"
            :imovel="p"
            :selection-mode="selectionMode"
            :selected="isItemSelected(p.id)"
            @open-details="handleOpenDetails"
            @toggle-listing="handleToggleListing"
            @delete="handleDeleteImovel"
            @select="handleSelectItem"
          />
        </div>

        <!-- Visualização em Lista -->
        <div v-else class="space-y-2">
          <ImovelListRow
            v-for="p in produtosLocal.data"
            :key="p.id"
            :imovel="p"
            :selection-mode="selectionMode"
            :selected="isItemSelected(p.id)"
            @open-details="handleOpenDetails"
            @toggle-listing="handleToggleListing"
            @delete="handleDeleteImovel"
            @select="handleSelectItem"
          />
        </div>
      </div>

      <div v-else class="text-gray-500">Nenhum imóvel encontrado.</div>
    </div>
    
    <!-- Modal de detalhes/edição do imóvel -->
    <ImovelDetailsModal
      :open="showDetailsModal"
      :imovel="selectedImovel"
      @close="closeDetailsModal"
      @updated="handleImovelUpdated"
    />

    <!-- Painel de filtros -->
    <FilterPanel
      :open="showFilterPanel"
      :filters="filters"
      :is-loading="filtersLoading"
      :has-active-filters="hasActiveFilters"
      :favorite-filters="getFavoriteFilters()"
      @update:open="showFilterPanel = $event"
      @apply="applyFilters"
      @clear="clearFilters"
      @update:filter="updateFilter"
      @save-favorite="saveFavoriteFilters"
      @apply-favorite="applyFavoriteFilter"
      @remove-favorite="removeFavoriteFilter"
    />
  </AuthLayout>
</template>
