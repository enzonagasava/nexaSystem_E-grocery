<script setup lang="ts">
import LabelWithTooltip from '@/components/ui/label/LabelWithTooltip.vue';
import Input from '@/components/ui/input/Input.vue';
import { imovelTooltips } from '@/constants/imovelTooltips';

const props = defineProps<{ formRef: any, errors?: any, onAutorizacaoChange?: Function, autorizacaoName?: string, produto?: any }>();
const emits = defineEmits<{
  (e: 'clear-field', field: string): void
}>()
</script>

<template>
  <div class="mt-4 border p-4 rounded">
    <h2 class="font-semibold">Proprietário</h2>
    <div class="mt-2 grid grid-cols-3 gap-3">
      <div>
        <LabelWithTooltip label="Nome" :tooltip="imovelTooltips.proprietario_nome" />
        <Input name="proprietario_nome" v-model:modelValue="formRef.proprietario_nome" class="border rounded px-2 py-1 w-full" :error="errors?.proprietario_nome" @clear-error="() => emits('clear-field','proprietario_nome')" />
      </div>
      <div>
        <LabelWithTooltip label="Telefone" :tooltip="imovelTooltips.proprietario_telefone" />
        <Input name="proprietario_telefone" v-model:modelValue="formRef.proprietario_telefone" class="border rounded px-2 py-1 w-full" :error="errors?.proprietario_telefone" placeholder="(11) 99999-9999" @clear-error="() => emits('clear-field','proprietario_telefone')" />
      </div>
      <div>
        <LabelWithTooltip label="E-mail" :tooltip="imovelTooltips.proprietario_email" />
        <Input name="proprietario_email" v-model:modelValue="formRef.proprietario_email" type="email" class="border rounded px-2 py-1 w-full" :error="errors?.proprietario_email" @clear-error="() => emits('clear-field','proprietario_email')" />
      </div>
      <div class="col-span-2">
        <LabelWithTooltip label="Documento (CPF)" :tooltip="imovelTooltips.proprietario_documento" />
        <Input name="proprietario_cpf" v-model:modelValue="formRef.proprietario_documento" class="border rounded px-2 py-1 w-full" :error="errors?.proprietario_documento" placeholder="000.000.000-00" @clear-error="() => emits('clear-field','proprietario_documento')" />
      </div>
      <div>
        <LabelWithTooltip label="Autorização de venda (img/pdf)" :tooltip="imovelTooltips.autorizacao" />
        <input type="file" name="autorizacao" accept="image/*,application/pdf" @change="(e) => { onAutorizacaoChange && onAutorizacaoChange(e); emits('clear-field','autorizacao') }" class="mt-2" />
        <p v-if="autorizacaoName" class="text-sm mt-1">{{ autorizacaoName }}</p>
        <div v-if="produto && produto.autorizacoes && produto.autorizacoes.length" class="mt-2 text-sm">
          <a :href="route('admin.corretor.imoveis.autorizacao.download', { id: produto.id, auth: produto.autorizacoes[0].id })" target="_blank" class="text-blue-600 hover:underline">Baixar autorização</a>
        </div>
        <p v-if="errors?.autorizacao" class="text-red-600 text-xs mt-1">{{ errors.autorizacao }}</p>
      </div>
    </div>
  </div>
</template>
