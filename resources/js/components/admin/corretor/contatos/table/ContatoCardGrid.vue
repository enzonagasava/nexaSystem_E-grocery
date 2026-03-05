<script setup lang="ts">
import { ref, computed } from 'vue'
import Card from '@/components/ui/Card.vue'
import { ContatoListagem } from '@/types/forms/contato-form'
import { Mail, Smartphone, MapPin, UserRound, Briefcase, Calendar, Star, Award } from 'lucide-vue-next';

const props = defineProps<{
  contato: ContatoListagem  
}>()

const emit = defineEmits<{
  (e: 'open-details', contato: ContatoListagem): void
  (e: 'edit', contato: ContatoListagem): void
  (e: 'toggle-listing', contato: ContatoListagem, ativo: boolean): void
}>()

const contato = props.contato

const loading = ref(false)

// Computed properties
const iniciais = computed(() => {
  return props.contato.nome_completo
    .split(' ')
    .map(n => n[0])
    .slice(0, 2)
    .join('')
    .toUpperCase();
});

const contatoPrincipal = computed(() => {
  const contatos = props.contato.contatos || [];
  const principal = contatos.find(c => c.principal);
  return principal || contatos[0];
});


const tipoRelacaoLabel = computed(() => {
  const map: Record<string, { label: string; icon: any; color: string }> = {
    'cliente': { label: 'Cliente', icon: Star, color: 'text-yellow-600' },
    'investidor': { label: 'Investidor', icon: Award, color: 'text-purple-600' },
    'parceiro': { label: 'Parceiro', icon: Briefcase, color: 'text-blue-600' },
    'indicador': { label: 'Indicador', icon: UserRound, color: 'text-green-600' },
    'proprietario': { label: 'Proprietário', icon: MapPin, color: 'text-orange-600' }
  };
  return map[props.contato.tipo_relacao || ''] || null;
});

const idade = computed(() => {
  if (!props.contato.data_nascimento) return null;
  const hoje = new Date();
  const nasc = new Date(props.contato.data_nascimento);
  let idade = hoje.getFullYear() - nasc.getFullYear();
  const m = hoje.getMonth() - nasc.getMonth();
  if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) {
    idade--;
  }
  return idade;
});

// Métodos
function toggleRodizio(event: Event) {
  const checked = (event.target as HTMLInputElement).checked;
  loading.value = true;
  emit('toggle-listing', props.contato, checked);
  setTimeout(() => { loading.value = false; }, 500);
}
</script>

<template>
  <Card :ariaLabel="contato.nome_completo" class="hover:shadow-md transition-all">
    <template #image>
      <div class="relative h-48 w-full flex items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20">
        <!-- Avatar com iniciais -->
        <div class="flex flex-col items-center justify-center">
          <div class="w-20 h-20 rounded-full bg-primary-600/10 flex items-center justify-center text-primary-700 dark:text-primary-400 text-3xl font-semibold mb-2 border-2 border-primary-200 dark:border-primary-800">
            {{ iniciais }}
          </div>
          
          <!-- span de tipo de relação -->
          <div v-if="tipoRelacaoLabel" class="flex items-center gap-1 mt-1">
            <component :is="tipoRelacaoLabel.icon" :class="['w-3 h-3', tipoRelacaoLabel.color]" />
            <span class="text-xs font-medium" :class="tipoRelacaoLabel.color">
              {{ tipoRelacaoLabel.label }}
            </span>
          </div>
        </div>

        <!-- Status span -->
        <div class="absolute top-2 right-2">
          <span variant="outline">
            {{ contato.status.nome }}
          </span>
        </div>

        <!-- Indicador de idade (se tiver) -->
        <div v-if="idade" class="absolute top-2 left-2">
          <span variant="outline" class="bg-white/80 dark:bg-gray-800/80">
            <Calendar class="w-3 h-3 mr-1" />
            {{ idade }} anos
          </span>
        </div>
      </div>
      
      <!-- Conteúdo do Card -->
      <div class="p-4">
        <div class="flex items-start justify-between mb-3">
          <h2 class="font-semibold text-lg truncate flex-1" :title="contato.nome_completo">
            {{ contato.nome_completo }}
          </h2>
        </div>
        
        <div class="mt-2 text-sm space-y-2">
          <!-- Código/ID -->
          <div class="flex items-center justify-between pt-1 border-t border-[var(--border)]">
            <span class="text-xs text-[var(--text-muted)]">Código</span>
            <span class="ml-2 text-sm font-mono font-medium bg-[var(--card)] px-2 py-0.5 rounded">
              #{{ contato.id?.toString().padStart(4, '0') || '0000' }}
            </span>
          </div>

          <!-- Email -->
          <div v-if="contato.email" class="flex items-center gap-2">
            <Mail class="w-4 h-4 text-[var(--text-muted)]" />
            <span class="text-sm truncate" :title="contato.email">{{ contato.email }}</span>
          </div>

          <!-- Contato principal -->
          <div v-if="contatoPrincipal" class="flex items-center gap-2">
            <Smartphone class="w-4 h-4 text-[var(--text-muted)]" />
            <div class="flex items-center gap-1 flex-wrap">
              <span class="text-sm">{{ contatoPrincipal.numero }}</span>
              <span variant="outline" class="text-xs px-1.5 py-0.5 bg-[var(--muted)]">
                {{ contatoPrincipal.tipo }}
              </span>
              <Star v-if="contatoPrincipal.principal" class="w-3 h-3 text-yellow-500" />
            </div>
          </div>

          <!-- Localização -->
          <div v-if="contato.idade || contato.estado" class="flex items-center gap-2">
            <MapPin class="w-4 h-4 text-[var(--text-muted)]" />
            <span class="text-sm truncate">
              {{ contato.cidade }}{{ contato.estado ? `, ${contato.estado}` : '' }}
            </span>
          </div>

          <!-- Profissão/Empresa -->
          <div v-if="contato.profissao || contato.empresa" class="flex items-center gap-2">
            <Briefcase class="w-4 h-4 text-[var(--text-muted)]" />
            <span class="text-sm truncate">
              {{ contato.profissao || '' }}{{ contato.empresa ? ` na ${contato.empresa}` : '' }}
            </span>
          </div>

          <!-- Renda mensal -->
          <div v-if="contato.renda_mensal" class="flex items-center gap-2">
            <Award class="w-4 h-4 text-[var(--text-muted)]" />
            <span class="text-sm font-medium text-green-600 dark:text-green-400">
              {{ new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(contato.renda_mensal) }}
            </span>
          </div>
        </div>
      </div>
    </template>

    <!-- Footer com ações -->
    <template #footer>
      <div class="flex items-center justify-between p-3 bg-[var(--card)] border-t border-[var(--border)] rounded-b-lg">
        <!-- Corretor responsável -->
        <div class="flex items-center gap-2">
          <UserRound class="w-4 h-4 text-[var(--text-muted)]" />
          <span class="text-sm">{{ contato.corretor?.nome || 'Não atribuído' }}</span>
        </div>

        <div class="flex items-center gap-2">
          <!-- Botão Detalhes -->
          <button
            @click="emit('open-details', contato)"
            class="px-3 py-1.5 text-xs font-medium text-primary-700 bg-primary-50 rounded-md hover:bg-primary-100 transition-colors flex items-center gap-1 dark:bg-primary-900/30 dark:text-primary-400"
          >
            <span>Detalhes</span>
            <span>→</span>
          </button>
          
          <!-- Botão Editar -->
          <button
            @click="emit('edit', contato)"
            class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-50 rounded-md hover:bg-gray-100 transition-colors dark:bg-gray-800 dark:text-gray-300"
          >
            Editar
          </button>
        </div>
      </div>
    </template>
  </Card>
</template>