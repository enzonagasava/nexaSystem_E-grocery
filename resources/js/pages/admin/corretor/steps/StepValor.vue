<script setup lang="ts">
import Select from '@/components/ui/select/Select.vue';
import LabelWithTooltip from '@/components/ui/label/LabelWithTooltip.vue';
import Input from '@/components/ui/input/Input.vue';
import { imovelTooltips } from '@/constants/imovelTooltips';

const props = defineProps<{ formRef: any, errors?: any }>();
</script>

<template>
  <div class="mt-4 border p-4 rounded">
    <h2 class="font-semibold">Valores</h2>
    <div class="mt-2 grid grid-cols-3 gap-3">
      <div>
        <LabelWithTooltip label="Valor de venda" :tooltip="imovelTooltips.valor_venda" />
        <Input name="valor_venda" v-model:modelValue="formRef.valor_venda" type="number" step="0.01" class="border rounded px-2 py-1 w-full" :error="errors?.valor_venda" />
      </div>

      <div v-if="formRef.categoria === 'apartamento'">
        <LabelWithTooltip label="Valor do condomínio" :tooltip="imovelTooltips.valor_condominio" />
        <Input name="valor_condominio" v-model:modelValue="formRef.valor_condominio" type="number" step="0.01" class="border rounded px-2 py-1 w-full" :error="errors?.valor_condominio" />
      </div>

      <div>
        <LabelWithTooltip label="Valor do IPTU" :tooltip="imovelTooltips.valor_iptu" />
        <Input name="valor_iptu" v-model:modelValue="formRef.valor_iptu" type="number" step="0.01" class="border rounded px-2 py-1 w-full" :error="errors?.valor_iptu" />
      </div>

      <div v-if="formRef.categoria === 'apartamento'">
        <LabelWithTooltip label="Gás (opcional)" :tooltip="imovelTooltips.gas" />
        <Input name="gas" v-model:modelValue="formRef.gas" class="border rounded px-2 py-1 w-full" />
        <!-- Gás é opcional -->
      </div>

      <div v-if="formRef.categoria === 'apartamento'">
        <LabelWithTooltip label="Luz (opcional)" :tooltip="imovelTooltips.luz" />
        <Input name="luz" v-model:modelValue="formRef.luz" class="border rounded px-2 py-1 w-full" />
        <!-- Luz é opcional -->
      </div>

      <div>
        <LabelWithTooltip label="Aceita financiamento?" :tooltip="imovelTooltips.aceita_financiamento" />
        <Select name="aceita_financiamento" v-model="formRef.aceita_financiamento" class="border rounded px-2 py-1 w-full" :error="errors?.aceita_financiamento">
          <option :value="0">Não</option>
          <option :value="1">Sim</option>
        </Select>
        
      </div>

      <div>
        <LabelWithTooltip label="Aceita permuta?" :tooltip="imovelTooltips.aceita_permuta" />
        <Select name="aceita_permuta" v-model="formRef.aceita_permuta" class="border rounded px-2 py-1 w-full" :error="errors?.aceita_permuta">
          <option :value="0">Não</option>
          <option :value="1">Sim</option>
        </Select>
        
      </div>

      <div>
        <LabelWithTooltip label="Comissão (%)" :tooltip="imovelTooltips.comissao_percent" />
        <Input name="comissao_percent" v-model:modelValue="formRef.comissao_percent" type="number" step="0.01" class="border rounded px-2 py-1 w-full" :error="errors?.comissao_percent" />
      </div>
    </div>
  </div>
</template>
