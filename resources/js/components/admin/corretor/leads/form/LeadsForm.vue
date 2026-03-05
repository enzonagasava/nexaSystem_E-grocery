<script lang="ts" setup>
import { ref, computed, onMounted, reactive } from 'vue';
import { vMaska } from 'maska/vue';
import { validateLead } from '@/utils/validateLead';
import StepInformacaoPessoal from './steps/StepInformacaoPessoal.vue';
import StepLocalizacao from './steps/StepLocalizacao.vue';
import StepBanco from './steps/StepBanco.vue';
import StepGerenciamento from './steps/StepGerenciamento.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import type { LeadForm } from '@/types/forms/lead-form';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
  lead: Object,
  corretor: Array,
  imovel: Array,
  status: Array
});

const toast = useToastStore();
const emit = defineEmits(['saved', 'cancel']);

const loading = ref(false);
const isSubmitting = ref(false);

// Form reativo simples (sem useForm do Inertia)
const form = reactive<LeadForm>({
    nome_completo: props.lead?.nome_completo || '',
    email: props.lead?.email || '',
    contatos: props.lead?.contatos || [{ 
        numero: '', 
        tipo: 'WhatsApp', 
        principal: true 
    }],
    genero: props.lead?.genero || '',
    data_nascimento: props.lead?.data_nascimento || '',
    redes_sociais: props.lead?.redes_sociais || [],
    cpf: props.lead?.cpf || '',
    rg: props.lead?.rg || '',
    cep: props.lead?.cep || '',
    rua: props.lead?.rua || '',
    bairro: props.lead?.bairro || '',
    cidade: props.lead?.cidade || '',
    estado: props.lead?.estado || '',
    complemento: props.lead?.complemento || '',
    numero: props.lead?.numero || '',
    banco_nome: props.lead?.banco_nome || '',
    banco_codigo: props.lead?.banco_codigo || '',
    agencia: props.lead?.agencia || '',
    conta: props.lead?.conta || '',
    conta_tipo: props.lead?.conta_tipo || '',
    pix: props.lead?.pix || '',
    pix_tipo: props.lead?.pix_tipo || '',
    corretor_id: props.lead?.corretor_id || '',
    adicionar_rodizio: props.lead?.adicionar_rodizio || false,
    status_id: props.lead?.status || '',
    imovel: props.imovel || [],
});

// Computed para placeholder do PIX
const placeholderPix = computed(() => {
  switch(form.pix_tipo) {
    case 'cpf': return '000.000.000-00';
    case 'celular': return '(11) 99999-9999';
    case 'email': return 'seu@email.com';
    case 'aleatorio': return 'Chave aleatória do PIX';
    default: return 'Digite a chave PIX';
  }
});

// Submit do formulário com FETCH
const submitForm = async () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    
    try {
        const url = props.lead 
        ? `/admin/corretor/leads/${props.lead.id}`
        : '/admin/corretor/leads';

        const method = props.lead ? 'PUT' : 'POST';
        
        //Busca o token xsrf-token
        const xsrfToken = getCookie('XSRF-TOKEN');

        const headers: Record<string, string> = {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }

        if (xsrfToken) headers['X-XSRF-TOKEN'] = xsrfToken

        const fetchOptions: RequestInit = {
          method: method,
          headers,
          body: JSON.stringify(form)
        };

        const response = await fetch(url, fetchOptions);

        const data = await response.json();

        // ✅ VERIFICAR SE A RESPOSTA NÃO FOI BEM SUCEDIDA
        if (!response.ok) {
            // Criar um objeto de erro com status e data
            throw {
                status: response.status,
                data: data,
                message: data.message || 'Erro na requisição'
            };
        }

        // ✅ SUCESSO
        toast.show(data.message, 'success');
        
        setTimeout(() => {
            window.location.href = '/admin/corretor/leads';
        }, 1500);
        
    } catch (error: unknown) {
      console.error('❌ Erro:', error);
      const err: any = error

      // ✅ Erro 422 - validação
      if (err && err.status === 422) {
        const errors = err.data?.errors || err.data || {}

        Object.keys(errors).forEach((field: string) => {
          const message = Array.isArray(errors[field])
            ? errors[field][0]
            : errors[field]
          toast.show(message, 'error')
        })
      }

      // ✅ Erro 401 - não autenticado
      else if (err && err.status === 401) {
        toast.show('Sessão expirada. Faça login novamente.', 'error')
        setTimeout(() => {
          window.location.href = '/login'
        }, 2000)
      }

      // ✅ Erro 403 - não autorizado
      else if (err && err.status === 403) {
        toast.show('Você não tem permissão para essa ação.', 'error')
      }

      // ✅ Erro 500 - servidor
      else if (err && err.status >= 500) {
        toast.show('Erro no servidor. Tente novamente.', 'error')
      }

      // ✅ Erro de rede ou outro
      else {
        toast.show(err?.message || 'Erro ao criar lead', 'error')
      }

    } finally {
        isSubmitting.value = false;
    }
};

// Preencher formulário se estiver editando
onMounted(() => {
  if (props.lead) {
    Object.assign(form, {
      ...props.lead,
      contatos: typeof props.lead.contatos === 'string' 
        ? JSON.parse(props.lead.contatos) 
        : props.lead.contatos || [{ numero: '', tipo: 'celular', principal: true }],
      redes_sociais: typeof props.lead.redes_sociais === 'string'
        ? JSON.parse(props.lead.redes_sociais)
        : props.lead.redes_sociais || []
    });
  }
});

// Step control
const step = ref(0);
const totalSteps = 4;
const maxUnlocked = ref(0);
const completed = ref<boolean[]>(Array(totalSteps).fill(false));
const lastOpened = ref(0);
const errors = reactive<any>({});
const stepNames = [
    'StepInformacaoPessoal',
    'StepLocalizacao',
    'StepBanco',
    'StepGerenciamento',
];

function confirmCard(index: number) {
  if (!validateCurrentStep(index)) {
    return;
  }
  completed.value[index] = true;
  const next = index + 1;
  if (next < totalSteps) {
    maxUnlocked.value = Math.max(maxUnlocked.value, next);
    step.value = next;
    lastOpened.value = next;
  } else {
    step.value = -1;
    lastOpened.value = index;
  }
}

function cancelCard(index: number) {
  if (index > 0) step.value = index - 1;
}

function toggleCard(index: number) {
  if (step.value === index) {
    step.value = -1;
  } else {
    maxUnlocked.value = Math.max(maxUnlocked.value, index);
    step.value = index;
    lastOpened.value = index;
  }
}

function validateCurrentStep(idx: number): boolean {
  const stepName = stepNames[idx];
  const validation = validateLead(form, stepName);
  
  Object.keys(errors).forEach(k => delete errors[k]);
  Object.assign(errors, validation);
  
  return Object.keys(validation).length === 0;
}
</script>
<template> 
  <Toast />
    <form @submit.prevent="submitForm" >
        
        <!-- Status do Lead -->
        <Input type="hidden" name="status" v-model="form.status" />

                <!-- Progress / Step indicator -->
        <div class="flex items-center justify-between mb-4">
          <div class="text-sm font-medium">Passo {{ lastOpened + 1 }} de {{ totalSteps }}</div>
          <div class="text-sm text-gray-500">{{ ['Categoria','Identificação','Localização','Características','Detalhes','Mídia','Valor','Proprietário'][lastOpened] }}</div>
        </div>

        <!-- Stacked sequential cards: only unlocked cards are shown; the active card is expanded -->
        <div>
          <!-- Step 0 -->
          <div v-if="0 <= maxUnlocked" class="mb-4 background-primary border rounded shadow-sm">
            <div class="p-4 flex items-center justify-between cursor-pointer" @click="toggleCard(0)">
              <div class="font-medium">1. Informações Pessoais</div>
              <div class="flex items-center gap-3">
                <div class="text-sm text-green-600" v-if="completed[0]">Concluído</div>
                <div class="text-sm text-gray-400" v-else>Preencher</div>
              </div>
            </div>
            <div v-show="step === 0" class="border-t p-4">
              <StepInformacaoPessoal :formRef="form" :errors="errors" />
              <div class="mt-4 flex justify-end gap-2">
                <Button type="button" variant="ghost" @click="cancelCard(0)">Cancelar</Button>
                <Button type="button" variant="primary" @click="confirmCard(0)">Confirmar</Button>
              </div>
            </div>
          </div>

          <!-- Step 1 -->
          <div v-if="1 <= maxUnlocked" class="mb-4 background-primary border rounded shadow-sm">
            <div class="p-4 flex items-center justify-between cursor-pointer" @click="toggleCard(1)">
              <div class="font-medium">2. Endereço</div>
              <div class="text-sm text-green-600" v-if="completed[1]">Concluído</div>
            </div>
            <div v-show="step === 1" class="border-t p-4">
              <StepLocalizacao :formRef="form" :errors="errors" />
              <div class="mt-4 flex justify-end gap-2">
                <Button type="button" variant="ghost" @click="cancelCard(1)">Cancelar</Button>
                <Button type="button" variant="primary" @click="confirmCard(1)">Confirmar</Button>
              </div>
            </div>
          </div>

          <!-- Step 2 -->
          <div v-if="2 <= maxUnlocked" class="mb-4 background-primary border rounded shadow-sm">
            <div class="p-4 flex items-center justify-between cursor-pointer" @click="toggleCard(2)">
              <div class="font-medium">3. Dados Bancarios <span class="text-yellow-400 text-xs ml-1">Opcional!</span></div>
              <div class="text-sm text-green-600" v-if="completed[2]">Concluído</div>
            </div>
            <div v-show="step === 2" class="border-t p-4">
              <StepBanco :formRef="form" :errors="errors" :fetchCep="fetchCep" />
              <div class="mt-4 flex justify-end gap-2">
                <Button type="button" variant="ghost" @click="cancelCard(2)">Cancelar</Button>
                <Button type="button" variant="primary" @click="confirmCard(2)">Confirmar</Button>
              </div>
            </div>
          </div>

          <!-- Step 3 -->
          <div v-if="3 <= maxUnlocked" class="mb-4 background-primary border rounded shadow-sm">
            <div class="p-4 flex items-center justify-between cursor-pointer" @click="toggleCard(3)">
              <div class="font-medium">4. Gerenciamento</div>
              <div class="text-sm text-green-600" v-if="completed[3]">Concluído</div>
            </div>
            <div v-show="step === 3" class="border-t p-4">
              <StepGerenciamento :formRef="form" :errors="errors" :corretor="props.corretor" :imovel="props.imovel" :status="props.status" />
              <div class="mt-4 flex justify-end gap-2">
                <Button type="button" variant="ghost" @click="cancelCard(3)">Cancelar</Button>
                <Button type="button" variant="primary" @click="confirmCard(3)">Confirmar</Button>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-start space-x-4 pt-6">
            <Button
                type="submit"
                v-if="completed[totalSteps - 1]"
                :loading="isSubmitting"
                loadingPosition="start"
                class="bg-primary text-white px-4 py-2 rounded disabled:opacity-50"
            >
                <span v-if="!isSubmitting">Salvar</span>
                <span v-else>Salvando...</span>
            </Button>
        </div>
    </form>
</template>
