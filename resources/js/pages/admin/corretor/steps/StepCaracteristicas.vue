<script setup lang="ts">
import LabelWithTooltip from '@/components/ui/label/LabelWithTooltip.vue';
import Input from '@/components/ui/input/Input.vue';
import { imovelTooltips } from '@/constants/imovelTooltips';

const props = defineProps<{ formRef: any, errors?: any }>();
</script>

<template>
  <div class="mt-4 border p-4 rounded">
    <h2 class="font-semibold">Características físicas</h2>
    <div class="mt-2 grid grid-cols-4 gap-3">
      <template v-if="formRef.categoria === 'casa' || formRef.categoria === 'apartamento'">
        <div>
          <LabelWithTooltip label="Quartos" :tooltip="imovelTooltips.dormitorios" />
          <Input name="quartos" v-model:modelValue="formRef.dormitorios" type="number" class="border rounded px-2 py-1 w-full" :error="errors?.dormitorios" />
        </div>
        <div>
          <LabelWithTooltip label="Suítes" :tooltip="imovelTooltips.suites" />
          <Input name="suites" v-model:modelValue="formRef.suites" type="number" class="border rounded px-2 py-1 w-full" :error="errors?.suites" />
        </div>
        <div>
          <LabelWithTooltip label="Banheiros" :tooltip="imovelTooltips.banheiros" />
          <Input name="banheiros" v-model:modelValue="formRef.banheiros" type="number" class="border rounded px-2 py-1 w-full" :error="errors?.banheiros" />
        </div>
        <div>
          <LabelWithTooltip label="Vagas" :tooltip="imovelTooltips.vagas" />
          <Input name="vagas" v-model:modelValue="formRef.vagas" type="number" class="border rounded px-2 py-1 w-full" :error="errors?.vagas" />
        </div>
        <div>
          <LabelWithTooltip label="Área construída (m²)" :tooltip="imovelTooltips.area_construida" />
          <Input name="area_construida" v-model:modelValue="formRef.area_construida" type="number" step="0.01" class="border rounded px-2 py-1 w-full" :error="errors?.area_construida" />
        </div>
      </template>

      <template v-if="formRef.categoria === 'comercial'">
        <div>
          <LabelWithTooltip label="Salas" :tooltip="imovelTooltips.salas" />
          <Input name="salas" v-model:modelValue="formRef.salas" type="number" class="border rounded px-2 py-1 w-full" :error="errors?.salas" />
        </div>
        <div>
          <LabelWithTooltip label="Banheiros" :tooltip="imovelTooltips.banheiros" />
          <Input name="banheiros" v-model:modelValue="formRef.banheiros" type="number" class="border rounded px-2 py-1 w-full" :error="errors?.banheiros" />
        </div>
        <div>
          <LabelWithTooltip label="Vagas" :tooltip="imovelTooltips.vagas" />
          <Input name="vagas" v-model:modelValue="formRef.vagas" type="number" class="border rounded px-2 py-1 w-full" :error="errors?.vagas" />
        </div>
        <div>
          <LabelWithTooltip label="Área construída (m²)" :tooltip="imovelTooltips.area_construida" />
          <Input name="area_construida" v-model:modelValue="formRef.area_construida" type="number" step="0.01" class="border rounded px-2 py-1 w-full" :error="errors?.area_construida" />
        </div>
      </template>

      <template v-if="formRef.categoria === 'terreno'">
        <div class="col-span-2">
          <LabelWithTooltip label="Área do terreno - largura (m)" :tooltip="imovelTooltips.area_terreno_largura" />
          <Input name="area_terreno_largura" v-model:modelValue="formRef.area_terreno_largura" type="number" step="0.01" class="border rounded px-2 py-1 w-full" :error="errors?.area_terreno_largura" />
        </div>
        <div class="col-span-2">
          <LabelWithTooltip label="Área do terreno - comprimento (m)" :tooltip="imovelTooltips.area_terreno_comprimento" />
          <Input name="area_terreno_comprimento" v-model:modelValue="formRef.area_terreno_comprimento" type="number" step="0.01" class="border rounded px-2 py-1 w-full" :error="errors?.area_terreno_comprimento" />
        </div>
      </template>

      <div>
        <LabelWithTooltip label="Área útil (m²)" :tooltip="imovelTooltips.area_util" />
        <Input name="area_util" v-model:modelValue="formRef.area_util" type="number" step="0.01" class="border rounded px-2 py-1 w-full" :error="errors?.area_util" />
      </div>

      <div>
        <LabelWithTooltip label="Área total (m²)" :tooltip="imovelTooltips.area_total" />
        <Input name="area_total" v-model:modelValue="formRef.area_total" type="number" step="0.01" class="border rounded px-2 py-1 w-full" :error="errors?.area_total" />
      </div>

      <div>
        <LabelWithTooltip label="Ano de construção" :tooltip="imovelTooltips.ano_construcao" />
        <Input name="ano_construcao" v-model:modelValue="formRef.ano_construcao" type="number" class="border rounded px-2 py-1 w-full" :error="errors?.ano_construcao" />
      </div>
    </div>
  </div>
</template>
