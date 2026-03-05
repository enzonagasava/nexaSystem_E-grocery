<script setup lang="ts">
import Select from '@/components/ui/select/Select.vue';
import LabelWithTooltip from '@/components/ui/label/LabelWithTooltip.vue';
import Input from '@/components/ui/input/Input.vue';
import { imovelTooltips } from '@/constants/imovelTooltips';

const props = defineProps<{ formRef: any, errors?: any, fetchCep?: Function }>();
</script>

<template>
  <div class="mt-4 border p-4 rounded">
    <h2 class="font-semibold">Localização</h2>
    <div class="mt-2 grid grid-cols-2 gap-3">
      <div>
        <LabelWithTooltip label="CEP" :tooltip="imovelTooltips.cep" />
        <Input name="cep" v-model:modelValue="formRef.cep" @blur="fetchCep && fetchCep()" class="border rounded px-2 py-1 w-full" :error="errors?.cep" />
      </div>
      <div>
        <label class="block">Estado</label>
        <Input name="estado" v-model:modelValue="formRef.estado" class="border rounded px-2 py-1 w-full" :error="errors?.estado" />
      </div>
      <div>
        <label class="block">Cidade</label>
        <Input name="cidade" v-model:modelValue="formRef.cidade" class="border rounded px-2 py-1 w-full" :error="errors?.cidade" />
      </div>
      <div>
        <label class="block">Bairro</label>
        <Input name="bairro" v-model:modelValue="formRef.bairro" class="border rounded px-2 py-1 w-full" :error="errors?.bairro" />
      </div>
      <div class="col-span-2 mt-2">
        <label class="block">Endereço (rua)</label>
        <Input name="endereco" v-model:modelValue="formRef.endereco" class="border rounded px-2 py-1 w-full" :error="errors?.endereco" />
      </div>
      <div>
        <label class="block">Número</label>
        <Input name="numero" v-model:modelValue="formRef.numero" class="border rounded px-2 py-1 w-full" :error="errors?.numero" />
      </div>
      <div>
        <label class="block">Complemento</label>
        <Input name="complemento" v-model:modelValue="formRef.complemento" class="border rounded px-2 py-1 w-full" />
        <!-- Complemento geralmente não é obrigatório -->
      </div>
      <div class="col-span-2 mt-2">
        <LabelWithTooltip label="Ponto de referência" :tooltip="imovelTooltips.referencia" />
        <Input name="referencia" v-model:modelValue="formRef.referencia" class="border rounded px-2 py-1 w-full" />
        <!-- Referência geralmente não é obrigatória -->
      </div>
      <div>
        <LabelWithTooltip label="Mostrar endereço completo?" :tooltip="imovelTooltips.mostrar_endereco_completo" />
        <Select name="mostrar_endereco_completo" v-model="formRef.mostrar_endereco_completo" class="border rounded px-2 py-1 w-full" :error="errors?.mostrar_endereco_completo">
          <option :value="1">Sim</option>
          <option :value="0">Não</option>
        </Select>
      </div>

      <div v-if="formRef.categoria === 'apartamento' || formRef.categoria === 'comercial'" class="col-span-2 mt-2">
        <LabelWithTooltip label="Torre / Bloco (opcional)" :tooltip="imovelTooltips.torre" />
        <Input name="torre" v-model:modelValue="formRef.torre" class="border rounded px-2 py-1 w-full" />
        <!-- Torre é opcional -->
      </div>
      <div v-if="formRef.categoria === 'apartamento' || formRef.categoria === 'comercial'">
        <LabelWithTooltip label="Andar (opcional)" :tooltip="imovelTooltips.andar" />
        <Input name="andar" v-model:modelValue="formRef.andar" class="border rounded px-2 py-1 w-full" />
        <!-- Andar é opcional -->
      </div>
    </div>
  </div>
</template>
