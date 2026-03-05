<script setup lang="ts">
import { ref, computed } from 'vue'
import { ContatoListagem } from '@/types/forms/contato-form'
import { Mail, Smartphone, MapPin, UserRound, Briefcase, Calendar, Star, Award } from 'lucide-vue-next';

interface Props {
  contato: ContatoListagem
}

const props = defineProps<Props>()

const emit = defineEmits<{
  (e: 'open-details', contato: ContatoListagem): void
  (e: 'edit', contato: ContatoListagem): void
  (e: 'toggle-listing', contato: ContatoListagem, ativo: boolean): void
}>()

const loading = ref(false)

// Computed properties
const iniciais = computed(() => {
  return props.contato.nome_completo
    .split(' ')
    .map(n => n[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()
})

const contatoPrincipal = computed(() => {
  const contatos = props.contato.contatos || []
  const principal = contatos.find(c => c.principal)
  return principal || contatos[0]
})

const tipoRelacaoLabel = computed(() => {
  const map: Record<string, { label: string; icon: any; color: string }> = {
    'cliente': { label: 'Cliente', icon: Star, color: 'text-yellow-600' },
    'investidor': { label: 'Investidor', icon: Award, color: 'text-purple-600' },
    'parceiro': { label: 'Parceiro', icon: Briefcase, color: 'text-blue-600' },
    'indicador': { label: 'Indicador', icon: UserRound, color: 'text-green-600' },
    'proprietario': { label: 'Proprietário', icon: MapPin, color: 'text-orange-600' }
  }
  return map[props.contato.tipo_relacao || ''] || null
})

const idade = computed(() => {
  if (!props.contato.data_nascimento) return null
  const hoje = new Date()
  const nasc = new Date(props.contato.data_nascimento)
  let idade = hoje.getFullYear() - nasc.getFullYear()
  const m = hoje.getMonth() - nasc.getMonth()
  if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) {
    idade--
  }
  return idade
})

function formatAddress(): string {
  const partes = []
  if (props.contato.rua) partes.push(props.contato.rua)
  if (props.contato.numero) partes.push(props.contato.numero)
  const endereco = partes.filter(Boolean).join(', ')
  
  const cidadeEstado = []
  if (props.contato.cidade) cidadeEstado.push(props.contato.cidade)
  if (props.contato.estado) cidadeEstado.push(props.contato.estado)
  
  return [endereco, cidadeEstado.join('/')].filter(Boolean).join(' - ') || '—'
}

function handleOpenDetails() {
  emit('open-details', props.contato)
}
</script>

<template>
  <div class="nexa-list-row group">
    <!-- Avatar -->
    <div class="nexa-list-row-avatar">
      <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-700 dark:text-primary-400 font-semibold text-lg">
        {{ iniciais }}
      </div>
    </div>

    <!-- Conteúdo principal - clicável -->
    <div 
      class="nexa-list-row-content" 
      @click="handleOpenDetails" 
      role="button" 
      tabindex="0"
    >
      <!-- Header com nome e spans -->
      <div class="nexa-list-row-header">
        <div class="flex items-center gap-3 flex-wrap">
          <h3 class="nexa-list-row-title">{{ contato.nome_completo }}</h3>
          
          <!-- span de tipo de relação -->
          <div v-if="tipoRelacaoLabel" class="flex items-center gap-1">
            <component :is="tipoRelacaoLabel.icon" :class="['w-3 h-3', tipoRelacaoLabel.color]" />
            <span class="text-xs font-medium" :class="tipoRelacaoLabel.color">
              {{ tipoRelacaoLabel.label }}
            </span>
          </div>

          <!-- span de status -->
          <span  variant="outline" size="sm">
            {{ contato.status.nome }}
          </span>

          <!-- Idade -->
          <div v-if="idade" class="flex items-center gap-1 text-xs text-[var(--text-muted)]">
            <Calendar class="w-3 h-3" />
            {{ idade }} anos
          </div>
        </div>
      </div>

      <!-- Grid de informações -->
      <div class="nexa-list-row-grid">
        <!-- Email -->
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">
            <Mail class="w-3 h-3" />
            Email
          </span>
          <span class="nexa-list-row-value">{{ contato.email || '—' }}</span>
        </div>

        <!-- Telefone -->
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">
            <Smartphone class="w-3 h-3" />
            Telefone
          </span>
          <span class="nexa-list-row-value">
            {{ contatoPrincipal?.numero || '—' }}
            <span v-if="contatoPrincipal?.tipo" class="text-xs ml-1 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded-full">
              {{ contatoPrincipal.tipo }}
            </span>
          </span>
        </div>

        <!-- Localização -->
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">
            <MapPin class="w-3 h-3" />
            Localização
          </span>
          <span class="nexa-list-row-value">
            {{ contato.cidade || '—' }}{{ contato.estado ? `/${contato.estado}` : '' }}
          </span>
        </div>

        <!-- Profissão/Empresa -->
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">
            <Briefcase class="w-3 h-3" />
            Profissão
          </span>
          <span class="nexa-list-row-value">
            {{ contato.profissao || '—' }}{{ contato.empresa ? ` (${contato.empresa})` : '' }}
          </span>
        </div>

        <!-- Renda -->
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">
            <Award class="w-3 h-3" />
            Renda
          </span>
          <span class="nexa-list-row-value font-medium text-green-600 dark:text-green-400">
            {{ contato.renda_mensal ? new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(contato.renta_mensal) : '—' }}
          </span>
        </div>

        <!-- Código -->
        <div class="nexa-list-row-item">
          <span class="nexa-list-row-label">Código</span>
          <span class="nexa-list-row-value font-mono">#{{ contato.id?.toString().padStart(4, '0') || '0000' }}</span>
        </div>
      </div>

      <!-- Endereço completo -->
      <div class="nexa-list-row-address">
        {{ formatAddress() }}
      </div>
    </div>

    <!-- Ações -->
    <div class="nexa-list-row-actions">
      <!-- Corretor responsável -->
      <div class="flex items-center gap-2 text-sm text-[var(--text-muted)]">
        <UserRound class="w-4 h-4" />
        <span>{{ contato.corretor?.nome || 'Não atribuído' }}</span>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="emit('open-details', contato)"
          class="px-3 py-1.5 text-xs font-medium text-primary-700 bg-primary-50 rounded-md hover:bg-primary-100 transition-colors dark:bg-primary-900/30 dark:text-primary-400"
        >
          Detalhes
        </button>
        <button
          @click="emit('edit', contato)"
          class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-50 rounded-md hover:bg-gray-100 transition-colors dark:bg-gray-800 dark:text-gray-300"
        >
          Editar
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
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

.nexa-list-row-avatar {
  flex-shrink: 0;
}

.nexa-list-row-content {
  flex: 1;
  min-width: 0;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.nexa-list-row-header {
  display: flex;
  align-items: center;
}

.nexa-list-row-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}

.nexa-list-row-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  font-size: 0.875rem;
}

.nexa-list-row-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.nexa-list-row-label {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  color: var(--text-muted);
  text-transform: uppercase;
  font-weight: 500;
  letter-spacing: 0.5px;
}

.nexa-list-row-value {
  color: var(--text-primary);
  font-weight: 500;
  word-break: break-word;
}

.nexa-list-row-address {
  font-size: 0.8125rem;
  color: var(--text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  width: 100%;
}

.nexa-list-row-actions {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.75rem;
  flex-shrink: 0;
}

/* Responsivo */
@media (max-width: 768px) {
  .nexa-list-row {
    flex-direction: column;
    align-items: stretch;
  }

  .nexa-list-row-avatar {
    display: none;
  }

  .nexa-list-row-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .nexa-list-row-actions {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.5rem;
  }
}
</style>