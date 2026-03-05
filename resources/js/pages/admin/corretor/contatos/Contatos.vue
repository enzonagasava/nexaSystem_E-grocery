<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import ViewToggle from '@/components/ui/toggle/ViewToggle.vue';
import ContatoCardGrid from '@/components/admin/corretor/contatos/table/ContatoCardGrid.vue';
import ContatoListRow from '@/components/admin/corretor/contatos/table/ContatoListRow.vue';
import ContatoDetailsModal from '@/components/admin/corretor/contatos/modal/ContatoDetailsModal.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Select } from '@/components/ui/select';
import { Search, Filter, Plus, UserPlus } from 'lucide-vue-next';
import type { ContatoListagem } from '@/types/forms/contato-form';

const props = defineProps<{ 
  contatos: {
    data: ContatoListagem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  } | ContatoListagem[] 
  status: any
}>();

// Estado
const viewMode = ref<'grid' | 'list'>('grid');
const searchTerm = ref('');
const statusFilter = ref('todos');
const tipoFilter = ref('todos');
const showFilters = ref(false);
const selectedContato = ref<ContatoListagem | null>(null);
const showDetailsModal = ref(false);

// Computed para normalizar os contatos
const contatosList = computed<ContatoListagem[]>(() => {
  if (Array.isArray(props.contatos)) {
    return props.contatos;
  } else if (props.contatos?.data && Array.isArray(props.contatos.data)) {
    return props.contatos.data;
  }
  return [];
});

// Filtros
const filteredContatos = computed(() => {
  let filtered = [...contatosList.value];
  
  // Filtro por termo de busca
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase();
    filtered = filtered.filter(c => 
      c.nome_completo?.toLowerCase().includes(term) ||
      c.email?.toLowerCase().includes(term) ||
      c.cpf?.includes(term) ||
      c.telefone?.includes(term)
    );
  }
  
  // Filtro por status
  if (statusFilter.value !== 'todos') {
    filtered = filtered.filter(c => c.status === statusFilter.value);
  }
  
  // Filtro por tipo de relação
  if (tipoFilter.value !== 'todos') {
    filtered = filtered.filter(c => c.tipo_relacao === tipoFilter.value);
  }
  
  return filtered;
});

// Handlers
function handleOpenDetails(contato: ContatoListagem) {
  selectedContato.value = contato;
  showDetailsModal.value = true;
}

function handleEdit(contato: ContatoListagem) {
  router.get(route('admin.corretor.contatos.edit', { id: contato.id }));
}

function handleCloseDetails() {
  showDetailsModal.value = false;
  selectedContato.value = null;
}

function handleContatoUpdated(updatedContato: ContatoListagem) {
  if (!contatosList.value) return;

  const index = contatosList.value.findIndex((c: ContatoListagem) => c.id === updatedContato.id);
  if (index === -1) return;

  // Atualiza a lista
  if (Array.isArray(props.contatos)) {
    (props.contatos as ContatoListagem[])[index] = updatedContato;
  } else if (props.contatos?.data) {
    props.contatos.data[index] = updatedContato;
  }

  // Atualiza o contato selecionado se necessário
  if (selectedContato.value?.id === updatedContato.id) {
    selectedContato.value = updatedContato;
  }
}

function handleToggleListing(contato: ContatoListagem, checked: boolean) {
  const idx = contatosList.value.findIndex(c => c.id === contato.id);
  if (idx !== -1) {
    contato.adicionar_rodizio = checked;
    
    // TODO: chamar API para persistir
    // axios.put(`/admin/corretor/contatos/${contato.id}`, { adicionar_rodizio: checked })
  }
}

function criarNovoContato() {
  router.get(route('admin.corretor.contatos.create'));
}

// Opções para filtros
const statusOptions = [
  { value: 'todos', label: 'Todos os status' },
  { value: 'ativo', label: 'Ativo' },
  { value: 'inativo', label: 'Inativo' },
  { value: 'negociacao', label: 'Em negociação' },
  { value: 'fechado', label: 'Fechado' },
  { value: 'perdido', label: 'Perdido' }
];

const tipoRelacaoOptions = [
  { value: 'todos', label: 'Todos os tipos' },
  { value: 'cliente', label: 'Cliente' },
  { value: 'investidor', label: 'Investidor' },
  { value: 'parceiro', label: 'Parceiro' },
  { value: 'indicador', label: 'Indicador' },
  { value: 'proprietario', label: 'Proprietário' }
];
</script>

<template>
  <Head>
    <title>Contatos - Corretor</title>
  </Head>
  
  <AuthLayout>
    <div class="p-6">
      <!-- Header com título e ações -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-semibold text-[var(--text-primary)]">Contatos</h1>
          <p class="text-sm text-[var(--text-muted)] mt-1">
            Gerencie todos os seus contatos e clientes
          </p>
        </div>
        
        <div class="flex items-center gap-3 w-full sm:w-auto">
          <Button 
            variant="primary" 
            @click="criarNovoContato"
            class="w-full sm:w-auto"
          >
            <UserPlus class="w-4 h-4 mr-2" />
            Novo Contato
          </Button>
          <ViewToggle v-model="viewMode" />
        </div>
      </div>

      <!-- Barra de busca e filtros -->
      <div class="mb-6 space-y-3">
        <div class="flex items-center gap-3">
          <div class="relative flex-1">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-muted)]" />
            <Input
              v-model="searchTerm"
              type="text"
              placeholder="Buscar por nome, email, CPF ou telefone..."
              class="pl-9"
            />
          </div>
          <Button 
            variant="outline" 
            @click="showFilters = !showFilters"
            :class="{ 'bg-primary/10 border-primary': showFilters }"
          >
            <Filter class="w-4 h-4 mr-2" />
            Filtros
          </Button>
        </div>

        <!-- Filtros expandidos -->
        <div v-if="showFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-4 bg-[var(--card)] border border-[var(--border)] rounded-lg">
          <Select
            v-model="statusFilter"
            label="Status"
            :options="statusOptions"
          />
          
          <Select
            v-model="tipoFilter"
            label="Tipo de Relação"
            :options="tipoRelacaoOptions"
          />
        </div>
      </div>

      <!-- Contadores e resultados -->
      <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-[var(--text-muted)]">
          Mostrando {{ filteredContatos.length }} de {{ contatosList.length }} contatos
        </p>
      </div>

      <!-- Visualizações -->
      <div v-if="filteredContatos.length > 0">
        <!-- Visualização em Grade -->
        <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          <ContatoCardGrid 
            v-for="contato in filteredContatos" 
            :key="contato.id"
            :contato="contato"
            @open-details="handleOpenDetails"
            @edit="handleEdit"
            @toggle-listing="handleToggleListing"
          />
        </div>

        <!-- Visualização em Lista -->
        <div v-else-if="viewMode === 'list'" class="space-y-2">
          <ContatoListRow
            v-for="contato in filteredContatos"
            :key="contato.id"
            :contato="contato"
            @open-details="handleOpenDetails"
            @edit="handleEdit"
            @toggle-listing="handleToggleListing"
          />
        </div>
      </div>

      <!-- Estado vazio -->
      <div v-else class="text-center py-12 bg-[var(--card)] rounded-lg border border-[var(--border)]">
        <UserPlus class="w-12 h-12 mx-auto text-[var(--text-muted)] mb-3" />
        <h3 class="text-lg font-medium text-[var(--text-primary)] mb-1">Nenhum contato encontrado</h3>
        <p class="text-sm text-[var(--text-muted)] mb-4">
          {{ searchTerm ? 'Tente ajustar seus filtros ou busca' : 'Comece adicionando seu primeiro contato' }}
        </p>
        <Button variant="primary" @click="criarNovoContato">
          <Plus class="w-4 h-4 mr-2" />
          Novo Contato
        </Button>
      </div>
    </div>

    <!-- Modal de detalhes -->
    <ContatoDetailsModal
      :open="showDetailsModal"
      :contato="selectedContato"
      :status="props.status"
      @close="handleCloseDetails"
      @updated="handleContatoUpdated"
    />
  </AuthLayout>
</template>