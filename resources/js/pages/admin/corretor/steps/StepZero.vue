<script setup lang="ts">

import { toRefs } from 'vue';
import Select from '@/components/ui/select/Select.vue';
import LabelWithTooltip from '@/components/ui/label/LabelWithTooltip.vue';
import { imovelTooltips } from '@/constants/imovelTooltips';

const props = defineProps<{ formRef: any, errors?: any }>();
const { formRef } = toRefs(props);

</script>

<template>
  <div class="mt-4 border p-4 rounded">
    <h2 class="font-semibold">Imóvel</h2>
    <div class="mt-2">
      <LabelWithTooltip label="Modalidade" :tooltip="imovelTooltips.modalidade" />
      <Select name="modalidade" v-model="formRef.modalidade" class="background-primary border rounded px-2 py-1 w-full" :error="errors?.modalidade">
        <option value="venda">Venda</option>
        <option value="locacao">Locação</option>
      </Select>
    </div>
  </div>
</template>
