<script setup lang="ts">
import { ref, computed } from 'vue'
import { LeadListagem } from '@/types/forms/lead-form'

interface Props {
  lead: LeadListagem
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'open-details', lead: LeadListagem): void
  (e: 'toggle-listing', lead: LeadListagem, ativo: boolean): void
}>()

const loading = ref(false)

// Formatar contato principal
const contatoPrincipal = computed(() => {
  const principal = props.lead.contatos?.find(c => c.principal)
  return principal || props.lead.contatos?.[0]
})

// Status do lead
const statusLabel = computed(() => {
  const statusMap: Record<string, string> = {
    'novo': 'Novo',
    'ativo': 'Ativo',
    'inativo': 'Inativo',
    'convertido': 'Convertido',
    'arquivado': 'Arquivado'
  }
  return statusMap[props.lead.status.nome] || props.lead.status.nome || '—'
})

const statusClass = computed(() => {
  const classes: Record<string, string> = {
    'novo': 'bg-blue-100 text-blue-800 border border-blue-200',
    'ativo': 'bg-green-100 text-green-800 border border-green-200',
    'inativo': 'bg-gray-100 text-gray-800 border border-gray-200',
    'convertido': 'bg-purple-100 text-purple-800 border border-purple-200',
    'arquivado': 'bg-red-100 text-red-800 border border-red-200'
  }
  return classes[props.lead.status] || 'bg-gray-100 text-gray-800 border border-gray-200'
})

// Iniciais para avatar
const iniciais = computed(() => {
  return props.lead.nome_completo
    .split(' ')
    .map(n => n[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()
})

function formatAddress(lead: LeadListagem): string {
  const partes = []
  if (lead.endereco) partes.push(lead.endereco)
  if (lead.numero) partes.push(lead.numero)
  const endereco = partes.filter(Boolean).join(', ')
  
  const cidadeEstado = []
  if (lead.cidade) cidadeEstado.push(lead.cidade)
  if (lead.estado) cidadeEstado.push(lead.estado)
  
  return [endereco, cidadeEstado.join('/')].filter(Boolean).join(' - ') || '—'
}

async function toggleRodizio(e: Event) {
  const target = e.target as HTMLInputElement | null
  if (loading.value) return
  const checked = !!target?.checked

  loading.value = true

  try {
    emit('toggle-listing', props.lead, checked)
  } catch (err) {
    console.error('Falha ao atualizar rodízio', err)
  } finally {
    loading.value = false
  }
}

const editarLead = (lead: LeadListagem) => {
  window.location.href = `/admin/corretor/leads/${lead.id}/edit`;
}


function handleOpenDetails() {
  emit('open-details', props.lead)
}
</script>

<template>
  <div class="nexa-list-row">
    <!-- Avatar/Imagem -->
    <div class="nexa-list-row-image">
      <div v-if="!lead.imageUrl" class="flex items-center justify-center bg-primary-100 text-primary-700 w-full h-full">
        <span class="text-lg font-semibold">{{ iniciais }}</span>
      </div>
      <img v-else :src="lead.imageUrl" :alt="lead.nome_completo" loading="lazy" class="object-cover w-full h-full" />
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
        <h3 class="nexa-list-row-title">{{ lead.nome_completo }}</h3>
        <div class="nexa-list-row-status">
          <span :class="statusClass" class="text-xs px-2 py-1 rounded-full">
            {{ statusLabel }}
          </span>
        </div>
      </div>

      <!-- Informações em grid responsivo -->
      <div class="nexa-list-row-grid">
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">Email</span>
          <span class="nexa-list-row-value">{{ lead.email || '—' }}</span>
        </div>
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">Contato</span>
          <span class="nexa-list-row-value">
            {{ contatoPrincipal?.numero || '—' }}
            <span v-if="contatoPrincipal?.tipo" class="text-xs ml-1 px-1.5 py-0.5 bg-gray-100 rounded-full">
              {{ contatoPrincipal.tipo }}
            </span>
          </span>
        </div>
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">Cidade/UF</span>
          <span class="nexa-list-row-value">
            {{ lead.cidade || '—' }}{{ lead.estado ? `/${lead.estado}` : '' }}
          </span>
        </div>
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">Código</span>
          <span class="nexa-list-row-value font-mono">#{{ lead.id?.toString().padStart(4, '0') || '0000' }}</span>
        </div>
      </div>

      <!-- Endereço na parte inferior -->
      <div class="nexa-list-row-address">
        {{ formatAddress(lead) }}
      </div>
    </div>

    <div class="flex items-center justify-start p-2">
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
  </div>
</template>

<style scoped>
/* Mesmos estilos do ImovelListRow */
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
}
</style>