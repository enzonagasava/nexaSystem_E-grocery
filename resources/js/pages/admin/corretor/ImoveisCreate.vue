<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { ref, computed, reactive, onMounted } from 'vue';
import StepZero from './steps/StepZero.vue'
import StepIdentificacao from './steps/StepIdentificacao.vue'
import StepLocalizacao from './steps/StepLocalizacao.vue'
import StepCaracteristicas from './steps/StepCaracteristicas.vue'
import StepDetalhes from './steps/StepDetalhes.vue'
import StepMidia from './steps/StepMidia.vue'
import StepValor from './steps/StepValor.vue'

import StepProprietario from './steps/StepProprietario.vue'
import { Button } from '@/components/ui/button';
import { validateImovel } from '@/utils/validateImovel';

const props = defineProps<{ produto?: any }>();

// Step control (0..7)
const step = ref(0);
const totalSteps = 8; // 0..7

// which steps are unlocked (maxUnlocked: highest index unlocked)
const maxUnlocked = ref(0);

// per-step completion state
const completed = ref<boolean[]>(Array(totalSteps).fill(false));
// last opened card index (used for the 'Passo' indicator)
const lastOpened = ref(0);


const allCompleted = computed(() => completed.value.every((v: boolean) => !!v));


// Formulário principal
const form = ref<any>({});

// Erros de validação por step
const errors = reactive<any>({});

  const stepNames = [
  'StepZero',
  'StepIdentificacao',
  'StepLocalizacao',
  'StepCaracteristicas',
  'StepDetalhes',
  'StepMidia',
  'StepValor',
  'StepProprietario',
];


function validateCurrentStep(idx: number): boolean {
  const stepName = stepNames[idx];
  let formForValidation = { ...form.value };
  if (stepName === 'StepMidia') {
    // Pass media file arrays for validation
    const files = stepMidiaRef.value?.getFiles?.() ?? mediaFiles.value
    formForValidation.mediaImagens = files.imagens || []
  }
  const validation = validateImovel(formForValidation, stepName);
  // Limpa erros antigos
  Object.keys(errors).forEach(k => delete errors[k]);
  Object.assign(errors, validation);
  return Object.keys(validation).length === 0;
}

const nextStep = () => {
  if (step.value < totalSteps - 1) {
    // mark next unlocked and move to it
    const next = step.value + 1;
    maxUnlocked.value = Math.max(maxUnlocked.value, next);
    step.value = next;
    lastOpened.value = next;
  }
};

const prevStep = () => { if (step.value > 0) step.value--; };

function confirmCard(index: number) {
  if (!validateCurrentStep(index)) {
    // Não avança, exibe erros
    return;
  }
  completed.value[index] = true;
  const next = index + 1;
  if (next < totalSteps) {
    maxUnlocked.value = Math.max(maxUnlocked.value, next);
    step.value = next;
    lastOpened.value = next;
  } else {
    // Último step confirmado — mantém no último
    step.value = -1;
    lastOpened.value = index;
  }
}

function cancelCard(index: number) {
  // simple behavior: go back one step when cancelling, do not mark complete
  if (index > 0) step.value = index - 1;
}

function toggleCard(index: number) {
  if (step.value === index) {
    // collapse
    step.value = -1;
  } else {
    // open and ensure unlocked
    maxUnlocked.value = Math.max(maxUnlocked.value, index);
    step.value = index;
    // record last opened but do not change when collapsing
    lastOpened.value = index;
  }
}

// Media handling
const mediaFiles = ref<{ imagens: File[]; videos: File[]; plantas: File[] }>({ imagens: [], videos: [], plantas: [] })
const stepMidiaRef = ref<InstanceType<typeof StepMidia> | null>(null)
const autorizacao = ref<File | null>(null);
const autorizacaoName = ref<string>('');
// form element ref for programmatic submit
const formEl = ref<HTMLFormElement | null>(null);
const isSubmitting = ref(false);

function onMediaFilesUpdate(data: { imagens: File[]; videos: File[]; plantas: File[] }) {
  mediaFiles.value = data
}

// Helper to read cookie (local fallback for build-time TS checks)
function getCookie(name: string) {
  try {
    const match = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]+)'))
    return match ? decodeURIComponent(match[2]) : null
  } catch (e) {
    return null
  }
}


async function handleSubmit(e: Event) {
  e.preventDefault();
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  
  const formData = new FormData();

  // Prepare form data, removing formatting from phone and document
  const cleanedForm = { ...form.value };
  if (cleanedForm.proprietario_telefone) {
    cleanedForm.proprietario_telefone = cleanedForm.proprietario_telefone.replace(/\D/g, '');
  }
  if (cleanedForm.proprietario_documento) {
    cleanedForm.proprietario_documento = cleanedForm.proprietario_documento.replace(/\D/g, '');
  }

  for (const [key, value] of Object.entries(cleanedForm)) {
    if (value !== null && value !== undefined && value !== '') {
      if (value instanceof File) {
        formData.append(key, value);
      } else if (Array.isArray(value) || (typeof value === 'object')) {
        formData.append(key, JSON.stringify(value));
      } else {
        formData.append(key, String(value));
      }
    }
  }

  // Append media files (imagens, vídeos, plantas)
  const files = stepMidiaRef.value?.getFiles?.() ?? mediaFiles.value
  for (const file of (files.imagens || [])) {
    formData.append('imagens[]', file)
  }
  for (const file of (files.videos || [])) {
    formData.append('videos[]', file)
  }
  for (const file of (files.plantas || [])) {
    formData.append('plantas[]', file)
  }
  
  try {
    const url = props.produto 
      ? `/admin/corretor/imoveis/${props.produto.id}`
      : '/admin/corretor/imoveis';
    
    const method = props.produto ? 'PUT' : 'POST';

    const xsrfToken = getCookie('XSRF-TOKEN');

    const headers: Record<string, string> = { 'Accept': 'application/json' }
    if (xsrfToken) headers['X-XSRF-TOKEN'] = xsrfToken

    const fetchOptions: RequestInit = {
      method: 'POST', // Sempre POST para FormData com arquivos
      headers,
      body: formData
    };
    
    // Adiciona método override se necessário
    if (method !== 'POST') {
      formData.append('_method', method);
    }
    
    const response = await fetch(url, fetchOptions);
    const data = await response.json();
    
    if (!response.ok) {
      throw { status: response.status, data };
    }
    
    console.log('✅ Sucesso via Fetch:', data);
    
    if (!props.produto && data.imovel) {
      // Após criar, redireciona para a listagem de imóveis
      window.location.href = '/admin/corretor/imoveis';
    } else {
      alert('Imóvel salvo com sucesso!');
    }
    
  } catch (err: any) {
    console.error('❌ Erro via Fetch:', err);
    
    if (err.data?.errors) {
      Object.keys(errors).forEach(k => delete errors[k]);
      Object.assign(errors, err.data.errors);
      alert('Verifique os campos e tente novamente.');
    } else {
      alert(err.data?.message || 'Erro ao salvar imóvel.');
    }
    
  } finally {
    isSubmitting.value = false;
  }
}

function onAutorizacaoChange(e: Event) {
  const input = e.target as HTMLInputElement;
  if (!input.files || !input.files[0]) { autorizacao.value = null; autorizacaoName.value = ''; return; }
  autorizacao.value = input.files[0];
  autorizacaoName.value = autorizacao.value.name;
}

// viaCEP
async function fetchCep() {
  const cepRaw = String(form.value.cep || '').replace(/\D/g, '');
  if (!cepRaw || cepRaw.length !== 8) return;
  try {
    const res = await fetch(`https://viacep.com.br/ws/${cepRaw}/json/`);
    const data = await res.json();
    if (!data || data.erro) return;
    form.value.endereco = data.logradouro || form.value.endereco;
    form.value.bairro = data.bairro || form.value.bairro;
    form.value.cidade = data.localidade || form.value.cidade;
    form.value.estado = data.uf || form.value.estado;
  } catch (err) {
    // ignore
  }
}
</script>

<template>
  <Head>
    <title>Adicionar Imóvel - Corretor</title>
  </Head>
  <AuthLayout>
    <div class="p-6">
      <h1 class="text-xl font-semibold">{{ props.produto ? 'Editar Imóvel' : 'Adicionar Imóvel' }}</h1>

      <form ref="formEl" @submit.prevent="handleSubmit" enctype="multipart/form-data">
        <input v-if="props.produto" type="hidden" name="_method" value="PUT" />

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
              <div class="font-medium">1. Tipo</div>
              <div class="flex items-center gap-3">
                <div class="text-sm text-green-600" v-if="completed[0]">Concluído</div>
                <div class="text-sm text-gray-400" v-else>Preencher</div>
              </div>
            </div>
            <div v-show="step === 0" class="border-t p-4">
              <StepZero :formRef="form" :errors="errors" />
              <div class="mt-4 flex justify-end gap-2">
                <Button type="button" variant="ghost" @click="cancelCard(0)">Cancelar</Button>
                <Button type="button" variant="primary" @click="confirmCard(0)">Confirmar</Button>
              </div>
            </div>
          </div>

          <!-- Step 1 -->
          <div v-if="1 <= maxUnlocked" class="mb-4 background-primary border rounded shadow-sm">
            <div class="p-4 flex items-center justify-between cursor-pointer" @click="toggleCard(1)">
              <div class="font-medium">2. Identificação</div>
              <div class="text-sm text-green-600" v-if="completed[1]">Concluído</div>
            </div>
            <div v-show="step === 1" class="border-t p-4">
              <StepIdentificacao :formRef="form" :errors="errors" />
              <div class="mt-4 flex justify-end gap-2">
                <Button type="button" variant="ghost" @click="cancelCard(1)">Cancelar</Button>
                <Button type="button" variant="primary" @click="confirmCard(1)">Confirmar</Button>
              </div>
            </div>
          </div>

          <!-- Step 2 -->
          <div v-if="2 <= maxUnlocked" class="mb-4 background-primary border rounded shadow-sm">
            <div class="p-4 flex items-center justify-between cursor-pointer" @click="toggleCard(2)">
              <div class="font-medium">3. Localização</div>
              <div class="text-sm text-green-600" v-if="completed[2]">Concluído</div>
            </div>
            <div v-show="step === 2" class="border-t p-4">
              <StepLocalizacao :formRef="form" :errors="errors" :fetchCep="fetchCep" />
              <div class="mt-4 flex justify-end gap-2">
                <Button type="button" variant="ghost" @click="cancelCard(2)">Cancelar</Button>
                <Button type="button" variant="primary" @click="confirmCard(2)">Confirmar</Button>
              </div>
            </div>
          </div>

          <!-- Step 3 -->
          <div v-if="3 <= maxUnlocked" class="mb-4 background-primary border rounded shadow-sm">
            <div class="p-4 flex items-center justify-between cursor-pointer" @click="toggleCard(3)">
              <div class="font-medium">4. Características</div>
              <div class="text-sm text-green-600" v-if="completed[3]">Concluído</div>
            </div>
            <div v-show="step === 3" class="border-t p-4">
              <StepCaracteristicas :formRef="form" :errors="errors" />
              <div class="mt-4 flex justify-end gap-2">
                <Button type="button" variant="ghost" @click="cancelCard(3)">Cancelar</Button>
                <Button type="button" variant="primary" @click="confirmCard(3)">Confirmar</Button>
              </div>
            </div>
          </div>

          <!-- Step 4 -->
          <div v-if="4 <= maxUnlocked" class="mb-4 background-primary border rounded shadow-sm">
            <div class="p-4 flex items-center justify-between cursor-pointer" @click="toggleCard(4)">
              <div class="font-medium">5. Detalhes</div>
              <div class="text-sm text-green-600" v-if="completed[4]">Concluído</div>
            </div>
            <div v-show="step === 4" class="border-t p-4">
              <StepDetalhes :formRef="form" :errors="errors" />
              <div class="mt-4 flex justify-end gap-2">
                <Button type="button" variant="ghost" @click="cancelCard(4)">Cancelar</Button>
                <Button type="button" variant="primary" @click="confirmCard(4)">Confirmar</Button>
              </div>
            </div>
          </div>

          <!-- Step 5 -->
          <div v-if="5 <= maxUnlocked" class="mb-4 background-primary border rounded shadow-sm">
            <div class="p-4 flex items-center justify-between cursor-pointer" @click="toggleCard(5)">
              <div class="font-medium">6. Mídia</div>
              <div class="text-sm text-green-600" v-if="completed[5]">Concluído</div>
            </div>
            <div v-show="step === 5" class="border-t p-4">
              <StepMidia ref="stepMidiaRef" :formRef="form" :errors="errors" @update:files="onMediaFilesUpdate" />
              <div class="mt-4 flex justify-end gap-2">
                <Button type="button" variant="ghost" @click="cancelCard(5)">Cancelar</Button>
                <Button type="button" variant="primary" @click="confirmCard(5)">Confirmar</Button>
              </div>
            </div>
          </div>

          <!-- Step 6 -->
          <div v-if="6 <= maxUnlocked" class="mb-4 background-primary border rounded shadow-sm">
            <div class="p-4 flex items-center justify-between cursor-pointer" @click="toggleCard(6)">
              <div class="font-medium">7. Valor</div>
              <div class="text-sm text-green-600" v-if="completed[6]">Concluído</div>
            </div>
            <div v-show="step === 6" class="border-t p-4">
              <StepValor :formRef="form" :errors="errors" />
              <div class="mt-4 flex justify-end gap-2">
                <Button type="button" variant="ghost" @click="cancelCard(6)">Cancelar</Button>
                <Button type="button" variant="primary" @click="confirmCard(6)">Confirmar</Button>
              </div>
            </div>
          </div>

          <!-- Step 7 -->
          <div v-if="7 <= maxUnlocked" class="mb-4 background-primary border rounded shadow-sm">
            <div class="p-4 flex items-center justify-between cursor-pointer" @click="toggleCard(7)">
              <div class="font-medium">8. Proprietário</div>
              <div class="text-sm text-green-600" v-if="completed[7]">Concluído</div>
            </div>
            <div v-show="step === 7" class="border-t p-4">
              <StepProprietario :formRef="form" :errors="errors" :onAutorizacaoChange="onAutorizacaoChange" :autorizacaoName="autorizacaoName" :produto="props.produto" />
              <div class="mt-4 flex justify-end gap-2">
                <Button type="button" variant="ghost" @click="cancelCard(7)">Cancelar</Button>
                <Button type="button" variant="primary" @click="confirmCard(7)">Confirmar</Button>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4 flex justify-between">
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

        <!-- Loading overlay -->
        <!-- <div v-if="isSubmitting" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
          <div class="bg-white rounded p-4 flex items-center gap-3">
            <svg class="animate-spin h-5 w-5 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <div>Salvando imóvel...</div>
          </div>
        </div> -->
      </form>
    </div>
  </AuthLayout>
</template>
