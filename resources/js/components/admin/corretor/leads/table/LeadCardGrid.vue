<script setup lang="ts">
import { ref, computed } from 'vue'
import axios from 'axios'
import Card from '@/components/ui/Card.vue'
import { LeadListagem } from '@/types/forms/lead-form'
import { Mail, Smartphone, MapPin, UserRound } from 'lucide-vue-next';

const props = defineProps<{
  lead: LeadListagem  
}>()

const leads = props.lead

const emit = defineEmits<{
  (e: 'open-details', lead: LeadListagem): void
  (e: 'toggle-listing', lead: LeadListagem, ativo: boolean): void
}>()

const loading = ref(false)

function formatCurrency(value: number | string | null | undefined): string {
  if (value === null || value === undefined || value === '') return '—'
  const num = typeof value === 'string' ? parseFloat(value.replace(/,/g, '.')) : value
  if (isNaN(Number(num))) return '—'
  return Number(num).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

const contatoPrincipal = computed(() => {
  const principal = props.lead.contatos?.find(c => c.principal);
  return principal || props.lead.contatos?.[0];
});

const statusLabel = computed(() => {
  const statusMap: Record<string, string> = {
    'novo': 'Novo',
    'ativo': 'Ativo',
    'inativo': 'Inativo',
    'convertido': 'Convertido',
    'arquivado': 'Arquivado'
  };
  return statusMap[props.lead.status.nome] || props.lead.status.nome;
});

const statusClass = computed(() => {
  const classes: Record<string, string> = {
    'novo': 'bg-blue-100 text-blue-800 border border-blue-200',
    'ativo': 'bg-green-100 text-green-800 border border-green-200',
    'inativo': 'bg-gray-100 text-gray-800 border border-gray-200',
    'convertido': 'bg-purple-100 text-purple-800 border border-purple-200',
    'arquivado': 'bg-red-100 text-red-800 border border-red-200'
  };
  return classes[props.lead.status] || 'bg-gray-100 text-gray-800 border border-gray-200';
});

// Toggle do rodízio
async function toggleRodizio(event: Event) {
  const checked = (event.target as HTMLInputElement).checked;
  loading.value = true;
  
  try {
    await emit('toggle-listing', props.lead, checked);
  } finally {
    loading.value = false;
  }
}

const iniciais = computed(() => {
  return props.lead.nome_completo
    .split(' ')
    .map(n => n[0])
    .slice(0, 2)
    .join('')
    .toUpperCase();
});

const editarLead = (lead: LeadListagem) => {
  window.location.href = `/admin/corretor/leads/${lead.id}/edit`;
}


// function handleOpenDetails() {
//   emit('open-details', props.imovel)
// }
</script>

<template>
  <Card :ariaLabel="lead.nome_completo" class="hover:shadow-md">
    <template #image>
      <div class="block cursor-pointer" :aria-label="'Ver detalhes de ' + lead.nome_completo" @click="emit('open-details', lead)">
        <div class="relative h-48 w-full flex items-center justify-center">
          <!-- Avatar com iniciais no lugar da imagem -->
          <div v-if="!lead.imageUrl" class="flex flex-col items-center justify-center text-muted">
            <div class="w-20 h-20 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 text-2xl font-semibold mb-2">
              {{ iniciais }}
            </div>
            <span class="text-sm">Sem imagem</span>
          </div>
          <img v-else :src="lead.imageUrl" :alt="lead.nome_completo" loading="lazy" class="object-cover w-full h-full" />

          <!-- Status Badge -->
          <div class="absolute top-2 right-2">
            <span :class="statusClass" class="text-xs px-2 py-1 rounded-full font-medium">
              {{ statusLabel }}
            </span>
          </div>
        </div>
      </div>
      
      <!-- Conteúdo do Card -->
      <div class="p-4">
        <div class="flex items-start justify-between mb-2">
          <h2 class="font-semibold text-lg truncate flex-1" :title="lead.nome_completo">
            {{ lead.nome_completo }}
          </h2>
        </div>
        
        <div class="mt-2 text-sm text-muted space-y-2">
            <!-- Código/ID -->
            <div class="flex items-center justify-between pt-1 ">
                <span class="text-xs">Código</span>
                <span class="ml-2 text-sm font-mono font-medium">
                #{{ lead.id?.toString().padStart(4, '0') || '0000' }}
                </span>
            </div>

            <!-- Email -->
            <div class="flex items-center gap-2">
                <span class="text-gray-400"><Mail/></span>
                <span class="text-sm truncate">{{ lead.email }}</span>
            </div>

            <!-- Contato principal -->
            <div v-if="contatoPrincipal" class="flex items-center gap-2">
                <span class="text-gray-400"><Smartphone/></span>
                <div class="flex items-center gap-1 flex-wrap">
                <span class="text-sm">{{ contatoPrincipal.numero }}</span>
                <span class="text-xs px-1.5 py-0.5 bg-gray-100 rounded-full text-gray-600 capitalize">
                    {{ contatoPrincipal.tipo }}
                </span>
                <span v-if="contatoPrincipal.principal" class="text-xs text-primary-600">★</span>
                </div>
            </div>

            <!-- Localização -->
            <div v-if="lead.cidade || lead.estado" class="flex items-center gap-2">
                <span class="text-gray-400"><MapPin/></span>
                <span class="text-sm truncate">
                {{ lead.cidade }}{{ lead.estado ? `, ${lead.estado}` : '' }}
                </span>
            </div>
        </div>
      </div>
    </template>

    <!-- Footer com toggle de rodízio -->
    <template #footer>
      <div class="flex items-center justify-between p-3 rounded-b-lg">
        <div class="flex items-center gap-2">
            <span class="text-sm text-muted-foreground"> <UserRound/></span>
            <span>{{ lead.corretor_id }}</span>
        </div>

        <!-- Botão Ver detalhes -->
        <button
          @click="emit('open-details', lead)"
          class="px-3 py-1.5 text-xs font-medium text-primary-700 bg-primary-50 rounded-md hover:bg-primary-100 transition-colors flex items-center gap-1"
        >
          <span>Detalhes</span>
          <span>→</span>
        </button>
        <button
          @click="editarLead(lead)"
          class="px-3 py-1.5 text-xs font-medium text-primary-700 bg-primary-50 rounded-md hover:bg-primary-100 transition-colors flex items-center gap-1"
        >
          <span>Editar</span>
        </button>
      </div>
    </template>
  </Card>
</template>