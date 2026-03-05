<script setup lang="ts">
import Select from '@/components/ui/select/Select.vue';
import LabelWithTooltip from '@/components/ui/label/LabelWithTooltip.vue';
import Input from '@/components/ui/input/Input.vue';
import { imovelTooltips } from '@/constants/imovelTooltips';

const props = defineProps<{ formRef: any, errors?: any }>();
</script>

<template>
  <div class="mt-4 border p-4 rounded">
    <h2 class="font-semibold">Detalhes, diferenciais</h2>
    <div class="mt-2">
      <LabelWithTooltip label="Descrição" :tooltip="imovelTooltips.descricao" />
      <textarea name="descricao" v-model="formRef.descricao" class="border rounded px-2 py-1 w-full"></textarea>
      <p v-if="errors?.descricao" class="text-red-600 text-xs mt-1">{{ errors.descricao }}</p>
    </div>

    <div class="mt-2 grid grid-cols-2 gap-3">
      <div>
        <LabelWithTooltip label="Mobília" :tooltip="imovelTooltips.mobilia" />
        <Select name="mobilia" v-model="formRef.mobilia" class="border rounded px-2 py-1 w-full" :error="errors?.mobilia">
          <option value="">-</option>
          <option value="mobiliado">Mobiliado</option>
          <option value="semi">Semi</option>
          <option value="nao">Não</option>
        </Select>
      </div>
      <div>
        <LabelWithTooltip label="Itens" :tooltip="imovelTooltips.itens" />
        <Input name="itens" v-model:modelValue="formRef.itens" class="border rounded px-2 py-1 w-full" :error="errors?.itens" />
      </div>
    </div>

    <div class="mt-2 grid grid-cols-2 gap-3">
      <div>
        <LabelWithTooltip label="Tem varanda?" :tooltip="imovelTooltips.varanda" />
        <Select name="varanda" v-model="formRef.varanda" class="border rounded px-2 py-1 w-full" :error="errors?.varanda">
          <option :value="0">Não</option>
          <option :value="1">Sim</option>
        </Select>
      </div>
      <div>
        <LabelWithTooltip label="Áreas de lazer" :tooltip="imovelTooltips.areas_lazer" />
        <input name="areas_lazer" v-model="formRef.areas_lazer" class="border rounded px-2 py-1 w-full" />
        <p v-if="errors?.areas_lazer" class="text-red-600 text-xs mt-1">{{ errors.areas_lazer }}</p>
      </div>
    </div>
  </div>
</template>
