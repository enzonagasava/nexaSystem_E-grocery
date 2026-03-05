<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import ViewToggle from '@/components/ui/toggle/ViewToggle.vue';
import LeadCardGrid from '@/components/admin/corretor/leads/table/LeadCardGrid.vue';
import LeadListRow from '@/components/admin/corretor/leads/table/LeadListRow.vue';
import { LeadListagem } from '@/types/forms/lead-form';
import LeadDetailsModal from '@/components/admin/corretor/leads/modal/LeadDetailsModal.vue';

const props = defineProps<{ 
  leads: LeadListagem[] 
}>();

const leadsLocal = ref(props.leads);
const viewMode = ref<'grid' | 'list'>('grid'); // 👈 ADICIONE 'kanban'
const selectedLead = ref<LeadListagem | null>(null);
const showDetailsModal = ref(false);

// Handlers
function handleOpenDetails(lead: LeadListagem) {
  selectedLead.value = lead;
  showDetailsModal.value = true;
  // Ou redirecionar para edição
  // router.get(route('admin.corretor.leads.edit', { id: lead.id }));
}

function closeDetailsModal() {
  showDetailsModal.value = false;
  selectedLead.value = null;
}



function handleToggleListing(lead: LeadListagem, checked: boolean) {
  const idx = leadsLocal.value.findIndex(l => l.id === lead.id);
  if (idx !== -1) {
    lead.adicionar_rodizio = checked;
    leadsLocal.value[idx] = { ...lead };
    
    // TODO: chamar API para persistir
  }
}

// 👈 NOVO HANDLER PARA ATUALIZAR STATUS VIA KANBAN
function handleUpdateStatus(lead: LeadListagem, newStatus: string) {
  console.log(`Movendo lead ${lead.id} de ${lead.status} para ${newStatus}`);
  
  // Atualiza localmente
  const idx = leadsLocal.value.findIndex(l => l.id === lead.id);
  if (idx !== -1) {
    leadsLocal.value[idx] = {
      ...leadsLocal.value[idx],
      status: newStatus
    };
  }
  
  // TODO: Chamar API para persistir a mudança
  // axios.put(`/admin/corretor/leads/${lead.id}`, { status: newStatus })
  //   .catch(error => console.error('Erro ao atualizar status:', error));
}
function handleLeadUpdated(updatedLead: LeadListagem) {
  if (!leadsLocal?.value) return;

  const index = leadsLocal.value.findIndex((lead: LeadListagem) => lead.id === updatedLead.id);
  if (index === -1) return;

  // Preserva o lead existente
  const existing = leadsLocal.value[index];
  
  // Usa spread operator para mesclar mantendo a tipagem
  const updatedItem: LeadListagem = {
    ...existing,
    ...updatedLead,
    // Garante que campos específicos sejam mesclados corretamente
    contatos: updatedLead.contatos ?? existing.contatos,
    corretor: updatedLead.corretor ?? existing.corretor,
    imovel_interesse: updatedLead.imovel_interesse ?? existing.imovel_interesse,
    // Preserva a data de criação original
    created_at: existing.created_at,
  };

  // Substitui o item no array (força reatividade)
  leadsLocal.value = [
    ...leadsLocal.value.slice(0, index),
    updatedItem,
    ...leadsLocal.value.slice(index + 1)
  ];

  // Atualiza o lead selecionado se necessário
  if (selectedLead.value?.id === updatedItem.id) {
    selectedLead.value = updatedItem;
  }
}
</script>

<template>
  <Head>
    <title>Leads - Corretor</title>
  </Head>
  
  <AuthLayout>
    <div class="p-6">
      <!-- Header com título e toggle de visualização -->
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold">Leads</h1>
        <ViewToggle v-model="viewMode" />
      </div>
      <div v-if="leadsLocal">
          <!-- Visualização em Grade -->
          <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <LeadCardGrid 
              v-for="lead in leadsLocal" 
              :key="lead.id"
              :lead="lead"
              @open-details="handleOpenDetails"
              @toggle-listing="handleToggleListing"
            />
          </div>

          <!-- Visualização em Lista -->
          <div v-else-if="viewMode === 'list'" class="space-y-2">
            <LeadListRow
              v-for="lead in leadsLocal"
              :key="lead.id"
              :lead="lead"
              @open-details="handleOpenDetails"
              @toggle-listing="handleToggleListing"
            />
          </div>
      </div>
      <div v-else class="text-gray-500">
        Nenhum Lead encontrado.
      </div>
    </div>

    <LeadDetailsModal
      :open="showDetailsModal"
      :lead="selectedLead"
      @close="closeDetailsModal"
      @updated="handleLeadUpdated"
    />
  </AuthLayout>
</template>