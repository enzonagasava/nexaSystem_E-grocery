<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import KanbanGrid from '@/components/admin/corretor/kanban/table/KanbanGrid.vue';
import { LeadListagem } from '@/types/forms/lead-form';

const props = defineProps<{ 
  leads: LeadListagem[],
  quadro?: any,
  colunas?: any[],
  cards?: any,
  todosQuadros: any[]
}>();

const leadsLocal = ref(props.leads);
const selectedLead = ref<LeadListagem | null>(null);
const showDetailsModal = ref(false);
const colunas = ref(props.colunas || []);
const quadro = ref(props.quadro);

// Computed para mapear os cards por coluna
const cardsPorColuna = computed(() => {
  if (props.cards) {
    return props.cards;
  }
  
  // Fallback para o comportamento antigo
  const mapa: Record<string, any[]> = {};
  colunas.value.forEach(coluna => {
    mapa[coluna.id] = {
      coluna,
      leads: leadsLocal.value.filter(lead => {
        const statusLead = (lead.status || '').toLowerCase();
        const tituloColuna = coluna.titulo.toLowerCase();
        
        // Mapeamento básico
        if (tituloColuna.includes('novo') && ['novo', 'lead'].includes(statusLead)) return true;
        if (tituloColuna.includes('simulação') && statusLead.includes('simula')) return true;
        if (tituloColuna.includes('visita') && statusLead.includes('visita')) return true;
        if (tituloColuna.includes('negociação') && ['negociação', 'contato'].includes(statusLead)) return true;
        
        return false;
      })
    };
  });
  return mapa;
});

// Handlers
function handleOpenDetails(lead: LeadListagem) {
  selectedLead.value = lead;
  showDetailsModal.value = true;
}

function handleToggleListing(lead: LeadListagem, checked: boolean) {
  const idx = leadsLocal.value.findIndex(l => l.id === lead.id);
  if (idx !== -1) {
    lead.adicionar_rodizio = checked;
    leadsLocal.value[idx] = { ...lead };
  }
}

// Handler para atualizar status via Kanban
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
}
</script>

<template>
  <Head>
    <title>Kanban - Corretor</title>
  </Head>
  
  <AuthLayout>
    <div class="p-6">
      <!-- Header com título e informações do quadro -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            Quadro Kanban
          </h1>
        </div>
      </div>

      <!-- Conteúdo da listagem -->
      <div v-if="leadsLocal?.length" class="space-y-4">
        
        <!-- ✅ VISUALIZAÇÃO KANBAN -->
        <div class="width-100">
          <KanbanGrid 
            :leads="leadsLocal"
            :colunas="colunas"
            :quadro="quadro"
            :todosQuadros="todosQuadros"
            :cards-por-coluna="cardsPorColuna"
            @open-details="handleOpenDetails"
            @update-status="handleUpdateStatus"
          />
        </div>
      </div>
      
      <div v-else class="text-center py-12 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
        <p class="text-gray-500">Nenhum Lead encontrado.</p>
        <p class="text-sm text-gray-400 mt-2">Arraste leads para começar a usar o Kanban</p>
      </div>
    </div>
  </AuthLayout>
</template>